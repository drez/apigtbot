<?php

namespace Tests\Custom\Bot;

use App\Config;
use App\ConfigPeer;
use App\ConfigQuery;
use App\Domains\Bot\MarketOutlook;
use App\Domains\Bot\MarketOutlookStore;
use App\MarketOutlookQuery;
use Tests\Builder\Support\DbTestCase;

/**
 * The outlook's persistence: one Change row and one message per CONFIRMED
 * change, a digest once per UTC day, and 7d/30d scoring off the stored daily
 * bars. Inputs are injected (never the BTCUSDT market_summary fixture, never
 * the collector) and every symbol is unique to the test.
 */
class MarketOutlookStoreTest extends DbTestCase
{
    private const T0 = 1_790_000_000; // 2026-09-21 UTC-ish; only differences matter

    private function inputs(string $dir, float $price = 100.0): array
    {
        // 300 dailies / 300 weeklies trending the asked way, so the slope
        // features agree with the hand-set summaries
        $k = ['up' => 1.004, 'down' => 0.996, 'flat' => 1.0][$dir];
        $c = $w = [];
        for ($i = 0; $i < 300; $i++) {
            $x = $price * ($k ** ($i - 299));
            $c[] = ['high' => (string) ($x * 1.01), 'low' => (string) ($x * 0.99), 'close' => (string) $x];
            $y = $price * ($k ** (7 * ($i - 299))); // a week of the same drift per bar: the cold-start replay rebuilds weeks from days
            $w[] = ['high' => (string) ($y * 1.03), 'low' => (string) ($y * 0.97), 'close' => (string) $y];
        }
        $ema = ['up' => [95.0, 90.0, 80.0], 'down' => [105.0, 110.0, 120.0], 'flat' => [95.0, 110.0, 120.0]][$dir];
        $s = ['price' => $price, 'ema20' => $ema[0] * $price / 100, 'ema50' => $ema[1] * $price / 100, 'ema200' => $ema[2] * $price / 100,
            'adx14' => 30.0, 'er20' => 0.5, 'stale' => false, 'candles_used' => 300, 'funding_pct' => null];
        return ['s1d' => $s, 's1w' => $s, 'c1d' => $c, 'c1w' => $w];
    }

    private function level(string $level): void
    {
        ConfigPeer::clearInstancePool();
        $c = ConfigQuery::create()->findOneByConfig(MarketOutlookStore::CONFIG_ALERTS) ?? (new Config())->setConfig(MarketOutlookStore::CONFIG_ALERTS);
        $c->setValue($level);
        $c->save();
    }

    /** @return array{0: array, 1: string[]} last pass + every message on the way */
    private function passes(string $symbol, string $dir, int $n, int &$now): array
    {
        $last = [];
        $msgs = [];
        for ($i = 0; $i < $n; $i++) {
            $now += 3600;
            $last = MarketOutlookStore::pass($symbol, $now, $this->inputs($dir));
            if ($last['message'] !== null) {
                $msgs[] = $last['message'];
            }
        }
        return [$last, $msgs];
    }

    /** one pass on a flat tape: the cold-start replay seeds NEUTRAL, so what follows is a real change */
    private function seedNeutral(string $symbol, int &$now): void
    {
        $this->passes($symbol, 'flat', 1, $now);
        $this->assertSame(MarketOutlook::NEUTRAL, MarketOutlookStore::state($symbol)['verdict']);
    }

    private function changes(string $symbol): int
    {
        return MarketOutlookQuery::create()->filterBySymbol($symbol)->filterByKind('Change')->count();
    }

    public function testOneChangeRowAndOneMessagePerConfirmedChange(): void
    {
        $this->level('major');
        $symbol = strtoupper(self::uniq('ol'));
        $now = self::T0;
        $this->seedNeutral($symbol, $now);

        [$last, $msgs] = $this->passes($symbol, 'up', MarketOutlook::CONFIRM_PASSES_MAJOR - 1, $now);
        $this->assertSame(MarketOutlook::NEUTRAL, $last['verdict'], 'not confirmed yet');
        $this->assertSame('MAJOR_UP', $last['raw']);
        $this->assertSame(0, $this->changes($symbol));
        $this->assertSame([], $msgs);

        [$last, $msgs] = $this->passes($symbol, 'up', 1, $now);
        $this->assertTrue($last['changed']);
        $this->assertSame('MAJOR_UP', MarketOutlookStore::state($symbol)['verdict']);
        $this->assertSame(1, $this->changes($symbol));
        $this->assertCount(1, $msgs);
        $this->assertStringContainsString('NEUTRAL → MAJOR_UP', $msgs[0]);
        $this->assertStringContainsString('Detection, not a forecast', $msgs[0]);
        $this->assertStringContainsString('Cycle:', $msgs[0]);

        // the state persists: a day of the same read says nothing more
        [, $msgs] = $this->passes($symbol, 'up', 30, $now);
        $this->assertSame(1, $this->changes($symbol));
        $this->assertSame([], $msgs);
    }

    public function testNoReadLeavesCountersAlone(): void
    {
        $symbol = strtoupper(self::uniq('ol'));
        $now = self::T0;
        $this->seedNeutral($symbol, $now);
        $this->passes($symbol, 'up', 5, $now);
        $stale = $this->inputs('up');
        $stale['s1w']['stale'] = true;
        $r = MarketOutlookStore::pass($symbol, $now + 3600, $stale);
        $this->assertNull($r['raw']);
        $this->assertSame(5, MarketOutlookStore::state($symbol)['candidate_passes']);
    }

    public function testAlertLevelGatesTheMessageNeverTheLog(): void
    {
        $now = self::T0;
        $this->level('off');
        $off = strtoupper(self::uniq('ol'));
        $this->seedNeutral($off, $now);
        [, $msgs] = $this->passes($off, 'down', MarketOutlook::CONFIRM_PASSES_MAJOR, $now);
        $this->assertSame([], $msgs);
        $this->assertSame(1, $this->changes($off), 'off still logs the call');

        $this->assertFalse(MarketOutlookStore::shouldNotify('major', 'NEUTRAL', 'UP_FORMING'));
        $this->assertTrue(MarketOutlookStore::shouldNotify('all', 'NEUTRAL', 'UP_FORMING'));
        $this->assertTrue(MarketOutlookStore::shouldNotify('major', 'UP_FORMING', 'MAJOR_UP'));
        $this->assertTrue(MarketOutlookStore::shouldNotify('major', 'MAJOR_DOWN', 'NEUTRAL'), 'leaving a MAJOR is news too');
        $this->assertFalse(MarketOutlookStore::shouldNotify('off', null, 'MAJOR_DOWN'));
        $this->level('nonsense');
        $this->assertSame('off', MarketOutlookStore::alertLevel());
    }

    public function testColdStartSeedsFromHistoryQuietlyAndUnscored(): void
    {
        $this->level('all');
        $symbol = strtoupper(self::uniq('ol'));
        $now = self::T0;
        [$last, $msgs] = $this->passes($symbol, 'up', 1, $now);

        $this->assertSame('MAJOR_UP', $last['verdict'], 'no six-day blank');
        $this->assertFalse($last['changed']);
        $this->assertSame([], $msgs, 'a seed is not news');
        $st = MarketOutlookStore::state($symbol);
        $this->assertSame('MAJOR_UP', $st['verdict']);
        $this->assertLessThan($now - 30 * 86400, $st['since_ts'], 'since = the replayed confirmation, not now');
        $this->assertSame(0, $this->changes($symbol));
        $seed = MarketOutlookQuery::create()->filterBySymbol($symbol)->filterByKind('Seed')->select(['Verdict', 'PrevVerdict', 'EvalStatus'])->find()->getArrayCopy();
        $this->assertSame([['Verdict' => 'MAJOR_UP', 'PrevVerdict' => null, 'EvalStatus' => 'Seed']], $seed);

        // seeded once: later passes hold it, and the seed row is never scored
        $this->passes($symbol, 'up', 3, $now);
        $this->assertSame(1, MarketOutlookQuery::create()->filterBySymbol($symbol)->filterByKind('Seed')->count());
        $bars = fn () => array_fill(0, 300, ['high' => '101', 'low' => '99', 'close' => '100']);
        MarketOutlookStore::scorePending($now + 40 * 86400, $bars);
        $this->assertSame('Seed', MarketOutlookQuery::create()->filterBySymbol($symbol)->filterByKind('Seed')->select(['EvalStatus'])->findOne());
    }

    public function testNoSeedFromAStaleTape(): void
    {
        $symbol = strtoupper(self::uniq('ol'));
        $stale = $this->inputs('up');
        $stale['s1d']['stale'] = true;
        MarketOutlookStore::pass($symbol, self::T0, $stale);
        $this->assertNull(MarketOutlookStore::state($symbol));
        $this->assertSame(0, MarketOutlookQuery::create()->filterBySymbol($symbol)->count());
    }

    public function testDryPassShowsTheSeedButWritesNothing(): void
    {
        $symbol = strtoupper(self::uniq('ol'));
        $r = MarketOutlookStore::pass($symbol, self::T0, $this->inputs('up'), true);
        $this->assertSame('MAJOR_UP', $r['verdict']);
        $this->assertNull(MarketOutlookStore::state($symbol));
    }

    public function testDryPassWritesNothing(): void
    {
        $symbol = strtoupper(self::uniq('ol'));
        $r = MarketOutlookStore::pass($symbol, self::T0, $this->inputs('up'), true);
        $this->assertSame('MAJOR_UP', $r['raw']);
        $this->assertNull(MarketOutlookStore::state($symbol));
    }

    public function testDigestOncePerUtcDayWithADailyRowPerSymbol(): void
    {
        $this->level('major');
        $symbol = strtoupper(self::uniq('ol'));
        $now = self::T0;
        $this->seedNeutral($symbol, $now);
        $this->passes($symbol, 'up', MarketOutlook::CONFIRM_PASSES_MAJOR, $now);
        $in = [$symbol => $this->inputs('up', 110.0)];

        $msg = MarketOutlookStore::digest([$symbol], $now, $in);
        $this->assertNotNull($msg);
        $this->assertStringContainsString('MAJOR_UP', $msg);
        $this->assertStringContainsString('+10.0% since', $msg);
        $this->assertStringContainsString('detection, not a forecast', $msg);
        $daily = fn () => MarketOutlookQuery::create()->filterBySymbol($symbol)->filterByKind('Daily')->count();
        $this->assertSame(1, $daily());

        $this->assertNull(MarketOutlookStore::digest([$symbol], $now + 60, $in), 'same UTC day → silent');
        $this->assertSame(1, $daily());

        $this->assertNotNull(MarketOutlookStore::digest([$symbol], $now + 86400, $in));
        $this->assertSame(2, $daily());
    }

    public function testScoringWalksPendingPartialScored(): void
    {
        $this->level('off');
        $symbol = strtoupper(self::uniq('ol'));
        $now = self::T0;
        $this->seedNeutral($symbol, $now);
        $this->passes($symbol, 'up', MarketOutlook::CONFIRM_PASSES_MAJOR, $now); // called @ 100
        $callDay = intdiv($now, 86400);

        // daily bars ending "today": +1%/day after the call, one −8% low on day +3
        $bars = function (int $today) use ($callDay): array {
            $out = [];
            for ($d = $today - 299; $d <= $today; $d++) {
                $x = 100.0 * (1.01 ** max(0, $d - $callDay));
                $out[] = ['high' => (string) ($x * 1.01), 'low' => (string) ($d === $callDay + 3 ? 92.0 : $x * 0.995), 'close' => (string) $x];
            }
            return $out;
        };
        $row = fn () => MarketOutlookQuery::create()->filterBySymbol($symbol)->filterByKind('Change')->select(['EvalStatus', 'Ret7d', 'Hit7d', 'Ret30d', 'Hit30d', 'MaxAdversePct'])->findOne();

        // day +7 still in progress → nothing
        $t = ($callDay + 7) * 86400 + 3600;
        $this->assertSame(0, MarketOutlookStore::scorePending($t, fn () => $bars(intdiv($t, 86400))));
        $this->assertSame('Pending', $row()['EvalStatus']);

        // a stale 1d series never scores
        $t = ($callDay + 8) * 86400 + 3600;
        $this->assertSame(0, MarketOutlookStore::scorePending($t, fn () => null));

        $this->assertSame(1, MarketOutlookStore::scorePending($t, fn () => $bars(intdiv($t, 86400))));
        $r = $row();
        $this->assertSame('Partial', $r['EvalStatus']);
        $this->assertEqualsWithDelta((1.01 ** 7 - 1) * 100, (float) $r['Ret7d'], 0.01);
        $this->assertSame(1, (int) $r['Hit7d']);

        $t = ($callDay + 31) * 86400 + 3600;
        $this->assertSame(1, MarketOutlookStore::scorePending($t, fn () => $bars(intdiv($t, 86400))));
        $r = $row();
        $this->assertSame('Scored', $r['EvalStatus']);
        $this->assertEqualsWithDelta((1.01 ** 30 - 1) * 100, (float) $r['Ret30d'], 0.01);
        $this->assertSame(1, (int) $r['Hit30d']);
        $this->assertEqualsWithDelta(-8.0, (float) $r['MaxAdversePct'], 0.01);

        $this->assertSame(['hits' => 1, 'n' => 1], MarketOutlookStore::record()['MAJOR_UP'] ?? null);
        $this->assertSame(0, MarketOutlookStore::scorePending($t + 86400, fn () => $bars(intdiv($t, 86400) + 1)), 'scored rows are done');
    }

    public function testBriefBlock(): void
    {
        $symbol = strtoupper(self::uniq('ol'));
        $now = self::T0;
        $this->seedNeutral($symbol, $now);
        $this->passes($symbol, 'down', MarketOutlook::CONFIRM_PASSES_MAJOR + 3, $now);
        $b = MarketOutlookStore::brief([$symbol, 'NOSUCH' . self::uniq('x')], $now + 7200);
        $this->assertStringContainsString('DETECTION, not forecast', $b['note']);
        $this->assertSame([$symbol], array_keys($b['symbols']));
        $s = $b['symbols'][$symbol];
        $this->assertSame('MAJOR_DOWN', $s['verdict']);
        $this->assertSame(5, $s['age_h']);
        $this->assertNull($s['candidate']);
        $this->assertSame(['hits' => 0, 'n' => 0], $s['record_30d']);
    }
}

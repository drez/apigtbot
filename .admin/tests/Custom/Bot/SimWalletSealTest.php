<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Config;
use App\ConfigQuery;
use App\Domains\Bot\SimWallet;
use App\GridRun;
use Tests\Builder\Support\DbTestCase;

/**
 * The paper wallet is DERIVED: deriveAndStore() wipes sim_wallet and replays
 * every simulated fill, resolving each order's symbol through its run. So
 * deleting a run used to hand the shared wallet back everything that run had
 * lost — silently, at the next daemon boot, and with gtbot_use_all_funds ON it
 * would change how much capital the fleet commits.
 *
 * sealRun() folds a departing run's net contribution into an opening baseline
 * before its rows go. The headline case here is
 * testPurgingARunDoesNotMoveTheWallet: snapshot, seal, delete, re-derive, and
 * every balance must come back bit-identical.
 */
class SimWalletSealTest extends DbTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->setConfig('gtbot_shared_budget_quote', '1000');
        $this->setConfig('gtbot_sim_wallet_epoch', '');
        $this->setConfig(SimWallet::CONFIG_BASELINE, '');
    }

    private function setConfig(string $key, string $value): void
    {
        $c = ConfigQuery::create()->findOneByConfig($key) ?? (new Config())->setConfig($key);
        $c->setValue($value);
        $c->save();
        // a Config row keeps its in-memory value after a write
        \App\ConfigPeer::clearInstancePool();
    }

    private function mkRun(string $symbol): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('seal'));
        $r->setSymbol($symbol);
        $r->setStatus('Testnet');
        $r->setAlgo('Grid');
        $r->setSimulated(true);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(5);
        $r->setSpacing('Geometric');
        $r->setAllocation('EqualQuote');
        $r->setBudgetQuote('300');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('300');
        $r->setMaxOrderQuote('100');
        $r->setDailyLossLimitQuote('30');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(30);
        $r->setRunUid(static::uniq('seal'));
        $r->save();
        return $r;
    }

    private function fill(GridRun $run, string $side, string $price, string $qty, string $fee): void
    {
        $row = new BotOrder();
        $row->setIdGridRun((int) $run->getIdGridRun());
        $row->setClientOrderId(static::uniq('seal-o'));
        $row->setLevelIdx(1);
        $row->setSide($side);
        $row->setState('Filled');
        $row->setPrice($price);
        $row->setQty($qty);
        $row->setFilledQty($qty);
        $row->setFeePaid($fee);
        $row->setSimulated(true);
        $row->save();
    }

    private function assertBalancesSame(array $before, array $after, string $why): void
    {
        // insertion order is an artefact of seed-then-baseline-then-replay; the
        // balances are what must not move
        $beforeKeys = array_keys($before);
        $afterKeys = array_keys($after);
        sort($beforeKeys);
        sort($afterKeys);
        $this->assertSame($beforeKeys, $afterKeys, "$why: the set of assets changed");
        foreach ($before as $asset => $qty) {
            $this->assertSame(
                0,
                bccomp($qty, $after[$asset], 12),
                "$why: $asset moved $qty -> {$after[$asset]}"
            );
        }
    }

    // ── the headline regression ─────────────────────────────────────────

    public function testPurgingARunDoesNotMoveTheWallet(): void
    {
        $keep = $this->mkRun('BTCUSDT');
        $going = $this->mkRun('BNBUSDT');

        $this->fill($keep, 'Buy', '100', '0.5', '0.05');
        $this->fill($keep, 'Sell', '120', '0.2', '0.024');
        // a run that ended DOWN: bought high, sold low. The bug handed this
        // loss back to the wallet when the run was deleted.
        $this->fill($going, 'Buy', '600', '0.4', '0.24');
        $this->fill($going, 'Sell', '500', '0.4', '0.2');

        $before = SimWallet::deriveAndStore();

        $result = SimWallet::sealRun($going);
        $this->assertTrue($result['sealed']);
        $going->delete();

        // next daemon boot
        $after = SimWallet::deriveAndStore();

        $this->assertBalancesSame($before, $after, 'purging a run');
    }

    public function testSealIsIdempotent(): void
    {
        $run = $this->mkRun('BTCUSDT');
        $this->fill($run, 'Buy', '100', '1', '0.1');

        $first = SimWallet::sealRun($run);
        $this->assertTrue($first['sealed']);
        $afterFirst = SimWallet::baseline();

        $second = SimWallet::sealRun($run);
        $this->assertFalse($second['sealed'], 'a second seal must be a no-op');
        $this->assertSame('already sealed', $second['reason']);

        $this->assertSame(
            $afterFirst['adjust'],
            SimWallet::baseline()['adjust'],
            'the delta was folded in twice'
        );
        $this->assertSame([(int) $run->getIdGridRun()], SimWallet::baseline()['sealed']);
    }

    public function testRunDeltaMatchesThatRunsSliceOfTheFullReplay(): void
    {
        $a = $this->mkRun('BTCUSDT');
        $b = $this->mkRun('BNBUSDT');
        $this->fill($a, 'Buy', '100', '0.5', '0.05');
        $this->fill($b, 'Buy', '600', '0.25', '0.15');
        $this->fill($b, 'Sell', '700', '0.1', '0.07');

        $withBoth = SimWallet::deriveAndStore();
        $deltaB = SimWallet::runDelta($b);

        // remove b's delta by hand; what's left must equal a wallet derived
        // without b at all. This is the pin that stops the seal arithmetic and
        // the replay arithmetic drifting apart.
        $b->delete();
        $withoutB = SimWallet::deriveAndStore();

        foreach ($deltaB as $asset => $qty) {
            $expected = bcsub($withBoth[$asset], $qty, 12);
            $this->assertSame(
                0,
                bccomp($expected, $withoutB[$asset] ?? '0', 12),
                "$asset: runDelta disagrees with the replay"
            );
        }
    }

    public function testSealedRunsDeltaIsNotCountedTwiceWhileItStillExists(): void
    {
        // sealing does NOT delete; until the rows go, the run is still replayed.
        // The baseline must therefore be applied on top, not instead — this
        // asserts the intermediate state is the expected double-count, so a
        // future change that reorders seal-then-delete is caught.
        $run = $this->mkRun('BTCUSDT');
        $this->fill($run, 'Buy', '100', '1', '0.1');

        $before = SimWallet::deriveAndStore();
        SimWallet::sealRun($run);
        $sealedButPresent = SimWallet::deriveAndStore();

        $this->assertSame(
            -1,
            bccomp($sealedButPresent['USDT'], $before['USDT'], 12),
            'seal-without-delete should double-count: seal must be immediately followed by the delete'
        );

        $run->delete();
        $this->assertBalancesSame($before, SimWallet::deriveAndStore(), 'seal then delete');
    }

    // ── seeded tokens ───────────────────────────────────────────────────

    public function testASeededTokenSurvivesEveryDerivation(): void
    {
        // the whole point: a hand-inserted sim_wallet row is wiped by the next
        // DELETE+rebuild, so a token must come in through the seed layer
        $this->setConfig(SimWallet::CONFIG_SEED_ASSETS, json_encode(['BTC' => '0.01']));

        $first = SimWallet::deriveAndStore();
        $this->assertSame(0, bccomp('0.01', $first['BTC'], 12));

        $second = SimWallet::deriveAndStore();   // the next daemon boot
        $this->assertSame(0, bccomp('0.01', $second['BTC'], 12), 'the seed must not evaporate');
        $this->assertSame(0, bccomp($first['BTC'], $second['BTC'], 12), 'nor double-count');
    }

    public function testASeededTokenIsSpendableAsInventory(): void
    {
        // a run selling it draws the balance down from the seeded level
        $this->setConfig(SimWallet::CONFIG_SEED_ASSETS, json_encode(['BTC' => '1']));
        $run = $this->mkRun('BTCUSDT');
        $this->fill($run, 'Sell', '100', '0.25', '0.025');

        $w = SimWallet::deriveAndStore();
        $this->assertSame(0, bccomp('0.75', $w['BTC'], 12), 'seed minus what was sold');
        $this->assertSame(
            1,
            bccomp($w['USDT'], SimWallet::sharedBudget(), 12),
            'and the proceeds landed in USDT'
        );
    }

    public function testSeedAssetsIgnoresGarbage(): void
    {
        $this->setConfig(SimWallet::CONFIG_SEED_ASSETS, '{not json');
        $this->assertSame([], SimWallet::seedAssets());

        $this->setConfig(SimWallet::CONFIG_SEED_ASSETS, json_encode(['BTC' => 'lots', 'ETH' => '-1', 'BNB' => '0.5']));
        $this->assertSame(['BNB' => '0.5'], SimWallet::seedAssets(), 'non-numeric and non-positive dropped');
    }

    public function testSeedAndPurgeBaselineCompose(): void
    {
        $this->setConfig(SimWallet::CONFIG_SEED_ASSETS, json_encode(['BTC' => '0.5']));
        $going = $this->mkRun('BNBUSDT');
        $this->fill($going, 'Buy', '600', '0.4', '0.24');

        $before = SimWallet::deriveAndStore();
        SimWallet::sealRun($going);
        $going->delete();
        $after = SimWallet::deriveAndStore();

        $this->assertBalancesSame($before, $after, 'seed + baseline together');
        $this->assertSame(0, bccomp('0.5', $after['BTC'], 12), 'the seed is untouched by a purge');
    }

    // ── baseline robustness ─────────────────────────────────────────────

    public function testCorruptBaselineIsIgnoredNotFatal(): void
    {
        $this->setConfig(SimWallet::CONFIG_BASELINE, '{not json at all');
        $b = SimWallet::baseline();
        $this->assertSame([], $b['adjust']);
        $this->assertSame([], $b['sealed']);

        $wallet = SimWallet::deriveAndStore();
        $this->assertSame(0, bccomp(SimWallet::sharedBudget(), $wallet['USDT'], 8));
    }

    public function testBaselineDropsNonNumericEntries(): void
    {
        $this->setConfig(
            SimWallet::CONFIG_BASELINE,
            json_encode(['adjust' => ['BTC' => '0.5', 'JUNK' => 'nope'], 'sealed' => [7, 'x']])
        );
        $b = SimWallet::baseline();
        $this->assertSame(['BTC' => '0.5'], $b['adjust']);
        $this->assertSame([7], $b['sealed']);
    }

    public function testBaselineIsAppliedToTheDerivedWallet(): void
    {
        $this->setConfig(
            SimWallet::CONFIG_BASELINE,
            json_encode(['adjust' => ['USDT' => '-40', 'BTC' => '0.25'], 'sealed' => [12345]])
        );
        $wallet = SimWallet::deriveAndStore();
        $this->assertSame(0, bccomp(bcsub(SimWallet::sharedBudget(), '40', 12), $wallet['USDT'], 12));
        $this->assertSame(0, bccomp('0.25', $wallet['BTC'], 12));
    }
}

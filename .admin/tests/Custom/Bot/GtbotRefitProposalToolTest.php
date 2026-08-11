<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\GridRunQuery;
use App\Mcp\Tools\GtbotRefitProposalTool;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

class GtbotRefitProposalToolTest extends TestCase
{
    private static bool $booted = false;
    private ExchangeSim $sim;

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        require_once $admin . '/vendor/autoload.php';
        (new \Ahc\Env\Loader())->load($admin . '/.env');
        if (!defined('_AUTH_VAR')) {
            require $admin . '/config/Built/config.php';
        }
        if (!\Propel::isInit()) {
            require $admin . '/config/Built/propel.php';
        }
        if (session_status() === PHP_SESSION_NONE) {
            @session_start();
        }
        $_SESSION[_AUTH_VAR] = new AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
        $this->sim = new ExchangeSim();
        // 60 candles swinging 115↔185 over ~30-candle cycles with small
        // per-candle ranges — the tape crosses the buy ladder repeatedly while
        // the envelope stays ≫ ATR (the old every-bar-spans-the-envelope
        // fixture is exactly what RangeFitter's 4×ATR width floor now rejects)
        for ($i = 0; $i < 60; $i++) {
            $close = 150 + 35 * sin($i * 0.2);
            $this->sim->klines[] = [
                'low' => sprintf('%.2F', $close - 2),
                'high' => sprintf('%.2F', $close + 2),
                'close' => sprintf('%.2F', $close),
            ];
        }
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    private function tool(): GtbotRefitProposalTool
    {
        return new GtbotRefitProposalTool(
            new BinanceGateway('https://sim.local', '', '', $this->sim->transport())
        );
    }

    private function decode(array $result): array
    {
        return json_decode($result['content'][0]['text'], true);
    }

    public function testReturnsRankedBacktestedCandidates(): void
    {
        $out = $this->decode($this->tool()->handle(
            ['budget_quote' => '1000'],
            $this->createMock(AuthySession::class)
        ));
        $this->assertNotEmpty($out['candidates']);
        $this->assertArrayHasKey('honesty_note', $out);
        $viable = array_filter($out['candidates'], fn ($c) => $c['viable']);
        $this->assertNotEmpty($viable, 'oscillating market must yield viable candidates');
        $first = $out['candidates'][0];
        $this->assertGreaterThan(0, $first['backtest']['cycles'], 'chop must harvest cycles');
        // ranked by realized pnl descending
        $pnls = array_map(fn ($c) => $c['viable'] ? (float) $c['backtest']['realized_pnl'] : -INF, $out['candidates']);
        $sorted = $pnls;
        rsort($sorted);
        $this->assertSame($sorted, $pnls);
    }

    public function testCreateDraftIsDisabledAndWritesNothing(): void
    {
        $before = GridRunQuery::create()->count();
        $out = $this->decode($this->tool()->handle(
            ['budget_quote' => '1000', 'create_draft' => true, 'chosen' => 'wide', 'confirm' => true],
            $this->createMock(AuthySession::class)
        ));
        // read-only now: still returns candidates, but creates no run + says so
        $this->assertNotEmpty($out['candidates']);
        $this->assertArrayHasKey('create_draft_disabled', $out);
        $this->assertArrayNotHasKey('draft_created', $out);
        $this->assertSame($before, GridRunQuery::create()->count(), 'no run persisted');
    }
}

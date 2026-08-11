<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Daemon;
use App\Domains\Bot\EventLog;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\GridRun;
use PHPUnit\Framework\TestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/**
 * C1 (final-fix review): Daemon::codeMtime() globbed Bot/*.php + Gateway/*.php
 * + bin/gtbot* but NOT Bot/Engine/*.php — an Engine/-only deploy (e.g. a
 * TrendEngine fix) never bumped the observed max mtime, so codeChanged()
 * never tripped and the daemon never self-restarted to pick it up. Pins the
 * fix behaviorally: bump a real Engine/*.php file's mtime (restored after)
 * and assert Daemon::codeMtime() reflects it, rather than merely asserting
 * the source text contains the right glob() call.
 */
class DaemonCodeMtimeTest extends TestCase
{
    private static bool $booted = false;

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
        $_SESSION[_AUTH_VAR] = new \ApiGoat\Sessions\AuthySession();
        self::$booted = true;
    }

    protected function setUp(): void
    {
        \Propel::getConnection()->beginTransaction();
    }

    protected function tearDown(): void
    {
        \Propel::getConnection()->rollBack();
    }

    public function testCodeMtimeReflectsBotEngineDirectoryChanges(): void
    {
        $run = new GridRun();
        $run->setLabel('codemtime-' . bin2hex(random_bytes(4)));
        $run->setSymbol('BTCUSDT');
        $run->setStatus('Testnet');
        $run->setAlgo('Trend');
        $run->setProfile('Balanced');
        $run->setPLow('100');
        $run->setPHigh('200');
        $run->setNLevels(4);
        $run->setSpacing('Arithmetic');
        $run->setAllocation('EqualBase');
        $run->setBudgetQuote('550');
        $run->setFeePct('0.001');
        $run->setMaxPositionQuote('10000');
        $run->setMaxOrderQuote('1000');
        $run->setDailyLossLimitQuote('10000');
        $run->setBreakoutBufferPct('0.02');
        $run->setBreakoutPolicy('HaltAndHold');
        $run->setMaxOpenOrders(60);
        $run->setRunUid(substr(bin2hex(random_bytes(6)), 0, 10));
        $run->save();

        $sim = new ExchangeSim();
        $gw = new BinanceGateway('https://sim.local', 'k', 's', $sim->transport());
        // no boot(): codeMtime() is a pure filesystem scan that doesn't touch
        // instance state, so a constructed-but-unbooted Daemon is enough and
        // this avoids reconciling against the exchange for a Trend run
        $daemon = new Daemon($run, $gw, true, new EventLog((int) $run->getIdGridRun(), false));

        $engineFile = dirname(__DIR__, 3) . '/src/App/Domains/Bot/Engine/TrendEngine.php';
        $this->assertFileExists($engineFile);
        $origMtime = filemtime($engineFile);
        $this->assertNotFalse($origMtime);

        $m = new \ReflectionMethod(Daemon::class, 'codeMtime');
        $m->setAccessible(true);
        $before = (int) $m->invoke($daemon);

        try {
            // strictly past whatever codeMtime() already observed (not just
            // past this one file's own prior mtime), so the touch is
            // guaranteed to move the daemon's own computed max
            touch($engineFile, $before + 5);
            clearstatcache(true, $engineFile);

            $after = (int) $m->invoke($daemon);
            $this->assertGreaterThan($before, $after,
                'C1: codeMtime() must glob Bot/Engine/*.php — an Engine-only deploy has to trigger the algo_update self-restart');
        } finally {
            touch($engineFile, $origMtime);
            clearstatcache(true, $engineFile);
        }
    }
}

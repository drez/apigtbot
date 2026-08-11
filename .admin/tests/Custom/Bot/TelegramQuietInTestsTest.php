<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\TelegramNotifier;
use PHPUnit\Framework\TestCase;

/**
 * The suite exercises the REAL kill/alert code paths (gtbot_kill writes
 * kill_requested, daemons write Alerts) — if TelegramNotifier::fromEnv()
 * resolves real credentials here, every test run spams the operator's
 * actual Telegram (2026-08-11 incident: dozens of "kill_requested: test…"
 * messages). phpunit.xml force-blanks the env vars; this test pins that.
 */
class TelegramQuietInTestsTest extends TestCase
{
    private static bool $booted = false;

    public static function setUpBeforeClass(): void
    {
        if (self::$booted) {
            return;
        }
        $admin = dirname(__DIR__, 3);
        require_once $admin . '/vendor/autoload.php';
        // Load .env exactly like the other suites do — the phpunit.xml
        // force-blank must survive it for the guard to mean anything.
        (new \Ahc\Env\Loader())->load($admin . '/.env');
        self::$booted = true;
    }

    public function testNotifierResolvesToNullUnderTheTestSuite(): void
    {
        $this->assertSame('', (string) env('GTBOT_TELEGRAM_BOT_TOKEN', ''), 'phpunit.xml must force-blank the bot token');
        $this->assertNull(TelegramNotifier::fromEnv(), 'tests must never be able to reach the real Telegram');
    }
}

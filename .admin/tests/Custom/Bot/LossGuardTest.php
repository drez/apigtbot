<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\LossGuard;
use PHPUnit\Framework\TestCase;

/**
 * LossGuard is the single answer to "would selling at this price realize a
 * loss?" — shared by the Trend stop-out gate and both Flatten gates, so the
 * three cannot drift apart again (2026-09-18: Flatten refused a PROFITABLE
 * liquidation because it only looked at the sell_at_loss switch).
 */
class LossGuardTest extends TestCase
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
        self::$booted = true;
    }

    public function testBreakevenGrossesUpBothFeeLegs(): void
    {
        // the entry fee is already paid, the exit fee is still to pay
        $this->assertSame(0, bccomp('100.20000000', LossGuard::breakeven('100', '0.001'), 8));
        $this->assertSame(0, bccomp('79959.60000000', LossGuard::breakeven('79800', '0.001'), 8));
    }

    public function testZeroFeeBreakevenIsTheVwap(): void
    {
        $this->assertSame(0, bccomp('100', LossGuard::breakeven('100', '0'), 8));
    }

    public function testBelowBreakevenIsALoss(): void
    {
        $this->assertTrue(LossGuard::wouldRealizeLoss('100', '0.001', '100.19'));
        $this->assertTrue(LossGuard::wouldRealizeLoss('79800', '0.001', '79959.59'));
    }

    public function testAtOrAboveBreakevenIsNotALoss(): void
    {
        $this->assertFalse(LossGuard::wouldRealizeLoss('100', '0.001', '100.20'), 'exactly breakeven nets zero, not a loss');
        $this->assertFalse(LossGuard::wouldRealizeLoss('100', '0.001', '120'));
        // prod run 8 on 2026-09-18: entry 79214, spot 80106 — a gain
        $this->assertFalse(LossGuard::wouldRealizeLoss('79214', '0.001', '80106.10'));
    }

    public function testNothingHeldIsNeverALoss(): void
    {
        $this->assertFalse(LossGuard::wouldRealizeLoss(null, '0.001', '100'), 'no position: nothing to realize');
        $this->assertFalse(LossGuard::wouldRealizeLoss('0', '0.001', '100'));
    }

    public function testUnknownPriceIsNeverALoss(): void
    {
        $this->assertFalse(LossGuard::wouldRealizeLoss('100', '0.001', null));
    }
}

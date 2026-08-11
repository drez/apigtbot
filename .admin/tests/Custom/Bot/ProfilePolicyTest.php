<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\ProfilePolicy;
use App\GridRun;
use PHPUnit\Framework\TestCase;

class ProfilePolicyTest extends TestCase
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

    public function testCapsBalancedAtReferenceSlice(): void
    {
        $caps = ProfilePolicy::caps('Balanced', '500');
        $this->assertSame('30.00000000', $caps['DailyLossLimitQuote']);      // 6%
        $this->assertSame('75.00000000', $caps['MaxUnrealizedLossQuote']);   // 15%
        $this->assertSame('500.00000000', $caps['MaxPositionQuote']);        // 1.0x
        $this->assertSame('150.00000000', $caps['MaxOrderQuote']);           // 0.30x
        $this->assertSame('HaltAndHold', $caps['BreakoutPolicy']);
    }

    public function testCapsNoLossDisablesUnrealizedStop(): void
    {
        $caps = ProfilePolicy::caps('NoLoss', '500');
        $this->assertSame('10.00000000', $caps['DailyLossLimitQuote']);      // 2% fee-dust net
        $this->assertNull($caps['MaxUnrealizedLossQuote']);
        $this->assertSame('HaltAndHold', $caps['BreakoutPolicy']);
    }

    public function testCapsAggressiveAndMaxAllowFlatten(): void
    {
        $this->assertSame('Flatten', ProfilePolicy::caps('Aggressive', '500')['BreakoutPolicy']);
        $this->assertSame('Flatten', ProfilePolicy::caps('Max', '500')['BreakoutPolicy']);
        $this->assertSame('75.00000000', ProfilePolicy::caps('Max', '500')['DailyLossLimitQuote']); // 15%
        $this->assertSame('175.00000000', ProfilePolicy::caps('Max', '500')['MaxUnrealizedLossQuote']); // 35%
    }

    public function testAllowsRealizedLossMatrix(): void
    {
        $this->assertFalse(ProfilePolicy::allowsRealizedLoss('NoLoss'));
        foreach (['Cautious', 'Balanced', 'Aggressive', 'Max'] as $p) {
            $this->assertTrue(ProfilePolicy::allowsRealizedLoss($p), $p);
        }
    }

    public function testUnknownProfileThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ProfilePolicy::caps('YOLO', '500');
    }

    public function testUnknownProfileInAllowsRealizedLossThrows(): void
    {
        $this->expectException(\InvalidArgumentException::class);
        ProfilePolicy::allowsRealizedLoss('YOLO');
    }

    public function testApplyStampsAndReportsChange(): void
    {
        $run = new GridRun();
        $run->setProfile('Cautious');
        $run->setBudgetQuote('400');
        $this->assertTrue(ProfilePolicy::apply($run));
        $this->assertSame('12.00000000', (string) $run->getDailyLossLimitQuote());     // 3%
        $this->assertSame('40.00000000', (string) $run->getMaxUnrealizedLossQuote());  // 10%
        $this->assertSame('HaltAndHold', (string) $run->getBreakoutPolicy());
        // idempotent: second apply changes nothing
        $this->assertFalse(ProfilePolicy::apply($run));
        // slice change re-derives
        $run->setBudgetQuote('800');
        $this->assertTrue(ProfilePolicy::apply($run));
        $this->assertSame('24.00000000', (string) $run->getDailyLossLimitQuote());
    }

    public function testTrendCapsMatrix(): void
    {
        $this->assertSame(['AtrStopMult' => null, 'AtrInitialMult' => null, 'ReentryCooldown' => null], ProfilePolicy::trendCaps('NoLoss'));
        $this->assertSame(['AtrStopMult' => '2.0000', 'AtrInitialMult' => '1.5000', 'ReentryCooldown' => 6], ProfilePolicy::trendCaps('Cautious'));
        $this->assertSame(['AtrStopMult' => '3.0000', 'AtrInitialMult' => '2.0000', 'ReentryCooldown' => 3], ProfilePolicy::trendCaps('Balanced'));
        $this->assertSame(['AtrStopMult' => '4.0000', 'AtrInitialMult' => '2.5000', 'ReentryCooldown' => 1], ProfilePolicy::trendCaps('Aggressive'));
        $this->assertSame(['AtrStopMult' => '5.0000', 'AtrInitialMult' => '3.0000', 'ReentryCooldown' => 0], ProfilePolicy::trendCaps('Max'));
        $this->expectException(\InvalidArgumentException::class);
        ProfilePolicy::trendCaps('YOLO');
    }

    public function testApplyStampsTrendKnobs(): void
    {
        $run = new GridRun();
        $run->setProfile('Aggressive');
        $run->setBudgetQuote('400');
        ProfilePolicy::apply($run);
        $this->assertSame('4.0000', (string) $run->getAtrStopMult());
        $this->assertSame('2.5000', (string) $run->getAtrInitialMult());
        $this->assertSame(1, (int) $run->getReentryCooldown());
        $this->assertFalse(ProfilePolicy::apply($run)); // still idempotent
    }
}

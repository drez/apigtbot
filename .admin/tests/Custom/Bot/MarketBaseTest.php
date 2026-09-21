<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\RunFactory;
use PHPUnit\Framework\TestCase;

/** Geometry is priced on the tape the run TRADES — see RunFactory::marketBase. */
class MarketBaseTest extends TestCase
{
    protected function tearDown(): void
    {
        putenv('GTBOT_USE_TESTNET');
        putenv('GTBOT_ANALYSIS_BASE');
        unset($_ENV['GTBOT_USE_TESTNET'], $_ENV['GTBOT_ANALYSIS_BASE'], $_SERVER['GTBOT_USE_TESTNET'], $_SERVER['GTBOT_ANALYSIS_BASE']);
    }

    private function env(string $k, string $v): void
    {
        putenv("$k=$v");
        $_ENV[$k] = $_SERVER[$k] = $v;
    }

    public function testAPaperRunIsPricedOnTheRealMarketWhateverTheTestnetFlagSays(): void
    {
        $this->env('GTBOT_USE_TESTNET', '1');
        $this->assertSame('https://api.binance.com', RunFactory::marketBase(true));
    }

    public function testARealRunOnTheTestnetIsPricedOnTheTestnet(): void
    {
        $this->env('GTBOT_USE_TESTNET', '1');
        $this->assertSame('https://testnet.binance.vision', RunFactory::marketBase(false));
    }

    public function testAMainnetRunIsPricedOnTheAnalysisBase(): void
    {
        $this->env('GTBOT_USE_TESTNET', '0');
        $this->env('GTBOT_ANALYSIS_BASE', 'https://data.example');
        $this->assertSame('https://data.example', RunFactory::marketBase(false));
    }
}

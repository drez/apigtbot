<?php

namespace Tests\Custom\Bot;

use ApiGoat\Sessions\AuthySession;
use App\BotEventQuery;
use App\GridRun;
use App\GridRunQuery;
use App\Mcp\Tools\GtbotCreateRunTool;
use PHPUnit\Framework\TestCase;

class GtbotCreateRunToolTest extends TestCase
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
        $_SESSION[_AUTH_VAR] = new AuthySession();
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

    private function tool(?string $price = '600'): GtbotCreateRunTool
    {
        // inject a fixed market price so the test needs no network
        return new GtbotCreateRunTool($price);
    }

    private function decode(array $r): array
    {
        return json_decode($r['content'][0]['text'], true);
    }

    private function session(): AuthySession
    {
        return $_SESSION[_AUTH_VAR];
    }

    private function baseArgs(): array
    {
        return [
            'label' => 'createrun-' . bin2hex(random_bytes(4)),
            'symbol' => 'bnbusdt',
            'status' => 'Testnet',
            'p_low' => '500',
            'p_high' => '700',
            'n_levels' => 12,
            'budget_quote' => '300',
        ];
    }

    public function testWithoutConfirmNothingIsCreated(): void
    {
        $args = $this->baseArgs();
        $out = $this->decode($this->tool()->handle($args, $this->session()));
        $this->assertTrue($out['pending']);
        $this->assertSame('BNBUSDT', $out['preview']['record']['symbol']);
        $this->assertCount(13, $out['preview']['levels']);
        $this->assertNull(GridRunQuery::create()->findOneByLabel($args['label']));
    }

    public function testRangeMustBracketPrice(): void
    {
        $args = ['p_low' => '610', 'p_high' => '700', 'confirm' => true] + $this->baseArgs();
        $out = $this->decode($this->tool('600')->handle($args, $this->session()));
        $this->assertFalse($out['created']);
        $this->assertStringContainsString('bracket', implode(' ', $out['errors']));
        $this->assertNull(GridRunQuery::create()->findOneByLabel($args['label']));
    }

    public function testUnknownProfileIsRefused(): void
    {
        $args = ['profile' => 'YOLO', 'confirm' => true] + $this->baseArgs();
        $out = $this->decode($this->tool()->handle($args, $this->session()));
        $this->assertFalse($out['created']);
        $this->assertStringContainsString('unknown profile', implode(' ', $out['errors']));
        $this->assertNull(GridRunQuery::create()->findOneByLabel($args['label']));
    }

    public function testLiveStatusIsRefused(): void
    {
        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        $this->tool()->handle(['status' => 'Live'] + $this->baseArgs(), $this->session());
    }

    public function testDuplicateLabelIsRefused(): void
    {
        $args = $this->baseArgs();
        $r = new GridRun();
        $r->setLabel($args['label']);
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Draft');
        $r->setPLow('1');
        $r->setPHigh('2');
        $r->setNLevels(2);
        $r->setBudgetQuote('10');
        $r->setMaxPositionQuote('10');
        $r->setMaxOrderQuote('10');
        $r->setDailyLossLimitQuote('10');
        $r->save();

        $this->expectException(\ApiGoat\Mcp\ToolError::class);
        $this->tool()->handle($args, $this->session());
    }

    public function testConfirmCreatesRunAndEvent(): void
    {
        $args = ['confirm' => true] + $this->baseArgs();
        $out = $this->decode($this->tool()->handle($args, $this->session()));
        $this->assertTrue($out['created']);

        $run = GridRunQuery::create()->findPk($out['id_grid_run']);
        $this->assertNotNull($run);
        $this->assertSame('BNBUSDT', (string) $run->getSymbol());
        $this->assertSame('Testnet', (string) $run->getStatus());
        $this->assertSame(12, (int) $run->getNLevels());
        $this->assertSame(0, bccomp('300', (string) $run->getBudgetQuote(), 8));
        // profile defaults to Balanced and stamps live-policy caps off budget
        // (6% daily, 15% unrealized, 1.0x position, 0.30x order — ProfilePolicy::RULES)
        $this->assertSame('Balanced', (string) $run->getProfile());
        $this->assertSame(0, bccomp('18', (string) $run->getDailyLossLimitQuote(), 8));
        $this->assertSame(0, bccomp('45', (string) $run->getMaxUnrealizedLossQuote(), 8));
        $this->assertSame(0, bccomp('300', (string) $run->getMaxPositionQuote(), 8));
        $this->assertSame(0, bccomp('90', (string) $run->getMaxOrderQuote(), 8));
        $this->assertSame('HaltAndHold', (string) $run->getBreakoutPolicy());

        $e = BotEventQuery::create()
            ->filterByIdGridRun((int) $run->getIdGridRun())
            ->filterByKind('run_created')
            ->findOne();
        $this->assertNotNull($e);
    }

    public function testSellAtLossArgIsStampedAndDefaultsOff(): void
    {
        $out = $this->decode($this->tool()->handle(['confirm' => true] + $this->baseArgs(), $this->session()));
        $this->assertFalse((bool) GridRunQuery::create()->findPk($out['id_grid_run'])->getSellAtLoss(), 'default OFF');

        $args = ['sell_at_loss' => true, 'confirm' => true, 'label' => 'sal-on-' . bin2hex(random_bytes(3))] + $this->baseArgs();
        $out = $this->decode($this->tool()->handle($args, $this->session()));
        $this->assertTrue((bool) GridRunQuery::create()->findPk($out['id_grid_run'])->getSellAtLoss());
    }

    public function testExplicitProfileStampsItsOwnCaps(): void
    {
        $args = ['profile' => 'Cautious', 'confirm' => true] + $this->baseArgs();
        $out = $this->decode($this->tool()->handle($args, $this->session()));
        $this->assertTrue($out['created']);

        $run = GridRunQuery::create()->findPk($out['id_grid_run']);
        $this->assertSame('Cautious', (string) $run->getProfile());
        // Cautious: 3% daily, 10% unrealized on a 300 budget
        $this->assertSame(0, bccomp('9', (string) $run->getDailyLossLimitQuote(), 8));
        $this->assertSame(0, bccomp('30', (string) $run->getMaxUnrealizedLossQuote(), 8));
    }
}

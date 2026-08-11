<?php

namespace Tests\Custom\Bot;

use App\GridRunServiceWrapper;
use PHPUnit\Framework\TestCase;

class GridRunWrapperProfileTest extends TestCase
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

    /** A persisted row to restore FROM — the Critical-fix tests need a real
     *  pristine DB value, not an in-memory-only model. */
    private function makePersistedRun(): \App\GridRun
    {
        $r = new \App\GridRun();
        $r->setLabel('wraptest-' . bin2hex(random_bytes(4)));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Testnet');
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setSpacing('Arithmetic');
        $r->setAllocation('EqualBase');
        $r->setBudgetQuote('400');
        $r->setFeePct('0.001');
        $r->setMaxPositionQuote('10000');
        $r->setMaxOrderQuote('1000');
        $r->setDailyLossLimitQuote('10000');
        $r->setBreakoutBufferPct('0.02');
        $r->setBreakoutPolicy('HaltAndHold');
        $r->setMaxOpenOrders(60);
        $r->setRunUid('wraptest-' . bin2hex(random_bytes(3)));
        $r->setEngineState('{"algo":"Grid"}');
        $r->save();
        return $r;
    }

    private function createWrapper(): GridRunServiceWrapper
    {
        $request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        $args = [];
        return new GridRunServiceWrapper($request, $response, $args);
    }

    public function testBeforeSaveInjectsDerivedCaps(): void
    {
        $wrapper = $this->createWrapper();
        $data = ['Profile' => 'Cautious', 'BudgetQuote' => '400', 'Status' => 'Draft'];
        $messages = [];
        $ext = [];
        $error = null;
        $e = null;
        $wrapper->beforeSave($e, $data, true, $messages, $ext, $error);
        $this->assertSame('12.00000000', $data['DailyLossLimitQuote']);
        $this->assertSame('40.00000000', $data['MaxUnrealizedLossQuote']);
        $this->assertSame('400.00000000', $data['MaxPositionQuote']);
        $this->assertSame('120.00000000', $data['MaxOrderQuote']);
        $this->assertSame('HaltAndHold', $data['BreakoutPolicy']);
        // Verify trendCaps are also in $data
        $this->assertSame('2.0000', $data['AtrStopMult']);
        $this->assertSame('1.5000', $data['AtrInitialMult']);
        $this->assertSame(6, $data['ReentryCooldown']);
    }

    public function testBeforeSaveNoLossNullsUnrealizedStop(): void
    {
        $wrapper = $this->createWrapper();
        $data = ['Profile' => 'NoLoss', 'BudgetQuote' => '400', 'Status' => 'Draft'];
        $messages = [];
        $ext = [];
        $error = null;
        $e = null;
        $wrapper->beforeSave($e, $data, true, $messages, $ext, $error);
        $this->assertNull($data['MaxUnrealizedLossQuote']);
        $this->assertSame('HaltAndHold', $data['BreakoutPolicy']);
        // Verify NoLoss nulls all trendCaps
        $this->assertNull($data['AtrStopMult']);
        $this->assertNull($data['AtrInitialMult']);
        $this->assertNull($data['ReentryCooldown']);
    }

    public function testBeforeSaveStampsGuiPathModelInstance(): void
    {
        $wrapper = $this->createWrapper();
        $e = new \App\GridRun();
        $e->setProfile('Cautious');
        $e->setBudgetQuote('400');
        // Simulate GUI post: $data carries only non-cap fields (caps are readonly)
        $data = ['Status' => 'Draft'];
        $messages = [];
        $ext = [];
        $error = null;
        $wrapper->beforeSave($e, $data, true, $messages, $ext, $error);
        // Verify caps are stamped onto the model instance
        $this->assertSame('12.00000000', (string) $e->getDailyLossLimitQuote());
        $this->assertSame('40.00000000', (string) $e->getMaxUnrealizedLossQuote());
        $this->assertSame('400.00000000', (string) $e->getMaxPositionQuote());
        $this->assertSame('120.00000000', (string) $e->getMaxOrderQuote());
        $this->assertSame('HaltAndHold', (string) $e->getBreakoutPolicy());
    }

    public function testBeforeSaveGuiPathOverridesProfileFromData(): void
    {
        $wrapper = $this->createWrapper();
        $e = new \App\GridRun();
        $e->setProfile('Balanced');
        $e->setBudgetQuote('400');
        // Simulate GUI post with profile change in $data
        $data = ['Profile' => 'NoLoss', 'Status' => 'Draft'];
        $messages = [];
        $ext = [];
        $error = null;
        $wrapper->beforeSave($e, $data, true, $messages, $ext, $error);
        // Verify profile is updated from $data and NoLoss caps are applied
        $this->assertSame('NoLoss', (string) $e->getProfile());
        $this->assertNull($e->getMaxUnrealizedLossQuote(), 'NoLoss profile should null MaxUnrealizedLossQuote');
        $this->assertSame('HaltAndHold', (string) $e->getBreakoutPolicy());
    }

    public function testTrendAlgoRefusedForNoLossOnAlgoChange(): void
    {
        $wrapper = $this->createWrapper();
        $e = new \App\GridRun();
        $e->setProfile('NoLoss');
        $e->setBudgetQuote('400');
        $data = ['Algo' => 'Trend', 'Status' => 'Draft', 'BudgetQuote' => '400'];
        $messages = []; $ext = []; $error = null;
        $wrapper->beforeSave($e, $data, false, $messages, $ext, $error);
        $this->assertNotEmpty($ext, 'Trend x NoLoss must be refused');
        $this->assertStringContainsString('NoLoss', json_encode(array_keys($ext)));
    }

    public function testNoLossProfileRefusedOnTrendRun(): void
    {
        $wrapper = $this->createWrapper();
        $e = new \App\GridRun();
        $e->setAlgo('Trend');
        $e->setProfile('Balanced');
        $e->setBudgetQuote('400');
        $data = ['Profile' => 'NoLoss', 'Status' => 'Draft', 'BudgetQuote' => '400'];
        $messages = []; $ext = []; $error = null;
        $wrapper->beforeSave($e, $data, false, $messages, $ext, $error);
        $this->assertNotEmpty($ext);
    }

    /**
     * Task-5 review (a): engine_state is a daemon-managed column — the
     * schema's set_readonly_columns declares it, but the generated Api's
     * editable-field allowlist doesn't actually honor that (a goatcheese
     * emitter gap — see the wrapper's docblock), so the wrapper strips it
     * from $data directly on every save, API/MCP or GUI.
     */
    public function testBeforeSaveStripsEngineStateFromApiWrites(): void
    {
        $wrapper = $this->createWrapper();
        $e = null;
        $data = ['EngineState' => '{"algo":"Trend","qty":"999"}', 'BudgetQuote' => '400', 'Status' => 'Draft'];
        $messages = []; $ext = []; $error = null;
        $wrapper->beforeSave($e, $data, true, $messages, $ext, $error);
        $this->assertArrayNotHasKey('EngineState', $data, 'an external caller must never be able to overwrite the daemon-owned engine state');
    }

    /**
     * CRITICAL (review of task-5 review): the $data strip above only guards
     * the API/MCP path, where Api::setEntry calls beforeSave BEFORE applying
     * $data to the model. The GUI path is the OPPOSITE order —
     * Service::saveUpdate calls Form::setUpdateDefaultsGridRun($data) FIRST,
     * which loads $e then runs $e->fromArray($data) with NO allowlist
     * (Propel's fromArray calls set<Col> for every matching key) — THEN
     * calls beforeSave. So by the time beforeSave runs on the GUI path, a
     * crafted EngineState is already sitting on $e, and stripping $data does
     * nothing to undo it. Reproduces that exact sequencing (real persisted
     * row → fromArray → beforeSave) rather than asserting against $data.
     */
    public function testBeforeSaveResetsEngineStateAfterGuiFormArrayPollution(): void
    {
        $run = $this->makePersistedRun();
        $pristine = (string) $run->getEngineState();
        $this->assertSame('{"algo":"Grid"}', $pristine, 'fixture sanity');

        // Mirror Form::setUpdateDefaultsGridRun exactly: load the row, then
        // fromArray($data) with the crafted field — no allowlist involved.
        $e = \App\GridRunQuery::create()->findPk((int) $run->getIdGridRun());
        $data = ['EngineState' => '{"algo":"Trend","qty":"999999","entry":"1"}'];
        $e->fromArray($data);
        $this->assertSame($data['EngineState'], (string) $e->getEngineState(),
            'fixture: fromArray has no allowlist — this IS the vulnerability the fix closes');

        $wrapper = $this->createWrapper();
        $messages = []; $ext = []; $error = null;
        $wrapper->beforeSave($e, $data, false, $messages, $ext, $error);

        $this->assertSame($pristine, (string) $e->getEngineState(),
            'the MODEL must be reset to its persisted value — the API-path $data strip alone cannot undo fromArray() pollution');
    }

    /** isNew has no persisted row to restore from — the crafted value must
     *  still never survive onto a freshly created run. */
    public function testBeforeSaveNullsEngineStateOnANewRunEvenAfterFormArrayPollution(): void
    {
        $e = new \App\GridRun();
        $data = ['EngineState' => '{"algo":"Trend","qty":"5"}'];
        $e->fromArray($data);
        $this->assertSame($data['EngineState'], (string) $e->getEngineState(), 'fixture sanity');

        $wrapper = $this->createWrapper();
        $messages = []; $ext = []; $error = null;
        $wrapper->beforeSave($e, $data, true, $messages, $ext, $error);

        $this->assertNull($e->getEngineState(), 'a brand new row has no persisted value to restore — must be nulled, not left with the crafted one');
    }
}

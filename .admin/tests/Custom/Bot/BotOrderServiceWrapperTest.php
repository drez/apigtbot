<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\BotOrderPeer;
use App\BotOrderQuery;
use App\BotOrderServiceWrapper;
use App\GridRun;
use PHPUnit\Framework\TestCase;

/**
 * Task-5 review (a): legacy_buy_price/legacy_buy_fee (the pinned buy
 * attribution Daemon::placeLegacyExit stamps on a legacy exit) are declared
 * set_readonly_columns in schema, but the generated Api's editable-field
 * allowlist doesn't actually honor that (a goatcheese emitter gap — see the
 * wrapper's docblock), so the wrapper strips both from $data directly on
 * every save, API/MCP or GUI. Internal daemon writes go through the Propel
 * model's own setters (OrderStore::markLegacy), never this path.
 */
class BotOrderServiceWrapperTest extends TestCase
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

    private function wrapper(): BotOrderServiceWrapper
    {
        $request = $this->createMock(\Psr\Http\Message\ServerRequestInterface::class);
        $response = $this->createMock(\Psr\Http\Message\ResponseInterface::class);
        return new BotOrderServiceWrapper($request, $response, []);
    }

    private function makeRun(): GridRun
    {
        $r = new GridRun();
        $r->setLabel('boswtest-' . bin2hex(random_bytes(4)));
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
        $r->setRunUid('boswtest-' . bin2hex(random_bytes(3)));
        $r->save();
        return $r;
    }

    /** A persisted legacy-exit row to restore FROM — the Critical-fix test
     *  needs a real pristine DB value, not an in-memory-only model. */
    private function makePersistedLegacyExit(GridRun $run): BotOrder
    {
        $o = new BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId('bosw-' . bin2hex(random_bytes(6)));
        $o->setLevelIdx(0);
        $o->setSide('Sell');
        $o->setState('SELL_OPEN');
        $o->setPrice('130');
        $o->setQty('1.0');
        $o->setIsLegacy(true);
        $o->setLegacyBuyPrice('130');
        $o->setLegacyBuyFee('0.13');
        $o->save();
        return $o;
    }

    public function testBeforeSaveStripsLegacyBuyAttributionFromApiWrites(): void
    {
        $e = null;
        $data = ['LegacyBuyPrice' => '99999', 'LegacyBuyFee' => '1', 'Qty' => '1.0'];
        $messages = []; $ext = []; $error = null;
        $this->wrapper()->beforeSave($e, $data, true, $messages, $ext, $error);

        $this->assertArrayNotHasKey('LegacyBuyPrice', $data, 'an external caller must never fabricate a cost basis');
        $this->assertArrayNotHasKey('LegacyBuyFee', $data);
        $this->assertSame('1.0', $data['Qty'], 'unrelated fields are left alone');
    }

    /**
     * CRITICAL (review of task-5 review): the $data strip above only guards
     * the API/MCP path (Api::setEntry calls beforeSave BEFORE applying $data
     * to the model). The GUI path is the OPPOSITE order —
     * Service::saveUpdate calls Form::setUpdateDefaultsBotOrder($data) FIRST,
     * which loads $e then runs $e->fromArray($data) with NO allowlist — THEN
     * calls beforeSave. So by the time beforeSave runs on the GUI path, a
     * crafted LegacyBuyPrice/LegacyBuyFee is already sitting on $e, and
     * stripping $data does nothing to undo it. Reproduces that exact
     * sequencing (real persisted row → fromArray → beforeSave).
     */
    public function testBeforeSaveResetsLegacyBuyAttributionAfterGuiFormArrayPollution(): void
    {
        $run = $this->makeRun();
        $row = $this->makePersistedLegacyExit($run);
        // re-fetch to read back the DB-canonical (formatted) persisted value,
        // not the raw string the in-memory setter still holds pre-round-trip
        BotOrderPeer::removeInstanceFromPool((int) $row->getIdBotOrder());
        $pristineRow = BotOrderQuery::create()->findPk((int) $row->getIdBotOrder());
        $pristinePrice = (string) $pristineRow->getLegacyBuyPrice();
        $pristineFee = (string) $pristineRow->getLegacyBuyFee();
        $this->assertSame('130.00000000', $pristinePrice, 'fixture sanity');

        // Mirror Form::setUpdateDefaultsBotOrder exactly: load the row, then
        // fromArray($data) with the crafted fields — no allowlist involved.
        $e = BotOrderQuery::create()->findPk((int) $row->getIdBotOrder());
        $data = ['LegacyBuyPrice' => '1', 'LegacyBuyFee' => '999'];
        $e->fromArray($data);
        $this->assertSame('1', (string) $e->getLegacyBuyPrice(),
            'fixture: fromArray has no allowlist — this IS the vulnerability the fix closes');

        $wrapper = $this->wrapper();
        $messages = []; $ext = []; $error = null;
        $wrapper->beforeSave($e, $data, false, $messages, $ext, $error);

        $this->assertSame($pristinePrice, (string) $e->getLegacyBuyPrice(),
            'the MODEL must be reset to its persisted cost basis — the API-path $data strip alone cannot undo fromArray() pollution');
        $this->assertSame($pristineFee, (string) $e->getLegacyBuyFee());
    }

    /** isNew has no persisted row to restore from — the crafted value must
     *  still never survive onto a freshly created order. */
    public function testBeforeSaveNullsLegacyBuyAttributionOnANewOrderEvenAfterFormArrayPollution(): void
    {
        $e = new BotOrder();
        $data = ['LegacyBuyPrice' => '1', 'LegacyBuyFee' => '999'];
        $e->fromArray($data);
        $this->assertSame('1', (string) $e->getLegacyBuyPrice(), 'fixture sanity');

        $wrapper = $this->wrapper();
        $messages = []; $ext = []; $error = null;
        $wrapper->beforeSave($e, $data, true, $messages, $ext, $error);

        $this->assertNull($e->getLegacyBuyPrice(), 'a brand new row has no persisted value to restore — must be nulled, not left with the crafted one');
        $this->assertNull($e->getLegacyBuyFee());
    }
}

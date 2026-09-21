<?php

namespace Tests\Custom\Bot;

use App\BotOrder;
use App\Domains\Bot\AccountAudit;
use App\Domains\Bot\Gateway\BinanceGateway;
use App\GridRun;
use App\GridRunQuery;
use Tests\Builder\Support\DbTestCase;
use Tests\Custom\Bot\Support\ExchangeSim;

/** Does the real ACCOUNT back what the ledgers say the fleet holds and works? */
class AccountAuditTest extends DbTestCase
{
    private ExchangeSim $sim;
    private GridRun $run;

    protected function setUp(): void
    {
        parent::setUp();
        foreach (GridRunQuery::create()->filterByStatus('Done', \Criteria::NOT_EQUAL)->find() as $r) {
            $r->setStatus('Done');
            $r->save();
        }
        $this->sim = new ExchangeSim();
        $this->sim->setPrice('100');
        $this->run = $this->mkRun(false);
    }

    private function mkRun(bool $simulated): GridRun
    {
        $r = new GridRun();
        $r->setLabel(static::uniq('audit'));
        $r->setSymbol('BTCUSDT');
        $r->setStatus('Live');
        $r->setSimulated($simulated);
        $r->setPLow('100');
        $r->setPHigh('200');
        $r->setNLevels(4);
        $r->setBudgetQuote('100');
        $r->setRunUid(static::uniq('a'));
        $r->save();
        return $r;
    }

    private function order(GridRun $run, string $side, string $state, string $qty, ?string $cid = null): string
    {
        $cid ??= static::uniq('cid');
        $o = new BotOrder();
        $o->setIdGridRun((int) $run->getIdGridRun());
        $o->setClientOrderId($cid);
        $o->setSide($side);
        $o->setLevelIdx(0);
        $o->setPrice('100');
        $o->setQty($qty);
        $o->setFilledQty($state === 'Filled' ? $qty : '0');
        $o->setState($state);
        $o->setSimulated((bool) $run->getSimulated());
        $o->save();
        return $cid;
    }

    private function audit(): array
    {
        return AccountAudit::run(new BinanceGateway('https://sim.local', 'k', 's', $this->sim->transport()));
    }

    private function kinds(array $res): array
    {
        return array_column($res['findings'], 'kind');
    }

    public function testAnAccountThatBacksTheLedgerIsClean(): void
    {
        $this->order($this->run, 'Buy', 'Filled', '0.5');
        $cid = $this->order($this->run, 'Sell', 'SELL_OPEN', '0.5');
        $this->sim->open[$cid] = ['clientOrderId' => $cid, 'side' => 'SELL', 'price' => '150', 'origQty' => '0.5', 'executedQty' => '0', 'status' => 'NEW'];
        $this->sim->balances = ['BTC' => ['free' => '0', 'locked' => '0.5003'], 'USDT' => ['free' => '50', 'locked' => '0']];

        $res = $this->audit();
        $this->assertTrue($res['ok'], json_encode($res['findings']));
        $this->assertSame(0, bccomp('0.0003', $res['assets']['BTC']['float'], 8));
    }

    public function testInventoryTheAccountDoesNotHoldIsFound(): void
    {
        $this->order($this->run, 'Buy', 'Filled', '0.5');
        $this->sim->balances = ['BTC' => ['free' => '0.3', 'locked' => '0']];
        $res = $this->audit();
        $this->assertFalse($res['ok']);
        $this->assertContains('inventory_missing', $this->kinds($res));
    }

    public function testAnExitTheLedgerBelievesInButTheBookLacksIsFound(): void
    {
        $this->order($this->run, 'Buy', 'Filled', '0.5');
        $this->order($this->run, 'Sell', 'SELL_OPEN', '0.5');
        $this->sim->balances = ['BTC' => ['free' => '0.5', 'locked' => '0']];
        $res = $this->audit();
        $this->assertContains('order_not_on_book', $this->kinds($res));
    }

    public function testAnOrderNobodysLedgerOwnsIsFound(): void
    {
        $this->sim->open['manual-1'] = ['clientOrderId' => 'manual-1', 'side' => 'BUY', 'price' => '90', 'origQty' => '1', 'executedQty' => '0', 'status' => 'NEW'];
        $res = $this->audit();
        $this->assertContains('foreign_order', $this->kinds($res));
    }

    public function testPaperRunsAreNotTheAccountsBusiness(): void
    {
        $paper = $this->mkRun(true);
        $this->order($paper, 'Buy', 'Filled', '9');
        $this->order($paper, 'Sell', 'SELL_OPEN', '9');
        $res = $this->audit();
        $this->assertTrue($res['ok'], json_encode($res['findings']));
    }
}

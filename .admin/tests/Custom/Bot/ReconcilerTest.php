<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\Reconciler;
use PHPUnit\Framework\TestCase;

class ReconcilerTest extends TestCase
{
    public function testMatchingStateNeedsNoActions(): void
    {
        $actions = Reconciler::plan(
            [['client_order_id' => 'gt-abc-L01-B-1']],
            [['clientOrderId' => 'gt-abc-L01-B-1']],
            'gt-abc-'
        );
        $this->assertSame([], $actions);
    }

    public function testDbOpenMissingOnExchangeNeedsStatusCheck(): void
    {
        $actions = Reconciler::plan(
            [['client_order_id' => 'gt-abc-L01-B-1'], ['client_order_id' => 'gt-abc-L02-B-2']],
            [['clientOrderId' => 'gt-abc-L02-B-2']],
            'gt-abc-'
        );
        $this->assertSame([['check_status', 'gt-abc-L01-B-1']], $actions);
    }

    public function testOurExchangeOrderUnknownToDbIsAdopted(): void
    {
        $actions = Reconciler::plan(
            [],
            [['clientOrderId' => 'gt-abc-L03-S-9']],
            'gt-abc-'
        );
        $this->assertSame([['adopt', 'gt-abc-L03-S-9']], $actions);
    }

    public function testForeignOrderRaisesAlertAndIsNeverTouched(): void
    {
        $actions = Reconciler::plan(
            [],
            [['clientOrderId' => 'manual-trade-1'], ['clientOrderId' => 'gt-OTHERRUN-L01-B-1']],
            'gt-abc-'
        );
        $this->assertSame(
            [['alert_foreign', 'manual-trade-1'], ['alert_foreign', 'gt-OTHERRUN-L01-B-1']],
            $actions
        );
    }
}

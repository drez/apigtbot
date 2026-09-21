<?php

namespace Tests\Custom\Bot;

use App\Domains\Bot\ClampTrail;
use PHPUnit\Framework\TestCase;

/**
 * "Conviction requests, risk disposes": every gate that changes a refit
 * request must leave a {limit, before, after} record, so a decision's
 * requested-vs-applied geometry is reconstructible from its own row.
 */
class ClampTrailTest extends TestCase
{
    public function testNoChangeLeavesNoRecord(): void
    {
        $t = new ClampTrail();
        $t->add('regime_gate', 40, 40);
        $this->assertSame([], $t->all());
        $this->assertFalse($t->any());
        $this->assertSame('[]', $t->toJson());
    }

    public function testRecordsEveryLimitThatFired(): void
    {
        $t = new ClampTrail();
        $t->add('regime_gate', 70, 25, 'hostile 4h');
        $t->add('profile_wall', 25, 25); // no-op, dropped
        $t->add('spacing_floor', 0.011, 0.013);
        $this->assertTrue($t->any());
        $this->assertSame(['regime_gate', 'spacing_floor'], array_column($t->all(), 'limit'));
        $decoded = json_decode($t->toJson(), true);
        $this->assertSame(['limit' => 'regime_gate', 'before' => 70, 'after' => 25, 'why' => 'hostile 4h'], $decoded[0]);
        $this->assertArrayNotHasKey('why', $decoded[1]);
    }

    /** candidate_delta: did the caller follow the deterministic candidate? */
    public function testCandidateDelta(): void
    {
        $cand = ['p_low' => '60000.00', 'p_high' => '72000.00', 'n_levels' => 12];
        $this->assertSame('none', ClampTrail::candidateDelta(null, '60000', '72000', 12));
        $this->assertSame('same', ClampTrail::candidateDelta($cand, '60000', '72000', 12));
        // within 1% of each bound + same level count still counts as "same"
        $this->assertSame('same', ClampTrail::candidateDelta($cand, '60500', '71500', 12));
        $this->assertSame('deviated', ClampTrail::candidateDelta($cand, '58000', '72000', 12));
        $this->assertSame('deviated', ClampTrail::candidateDelta($cand, '60000', '72000', 20));
    }
}

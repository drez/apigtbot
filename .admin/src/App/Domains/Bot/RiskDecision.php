<?php

namespace App\Domains\Bot;

/** Outcome of a risk review. kill implies the run must stop taking entries. */
class RiskDecision
{
    public function __construct(
        public readonly bool $approved,
        public readonly string $reason = '',
        public readonly bool $kill = false,
        public readonly bool $halt = false,
        public readonly bool $flatten = false
    ) {
    }
}

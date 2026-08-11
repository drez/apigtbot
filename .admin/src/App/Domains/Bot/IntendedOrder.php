<?php

namespace App\Domains\Bot;

/** A strategy-emitted order before risk review and filter normalization. */
class IntendedOrder
{
    public function __construct(
        public readonly string $side,      // 'Buy' | 'Sell'
        public readonly int $levelIdx,
        public readonly string $price,     // bcmath string
        public readonly string $qty        // bcmath string
    ) {
    }
}

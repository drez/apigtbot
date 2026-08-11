<?php

namespace App\Domains\Bot\Gateway;

class BinanceApiError extends \RuntimeException
{
    public function __construct(
        string $message,
        public readonly int $httpStatus,
        public readonly int $binanceCode = 0,
        public readonly ?int $retryAfter = null
    ) {
        parent::__construct($message);
    }
}

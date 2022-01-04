<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test;

class Money
{
    public function __construct(private int $amount, private string $currency)
    {
    }
}

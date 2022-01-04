<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test;

class InfraEntity
{
    public function __construct(
        private string $propString,
        private int $propInt,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test\Fixtures;

class ClassWithFields
{
    public function __construct(
        public bool $boolProp = false,
        public array $arrayProp = [1, 2, 3],
        public float $floatProp = 1.4,
        public int|string $unionType = 2,
    ) {
    }
}

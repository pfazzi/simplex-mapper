<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test\Fixtures;

class ClassWithProps
{
    public function __construct(
        public string $one = '1',
        public string $two = '2',
        public string $three = '3',
    ) {
    }
}

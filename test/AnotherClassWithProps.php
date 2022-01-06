<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test;

class AnotherClassWithProps
{
    public function __construct(
        public string $oneOne = '1',
        public string $twoTwo = '2',
        public string $threeThree = '3',
    ) {
    }
}

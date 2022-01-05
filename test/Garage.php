<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test;

class Garage
{
    public function __construct(
        public Car $car
    ) {
    }
}

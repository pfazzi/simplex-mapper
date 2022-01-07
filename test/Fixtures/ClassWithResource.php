<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test\Fixtures;

class ClassWithResource
{
    public function __construct(
        /** @var resource */
        public $resource
    ) {
    }
}

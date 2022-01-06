<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test;

class ClassWithResource
{
    public function __construct(
        /** @var resource */
        public $resource
    ) {
    }
}

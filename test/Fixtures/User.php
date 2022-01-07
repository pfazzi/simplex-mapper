<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test\Fixtures;

class User
{
    public function __construct(
        private string $username,
        private string $emailAddress,
        private bool $isEnabled,
    ) {
    }
}

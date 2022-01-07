<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test\Fixtures\Validation;

class UserDTO
{
    public function __construct(
        private string $username,
        private string $emailAddress,
        private bool $isEnabled,
    ) {
    }
}
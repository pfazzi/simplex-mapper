<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test\Fixtures\Validation;

class UserDTOWithBrokenTypes
{
    public function __construct(
        private int $username,
        private array $emailAddress,
        private bool $isEnabled,
    ) {
    }
}

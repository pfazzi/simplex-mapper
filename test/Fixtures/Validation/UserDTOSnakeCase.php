<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test\Fixtures\Validation;

class UserDTOSnakeCase
{
    public function __construct(
        private string $username,
        private string $email_address,
        private bool $is_enabled,
    ) {
    }
}

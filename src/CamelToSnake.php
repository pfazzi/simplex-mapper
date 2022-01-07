<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper;

class CamelToSnake implements NameConverter
{
    public function convert(string $name): string
    {
        return strtolower(preg_replace('/(?<!^)[A-Z]/', '_$0', $name));
    }
}

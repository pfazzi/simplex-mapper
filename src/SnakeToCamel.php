<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper;

class SnakeToCamel implements NameConverter
{
    public function convert(string $name): string
    {
        return lcfirst(str_replace(' ', '', ucwords(str_replace('_', ' ', $name))));
    }
}

<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper;

interface NameConverter
{
    public function convert(string $name): string;
}

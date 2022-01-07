<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper;

class NameConverterPair
{
    public function __construct(
        public NameConverter $sourceToTarget,
        public NameConverter $targetToSource,
    ) {
    }
}

<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper;

interface Constraint
{
    /**
     * @param array|object|class-string $source
     * @param object|class-string       $target
     *
     * @return list<string>|true $report
     */
    public function isSatisfied(array|object|string $source, object|string $target, ?NameConverterPair $nameConverterPair = null): array|bool;
}

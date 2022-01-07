<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper;

class PropNamesShouldBeEqual implements Constraint
{
    public function isSatisfied(object|array|string $source, object|string $target, ?NameConverterPair $nameConverterPair = null): array|bool
    {
        if (is_array($source)) {
            $source = (object) $source;
        }

        $sourceRef = new \ReflectionClass($source);
        $targetRef = new \ReflectionClass($target);

        $violations = [
            ...$this->compareProperties($sourceRef, $targetRef, $nameConverterPair?->sourceToTarget),
            ...$this->compareProperties($targetRef, $sourceRef, $nameConverterPair?->targetToSource),
        ];

        /** @psalm-suppress TypeDoesNotContainType */
        if (empty($violations)) {
            return true;
        }

        return $violations;
    }

    /**
     * @return list<string>
     */
    private function compareProperties(\ReflectionClass $sourceRef, \ReflectionClass $targetRef, ?NameConverter $nameConverter = null): array
    {
        $violations = [];
        foreach ($sourceRef->getProperties() as $sourcePropertyRef) {
            $nameInSource = $sourcePropertyRef->getName();
            $nameInTarget = null === $nameConverter ? $nameInSource : $nameConverter->convert($nameInSource);
            if (!$targetRef->hasProperty($nameInTarget)) {
                $violations[] = "Property '$nameInTarget' missing in class {$targetRef->getName()}";
            }
        }

        return $violations;
    }
}

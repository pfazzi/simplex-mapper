<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper;

/**
 * @psalm-suppress MixedAssignment
 */
class Mapper
{
    /**
     * Hydrates an object instance with data from the source.
     */
    public function hydrate(array|object $source, object $target, ?NameConverter $nameConverter = null): void
    {
        $assign = fn (string $prop, mixed $val): mixed => $this->$prop = $val;

        $targetClassRef = new \ReflectionClass($target);

        $sourceArray = $this->mapSourceToArray($source);

        foreach ($sourceArray as $propertyName => $value) {
            $this->assertPropertyNameIsString($propertyName);

            if ($nameConverter) {
                $propertyName = $nameConverter->convert($propertyName);
            }

            if (!$targetClassRef->hasProperty($propertyName)) {
                continue;
            }

            $value = $this->convertValueToTargetType($targetClassRef, $propertyName, $value);

            $assign->call($target, $propertyName, $value);
        }
    }

    /**
     * Maps the source data to a new instance of the target class.
     *
     * @template T of object
     *
     * @psalm-param class-string<T> $target
     *
     * @throws \ReflectionException
     *
     * @return T
     */
    public function map(array|object $source, string $target, ?NameConverter $nameConverter = null): object
    {
        $targetClassRef = new \ReflectionClass($target);
        $instance = $targetClassRef->newInstanceWithoutConstructor();

        $assign = fn (string $prop, mixed $val): mixed => $this->$prop = $val;

        $this->assignDefaultValuesToTargetObject($targetClassRef, $assign, $instance);

        $this->hydrate($source, $instance, $nameConverter);

        return $instance;
    }

    private function cast(mixed $value, string $type): mixed
    {
        if (\is_array($value) && class_exists($type)) {
            return $this->map($value, $type);
        }

        if (settype($value, $type)) {
            return $value;
        }

        throw new \RuntimeException("Unable to convert to '$type'");
    }

    private function assignDefaultValuesToTargetObject(\ReflectionClass $targetClassRef, \Closure $assigner, object $instance): void
    {
        foreach ($targetClassRef->getDefaultProperties() as $property => $defaultValue) {
            $assigner->call($instance, $property, $defaultValue);
        }

        if ($constructorRef = $targetClassRef->getConstructor()) {
            foreach ($constructorRef->getParameters() as $parameter) {
                if ($parameter->isPromoted() && $parameter->isDefaultValueAvailable()) {
                    $assigner->call($instance, $parameter->getName(), $parameter->getDefaultValue());
                }
            }
        }
    }

    private function extractDataFrom(object $source): array
    {
        $sourceArray = [];

        $sourceRef = new \ReflectionClass($source);
        foreach ($sourceRef->getProperties() as $reflectionProperty) {
            $name = $reflectionProperty->getName();
            if (PHP_MAJOR_VERSION < 8 || PHP_MINOR_VERSION === 0) {
                $reflectionProperty->setAccessible(true);
            }
            $value = $reflectionProperty->getValue($source);
            $sourceArray[$name] = $value;
        }

        return $sourceArray;
    }

    /**
     * @psalm-assert string $property
     */
    private function assertPropertyNameIsString(mixed $property): void
    {
        if (!is_string($property)) {
            throw new \RuntimeException('Unable to map int key');
        }
    }

    private function mapSourceToArray(object|array $source): array|object
    {
        if (\is_object($source) && !$source instanceof \stdClass) {
            $sourceArray = $this->extractDataFrom($source);
        } else {
            $sourceArray = $source;
        }

        return $sourceArray;
    }

    private function convertValueToTargetType(\ReflectionClass $classRef, string $propertyName, mixed $value): mixed
    {
        $property = $classRef->getProperty($propertyName);
        $propertyType = $property->getType();

        if (null === $propertyType) {
            return $value;
        }

        if (null === $value && !$propertyType->allowsNull()) {
            throw new \RuntimeException('Unable to set null value to property '.$property->getName());
        }

        $valueType = \gettype($value);
        if ('object' === $valueType) {
            $valueType = $value::class;
        }

        if ($propertyType instanceof \ReflectionUnionType) {
            return $this->convertValueForUnionType($propertyType, $valueType, $value);
        }

        if ($propertyType instanceof \ReflectionNamedType) {
            return $this->convertValueForNamedType($propertyType, $valueType, $value);
        }

        throw new \RuntimeException('Unable to deserialize intersection types');
    }

    private function convertValueForUnionType(\ReflectionUnionType $propertyType, string $valueType, mixed $value): mixed
    {
        $propertyTypeNames = array_map(
            fn (\ReflectionNamedType $t): string => $t->getName(),
            $propertyType->getTypes()
        );

        if (in_array($valueType, $propertyTypeNames)) {
            return $value;
        }

        foreach ($propertyTypeNames as $propertyTypeName) {
            try {
                return self::cast($value, $propertyTypeName);
            } catch (\Exception $exception) {
            }
        }

        $propertyTypeNames = implode('|', $propertyTypeNames);
        throw new \RuntimeException("Unable to cast '$valueType' to '$propertyTypeNames'}");
    }

    private function convertValueForNamedType(\ReflectionNamedType $propertyType, string $valueType, mixed $value): mixed
    {
        $propertyTypeName = $propertyType->getName();
        if ($propertyTypeName === $valueType) {
            return $value;
        }

        $isClassOrInterface = class_exists($propertyTypeName) || interface_exists($propertyTypeName);
        if ($isClassOrInterface && is_a($value, $propertyTypeName)) {
            return $value;
        }

        try {
            return self::cast($value, $propertyTypeName);
        } catch (\Exception $exception) {
            throw new \RuntimeException("Unable to cast '$valueType' to '$propertyType': {$exception->getMessage()}");
        }
    }
}

<?php
declare(strict_types=1);

namespace Pfazzi\SimplexMapper;

class Mapper
{
    public function map(array|object $source, string $target, ?NameConverter $nameConverter = null): object
    {
        $ref = new \ReflectionClass($target);
        $instance = $ref->newInstanceWithoutConstructor();

        $assign = fn (string $prop, mixed $val): mixed => $this->$prop = $val;

        foreach ($ref->getDefaultProperties() as $property => $defaultValue) {
            $assign->call($instance, $property, $defaultValue);
        }

        if ($constructorRef = $ref->getConstructor()) {
            foreach ($constructorRef->getParameters() as $parameter) {
                if ($parameter->isPromoted() && $parameter->isDefaultValueAvailable()) {
                    $assign->call($instance, $parameter->getName(), $parameter->getDefaultValue());
                }
            }
        }

        if (\is_object($source) && !$source instanceof \stdClass) {
            $sourceArray = [];
            $sourceRef = new \ReflectionClass($source);
            foreach ($sourceRef->getProperties() as $reflectionProperty) {
                $name = $reflectionProperty->getName();
                $private = $reflectionProperty->isPrivate();
                $reflectionProperty->setAccessible(true);
                $value = $reflectionProperty->getValue($source);
                if ($private) {
                    $reflectionProperty->setAccessible(false);
                }
                $sourceArray[$name] = $value;
            }
        } else {
            $sourceArray = $source;
        }

        foreach ($sourceArray as $property => $value) {
            if ($nameConverter) {
                $property = $nameConverter->convert($property);
            }
            $propertyType = $ref->getProperty($property)->getType();
            if (null !== $value && null !== $propertyType) {
                $valueType = \gettype($value);
                if ('object' === $valueType) {
                    $valueType = $value::class;
                }

                $propertyTypeName = $propertyType->getName();
                if ($propertyTypeName !== $valueType) {
                    try {
                        $value = self::cast($value, $propertyTypeName);
                    } catch (\Exception $exception) {
                        throw new \RuntimeException("Unable to cast '$valueType' to '$propertyType': {$exception->getMessage()}");
                    }
                }
            }

            $assign->call($instance, $property, $value);
        }

        return $instance;
    }

    private function cast(mixed $value, string $type): mixed
    {
        if (\is_array($value) && class_exists($type)) {
            return $this->map($value, $type);
        }

        return match ($type) {
            'int' => (int) $value,
            'float' => (float) $value,
            default => throw new \RuntimeException("Unhandled type '$type'"),
        };
    }
}

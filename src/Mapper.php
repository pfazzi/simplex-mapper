<?php
declare(strict_types=1);

namespace Pfazzi\SimplexMapper;

/** @psalm-suppress MixedAssignment */
class Mapper
{
    /**
     * @psalm-param class-string $target
     */
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
                if (PHP_MAJOR_VERSION  < 8 || PHP_MINOR_VERSION === 0) {
                    $reflectionProperty->setAccessible(true);
                }
                $value = $reflectionProperty->getValue($source);
                $sourceArray[$name] = $value;
            }
        } else {
            $sourceArray = $source;
        }

        foreach ($sourceArray as $property => $value) {
            if (!is_string($property)) {
                throw new \RuntimeException('Unable to map int key');
            }
            if ($nameConverter) {
                $property = $nameConverter->convert($property);
            }
            $propertyType = $ref->getProperty($property)->getType();
            if (null !== $value && null !== $propertyType) {
                $valueType = \gettype($value);
                if ('object' === $valueType) {
                    $valueType = $value::class;
                }

                if ($propertyType instanceof \ReflectionUnionType) {
                    $propertyTypeNames = array_map(
                        fn (\ReflectionNamedType $t): string => $t->getName(),
                        $propertyType->getTypes()
                    );
                    if (!in_array($valueType, $propertyTypeNames)) {
                        foreach ($propertyTypeNames as $propertyTypeName) {
                            try {
                                $value = self::cast($value, $propertyTypeNames[0]);
                            } catch (\Exception $exception) {

                            }
                        }
                        $propertyTypeNames = implode('|', $propertyTypeNames);
                        throw new \RuntimeException("Unable to cast '$valueType' to '$propertyTypeNames'}");
                    }
                } elseif ($propertyType instanceof \ReflectionNamedType) {
                    $propertyTypeName = $propertyType->getName();
                    if ($propertyTypeName !== $valueType) {
                        try {
                            $value = self::cast($value, $propertyTypeName);
                        } catch (\Exception $exception) {
                            throw new \RuntimeException("Unable to cast '$valueType' to '$propertyType': {$exception->getMessage()}");
                        }
                    }
                } else {
                    throw new \RuntimeException('Unable to deserialize intersection types');
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

        if (settype($value, $type)){
            return $value;
        }

        throw new \RuntimeException("Unhandled type '$type'");
    }
}

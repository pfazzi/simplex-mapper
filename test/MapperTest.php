<?php
declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test;

use Pfazzi\SimplexMapper\Mapper;
use Pfazzi\SimplexMapper\SnakeToCamel;
use PHPUnit\Framework\TestCase;

class MapperTest extends TestCase
{
    private Mapper $mapper;

    protected function setUp(): void
    {
        $this->mapper = new Mapper();
    }

    public function test_it_maps_an_array_to_an_object(): void
    {
        $source = [
            'propString' => 'ciao',
            'propInt' => 123,
            'defaultProp' => 5,
        ];

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', 123);
        $expected->defaultProp = 5;

        self::assertEquals($expected, $mapped);
    }

    public function test_it_converts_string_to_int(): void
    {
        $source = [
            'propString' => 'ciao',
            'propInt' => '123',
        ];

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', 123);

        self::assertEquals($expected, $mapped);
    }

    public function test_it_converts_nested_objects(): void
    {
        $source = [
            'propString' => 'ciao',
            'propInt' => '123',
            'propMoney' => ['amount' => '100', 'currency' => 'EUR'],
        ];

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', 123, new Money(100, 'EUR'));

        self::assertEquals($expected, $mapped);
    }

    public function test_it_converts_nested_objects_with_object_vals(): void
    {
        $source = [
            'propString' => 'ciao',
            'propInt' => '123',
            'propMoney' => new Money(100, 'EUR'),
        ];

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', 123, new Money(100, 'EUR'));

        self::assertEquals($expected, $mapped);
    }

    public function test_it_assign_null_values(): void
    {
        $source = [
            'propString' => 'ciao',
            'propInt' => null,
            'propMoney' => new Money(100, 'EUR'),
        ];

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', null, new Money(100, 'EUR'));

        self::assertEquals($expected, $mapped);
    }

    public function test_it_assign_values_to_untyped_props(): void
    {
        $source = [
            'propString' => 'ciao',
            'propInt' => null,
            'untypedProp' => 'ahaha',
        ];

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', null);
        $expected->untypedProp = 'ahaha';

        self::assertEquals($expected, $mapped);
    }

    public function test_it_uses_name_converter(): void
    {
        $source = [
            'prop_string' => 'ciao',
            'prop_int' => null,
            'untyped_prop' => 'ahaha',
        ];

        $mapped = $this->mapper->map($source, DomainEntity::class, new SnakeToCamel());

        $expected = new DomainEntity('ciao', null);
        $expected->untypedProp = 'ahaha';

        self::assertEquals($expected, $mapped);
    }

    public function test_it_maps_from_std_objects(): void
    {
        $source = new \stdClass();
        $source->propString = 'ciao';
        $source->propInt = null;
        $source->untypedProp = 'ahaha';

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', null);
        $expected->untypedProp = 'ahaha';

        self::assertEquals($expected, $mapped);
    }

    public function test_it_maps_from_objects(): void
    {
        $source = new InfraEntity('ciao', 10);

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', 10);

        self::assertEquals($expected, $mapped);
    }

    public function test_it_maps_nested_objects_of_objects(): void
    {
        $source = new class() {
            public function __construct(
                private $propString = 'ciao',
                private $propInt = 10,
                private $untypedProp = 200,
                private $propMoney = ['amount' => 100, 'currency' => 'EUR'],
                private float $uninitializedTypedProp = 300,
            ) {
            }
        };

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', 10, new Money(100, 'EUR'));
        $expected->untypedProp = 200;
        $expected->uninitializedTypedProp = 300;

        self::assertEquals($expected, $mapped);
    }

    public function test_it_maps_nested_objects_of_objects_instantiated(): void
    {
        $source = new class(propMoney: new Money(100, 'EUR')) {
            public function __construct(
                private $propString = 'ciao',
                private $propInt = 10,
                private $untypedProp = 200,
                private $propMoney = ['amount' => 100, 'currency' => 'EUR'],
                private float $uninitializedTypedProp = 300,
            ) {
            }
        };

        $mapped = $this->mapper->map($source, DomainEntity::class);

        $expected = new DomainEntity('ciao', 10, new Money(100, 'EUR'));
        $expected->untypedProp = 200;
        $expected->uninitializedTypedProp = 300;

        self::assertEquals($expected, $mapped);
    }

    public function test_it_maps_bool_values(): void
    {
        $source = [
            'boolProp' => true
        ];

        $mapped = $this->mapper->map($source, ClassWithFields::class);

        self::assertEquals(new ClassWithFields(boolProp: true),$mapped);
    }

    public function test_it_maps_array_values(): void
    {
        $source = [
            'arrayProp' => ['ciao', 'hola' => 'sp']
        ];

        $mapped = $this->mapper->map($source, ClassWithFields::class);

        self::assertEquals(new ClassWithFields(arrayProp: ['ciao', 'hola' => 'sp']),$mapped);
    }

    public function test_it_maps_float_values(): void
    {
        $source = [
            'floatProp' => 5.7
        ];

        $mapped = $this->mapper->map($source, ClassWithFields::class);

        self::assertEquals(new ClassWithFields(floatProp: 5.7),$mapped);
    }

    public function test_it_maps_union_types(): void
    {
        $source = [
            'unionType' => 'ciao'
        ];

        $mapped = $this->mapper->map($source, ClassWithFields::class);

        self::assertEquals(new ClassWithFields(unionType: 'ciao'),$mapped);
    }

    public function test_it_is_unable_to_map_intersection_types(): void
    {
        if (PHP_MAJOR_VERSION === 8 && PHP_MINOR_VERSION < 1) {
            self::markTestSkipped();
        }

        self::expectExceptionObject(new \RuntimeException('Unable to deserialize intersection types'));

        $source = [
            'intersectionType' => new CarAndTruck()
        ];

        $this->mapper->map($source, ClassWithFields81::class);
    }

    public function test_it_trows_error_if_array_key_is_numeric(): void
    {
        self::expectExceptionObject(new \RuntimeException('Unable to map int key'));

        $source = [
            4 => 'ciao'
        ];

        $this->mapper->map($source, ClassWithFields::class);
    }
}

# SimplexMapper

A simple PHP library to map data from one object to another.

## Installation

Install SimplexMapper with composer

```bash
composer require pfazzi/simplex-mapper
```

## Usage

```php
$mapper = new \Pfazzi\SimplexMapper\Mapper();

// From Array 

$someClassInstance = $mapper->map(source: ['name' => 'patrick'], target: SomeClass::class);

// From stdClass 

$rawData = new \stdClass();
$rawData->name = 'Patrick';

$someClassInstance = $mapper->map(source: $rawData, target: SomeClass::class);

// From anonymous class

$rawData = new class {
    public function __construct(private string $name = 'Patrick') {}
};

$someClassInstance = $mapper->map(source: $rawData, target: SomeClass::class);

// From object

$rawData = new UserEntity('Patrick');

$someClassInstance = $mapper->map(source: $rawData, target: SomeClass::class);
```

## Use Cases

### Map database data to read models

You want to read data from supplier table using Doctrine DBAL Connection and instantiate a Supplier read model:

```php
class Supplier
{
    public function __construct(
        public int $id,
        public string $taxId,
        public bool $onConsignment,
    ) {
    }
}

$result = $connection->executeQuery(<<<'SQL'
    SELECT
        supplier.id,
        supplier.tax_id AS taxId,
        supplier.on_consignment AS onConsignment
    FROM supplier
    WHERE supplier.tax_id = :taxId
SQL, ['taxId' => $taxId]);

$data = $result->fetchAssociative();
```

Without SimplexMapper you have to write mapping code, such as:

```php
$readModel = new Supplier(
    (int) $data['id'],
    $data['taxId'],
    '1' == $data['onConsignment'],
);
```

With SimplexMapper all you need to write is just the following line:

```php
$readModel = $this->mapper->map($data, Supplier::class);
```
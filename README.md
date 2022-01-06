# SimplexMapper

A simple PHP library to map data from a source (object or array) to an object.

```php
$dbData = [
    'username' => 'pfazzi',
    'email_address' => 'pfazzi@test.com',
    'is_enabled' => '1',
];

$mapper = new \Pfazzi\SimplexMapper\Mapper();

$user = $mapper->map(
    source: $dbData, 
    target: User::class, 
    nameConverter: new SnakeToCamel()
);
```

## Installation

Install SimplexMapper with composer

```bash
composer require pfazzi/simplex-mapper
```

## Features

- Easy to use
- Zero configuration
- Zero dependencies
- Supports PHP 8 union types

## Usage

### Map

Maps the source data to a new instance of the target class

```php
$mapper = new \Pfazzi\SimplexMapper\Mapper();

// From Array 

$userEntity = $mapper->map(source: ['name' => 'patrick'], target: UserEntity::class);

// From stdClass 

$rawData = new \stdClass();
$rawData->name = 'Patrick';

$userEntity = $mapper->map(source: $rawData, target: UserEntity::class);

// From anonymous class

$rawData = new class {
    public function __construct(private string $name = 'Patrick') {}
};

$userEntity = $mapper->map(source: $rawData, target: UserEntity::class);

// From object

$rawData = new UserDto('Patrick');

$userEntity = $mapper->map(source: $rawData, target: UserEntity::class);
```

### Hydrate

Hydrates an object instance with data from the source:

```php
$mapper = new \Pfazzi\SimplexMapper\Mapper();

$userEntity = $entityManager->find(UserEntity::class, 1);

// From Array 

$mapper->hydrate(source: ['name' => 'patrick'], target: $userEntity);

// From stdClass 

$rawData = new \stdClass();
$rawData->name = 'Patrick';

$mapper->map(source: $rawData, target: $userEntity);

// From anonymous class

$rawData = new class {
    public function __construct(private string $name = 'Patrick') {}
};

$mapper->map(source: $rawData, target: $userEntity);

// From object

$rawData = new UserDto('Patrick');

$mapper->map(source: $rawData, target: $userEntity);
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
        supplier.is_enabled AS isEnabled
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
    '1' == $data['isEnabled'],
);
```

With SimplexMapper all you need to write is just the following line:

```php
$readModel = $this->mapper->map($data, Supplier::class);
```
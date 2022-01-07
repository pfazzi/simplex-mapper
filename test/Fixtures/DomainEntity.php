<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test\Fixtures;

class DomainEntity
{
    public int $defaultProp = 1;
    public $untypedProp = 10.5;
    public $uninitializedUntypedProp;
    public float $uninitializedTypedProp = 12.5;

    public function __construct(
        private string $propString,
        private ?int $propInt,
        public ?Money $propMoney = null,
    ) {
    }
}

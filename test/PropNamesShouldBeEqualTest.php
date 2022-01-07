<?php

declare(strict_types=1);

namespace Pfazzi\SimplexMapper\Test;

use Pfazzi\SimplexMapper\CamelToSnake;
use Pfazzi\SimplexMapper\NameConverterPair;
use Pfazzi\SimplexMapper\PropNamesShouldBeEqual;
use Pfazzi\SimplexMapper\SnakeToCamel;
use Pfazzi\SimplexMapper\Test\Fixtures\Validation\Supplier;
use Pfazzi\SimplexMapper\Test\Fixtures\Validation\User;
use Pfazzi\SimplexMapper\Test\Fixtures\Validation\UserDTO;
use Pfazzi\SimplexMapper\Test\Fixtures\Validation\UserDTOSnakeCase;
use PHPUnit\Framework\TestCase;

class PropNamesShouldBeEqualTest extends TestCase
{
    public function test_it_returns_true_if_porp_names_are_equal(): void
    {
        $constraint = new PropNamesShouldBeEqual();

        $result = $constraint->isSatisfied(UserDTO::class, User::class);

        self::assertTrue($result);
    }

    public function test_it_uses_name_converters(): void
    {
        $constraint = new PropNamesShouldBeEqual();

        $result = $constraint->isSatisfied(
            UserDTOSnakeCase::class,
            User::class,
            new NameConverterPair(new SnakeToCamel(), new CamelToSnake())
        );

        self::assertTrue($result);
    }

    public function test_it_returns_violations_for_missing_fields(): void
    {
        $constraint = new PropNamesShouldBeEqual();

        $result = $constraint->isSatisfied(UserDTO::class, Supplier::class);

        self::assertEquals([
            "Property 'username' missing in class Pfazzi\SimplexMapper\Test\Fixtures\Validation\Supplier",
            "Property 'emailAddress' missing in class Pfazzi\SimplexMapper\Test\Fixtures\Validation\Supplier",
            "Property 'isEnabled' missing in class Pfazzi\SimplexMapper\Test\Fixtures\Validation\Supplier",
            "Property 'companyName' missing in class Pfazzi\SimplexMapper\Test\Fixtures\Validation\UserDTO",
        ], $result);
    }

    public function test_it_works_even_with_array_source(): void
    {
        $constraint = new PropNamesShouldBeEqual();

        $source = [
            'username' => 'test',
            'emailAddress' => 'test@test.com',
            'isEnabled' => true,
        ];

        $result = $constraint->isSatisfied($source, Supplier::class);

        self::assertEquals([
            "Property 'username' missing in class Pfazzi\SimplexMapper\Test\Fixtures\Validation\Supplier",
            "Property 'emailAddress' missing in class Pfazzi\SimplexMapper\Test\Fixtures\Validation\Supplier",
            "Property 'isEnabled' missing in class Pfazzi\SimplexMapper\Test\Fixtures\Validation\Supplier",
            "Property 'companyName' missing in class Pfazzi\SimplexMapper\Test\Fixtures\Validation\UserDTO",
        ], $result);
    }
}

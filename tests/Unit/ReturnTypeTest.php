<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Analyzer\Class\Name;
use Tactix\Analyzer\Class\NameType;
use Tactix\Analyzer\Class\ReturnType;
use Tactix\Analyzer\Class\ReturnTypeKind;

class ReturnTypeTest extends TestCase
{
    #[Test]
    public function void_creates_void_return_type(): void
    {
        $returnType = ReturnType::void();

        self::assertSame(ReturnTypeKind::VOID, $returnType->kind);
        self::assertNull($returnType->typeName);
        self::assertEmpty($returnType->unionTypes);
        self::assertEmpty($returnType->intersectionTypes);
    }

    #[Test]
    public function unknown_creates_unknown_return_type(): void
    {
        $returnType = ReturnType::unknown();

        self::assertSame(ReturnTypeKind::UNKNOWN, $returnType->kind);
        self::assertNull($returnType->typeName);
        self::assertEmpty($returnType->unionTypes);
        self::assertEmpty($returnType->intersectionTypes);
    }

    #[Test]
    public function regular_creates_regular_return_type(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);

        self::assertSame(ReturnTypeKind::REGULAR, $returnType->kind);
        self::assertSame($typeName, $returnType->typeName);
        self::assertEmpty($returnType->unionTypes);
        self::assertEmpty($returnType->intersectionTypes);
    }

    #[Test]
    public function nullable_creates_nullable_return_type(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::nullable($typeName);

        self::assertSame(ReturnTypeKind::NULLABLE, $returnType->kind);
        self::assertSame($typeName, $returnType->typeName);
        self::assertEmpty($returnType->unionTypes);
        self::assertEmpty($returnType->intersectionTypes);
    }

    #[Test]
    public function collection_creates_collection_return_type(): void
    {
        $typeName = new Name('MyValueObject', NameType::UNKNOWN);
        $returnType = ReturnType::collection($typeName);

        self::assertSame(ReturnTypeKind::COLLECTION, $returnType->kind);
        self::assertSame($typeName, $returnType->typeName);
        self::assertEmpty($returnType->unionTypes);
        self::assertEmpty($returnType->intersectionTypes);
    }

    #[Test]
    public function generator_creates_generator_return_type(): void
    {
        $typeName = new Name('MyValueObject', NameType::UNKNOWN);
        $returnType = ReturnType::generator($typeName);

        self::assertSame(ReturnTypeKind::GENERATOR, $returnType->kind);
        self::assertSame($typeName, $returnType->typeName);
        self::assertEmpty($returnType->unionTypes);
        self::assertEmpty($returnType->intersectionTypes);
    }

    #[Test]
    public function union_creates_union_return_type(): void
    {
        $types = [
            new Name('string', NameType::UNKNOWN),
            new Name('int', NameType::UNKNOWN),
        ];
        $returnType = ReturnType::union($types);

        self::assertSame(ReturnTypeKind::UNION, $returnType->kind);
        self::assertNotNull($returnType->typeName);
        self::assertSame('string|int', (string) $returnType->typeName);
        self::assertSame($types, $returnType->unionTypes);
        self::assertEmpty($returnType->intersectionTypes);
    }

    #[Test]
    public function intersection_creates_intersection_return_type(): void
    {
        $types = [
            new Name('IteratorAggregate', NameType::UNKNOWN),
            new Name('Traversable', NameType::UNKNOWN),
        ];
        $returnType = ReturnType::intersection($types);

        self::assertSame(ReturnTypeKind::INTERSECTION, $returnType->kind);
        self::assertNotNull($returnType->typeName);
        self::assertSame('IteratorAggregate&Traversable', (string) $returnType->typeName);
        self::assertEmpty($returnType->unionTypes);
        self::assertSame($types, $returnType->intersectionTypes);
    }

    #[Test]
    public function is_void_returns_true_for_void_type(): void
    {
        $returnType = ReturnType::void();
        self::assertTrue($returnType->isVoid());
    }

    #[Test]
    public function is_void_returns_false_for_non_void_type(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);
        self::assertFalse($returnType->isVoid());
    }

    #[Test]
    public function is_nullable_returns_true_for_nullable_type(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::nullable($typeName);
        self::assertTrue($returnType->isNullable());
    }

    #[Test]
    public function is_nullable_returns_false_for_non_nullable_type(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);
        self::assertFalse($returnType->isNullable());
    }

    #[Test]
    public function is_collection_returns_true_for_collection_type(): void
    {
        $typeName = new Name('MyValueObject', NameType::UNKNOWN);
        $returnType = ReturnType::collection($typeName);
        self::assertTrue($returnType->isCollection());
    }

    #[Test]
    public function is_collection_returns_false_for_non_collection_type(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);
        self::assertFalse($returnType->isCollection());
    }

    #[Test]
    public function is_generator_returns_true_for_generator_type(): void
    {
        $typeName = new Name('MyValueObject', NameType::UNKNOWN);
        $returnType = ReturnType::generator($typeName);
        self::assertTrue($returnType->isGenerator());
    }

    #[Test]
    public function is_generator_returns_false_for_non_generator_type(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);
        self::assertFalse($returnType->isGenerator());
    }

    #[Test]
    public function is_array_returns_true_for_array_type_name(): void
    {
        $typeName = new Name('array', NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);
        self::assertTrue($returnType->isArray());
    }

    #[Test]
    public function is_array_returns_false_for_non_array_type_name(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);
        self::assertFalse($returnType->isArray());
    }

    #[Test]
    public function is_array_returns_false_when_no_type_name(): void
    {
        $returnType = ReturnType::void();
        self::assertFalse($returnType->isArray());
    }

    #[Test]
    public function is_union_returns_true_for_union_type(): void
    {
        $types = [
            new Name('string', NameType::UNKNOWN),
            new Name('int', NameType::UNKNOWN),
        ];
        $returnType = ReturnType::union($types);
        self::assertTrue($returnType->isUnion());
    }

    #[Test]
    public function is_union_returns_false_for_non_union_type(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);
        self::assertFalse($returnType->isUnion());
    }

    #[Test]
    public function is_intersection_returns_true_for_intersection_type(): void
    {
        $types = [
            new Name('IteratorAggregate', NameType::UNKNOWN),
            new Name('Traversable', NameType::UNKNOWN),
        ];
        $returnType = ReturnType::intersection($types);
        self::assertTrue($returnType->isIntersection());
    }

    #[Test]
    public function is_intersection_returns_false_for_non_intersection_type(): void
    {
        $typeName = new Name('string', NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);
        self::assertFalse($returnType->isIntersection());
    }

    #[Test]
    #[DataProvider('provideCanBeIgnoredTypes')]
    public function can_be_ignored(string $type, bool $expected): void
    {
        $typeName = new Name($type, NameType::UNKNOWN);
        $returnType = ReturnType::regular($typeName);
        self::assertSame($expected, $returnType->canBeIgnored());
    }

    #[Test]
    public function can_be_ignored_returns_true_when_no_type_name(): void
    {
        $returnType = ReturnType::void();
        self::assertTrue($returnType->canBeIgnored());
    }

    /**
     * @return array<int, array{string, bool}>
     */
    public static function provideCanBeIgnoredTypes(): array
    {
        return [
            ['self', true],
            ['unknown', true],
            ['void', true],
            ['string', false],
            ['MyClass', false],
            ['array', false],
        ];
    }

    #[Test]
    #[DataProvider('provideToStringFormats')]
    public function to_string_returns_correct_format(ReturnType $returnType, string $expected): void
    {
        self::assertSame($expected, (string) $returnType);
    }

    /**
     * @return array<int, array{ReturnType, string}>
     */
    public static function provideToStringFormats(): array
    {
        return [
            [ReturnType::void(), 'void'],
            [ReturnType::unknown(), 'unknown'],
            [ReturnType::regular(new Name('string', NameType::UNKNOWN)), 'string'],
            [ReturnType::nullable(new Name('string', NameType::UNKNOWN)), '?string'],
            [
                ReturnType::union([
                    new Name('string', NameType::UNKNOWN),
                    new Name('int', NameType::UNKNOWN),
                ]),
                'string|int',
            ],
            [
                ReturnType::intersection([
                    new Name('IteratorAggregate', NameType::UNKNOWN),
                    new Name('Traversable', NameType::UNKNOWN),
                ]),
                'IteratorAggregate&Traversable',
            ],
            [ReturnType::collection(new Name('MyValueObject', NameType::UNKNOWN)), 'MyValueObject[]'],
            [ReturnType::generator(new Name('MyValueObject', NameType::UNKNOWN)), 'MyValueObject'],
        ];
    }
}

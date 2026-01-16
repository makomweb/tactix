<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Analyzer\Edge;
use Tactix\Analyzer\Relation;
use Tactix\Analyzer\YieldRelations;
use Tactix\Tests\Data\MyConsumesArray;
use Tactix\Tests\Data\MyConsumesArrayTemplate;
use Tactix\Tests\Data\MyConsumesIterable;
use Tactix\Tests\Data\MyConsumesList;
use Tactix\Tests\Data\MyEntity;
use Tactix\Tests\Data\MyProducesArray;
use Tactix\Tests\Data\MyProducesArrayTemplate;
use Tactix\Tests\Data\MyProducesGenerator;
use Tactix\Tests\Data\MyProducesIterable;
use Tactix\Tests\Data\MyProducesList;
use Tactix\Tests\Data\MyValueObject;

class CollectionTest extends TestCase
{
    /**
     * @param class-string   $fromClassName
     * @param class-string[] $toClassNames
     */
    #[Test]
    #[DataProvider('provideConsumingClasses')]
    public function consumes_collection(string $fromClassName, Edge $expectedEdge, array $toClassNames): void
    {
        $relations = iterator_to_array(YieldRelations::fromClassName($fromClassName));

        foreach ($relations as $relation) {
            self::assertInstanceOf(Relation::class, $relation);
            self::assertSame($fromClassName, $relation->from->fqcn);
            self::assertEquals($expectedEdge, $relation->edge);
            self::assertTrue(in_array($relation->to->fqcn, $toClassNames, strict: true));
        }
    }

    /**
     * @param class-string   $fromClassName
     * @param class-string[] $toClassNames
     */
    #[Test]
    #[DataProvider('provideProducingClasses')]
    public function produces_collection(string $fromClassName, Edge $expectedEdge, array $toClassNames): void
    {
        $relations = iterator_to_array(YieldRelations::fromClassName($fromClassName));

        foreach ($relations as $relation) {
            self::assertInstanceOf(Relation::class, $relation);
            self::assertSame($fromClassName, $relation->from->fqcn);
            self::assertSame($expectedEdge, $relation->edge);
            self::assertTrue(in_array($relation->to->fqcn, $toClassNames, strict: true));
        }
    }

    /**
     * @return array<int, array{class-string, Edge, class-string[]}>
     */
    public static function provideConsumingClasses(): array
    {
        return [
            [MyConsumesArray::class, Edge::CONSUMES, [MyValueObject::class]],
            [MyConsumesArrayTemplate::class, Edge::CONSUMES, [MyValueObject::class]],
            [MyConsumesList::class, Edge::CONSUMES, [MyValueObject::class]],
            [MyConsumesIterable::class, Edge::CONSUMES, [MyValueObject::class]],
        ];
    }

    /**
     * @return array<int, array{class-string, Edge, class-string[]}>
     */
    public static function provideProducingClasses(): array
    {
        return [
            [MyProducesArray::class, Edge::PRODUCES, [MyValueObject::class]],
            [MyProducesArrayTemplate::class, Edge::PRODUCES, [MyValueObject::class]],
            [MyProducesList::class, Edge::PRODUCES, [MyValueObject::class]],
            [MyProducesIterable::class, Edge::PRODUCES, [MyValueObject::class]],
            // [MyProducesGenerator::class, Edge::PRODUCES, [MyValueObject::class, MyEntity::class]],
        ];
    }
}

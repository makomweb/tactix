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
use Tactix\Tests\Data\MyValueObject;

class CollectionTest extends TestCase
{
    /**
     * @param class-string $fromClassName
     */
    #[Test]
    #[DataProvider('provideTestData')]
    public function consumes_array(string $fromClassName): void
    {
        $relations = iterator_to_array(YieldRelations::fromClassName($fromClassName));

        /** @var Relation $relation */
        $relation = $relations[0];

        self::assertSame($fromClassName, $relation->from->fqcn);
        self::assertEquals($relation->edge, Edge::CONSUMES);
        self::assertSame(MyValueObject::class, $relation->to->fqcn);
    }

    /**
     * @return array<int, string[]>
     */
    public static function provideTestData(): array
    {
        return [
            [MyConsumesArray::class],
            [MyConsumesArrayTemplate::class],
            [MyConsumesList::class],
            [MyConsumesIterable::class],
        ];
    }
}

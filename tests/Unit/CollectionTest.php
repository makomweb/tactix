<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Analyzer\Edge;
use Tactix\Analyzer\Relation;
use Tactix\Analyzer\YieldRelations;
use Tactix\Tests\Data\MyCollection;
use Tactix\Tests\Data\MyValueObject;

class CollectionTest extends TestCase
{
    #[Test]
    public function consume_collection_should_return_type(): void
    {
        $relations = iterator_to_array(YieldRelations::fromClassName(MyCollection::class));

        $first = $relations[0];
        assert($first instanceof Relation);

        self::assertEquals($first->edge, Edge::CONSUMES);
        self::assertSame(MyValueObject::class, $first->to->fqcn);
    }
}

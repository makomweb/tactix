<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final readonly class MyConsumesIterable
{
    /**
     * @param iterable<MyValueObject> $collection
     */
    public function consume(iterable $collection): void
    {
        throw new \Exception('Not yet implemented!');
    }
}

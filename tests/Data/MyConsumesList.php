<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final readonly class MyConsumesList
{
    /**
     * @param list<MyValueObject> $collection
     */
    public function consume(array $collection): void
    {
        throw new \Exception('Not yet implemented!');
    }
}

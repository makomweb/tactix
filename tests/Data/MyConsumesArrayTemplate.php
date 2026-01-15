<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final readonly class MyConsumesArrayTemplate
{
    /**
     * @param array<MyValueObject> $collection
     */
    public function consumes(array $collection): void
    {
        throw new \Exception('Not yet implemented!');
    }
}

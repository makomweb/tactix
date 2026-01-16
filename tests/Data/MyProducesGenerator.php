<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use PHPMolecules\DDD\Attribute\AggregateRoot;

#[AggregateRoot]
final readonly class MyProducesGenerator
{
    /**
     * @return \Generator<MyValueObject>
     */
    public function produceValueObjects(): \Generator
    {
        throw new \Exception('Not yet implemented!');
    }

    /**
     * @return \Generator<MyEntity>
     */
    public function produceEntities(): \Generator
    {
        throw new \Exception('Not yet implemented!');
    }
}

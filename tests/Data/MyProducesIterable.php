<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final readonly class MyProducesIterable
{
    /**
     * @return iterable<MyValueObject>
     */
    public function produce(): iterable
    {
        throw new \Exception('Not yet implemented!');
    }
}

<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use Exception;
use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final readonly class MyProducesIterable
{
    /**
     * @return iterable<MyValueObject>
     */
    public function produce(): \Generator
    {
        throw new Exception('Not yet implemented!');
    }
}

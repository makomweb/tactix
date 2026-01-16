<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use Exception;
use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final readonly class MyProducesList
{
    /**
     * @return list<MyValueObject>
     */
    public function produce(): array
    {
        throw new Exception('Not yet implemented!');
    }
}

<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
class MyValueObject
{
    public function __construct(public readonly MyEntity $entity)
    {
    }
}

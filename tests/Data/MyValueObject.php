<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use Tactix\Attribute\ValueObject;

#[ValueObject]
class MyValueObject
{
    public function __construct(public readonly MyEntity $entity)
    {
    }
}

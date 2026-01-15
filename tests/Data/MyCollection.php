<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

use PHPMolecules\DDD\Attribute\ValueObject;

#[ValueObject]
final readonly class MyCollection
{
    /**
     * @param MyValueObject[] $collection
     */
    public function __construct(public array $collection)
    {
    }
}

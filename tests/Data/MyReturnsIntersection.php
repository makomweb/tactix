<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

class MyReturnsIntersection
{
    public function example(): \IteratorAggregate&\Traversable
    {
        return new \ArrayObject();
    }
}

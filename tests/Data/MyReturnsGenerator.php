<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

class MyReturnsGenerator
{
    /**
     * @return \Generator<MyValueObject>
     */
    public function example()
    {
        yield new MyValueObject(new MyEntity());
    }
}

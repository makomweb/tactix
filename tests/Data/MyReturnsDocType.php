<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

class MyReturnsDocType
{
    /**
     * @return MyValueObject
     */
    public function example()
    {
        return new MyValueObject(new MyEntity());
    }
}

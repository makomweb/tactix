<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

class MyReturnsUnion
{
    public function example(): string|int
    {
        return '';
    }
}

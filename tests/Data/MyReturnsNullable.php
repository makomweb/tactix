<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

class MyReturnsNullable
{
    public function example(): ?string
    {
        return null;
    }
}

<?php

declare(strict_types=1);

namespace Tactix\Tests\Data;

final readonly class MyConsumesTrait
{
    use MyTrait;

    public function __construct()
    {
        $this->value = 23;
    }
}

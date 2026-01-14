<?php

declare(strict_types=1);

namespace Tactix;

final readonly class Violation implements \Stringable
{
    public function __construct(public string $message)
    {
    }

    public function __toString(): string
    {
        return $this->message;
    }
}

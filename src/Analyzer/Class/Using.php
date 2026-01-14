<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

final readonly class Using
{
    public function __construct(
        public string $name,
        public string $fqcn,
    ) {
    }
}

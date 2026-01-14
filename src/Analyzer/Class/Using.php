<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

final readonly class Using
{
    /**
     * @param class-string $fqcn
     */
    public function __construct(
        public string $name,
        public string $fqcn,
    ) {
    }
}

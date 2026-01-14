<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

final readonly class Argument implements \Stringable
{
    public function __construct(
        public string $name,
        public Name $type,
        public bool $isNullable,
        public bool $isArray,
    ) {
    }

    public function __toString(): string
    {
        return sprintf(
            '%s: %s%s%s',
            $this->name,
            $this->isNullable ? '?' : '',
            $this->type,
            $this->isArray ? '[]' : ''
        );
    }
}

<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

final readonly class MyNode implements \Stringable
{
    public function __construct(public string $fqcn)
    {
    }

    public function getName(): string
    {
        $parts = explode('\\', $this->fqcn);

        return (string) end($parts);
    }

    public function getFQCN(string $separator = '\\'): string
    {
        $parts = explode('\\', $this->fqcn);

        return implode($separator, $parts);
    }

    public function equals(self $other): bool
    {
        return $this->fqcn === $other->fqcn;
    }

    public function __toString(): string
    {
        return $this->getName();
    }
}

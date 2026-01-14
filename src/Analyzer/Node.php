<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Name;

final readonly class Node implements \Stringable
{
    /**
     * @param class-string $fqcn
     */
    private function __construct(public string $fqcn)
    {
    }

    public static function fromName(Name $name): self
    {
        return self::fromString((string) $name);
    }

    public static function fromString(string $name): self
    {
        /** @var class-string $value */
        $value = (string) $name;

        return new self($value);
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

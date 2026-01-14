<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

final readonly class Name implements \Stringable
{
    /** @var string[] */
    public const array STANDARD_NAMES = [
        'null',
        'bool',
        'int',
        'float',
        'class-string',
        'string',
        'array',
        'object',
        'callable',
        'resource',
        'mixed',
        'self',
        'DateTimeImmutable',
        'DateTime',
        'Throwable',
        'Stringable',
        'array<string,mixed>',
        'array<string,string>',
        'float|int',
        'string|Stringable',
    ];

    public function __construct(
        public string $name,
        public NameType $type,
    ) {
    }

    public function isStandardName(): bool
    {
        return in_array($this->name, self::STANDARD_NAMES, true);
    }

    public function isArray(): bool
    {
        return 'array' === $this->name;
    }

    public function isGenerator(): bool
    {
        return 'Generator' === $this->name || '\\Generator' === $this->name;
    }

    public function __toString(): string
    {
        return $this->name;
    }
}

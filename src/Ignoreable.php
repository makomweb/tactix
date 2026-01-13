<?php

declare(strict_types=1);

namespace Tactix;

final readonly class Ignoreable
{
    /** @var string[] */
    public const array TYPES = [
        'string',
        'class-string',
        'self',
        'callable',
        'mixed',
        'bool',
        'int',
        'float',
        'DateTimeImmutable',
        'DateTime',
        'Throwable',
        'Exception',
        'Stringable',
        'array',
        'float|int',
        'string|Stringable',
        'array<string,mixed>',
        'array<string,string>',
        'Generator',
        'object',
    ];
}

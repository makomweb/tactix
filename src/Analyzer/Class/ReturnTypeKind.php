<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

enum ReturnTypeKind: string
{
    case UNKNOWN = 'unknown';
    case VOID = 'void';
    case REGULAR = 'regular';
    case NULLABLE = 'nullable';
    case COLLECTION = 'collection';
    case UNION = 'union';
    case INTERSECTION = 'intersection';
    case HASH = 'hash';
    case GENERATOR = 'generator';
}

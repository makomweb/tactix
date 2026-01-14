<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

enum NameType: string
{
    case UNKNOWN = 'unknown';
    case QUALIFIED = 'qualified';
    case UNQUALIFIED = 'unqualified';
    case FULLYQUALIFIED = 'fully qualified';
    case RELATIVE = 'relative';
    case SPECIAL_CLASS_NAME = 'special';
}

<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

enum MyEdge: string
{
    case IMPLEMENTS = 'implements';
    case EXTENDS = 'extends';
    case CONSUMES = 'consumes';
    case THROWS = 'throws';
    case PRODUCES = 'produces';
}

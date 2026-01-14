<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Name as AnalyzerClassName;
use Tactix\Analyzer\Class\NameType;

final readonly class MyNodeFactory
{
    public static function createNode(Result $result, AnalyzerClassName $name): MyNode
    {
        if ($name->isStandardName()) {
            return new MyNode((string) $name);
        }

        return match ($name->type) {
            NameType::UNKNOWN, NameType::QUALIFIED, NameType::UNQUALIFIED => self::fromUsing($result, (string) $name),
            NameType::FULLYQUALIFIED => new MyNode((string) $name),
            default => new MyNode((string) $name),
        };
    }

    private static function fromUsing(Result $result, string $name): MyNode
    {
        foreach ($result->usings as $using) {
            if ($using->name === $name) {
                return new MyNode($using->fqcn);
            }
        }

        if (null === $result->namespace || '' === $result->namespace) {
            return new MyNode($name);
        }

        /*
         * We assume the name is in the same namespace
         * like the current class, though this might be wrong.
         */
        return new MyNode($result->namespace.'\\'.$name);
    }
}

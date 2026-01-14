<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Name;
use Tactix\Analyzer\Class\NameType;

final readonly class MyNodeFactory
{
    public static function createNode(SourceCodeItem $item, Name $name): MyNode
    {
        if ($name->isStandardName()) {
            return MyNode::fromName($name);
        }

        return match ($name->type) {
            NameType::UNKNOWN, NameType::QUALIFIED, NameType::UNQUALIFIED => self::fromUsing($item, $name),
            NameType::FULLYQUALIFIED => MyNode::fromName($name),
            default => MyNode::fromName($name),
        };
    }

    private static function fromUsing(SourceCodeItem $item, Name $name): MyNode
    {
        foreach ($item->usings as $using) {
            if ($using->name === $name->value) {
                return MyNode::fromString($using->fqcn);
            }
        }

        if (null === $item->namespace || '' === $item->namespace) {
            return MyNode::fromName($name);
        }

        /*
         * We assume the name is in the same namespace
         * like the current class, though this might be wrong.
         */
        return MyNode::fromString($item->namespace.'\\'.$name);
    }
}

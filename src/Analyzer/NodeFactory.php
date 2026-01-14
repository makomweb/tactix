<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Name;
use Tactix\Analyzer\Class\NameType;

final readonly class NodeFactory
{
    public static function createNode(SourceCodeItem $item, Name $name): Node
    {
        if ($name->isStandardName()) {
            return Node::fromName($name);
        }

        return match ($name->type) {
            NameType::UNKNOWN, NameType::QUALIFIED, NameType::UNQUALIFIED => self::fromUsing($item, $name),
            NameType::FULLYQUALIFIED => Node::fromName($name),
            default => Node::fromName($name),
        };
    }

    private static function fromUsing(SourceCodeItem $item, Name $name): Node
    {
        foreach ($item->usings as $using) {
            if ($using->name === $name->value) {
                return Node::fromString($using->fqcn);
            }
        }

        if (null === $item->namespace || '' === $item->namespace) {
            return Node::fromName($name);
        }

        /*
         * We assume the name is in the same namespace
         * like the current class, though this might be wrong.
         */
        return Node::fromString($item->namespace.'\\'.$name);
    }
}

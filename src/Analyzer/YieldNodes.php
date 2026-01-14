<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Method;
use Tactix\Analyzer\Class\Name;

final readonly class YieldNodes
{
    /** @return \Generator<MyNode> */
    public static function from(string $folder): \Generator
    {
        foreach (SourceCodeItem::yieldFromFolder($folder) as $item) {
            yield from self::yieldNodes($item);
        }
    }

    /** @return \Generator<MyNode> */
    private static function yieldNodes(SourceCodeItem $item): \Generator
    {
        yield MyNode::fromString($item->fullQualifiedClassName);

        foreach ($item->implements as $implements) {
            yield MyNodeFactory::createNode($item, $implements);
        }

        foreach ($item->extends as $extends) {
            yield MyNodeFactory::createNode($item, $extends);
        }

        foreach ($item->methods as $method) {
            foreach (self::fromMethod($item, $method) as $node) {
                yield $node;
            }
        }
    }

    /** @return \Generator<MyNode> */
    private static function fromMethod(SourceCodeItem $item, Method $method): \Generator
    {
        foreach ($method->arguments as $argument) {
            yield MyNodeFactory::createNode($item, $argument->type);
        }

        if (!$method->returnType->canBeIgnored()) {
            assert($method->returnType->typeName instanceof Name);
            yield MyNodeFactory::createNode($item, $method->returnType->typeName);
        }

        foreach ($method->throws as $exception) {
            yield MyNodeFactory::createNode($item, $exception);
        }
    }
}

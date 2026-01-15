<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Method;
use Tactix\Analyzer\Class\Name;

final readonly class YieldNodes
{
    /** @return \Generator<Node> */
    public static function from(string $folder): \Generator
    {
        foreach (SourceCodeItemFactory::yieldFromFolder($folder) as $item) {
            foreach (self::yieldNodes($item) as $node) {
                yield $node;
            }
        }
    }

    /** @return \Generator<Node> */
    private static function yieldNodes(SourceCodeItem $item): \Generator
    {
        yield Node::fromString($item->fullQualifiedClassName);

        foreach ($item->implements as $implements) {
            yield NodeFactory::createNode($item, $implements);
        }

        foreach ($item->extends as $extends) {
            yield NodeFactory::createNode($item, $extends);
        }

        foreach ($item->methods as $method) {
            foreach (self::fromMethod($item, $method) as $node) {
                yield $node;
            }
        }
    }

    /** @return \Generator<Node> */
    private static function fromMethod(SourceCodeItem $item, Method $method): \Generator
    {
        foreach ($method->arguments as $argument) {
            yield NodeFactory::createNode($item, $argument->type);
        }

        if (!$method->returnType->canBeIgnored()) {
            assert($method->returnType->typeName instanceof Name);
            yield NodeFactory::createNode($item, $method->returnType->typeName);
        }

        foreach ($method->throws as $exception) {
            yield NodeFactory::createNode($item, $exception);
        }
    }
}

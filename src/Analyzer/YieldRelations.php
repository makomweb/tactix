<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Method;
use Tactix\Analyzer\Class\Name;

final readonly class YieldRelations
{
    /**
     * @return \Generator<MyRelation>
     */
    public static function from(string $folder): \Generator
    {
        foreach (SourceCodeItem::yieldFromFolder($folder) as $item) {
            yield from self::yieldRelations($item);
        }
    }

    /**
     * @return \Generator<MyRelation>
     */
    public static function yieldRelations(SourceCodeItem $item): \Generator
    {
        yield from self::fromTargets($item, $item->implements, MyEdge::IMPLEMENTS);
        yield from self::fromTargets($item, $item->extends, MyEdge::EXTENDS);

        foreach ($item->methods as $method) {
            foreach (self::fromMethod($item, $method) as $relation) {
                yield $relation;
            }
        }
    }

    /**
     * @param Name[] $targets
     *
     * @return \Generator<MyRelation>
     */
    private static function fromTargets(SourceCodeItem $item, array $targets, MyEdge $edge): \Generator
    {
        foreach ($targets as $target) {
            yield MyRelation::create(
                MyNode::fromString($item->fullQualifiedClassName),
                $edge,
                MyNodeFactory::createNode($item, $target)
            );
        }
    }

    /**
     * @return \Generator<MyRelation>
     */
    private static function fromMethod(SourceCodeItem $item, Method $method): \Generator
    {
        foreach ($method->arguments as $argument) {
            yield MyRelation::create(
                MyNode::fromString($item->fullQualifiedClassName),
                MyEdge::CONSUMES,
                MyNodeFactory::createNode($item, $argument->type)
            );
        }

        if (!$method->returnType->canBeIgnored()) {
            assert($method->returnType->typeName instanceof Name);
            yield MyRelation::create(
                MyNode::fromString($item->fullQualifiedClassName),
                MyEdge::PRODUCES,
                MyNodeFactory::createNode($item, $method->returnType->typeName)
            );
        }

        foreach ($method->throws as $exception) {
            yield MyRelation::create(
                MyNode::fromString($item->fullQualifiedClassName),
                MyEdge::THROWS,
                MyNodeFactory::createNode($item, $exception)
            );
        }
    }
}

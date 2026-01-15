<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Method;
use Tactix\Analyzer\Class\Name;

final readonly class YieldRelations
{
    /**
     * @return \Generator<Relation>
     */
    public static function fromFolder(string $folder): \Generator
    {
        foreach (SourceCodeItemFactory::yieldFromFolder($folder) as $item) {
            yield from self::fromSourceCodeItem($item);
        }
    }

    /**
     * @param class-string $className
     *
     * @return \Generator<Relation>
     */
    public static function fromClassName(string $className): \Generator
    {
        $item = SourceCodeItemFactory::fromClassName($className);
        yield from self::fromSourceCodeItem($item);
    }

    /**
     * @return \Generator<Relation>
     */
    public static function fromSourceCodeItem(SourceCodeItem $item): \Generator
    {
        yield from self::fromTargets($item, $item->implements, Edge::IMPLEMENTS);
        yield from self::fromTargets($item, $item->extends, Edge::EXTENDS);

        foreach ($item->methods as $method) {
            foreach (self::fromMethod($item, $method) as $relation) {
                yield $relation;
            }
        }
    }

    /**
     * @param Name[] $targets
     *
     * @return \Generator<Relation>
     */
    private static function fromTargets(SourceCodeItem $item, array $targets, Edge $edge): \Generator
    {
        foreach ($targets as $target) {
            yield Relation::create(
                Node::fromString($item->fullQualifiedClassName),
                $edge,
                NodeFactory::createNode($item, $target)
            );
        }
    }

    /**
     * @return \Generator<Relation>
     */
    private static function fromMethod(SourceCodeItem $item, Method $method): \Generator
    {
        foreach ($method->arguments as $argument) {
            yield Relation::create(
                Node::fromString($item->fullQualifiedClassName),
                Edge::CONSUMES,
                NodeFactory::createNode($item, $argument->type)
            );
        }

        if (!$method->returnType->canBeIgnored()) {
            assert($method->returnType->typeName instanceof Name);
            yield Relation::create(
                Node::fromString($item->fullQualifiedClassName),
                Edge::PRODUCES,
                NodeFactory::createNode($item, $method->returnType->typeName)
            );
        }

        foreach ($method->throws as $exception) {
            yield Relation::create(
                Node::fromString($item->fullQualifiedClassName),
                Edge::THROWS,
                NodeFactory::createNode($item, $exception)
            );
        }
    }
}

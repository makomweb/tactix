<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Method;
use Tactix\Analyzer\Class\Name;
use Tactix\Assert\Assert;

final readonly class YieldRelations
{
    /**
     * @return \Generator<MyRelation>
     */
    public static function from(string $folder): \Generator
    {
        foreach (self::fromFolder($folder) as $result) {
            yield from self::fromResult($result);
        }
    }

    /**
     * @return \Generator<MyRelation>
     */
    public static function fromResult(Result $result): \Generator
    {
        yield from self::fromTargets($result, $result->implements, MyEdge::IMPLEMENTS);
        yield from self::fromTargets($result, $result->extends, MyEdge::EXTENDS);

        foreach ($result->methods as $method) {
            foreach (self::fromMethod($result, $method) as $relation) {
                yield $relation;
            }
        }
    }

    /**
     * @param Name[] $targets
     *
     * @return \Generator<MyRelation>
     */
    private static function fromTargets(Result $result, array $targets, MyEdge $edge): \Generator
    {
        foreach ($targets as $target) {
            yield MyRelation::create(
                new MyNode($result->fullQualifiedClassName),
                $edge,
                MyNodeFactory::creatNode($result, $target)
            );
        }
    }

    /**
     * @return \Generator<MyRelation>
     */
    private static function fromMethod(Result $result, Method $method): \Generator
    {
        foreach ($method->arguments as $argument) {
            yield MyRelation::create(
                new MyNode($result->fullQualifiedClassName),
                MyEdge::CONSUMES,
                MyNodeFactory::creatNode($result, $argument->type)
            );
        }

        if (!$method->returnType->canBeIgnored()) {
            assert($method->returnType->typeName instanceof Name);
            yield MyRelation::create(
                new MyNode($result->fullQualifiedClassName),
                MyEdge::PRODUCES,
                MyNodeFactory::creatNode($result, $method->returnType->typeName)
            );
        }

        foreach ($method->throws as $exception) {
            yield MyRelation::create(
                new MyNode($result->fullQualifiedClassName),
                MyEdge::THROWS,
                MyNodeFactory::creatNode($result, $exception)
            );
        }
    }

    /**
     * @return \Generator<Result>
     */
    private static function fromFolder(string $folder): \Generator
    {
        Assert::that(is_dir($folder), "$folder is not a directory!");

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder)) as $fileInfo) {
            assert($fileInfo instanceof \SplFileInfo);
            if ('php' === $fileInfo->getExtension()) {
                $filePath = $fileInfo->getRealPath();
                assert(is_string($filePath));
                $analyzer = PhpFileAnalyzer::fromFile($filePath);
                yield $analyzer->getResult();
            }
        }
    }
}

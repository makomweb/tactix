<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Tactix\Assert\Assert;

final class SourceCodeItemFactory
{
    /**
     * Create a SourceCodeItem from a class-string (auto-detect file path).
     *
     * @param class-string $className
     */
    public static function fromClassName(string $className): SourceCodeItem
    {
        $reflection = new \ReflectionClass($className);
        $filePath = $reflection->getFileName();
        assert(is_string($filePath) && !empty($filePath));

        return self::fromFilePath($className, $filePath);
    }

    /**
     * @return \Generator<SourceCodeItem>
     */
    public static function yieldFromFolder(string $folder): \Generator
    {
        Assert::that(is_dir($folder), "$folder is not a directory!");

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder)) as $fileInfo) {
            assert($fileInfo instanceof \SplFileInfo);
            if ('php' === $fileInfo->getExtension()) {
                $filePath = $fileInfo->getRealPath();
                assert(is_string($filePath) && !empty($filePath));
                $analyzer = PhpFileAnalyzer::fromFile($filePath);
                yield self::fromFilePath($analyzer->className, $analyzer->filePath);
            }
        }
    }

    /**
     * Create a SourceCodeItem from class-string and file path.
     *
     * @param class-string     $className
     * @param non-empty-string $filePath
     */
    public static function fromFilePath(string $className, string $filePath): SourceCodeItem
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $stmts = $parser->parse(self::getContent($filePath));
        $traverser = new NodeTraverser();
        $analyzer = ClassAnalyzer::create($className);
        $traverser->addVisitor($analyzer);

        assert(!is_null($stmts));
        $traverser->traverse($stmts);

        return $analyzer->item;
    }

    private static function getContent(string $filePath): string
    {
        $fileContent = file_get_contents($filePath);
        assert(is_string($fileContent));

        return $fileContent;
    }
}

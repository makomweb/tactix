<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

final readonly class ClassNameFactory
{
    /**
     * Extract the FQCN for the specified file path.
     *
     * @return class-string
     */
    public static function createFrom(string $filePath): string
    {
        $fileContent = file_get_contents($filePath);
        assert(is_string($fileContent));

        $parser = (new ParserFactory())->createForNewestSupportedVersion();

        try {
            $ast = $parser->parse($fileContent);
        } catch (\Throwable $ex) {
            throw new \RuntimeException("Error while parsing $filePath: ".$ex->getMessage());
        }

        assert(!is_null($ast));

        $analyzer = new ClassNameAnalyzer($filePath);
        $traverser = new NodeTraverser();
        $traverser->addVisitor($analyzer);

        /* @var array<\PhpParser\Node\Stmt> $ast */
        $traverser->traverse($ast);

        return $analyzer->getFQCN();
    }
}

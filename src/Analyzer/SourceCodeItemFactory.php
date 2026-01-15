<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;

final class SourceCodeItemFactory
{
    /**
     * @param class-string     $className
     * @param non-empty-string $filePath
     */
    public function getSourceCodeItem(string $className, string $filePath): SourceCodeItem
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $stmts = $parser->parse($this->getContent($filePath));
        $traverser = new NodeTraverser();
        $analyzer = ClassAnalyzer::create($className);
        $traverser->addVisitor($analyzer);

        assert(!is_null($stmts));
        $traverser->traverse($stmts);

        return $analyzer->item;
    }

    private function getContent(string $filePath): string
    {
        $fileContent = file_get_contents($filePath);
        assert(is_string($fileContent));

        return $fileContent;
    }
}

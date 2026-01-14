<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use Tactix\Analyzer\Class\ClassNameFactory;
use Tactix\Assert\Assert;

final class PhpFileAnalyzer
{
    /**
     * @param class-string $className
     */
    private function __construct(
        public readonly string $filePath,
        public readonly string $className,
    ) {
    }

    public static function fromFile(string $filePath): self
    {
        Assert::that(file_exists($filePath), "No such file: $filePath!");

        $className = ClassNameFactory::createFrom($filePath);

        return new self($filePath, $className);
    }

    public function getSourceCodeItem(): SourceCodeItem
    {
        $parser = (new ParserFactory())->createForNewestSupportedVersion();
        $stmts = $parser->parse($this->getContent());
        $traverser = new NodeTraverser();
        $analyzer = ClassAnalyzer::create($this->className);
        $traverser->addVisitor($analyzer);

        assert(!is_null($stmts));
        $traverser->traverse($stmts);

        return $analyzer->item;
    }

    private function getContent(): string
    {
        $fileContent = file_get_contents($this->filePath);
        assert(is_string($fileContent));

        return $fileContent;
    }
}

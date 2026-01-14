<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

use PhpParser\Node;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\Enum_;
use PhpParser\Node\Stmt\Interface_;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeVisitorAbstract;
use Tactix\Assert\Assert;

final class ClassNameAnalyzer extends NodeVisitorAbstract
{
    public function __construct(
        private readonly string $filePath,
        private ?string $namespace = null,
        private ?string $className = null,
    ) {
    }

    public function enterNode(Node $node): ?Node
    {
        if ($node instanceof Namespace_) {
            assert(!is_null($node->name));
            $this->namespace = implode('\\', $node->name->getParts());

            return null;
        }

        if ($node instanceof Class_) {
            if (!is_null($node->name)) {
                Assert::that(
                    is_null($this->className),
                    sprintf('Make sure "%s" contains only 1 class per file!', $this->filePath)
                );
                $this->className = $node->name->name;
            }

            return null;
        }

        if ($node instanceof Interface_ || $node instanceof Enum_) {
            assert(!is_null($node->name));
            Assert::that(
                is_null($this->className),
                sprintf('Make sure "%s" contains only 1 class per file!', $this->filePath)
            );
            $this->className = $node->name->name;

            return null;
        }

        return $node;
    }

    /**
     * Provide the full qualified class name.
     *
     * @return class-string
     */
    public function getFQCN(): string
    {
        assert(is_string($this->namespace), "File: {$this->filePath} - Namespace is not a string!");
        assert(is_string($this->className), "File: {$this->filePath} - Classname is not a string!");

        /** @var class-string $fqcn */
        $fqcn = $this->namespace.'\\'.$this->className;

        return $fqcn;
    }
}

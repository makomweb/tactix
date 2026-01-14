<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\NodeTraverser;
use Tactix\Analyzer\Class\Name as AnalyzerClassName;

class ExceptionAnalyzer
{
    public function __construct(private ClassMethod $method)
    {
    }

    /** @return AnalyzerClassName[] */
    public function getExceptionNames(): array
    {
        if (is_null($this->method->stmts)) {
            return [];
        }

        $collector = new ExceptionVisitor();
        $traverser = new NodeTraverser();
        $traverser->addVisitor($collector);
        $traverser->traverse($this->method->stmts);

        return $collector->exceptions;
    }
}

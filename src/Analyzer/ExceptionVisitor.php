<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Expr\MethodCall;
use PhpParser\Node\Expr\New_;
use PhpParser\Node\Expr\StaticCall;
use PhpParser\Node\Expr\Throw_;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Name;
use PhpParser\Node\Name\FullyQualified;
use PhpParser\Node\Stmt;
use PhpParser\Node\Stmt\Expression;
use PhpParser\NodeVisitorAbstract;
use Tactix\Analyzer\Class\Name as AnalyzerClassName;
use Tactix\Analyzer\Class\NameType;

class ExceptionVisitor extends NodeVisitorAbstract
{
    /** @param AnalyzerClassName[] $exceptions */
    public function __construct(public array $exceptions = [])
    {
    }

    public function enterNode(Node $node): ?Node
    {
        if (isset($node->stmts)) {
            assert(is_iterable($node->stmts));
            foreach ($node->stmts as $stmt) {
                assert($stmt instanceof Stmt);
                if ($stmt instanceof Expression && $stmt->expr instanceof Throw_) {
                    $this->exceptions[] = self::extractExceptionName($stmt->expr);
                }
                $this->traverse([$stmt]);
            }
        }

        return null;
    }

    /** @param Stmt[] $statements */
    public function traverse(array $statements): void
    {
        foreach ($statements as $statement) {
            $this->enterNode($statement);
        }
    }

    private static function extractExceptionName(Expr $expr): AnalyzerClassName
    {
        if ($expr instanceof Throw_) {
            return self::extractExceptionName($expr->expr);
        }

        if ($expr instanceof New_) {
            return match (get_class($expr->class)) {
                Name::class => new AnalyzerClassName($expr->class->name, NameType::QUALIFIED),
                FullyQualified::class => new AnalyzerClassName($expr->class->name, NameType::FULLYQUALIFIED),
                Expr::class => self::extractExceptionName($expr->class),
                default => new AnalyzerClassName('unknown exception', NameType::UNKNOWN),
            };
        }

        if ($expr instanceof StaticCall) {
            return match (get_class($expr->class)) {
                Name::class => new AnalyzerClassName($expr->class->name, NameType::QUALIFIED),
                Expr::class => self::extractExceptionName($expr->class),
                default => new AnalyzerClassName('unknown exception', NameType::UNKNOWN),
            };
        }

        if ($expr instanceof MethodCall) {
            return new AnalyzerClassName('unknown exception', NameType::UNKNOWN);
        }

        if ($expr instanceof Variable) {
            return is_string($expr->name)
                ? new AnalyzerClassName($expr->name, NameType::UNQUALIFIED)
                : new AnalyzerClassName('unknown exception', NameType::UNKNOWN);
        }

        return new AnalyzerClassName('unknown exception', NameType::UNKNOWN);
    }
}

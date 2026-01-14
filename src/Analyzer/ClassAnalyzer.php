<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use PhpParser\Node;
use PhpParser\Node\Expr\Variable;
use PhpParser\Node\Identifier;
use PhpParser\Node\IntersectionType;
use PhpParser\Node\Name;
use PhpParser\Node\NullableType;
use PhpParser\Node\Param;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassMethod;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\Node\UnionType;
use PhpParser\NodeVisitorAbstract;
use Tactix\Analyzer\Class\Argument;
use Tactix\Analyzer\Class\Method;
use Tactix\Analyzer\Class\Name as AnalyzerClassName;
use Tactix\Analyzer\Class\NameType;
use Tactix\Analyzer\Class\ReturnType;
use Tactix\Analyzer\Class\Using;

final class ClassAnalyzer extends NodeVisitorAbstract
{
    private function __construct(public readonly SourceCodeItem $item)
    {
    }

    /** @param class-string $className */
    public static function create(string $className): self
    {
        return new self(SourceCodeItem::initial($className));
    }

    public function enterNode(Node $node): ?Node
    {
        $this->collect($node);

        return null;
    }

    private function collect(Node $node): void
    {
        if ($node instanceof Namespace_) {
            assert(!is_null($node->name));
            $this->item->setNamespace($node->name->name);
        }

        if ($node instanceof Use_) {
            foreach ($node->uses as $using) {
                $alias = $using->alias ?? $using->name->getLast();
                /** @var class-string $fqcn */
                $fqcn = $using->name->toCodeString();
                $this->item->addUsing(
                    new Using($alias instanceof Identifier ? $alias->name : $alias, $fqcn)
                );
            }
        }

        if ($node instanceof Class_) {
            foreach ($node->implements as $interface) {
                $this->item->addImplements(
                    new AnalyzerClassName(
                        $interface->name,
                        match (true) {
                            $interface->isFullyQualified() => NameType::FULLYQUALIFIED,
                            $interface->isQualified() => NameType::QUALIFIED,
                            $interface->isUnqualified() => NameType::UNQUALIFIED,
                            $interface->isRelative() => NameType::RELATIVE,
                            $interface->isSpecialClassName() => NameType::SPECIAL_CLASS_NAME,
                            default => NameType::UNKNOWN,
                        }
                    )
                );
            }

            if (!is_null($node->extends)) {
                $extends = $node->extends;
                $this->item->addExtends(
                    new AnalyzerClassName(
                        $extends->name,
                        match (true) {
                            $extends->isFullyQualified() => NameType::FULLYQUALIFIED,
                            $extends->isQualified() => NameType::QUALIFIED,
                            $extends->isUnqualified() => NameType::UNQUALIFIED,
                            $extends->isRelative() => NameType::RELATIVE,
                            $extends->isSpecialClassName() => NameType::SPECIAL_CLASS_NAME,
                            default => NameType::UNKNOWN,
                        }
                    )
                );
            }

            foreach ($node->getMethods() as $method) {
                $this->add($method);
            }
        }
    }

    private function add(ClassMethod $method): void
    {
        $this->item->addMethod(
            new Method(
                class: $this->item->fullQualifiedClassName,
                name: (string) $method->name,
                arguments: iterator_to_array($this->yieldArguments($method)),
                returnType: $this->getReturnType($method),
                static: $method->isStatic(),
                throws: $this->getExceptionNames($method)
            )
        );
    }

    /** @return AnalyzerClassName[] */
    private function getExceptionNames(ClassMethod $method): array
    {
        return (new ExceptionAnalyzer($method))->getExceptionNames();
    }

    /** @return \Generator<Argument> */
    private function yieldArguments(ClassMethod $method): \Generator
    {
        foreach ($method->params as $param) {
            yield $this->getArgumentType($param);
        }
    }

    private function getReturnType(ClassMethod $method): ReturnType
    {
        if (is_null($method->returnType)) {
            return ReturnType::void();
        }

        $returnType = $method->returnType;

        if ($returnType instanceof Identifier || $returnType instanceof Name) {
            $t = (string) $returnType;
            if ('void' === $t) {
                return ReturnType::void();
            }
            if ('array' === $t || 'Generator' === $t || '\\Generator' === $t) {
                return ReturnType::unknown();
            }

            return ReturnType::regular(new AnalyzerClassName($t, NameType::UNKNOWN));
        }

        if ($returnType instanceof NullableType) {
            $t = (string) $returnType->type;
            if ('array' === $t || 'Generator' === $t || '\\Generator' === $t) {
                return ReturnType::unknown();
            }

            return ReturnType::nullable(new AnalyzerClassName($t, NameType::UNKNOWN));
        }

        if ($returnType instanceof UnionType || $returnType instanceof IntersectionType) {
            return ReturnType::unknown();
        }

        return ReturnType::unknown();
    }

    private function getArgumentType(Param $param): Argument
    {
        assert($param->var instanceof Variable);
        $name = $param->var->name;

        assert(is_string($name));
        assert(!is_null($param->type), "Param \"{$name}\" should not have type null!");

        $isNullable = $param->type instanceof NullableType;
        $isArray = $param->type instanceof Identifier && 'array' === $param->type->name;

        $type = new AnalyzerClassName(
            $isArray ? 'array' : $this->getTypeAsString($param->type),
            NameType::UNKNOWN
        );

        return new Argument($name, $type, $isNullable, $isArray);
    }

    private function getTypeAsString(?Node $node): string
    {
        if ($node instanceof NullableType) {
            return (string) $node->type;
        }
        if ($node instanceof Identifier || $node instanceof Name) {
            return (string) $node;
        }
        if ($node instanceof UnionType) {
            return implode('|', array_map(fn ($t) => $this->getTypeAsString($t), $node->types));
        }
        if ($node instanceof IntersectionType) {
            return implode('&', array_map(fn ($t) => $this->getTypeAsString($t), $node->types));
        }

        return 'unknown';
    }
}

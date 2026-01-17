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
        $docBlockParamTypes = self::getDocBlockParamTypes($method);

        foreach ($method->params as $param) {
            $paramName = $param->var instanceof Variable ? $param->var->name : null;
            $docType = is_string($paramName) ? $docBlockParamTypes[$paramName] ?? null : null;

            yield $this->getArgumentType($param, $docType);
        }
    }

    private function getReturnType(ClassMethod $method): ReturnType
    {
        $docReturnType = self::getDocBlockReturnType($method);
        $collectionDocType = self::resolveCollectionDocType($docReturnType);
        $generatorDocType = self::resolveGeneratorDocType($docReturnType);

        if (is_null($method->returnType)) {
            if (null !== $collectionDocType) {
                return ReturnType::collection(new AnalyzerClassName($collectionDocType, NameType::UNKNOWN));
            }

            if (null !== $generatorDocType) {
                return ReturnType::generator(new AnalyzerClassName($generatorDocType, NameType::UNKNOWN));
            }

            if (is_string($docReturnType) && !self::isCollectionDoc($docReturnType)) {
                return ReturnType::regular(new AnalyzerClassName($docReturnType, NameType::UNKNOWN));
            }

            return ReturnType::void();
        }

        $returnType = $method->returnType;

        if ($returnType instanceof Identifier || $returnType instanceof Name) {
            $typeName = (string) $returnType;

            if ('void' === $typeName) {
                return ReturnType::void();
            }

            if (self::isCollectionTypeName($typeName)) {
                if (null !== $collectionDocType) {
                    return ReturnType::collection(new AnalyzerClassName($collectionDocType, NameType::UNKNOWN));
                }

                return ReturnType::unknown();
            }

            if (self::isGeneratorTypeName($typeName)) {
                if (null !== $generatorDocType) {
                    return ReturnType::generator(new AnalyzerClassName($generatorDocType, NameType::UNKNOWN));
                }

                return ReturnType::unknown();
            }

            return ReturnType::regular(new AnalyzerClassName($typeName, NameType::UNKNOWN));
        }

        if ($returnType instanceof NullableType) {
            $typeName = (string) $returnType->type;

            if (self::isCollectionTypeName($typeName)) {
                if (null !== $collectionDocType) {
                    return ReturnType::collection(new AnalyzerClassName($collectionDocType, NameType::UNKNOWN));
                }

                return ReturnType::unknown();
            }

            if (self::isGeneratorTypeName($typeName)) {
                if (null !== $generatorDocType) {
                    return ReturnType::generator(new AnalyzerClassName($generatorDocType, NameType::UNKNOWN));
                }

                return ReturnType::unknown();
            }

            return ReturnType::nullable(new AnalyzerClassName($typeName, NameType::UNKNOWN));
        }

        if ($returnType instanceof UnionType || $returnType instanceof IntersectionType) {
            return ReturnType::unknown();
        }

        return ReturnType::unknown();
    }

    private function getArgumentType(Param $param, ?string $docType): Argument
    {
        assert($param->var instanceof Variable);
        $name = $param->var->name;

        assert(is_string($name));
        assert(!is_null($param->type), "Param \"{$name}\" should not have type null!");

        $isNullable = $param->type instanceof NullableType;
        $isCollection = $param->type instanceof Identifier && in_array($param->type->name, ['array', 'list', 'iterable']);
        $typeValue = $isCollection ? 'array' : self::getTypeAsString($param->type);

        if ($isCollection) {
            $collectionDocType = self::resolveCollectionDocType($docType);
            if (null !== $collectionDocType) {
                $typeValue = $collectionDocType;
            }
        }

        $type = new AnalyzerClassName($typeValue, NameType::UNKNOWN);

        return new Argument($name, $type, $isNullable, $isCollection);
    }

    /**
     * @return array<string, string>
     */
    private static function getDocBlockParamTypes(ClassMethod $method): array
    {
        $docComment = $method->getDocComment();
        if (null === $docComment) {
            return [];
        }

        $doc = $docComment->getText();
        preg_match_all('/@param\s+([^\s]+)\s+\$([^\s]+)/', $doc, $matches, PREG_SET_ORDER);

        $types = [];
        foreach ($matches as $match) {
            $paramName = $match[2];
            $type = $match[1];
            $types[$paramName] = $type;
        }

        return $types;
    }

    private static function resolveCollectionDocType(?string $docType): ?string
    {
        if (null === $docType) {
            return null;
        }

        $docType = trim($docType);
        if ('' === $docType) {
            return null;
        }

        $docType = trim(explode('|', $docType)[0]);
        if ('' === $docType) {
            return null;
        }

        if (str_ends_with($docType, '[]')) {
            return rtrim(substr($docType, 0, -2));
        }

        if (preg_match('/^(?:array|list|iterable)<\s*([^,>]+)\s*>$/i', $docType, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/^(?:array|list|iterable)<\s*[^,>]+,\s*([^>]+)\s*>$/i', $docType, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    private static function resolveGeneratorDocType(?string $docType): ?string
    {
        if (null === $docType) {
            return null;
        }

        $docType = trim($docType);
        if ('' === $docType) {
            return null;
        }

        $docType = trim(explode('|', $docType)[0]);
        if ('' === $docType) {
            return null;
        }

        if (!preg_match('/^(?:\\\\)?Generator<(.+)>$/i', $docType, $matches)) {
            return null;
        }

        $parameters = self::splitGenericParameters($matches[1]);
        if (empty($parameters)) {
            return null;
        }

        return trim($parameters[1] ?? $parameters[0]);
    }

    /**
     * @return string[]
     */
    private static function splitGenericParameters(string $typeList): array
    {
        $parts = [];
        $current = '';
        $depth = 0;
        $length = strlen($typeList);

        for ($i = 0; $i < $length; ++$i) {
            $char = $typeList[$i];

            if (',' === $char && 0 === $depth) {
                $trimmed = trim($current);
                if ('' !== $trimmed) {
                    $parts[] = $trimmed;
                }
                $current = '';
                continue;
            }

            if ('<' === $char) {
                ++$depth;
            } elseif ('>' === $char && $depth > 0) {
                --$depth;
            }

            $current .= $char;
        }

        $trimmed = trim($current);
        if ('' !== $trimmed) {
            $parts[] = $trimmed;
        }

        return $parts;
    }

    private static function isCollectionDoc(string $docType): bool
    {
        return null !== self::resolveCollectionDocType($docType);
    }

    private static function isCollectionTypeName(string $type): bool
    {
        return in_array(ltrim($type, '\\'), ['array', 'list', 'iterable'], true);
    }

    private static function isGeneratorTypeName(string $type): bool
    {
        return in_array(ltrim($type, '\\'), ['Generator'], true);
    }

    private static function getDocBlockReturnType(ClassMethod $method): ?string
    {
        $docComment = $method->getDocComment();
        if (null === $docComment) {
            return null;
        }

        if (!preg_match('/@return\s+([^\s]+)/', $docComment->getText(), $matches)) {
            return null;
        }

        return trim($matches[1]);
    }

    private static function getTypeAsString(?Node $node): string
    {
        if ($node instanceof NullableType) {
            return (string) $node->type;
        }
        if ($node instanceof Identifier || $node instanceof Name) {
            return (string) $node;
        }
        if ($node instanceof UnionType) {
            return implode('|', array_map(fn ($t) => self::getTypeAsString($t), $node->types));
        }
        if ($node instanceof IntersectionType) {
            return implode('&', array_map(fn ($t) => self::getTypeAsString($t), $node->types));
        }

        return 'unknown';
    }
}

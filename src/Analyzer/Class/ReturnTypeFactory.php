<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

use PhpParser\Node\Identifier;
use PhpParser\Node\NullableType;
use PhpParser\Node\Stmt\ClassMethod;
use Tactix\Analyzer\DocTypeExtractor;

final readonly class ReturnTypeFactory
{
    public static function fromMethod(ClassMethod $method): ReturnType
    {
        $docReturnType = self::getDocBlockReturnType($method);
        $collectionDocType = DocTypeExtractor::resolveCollectionDocType($docReturnType);
        $generatorDocType = DocTypeExtractor::resolveGeneratorDocType($docReturnType);

        if (is_null($method->returnType)) {
            if ($collectionDocType) {
                return ReturnType::collection(new Name($collectionDocType, NameType::UNKNOWN));
            }
            if ($generatorDocType) {
                return ReturnType::generator(new Name($generatorDocType, NameType::UNKNOWN));
            }
            if (is_string($docReturnType)) {
                return ReturnType::regular(new Name($docReturnType, NameType::UNKNOWN));
            }

            return ReturnType::void();
        }

        $returnType = $method->returnType;
        if ($returnType instanceof Identifier || $returnType instanceof \PhpParser\Node\Name || $returnType instanceof NullableType) {
            return self::fromString(
                (string) ($returnType instanceof NullableType ? $returnType->type : $returnType),
                $returnType instanceof NullableType,
                $collectionDocType,
                $generatorDocType
            );
        }

        throw new \LogicException(sprintf('"%s" (e.g. type union or type intersection) is not yet supported', get_debug_type($returnType)));
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

    private static function fromString(string $typeName, bool $nullable, ?string $collectionDocType, ?string $generatorDocType): ReturnType
    {
        if ('void' === $typeName) {
            return ReturnType::void();
        }
        $collectionOrGenerator = self::extractCollectionOrGenerator($typeName);
        if ('collection' === $collectionOrGenerator) {
            if (null !== $collectionDocType) {
                return ReturnType::collection(new Name($collectionDocType, NameType::UNKNOWN));
            }

            return ReturnType::unknown();
        }
        if ('generator' === $collectionOrGenerator) {
            if (null !== $generatorDocType) {
                return ReturnType::generator(new Name($generatorDocType, NameType::UNKNOWN));
            }

            return ReturnType::unknown();
        }

        return $nullable
            ? ReturnType::nullable(new Name($typeName, NameType::UNKNOWN))
            : ReturnType::regular(new Name($typeName, NameType::UNKNOWN));
    }

    private static function extractCollectionOrGenerator(string $type): ?string
    {
        $type = ltrim($type, '\\');
        if (in_array($type, ['array', 'list', 'iterable'], true)) {
            return 'collection';
        }
        if ('Generator' === $type) {
            return 'generator';
        }

        return null;
    }
}

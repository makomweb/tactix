<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use PhpParser\Node\Stmt\ClassMethod;

final class DocTypeExtractor
{
    /**
     * Returns a map of parameter name to parameter type.
     *
     * @return array<string, string>
     */
    public static function getDocBlockParamTypes(ClassMethod $method): array
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

    public static function getDocBlockReturnType(ClassMethod $method): ?string
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

    public static function resolveCollectionDocType(?string $docType): ?string
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

        if (preg_match('/^(?:\\\\?array|\\\\?list|\\\\?iterable)<\s*([^,>]+)\s*>$/i', $docType, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/^(?:\\\\?array|\\\\?list|\\\\?iterable)<\s*[^,>]+,\s*([^>]+)\s*>$/i', $docType, $matches)) {
            return trim($matches[1]);
        }

        return null;
    }

    public static function resolveGeneratorDocType(?string $docType): ?string
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
}

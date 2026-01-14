<?php

declare(strict_types=1);

namespace Tactix;

final readonly class AttributeNameFactory
{
    /**
     * @param class-string $className
     */
    public static function fromClassOrNull(string $className): ?AttributeName
    {
        try {
            return self::fromClass($className);
        } catch (\ReflectionException) {
            return null;
        }
    }

    /**
     * @param class-string $className
     */
    private static function fromClass(string $className): ?AttributeName
    {
        $result = iterator_to_array(self::yieldAttributeNames($className));

        if (empty($result)) {
            return null;
        }

        if (1 != count($result)) {
            throw new AmbiguityException($className);
        }

        return $result[0];
    }

    /**
     * @param class-string $className
     *
     * @return \Generator<AttributeName>
     */
    private static function yieldAttributeNames(string $className): \Generator
    {
        $reflection = new \ReflectionClass($className);

        foreach ($reflection->getAttributes() as $attribute) {
            try {
                yield AttributeName::fromAttributeClass($attribute->getName());
            } catch (\InvalidArgumentException) {
                /*
                 * An unsupported attribute does not deliver a tactical DDD attribute.
                 * Hence, ignore it.
                 */
            }
        }
    }
}

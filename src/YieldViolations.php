<?php

declare(strict_types=1);

namespace Tactix;

use Tactix\Analyzer\Class\YieldClassNames;
use Tactix\Analyzer\YieldRelations;

final readonly class YieldViolations
{
    /**
     * Yield violations for the classes in the specified folder.
     *
     * @param non-empty-string $folder
     *
     * @return \Generator<Violation>
     */
    public static function fromFolder(string $folder): \Generator
    {
        if (!is_dir($folder)) {
            throw new \InvalidArgumentException("{$folder} is not a directory!");
        }

        /* Check that all classes have a tactical tag. Yield a violation if not. */
        foreach (YieldClassNames::for($folder) as $className) {
            yield from self::fromClassName($className);
        }

        /* Yield a violation for every forbidden relation within this folder. */
        foreach (YieldRelations::fromFolder($folder) as $relation) {
            if ($relation->isForbidden()) {
                yield new Violation(sprintf('%s is a forbidden relation! ❌', $relation));
            }
        }
    }

    /**
     * @param class-string $className
     *
     * @return \Generator<Violation>
     */
    public static function fromClassName(string $className): \Generator
    {
        /* Interfaces do not carry tags nor create violations. */
        if (interface_exists($className)) {
            return;
        }

        /* Exceptions are value objects. It is not required that they carry this tag. */
        if (str_ends_with($className, 'Exception')) {
            return;
        }

        /* Don't check for violations when it is an ignorable type. */
        if (in_array($className, IgnoreableTypes::VALUES, true)) {
            return;
        }

        $tag = AttributeNameFactory::fromClassOrNull($className);

        if (is_null($tag)) {
            yield new Violation(sprintf('%s has no tactical tag! ❌', $className));
        }

        /* Yield a violation for every forbidden relation this class has. */
        foreach (YieldRelations::fromClassName($className) as $relation) {
            if ($relation->isForbidden()) {
                yield new Violation(sprintf('%s is a forbidden relation! ❌', $relation));
            }
        }
    }
}

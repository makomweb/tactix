<?php

declare(strict_types=1);

namespace Tactix;

final readonly class Check
{
    /**
     * @param class-string $className
     */
    public static function className(string $className): void
    {
        $violations = iterator_to_array(YieldViolations::fromClassName($className));

        if (!empty($violations)) {
            throw new ClassViolationException($className, $violations);
        }
    }

    /**
     * @param non-empty-string $folder
     */
    public static function folder(string $folder): void
    {
        $violations = iterator_to_array(YieldViolations::fromFolder($folder));

        if (!empty($violations)) {
            throw new FolderViolationException($folder, $violations);
        }
    }
}

<?php

declare(strict_types=1);

namespace Tactix;

final readonly class YieldViolations
{
    /**
     * @param non-empty-string $folder
     *
     * @return \Generator<Violation>
     */
    public static function fromFolder(string $folder): \Generator
    {
        if (!is_dir($folder)) {
            throw new \InvalidArgumentException("{$folder} is not a directory!");
        }

        foreach (self::yieldClassNamesFromFolder($folder) as $className) {
            yield from self::fromClassName($className);
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
        if (str_contains($className, 'Exception')) {
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
    }

    /**
     * @param non-empty-string $folder
     *
     * @return \Generator<class-string>
     */
    private static function yieldClassNamesFromFolder(string $folder): \Generator
    {
        $it = new \RecursiveIteratorIterator(
            new \RecursiveDirectoryIterator($folder, \FilesystemIterator::SKIP_DOTS)
        );

        /** @var \SplFileInfo $file */
        foreach ($it as $file) {
            if (!$file->isFile() || 'php' !== $file->getExtension()) {
                continue;
            }

            $contents = @file_get_contents($file->getPathname());
            if (!is_string($contents) || '' === $contents) {
                continue;
            }

            $namespace = null;
            if (1 === preg_match('/^\s*namespace\s+([^;]+);/m', $contents, $m)) {
                $namespace = trim($m[1]);
            }

            if (preg_match_all('/^\s*(?:final\s+|abstract\s+)?class\s+([A-Za-z_][A-Za-z0-9_]*)\b/m', $contents, $m) > 0) {
                foreach ($m[1] as $shortName) {
                    /** @var class-string $fqcn */
                    $fqcn = $namespace ? $namespace.'\\'.$shortName : $shortName;                    
                    yield $fqcn;
                }
            }
        }
    }
}

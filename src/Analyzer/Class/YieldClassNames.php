<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

final readonly class YieldClassNames
{
    /**
     * @return \Generator<class-string>
     */
    public static function for(string $folder): \Generator
    {
        foreach (self::yieldFilePaths($folder) as $filePath) {
            yield ClassNameFactory::createFrom($filePath);
        }
    }

    /**
     * @return \Generator<string>
     */
    private static function yieldFilePaths(string $folder): \Generator
    {
        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder)) as $fileInfo) {
            assert($fileInfo instanceof \SplFileInfo);
            if ('php' === $fileInfo->getExtension()) {
                $path = $fileInfo->getRealPath();
                assert(is_string($path));
                yield $path;
            }
        }
    }
}

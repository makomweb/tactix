<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\ClassNameFactory;
use Tactix\Assert\Assert;

final class PhpFileAnalyzer
{
    /**
     * @param non-empty-string $filePath
     * @param class-string     $className
     */
    private function __construct(
        public readonly string $filePath,
        public readonly string $className,
    ) {
    }

    /** @param non-empty-string $filePath */
    public static function fromFile(string $filePath): self
    {
        Assert::that(file_exists($filePath), "No such file: $filePath!");

        /** @var class-string $className */
        $className = ClassNameFactory::createFrom($filePath);

        return new self($filePath, $className);
    }
}

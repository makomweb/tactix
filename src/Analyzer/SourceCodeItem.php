<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Method;
use Tactix\Analyzer\Class\Name;
use Tactix\Analyzer\Class\Using;
use Tactix\Assert\Assert;

final class SourceCodeItem
{
    /**
     * @param class-string $fullQualifiedClassName
     * @param Using[]      $usings
     * @param array<Name>  $implements
     * @param array<Name>  $extends
     * @param Method[]     $methods
     */
    private function __construct(
        public readonly string $fullQualifiedClassName,
        public array $usings = [],
        public array $implements = [],
        public array $extends = [],
        public array $methods = [],
        public ?string $namespace = null,
    ) {
    }

    /** @param class-string $className */
    public static function initial(string $className): self
    {
        return new self($className);
    }

    public function setNamespace(string $namespace): void
    {
        assert(!empty($namespace));

        $this->namespace = $namespace;
    }

    public function addUsing(Using $using): void
    {
        $this->usings[] = $using;
    }

    public function addImplements(Name $implements): void
    {
        $this->implements[] = $implements;
    }

    public function addExtends(Name $extends): void
    {
        $this->extends[] = $extends;
    }

    public function addMethod(Method $method): void
    {
        $this->methods[] = $method;
    }

    /**
     * @return \Generator<SourceCodeItem>
     */
    public static function yieldFromFolder(string $folder): \Generator
    {
        Assert::that(is_dir($folder), "$folder is not a directory!");

        $factory = new SourceCodeItemFactory();

        foreach (new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($folder)) as $fileInfo) {
            assert($fileInfo instanceof \SplFileInfo);
            if ('php' === $fileInfo->getExtension()) {
                $filePath = $fileInfo->getRealPath();
                assert(is_string($filePath) && !empty($filePath));
                $analyzer = PhpFileAnalyzer::fromFile($filePath);
                yield $factory->getSourceCodeItem($analyzer->className, $analyzer->filePath);
            }
        }
    }
}

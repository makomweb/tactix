<?php

declare(strict_types=1);

namespace Tactix\Analyzer;

use Tactix\Analyzer\Class\Method;
use Tactix\Analyzer\Class\Name;
use Tactix\Analyzer\Class\Using;

final class Result
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
}

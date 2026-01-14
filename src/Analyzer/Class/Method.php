<?php

declare(strict_types=1);

namespace Tactix\Analyzer\Class;

final readonly class Method implements \Stringable
{
    /**
     * @param class-string $class
     * @param Argument[]   $arguments
     * @param Name[]       $throws
     */
    public function __construct(
        public string $class,
        public string $name,
        public array $arguments,
        public ReturnType $returnType,
        public bool $static,
        public array $throws,
    ) {
    }

    public function isVoid(): bool
    {
        return $this->returnType->isVoid();
    }

    public function __toString(): string
    {
        return sprintf(
            '%sfunction %s(%s): %s %s',
            $this->static ? 'static ' : '',
            $this->name,
            implode(', ', array_map(static fn (Argument $arg) => (string) $arg, $this->arguments)),
            $this->returnType,
            !empty($this->throws) ? 'throws '.implode('|', $this->throws) : ''
        );
    }
}

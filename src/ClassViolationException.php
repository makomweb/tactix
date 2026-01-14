<?php

declare(strict_types=1);

namespace Tactix;

class ClassViolationException extends ViolationException
{
    /**
     * @param class-string $className
     * @param Violation[]  $violations
     */
    public function __construct(
        public readonly string $className,
        array $violations,
    ) {
        parent::__construct(
            sprintf('Class %s has %d violation(s)!', $className, count($violations)),
            $violations
        );
    }
}

<?php

declare(strict_types=1);

namespace Tactix;

class ViolationException extends \Exception
{
    /**
     * @param Violation[] $violations
     */
    public function __construct(string $message, public readonly array $violations)
    {
        parent::__construct($message);
    }
}

<?php

declare(strict_types=1);

namespace Tactix;

class AmbiguityException extends TactixException
{
    /** @param class-string $className */
    public function __construct(
        public readonly string $className,
    ) {
        parent::__construct(sprintf('Class %s has ambigious DDD attributes!', $className));
    }
}

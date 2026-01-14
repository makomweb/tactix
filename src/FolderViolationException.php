<?php

declare(strict_types=1);

namespace Tactix;

class FolderViolationException extends ViolationException
{
    /**
     * @param non-empty-string $folder
     * @param Violation[]      $violations
     */
    public function __construct(
        public readonly string $folder,
        array $violations,
    ) {
        parent::__construct(
            sprintf('Folder %s has %d violation(s)!', $folder, count($violations)),
            $violations
        );
    }
}

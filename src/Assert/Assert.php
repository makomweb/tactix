<?php

declare(strict_types=1);

namespace Tactix\Assert;

final readonly class Assert
{
    public static function that(bool $condition, string|\Throwable|null $error = null): void
    {
        if ($condition) {
            return;
        }

        if (is_string($error)) {
            throw new AssertionException($error);
        }

        if ($error instanceof \Throwable) {
            throw $error;
        }

        throw new AssertionException('Assertion failed!');
    }
}

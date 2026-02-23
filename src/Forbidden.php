<?php

declare(strict_types=1);

namespace Tactix;

/**
 * Facade for DDD blacklist checks.
 * Delegates to Blacklist for actual rule checking.
 * Kept for backward compatibility.
 */
final class Forbidden
{
    /**
     * Set custom blacklist configuration at runtime.
     * Delegates to Blacklist::setBlacklist().
     *
     * @param array<string, array<string>> $blacklist
     */
    public static function setBlacklist(array $blacklist): void
    {
        Blacklist::setBlacklist($blacklist);
    }

    /**
     * Check if a relation from $from to $to is forbidden.
     * Delegates to Blacklist::check().
     */
    public static function check(AttributeName $from, AttributeName $to): bool
    {
        return Blacklist::check($from, $to);
    }
}

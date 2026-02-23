<?php

declare(strict_types=1);

namespace Tactix;

final class Forbidden
{
    /** @param AttributeName[] $to */
    private function __construct(public readonly AttributeName $from, public readonly array $to)
    {
    }

    private function isForbidden(AttributeName $to): bool
    {
        return in_array($to, $this->to, true);
    }

    /** @var array<string, string[]>|null */
    private static ?array $customBlacklist = null;

    /**
     * Set custom blacklist configuration at runtime.
     * Format: ['FROM_ATTRIBUTE' => ['TO_ATTRIBUTE1', 'TO_ATTRIBUTE2'], ...]
     * Attribute names should match AttributeName enum values.
     *
     * @param array<string, array<string>> $blacklist
     */
    public static function setBlacklist(array $blacklist): void
    {
        self::$customBlacklist = $blacklist;
    }

    public static function check(AttributeName $from, AttributeName $to): bool
    {
        foreach (self::createBlackList() as $forbidden) {
            if ($forbidden->from === $from) {
                return $forbidden->isForbidden($to);
            }
        }

        throw new \LogicException(sprintf('Add an entry for %s to the blacklist!', $from->value));
    }

    /** @return self[] */
    private static function createBlackList(): array
    {
        $blacklist = self::$customBlacklist ?? self::getDefaultBlacklist();

        $forbiddenRules = [];
        foreach ($blacklist as $fromValue => $toValues) {
            try {
                $from = AttributeName::from($fromValue);
                $to = array_map(
                    static fn (string $value) => AttributeName::from($value),
                    $toValues
                );
                $forbiddenRules[] = new self($from, $to);
            } catch (\ValueError $e) {
                throw new \LogicException(sprintf('Invalid attribute name in blacklist: %s', $e->getMessage()), 0, $e);
            }
        }

        return $forbiddenRules;
    }

    /** @return array<string, string[]> */
    private static function getDefaultBlacklist(): array
    {
        return [
            'Entity' => [
                'Factory',
                'Service',
                'AggregateRoot',
            ],
            'ValueObject' => [
                'Entity',
                'AggregateRoot',
                'Repository',
                'Factory',
                'Service',
            ],
            'AggregateRoot' => ['Factory'],
            'Repository' => ['Factory', 'Service'],
            'Factory' => ['Repository'],
            'Service' => [],
        ];
    }
}

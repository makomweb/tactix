<?php

declare(strict_types=1);

namespace Tactix;

final readonly class Blacklist
{
    public const DEFAULT = [
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

    /**
     * Check if a relation from $from to $to is forbidden.
     *
     * @throws \LogicException If the from attribute has no blacklist entry
     */
    public static function check(AttributeName $from, AttributeName $to): bool
    {
        foreach (self::buildRules() as $rule) {
            if ($rule['from'] === $from) {
                return in_array($to, $rule['to'], true);
            }
        }

        throw new \LogicException(sprintf('Add an entry for %s to the blacklist!', $from->value));
    }

    /**
     * Build rules from configuration, converting string names to AttributeName enums.
     *
     * @return array<int, array{from: AttributeName, to: AttributeName[]}>
     */
    private static function buildRules(): array
    {
        $config = self::$customBlacklist ?? self::DEFAULT;

        $rules = [];
        foreach ($config as $fromValue => $toValues) {
            try {
                $from = AttributeName::from($fromValue);
                $to = array_map(
                    static fn (string $value) => AttributeName::from($value),
                    $toValues
                );
                $rules[] = [
                    'from' => $from,
                    'to' => $to,
                ];
            } catch (\ValueError $e) {
                throw new \LogicException(sprintf('Invalid attribute name in blacklist: %s', $e->getMessage()), 0, $e);
            }
        }

        return $rules;
    }

    /**
     * Create a new Blacklist instance.
     * Format: ['FROM_ATTRIBUTE' => ['TO_ATTRIBUTE1', 'TO_ATTRIBUTE2'], ...]
     *
     * @param array<string, array<string>> $blacklist If empty, uses DEFAULT
     */
    public function __construct(public array $blacklist = [])
    {
    }
}

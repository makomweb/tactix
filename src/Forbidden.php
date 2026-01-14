<?php

declare(strict_types=1);

namespace Tactix;

final readonly class Forbidden
{
    /** @param AttributeName[] $to */
    private function __construct(public AttributeName $from, public array $to)
    {
    }

    private function isForbidden(AttributeName $to): bool
    {
        return in_array($to, $this->to, true);
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
        return [
            new self(AttributeName::ENTITY, [
                AttributeName::FACTORY,
                AttributeName::SERVICE,
                AttributeName::AGGREGATE_ROOT,
            ]),
            new self(AttributeName::VALUE_OBJECT, [
                AttributeName::ENTITY,
                AttributeName::AGGREGATE_ROOT,
                AttributeName::REPOSITORY,
                AttributeName::FACTORY,
                AttributeName::SERVICE,
            ]),
            new self(AttributeName::AGGREGATE_ROOT, [AttributeName::FACTORY]),
            new self(AttributeName::REPOSITORY, [AttributeName::FACTORY, AttributeName::SERVICE]),
            new self(AttributeName::FACTORY, [AttributeName::REPOSITORY]),
            new self(AttributeName::SERVICE, [/* nothing is forbidden */]),
        ];
    }
}

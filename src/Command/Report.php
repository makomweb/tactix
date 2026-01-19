<?php

declare(strict_types=1);

namespace Tactix\Command;

use Tactix\AmbiguityException;
use Tactix\AttributeName;
use Tactix\AttributeNameFactory;

final readonly class Report
{
    /**
     * @param string[] $aggregateRoots
     * @param string[] $entities
     * @param string[] $factories
     * @param string[] $repositories
     * @param string[] $services
     * @param string[] $valueObjects
     * @param string[] $interfaces
     * @param string[] $exceptions
     * @param string[] $uncategorized
     */
    private function __construct(
        public array $aggregateRoots = [],
        public array $entities = [],
        public array $factories = [],
        public array $repositories = [],
        public array $services = [],
        public array $valueObjects = [],
        public array $interfaces = [],
        public array $exceptions = [],
        public array $uncategorized = [],
    ) {
    }

    public static function initial(): self
    {
        return new self();
    }

    /** @param class-string $className */
    public function withClassName(string $className): self
    {
        if (interface_exists($className)) {
            return new Report(
                valueObjects: $this->valueObjects,
                entities: $this->entities,
                services: $this->services,
                factories: $this->factories,
                repositories: $this->repositories,
                aggregateRoots: $this->aggregateRoots,
                interfaces: [...$this->interfaces, $className],
                exceptions: $this->exceptions,
                uncategorized: $this->uncategorized
            );
        }

        if (str_contains($className, 'Exception')) {
            return new Report(
                valueObjects: $this->valueObjects,
                entities: $this->entities,
                services: $this->services,
                factories: $this->factories,
                repositories: $this->repositories,
                aggregateRoots: $this->aggregateRoots,
                interfaces: $this->interfaces,
                exceptions: [...$this->exceptions, $className],
                uncategorized: $this->uncategorized
            );
        }

        $attribute = self::tryGetAttribute($className);

        return is_null($attribute)
            ? new Report(
                valueObjects: $this->valueObjects,
                entities: $this->entities,
                services: $this->services,
                factories: $this->factories,
                repositories: $this->repositories,
                aggregateRoots: $this->aggregateRoots,
                interfaces: $this->interfaces,
                exceptions: $this->exceptions,
                uncategorized: [...$this->uncategorized, $className]
            )
            : $this->withIncrement($attribute, $className);
    }

    /** @param class-string $className */
    private static function tryGetAttribute(string $className): ?AttributeName
    {
        try {
            return AttributeNameFactory::fromClassOrNull($className);
        } catch (AmbiguityException) {
            return null;
        }
    }

    /** @param class-string $className */
    private function withIncrement(AttributeName $attribute, string $className): self
    {
        return match ($attribute) {
            AttributeName::AGGREGATE_ROOT => new Report(
                valueObjects: $this->valueObjects,
                entities: $this->entities,
                services: $this->services,
                factories: $this->factories,
                repositories: $this->repositories,
                aggregateRoots: [...$this->aggregateRoots, $className],
                interfaces: $this->interfaces,
                exceptions: $this->exceptions,
                uncategorized: $this->uncategorized
            ),
            AttributeName::VALUE_OBJECT => new Report(
                valueObjects: [...$this->valueObjects, $className],
                entities: $this->entities,
                services: $this->services,
                factories: $this->factories,
                repositories: $this->repositories,
                aggregateRoots: $this->aggregateRoots,
                interfaces: $this->interfaces,
                exceptions: $this->exceptions,
                uncategorized: $this->uncategorized
            ),
            AttributeName::ENTITY => new Report(
                valueObjects: $this->valueObjects,
                entities: [...$this->entities, $className],
                services: $this->services,
                factories: $this->factories,
                repositories: $this->repositories,
                aggregateRoots: $this->aggregateRoots,
                interfaces: $this->interfaces,
                exceptions: $this->exceptions,
                uncategorized: $this->uncategorized
            ),
            AttributeName::FACTORY => new Report(
                valueObjects: $this->valueObjects,
                entities: $this->entities,
                services: $this->services,
                factories: [...$this->factories, $className],
                repositories: $this->repositories,
                aggregateRoots: $this->aggregateRoots,
                interfaces: $this->interfaces,
                exceptions: $this->exceptions,
                uncategorized: $this->uncategorized
            ),
            AttributeName::SERVICE => new Report(
                valueObjects: $this->valueObjects,
                entities: $this->entities,
                services: [...$this->services, $className],
                factories: $this->factories,
                repositories: $this->repositories,
                aggregateRoots: $this->aggregateRoots,
                interfaces: $this->interfaces,
                exceptions: $this->exceptions,
                uncategorized: $this->uncategorized
            ),
            AttributeName::REPOSITORY => new Report(
                valueObjects: $this->valueObjects,
                entities: $this->entities,
                services: $this->services,
                factories: $this->factories,
                repositories: [...$this->repositories, $className],
                aggregateRoots: $this->aggregateRoots,
                interfaces: $this->interfaces,
                exceptions: $this->exceptions,
                uncategorized: $this->uncategorized
            ),
            // default => throw new \InvalidArgumentException("Case {$attribute->value} not covered!"),
        };
    }
}

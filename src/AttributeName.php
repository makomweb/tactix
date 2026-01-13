<?php

declare(strict_types=1);

namespace Tactix;

use Tactix\Attribute\AggregateRoot;
use Tactix\Attribute\Entity;
use Tactix\Attribute\Factory;
use Tactix\Attribute\Repository;
use Tactix\Attribute\Service;
use Tactix\Attribute\ValueObject;

enum AttributeName: string
{
    case AGGREGATE_ROOT = 'AggregateRoot';
    case VALUE_OBJECT = 'ValueObject';
    case ENTITY = 'Entity';
    case FACTORY = 'Factory';
    case SERVICE = 'Service';
    case REPOSITORY = 'Repository';

    /**
     * Get the full qualified class name.
     */
    public function getFQCN(): string
    {
        return match ($this) {
            self::AGGREGATE_ROOT => AggregateRoot::class,
            self::VALUE_OBJECT => ValueObject::class,
            self::ENTITY => Entity::class,
            self::FACTORY => Factory::class,
            self::SERVICE => Service::class,
            self::REPOSITORY => Repository::class,
        };
    }

    /**
     * @param class-string $className
     */
    public static function fromAttributeClass(string $className): self
    {
        return match ($className) {
            AggregateRoot::class => self::AGGREGATE_ROOT,
            ValueObject::class => self::VALUE_OBJECT,
            Entity::class => self::ENTITY,
            Factory::class => self::FACTORY,
            Service::class => self::SERVICE,
            Repository::class => self::REPOSITORY,
            default => throw new \InvalidArgumentException("Unknown attribute class {$className}!"),
        };
    }
}

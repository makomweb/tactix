<?php

declare(strict_types=1);

namespace Tactix;

use PHPMolecules\DDD\Attribute\AggregateRoot;
use PHPMolecules\DDD\Attribute\Entity;
use PHPMolecules\DDD\Attribute\Factory;
use PHPMolecules\DDD\Attribute\Repository;
use PHPMolecules\DDD\Attribute\Service;
use PHPMolecules\DDD\Attribute\ValueObject;

enum AttributeName: string
{
    case AGGREGATE_ROOT = 'AggregateRoot';
    case VALUE_OBJECT = 'ValueObject';
    case ENTITY = 'Entity';
    case FACTORY = 'Factory';
    case SERVICE = 'Service';
    case REPOSITORY = 'Repository';

    /**
     * Get the full qualified class name of the attribute class.
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

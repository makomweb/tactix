<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\AttributeName;
use Tactix\AttributeNameFactory;
use Tactix\Tests\Data\MyEntity;

class AttributeNameFactoryTest extends TestCase
{
    #[Test]
    public function std_class_object_does_not_carry_tactical_name(): void
    {
        $object = new \stdClass();

        $name = AttributeNameFactory::fromClassOrNull(get_class($object));

        self::assertNull($name);
    }

    #[Test]
    public function entity_should_carry_tactical_name(): void
    {
        $name = AttributeNameFactory::fromClassOrNull(MyEntity::class);

        self::assertEquals(AttributeName::ENTITY, $name);
    }
}

<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Tests\Data\MyInterface;
use Tactix\YieldViolations;

final class InterfaceTest extends TestCase
{
    #[Test]
    public function interface_should_not_yield_violation(): void
    {
        $violations = iterator_to_array(YieldViolations::fromClassName(MyInterface::class));
        self::assertEmpty($violations);
    }
}

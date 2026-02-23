<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Blacklist;
use Tactix\Tests\Data\MyException;
use Tactix\YieldViolations;

final class ExceptionTest extends TestCase
{
    #[Test]
    public function exception_class_should_not_yield_violation(): void
    {
        $yieldViolations = new YieldViolations(new Blacklist());

        $violations = iterator_to_array($yieldViolations->fromClassName(MyException::class));
        self::assertEmpty($violations);
    }
}

<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Check;
use Tactix\ClassViolationException;
use Tactix\Tests\Fixtures\MissingTag\MyWithoutTag;

final class MissingTagTest extends TestCase
{
    #[Test]
    public function class_without_tactical_tag_should_throw_with_details(): void
    {
        try {
            Check::className(MyWithoutTag::class);
            self::fail('Should have thrown before!');
        } catch (ClassViolationException $ex) {
            self::assertSame('Class Tactix\Tests\Fixtures\MissingTag\MyWithoutTag has 1 violation(s)!', $ex->getMessage());
            self::assertCount(1, $ex->violations);
            self::assertStringContainsString('has no tactical tag', (string) $ex->violations[0]);
        }
    }
}

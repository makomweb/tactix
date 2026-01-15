<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Check;
use Tactix\ClassViolationException;
use Tactix\FolderViolationException;
use Tactix\Tests\Data\MyValueObject;

final class ForbiddenRelationsTest extends TestCase
{
    #[Test]
    public function folder_with_forbidden_dependency_should_throw(): void
    {
        $folder = __DIR__.'/../Data';

        try {
            Check::folder($folder);
            self::fail('Should have thrown before!');
        } catch (FolderViolationException $ex) {
            self::assertSame(sprintf('Folder %s has 1 violation(s)!', $folder), $ex->getMessage());
            self::assertCount(1, $ex->violations);
            self::assertSame('(MyValueObject)-[consumes]->(MyEntity) is a forbidden relation! ❌', $ex->violations[0]->message);
        }
    }

    #[Test]
    public function class_with_forbidden_dependency_should_throw(): void
    {
        try {
            Check::className(MyValueObject::class);
            self::fail('Should have thrown before!');
        } catch (ClassViolationException $ex) {
            self::assertSame(sprintf('Class %s has 1 violation(s)!', MyValueObject::class), $ex->getMessage());
            self::assertCount(1, $ex->violations);
            self::assertSame('(MyValueObject)-[consumes]->(MyEntity) is a forbidden relation! ❌', $ex->violations[0]->message);
        }
    }
}

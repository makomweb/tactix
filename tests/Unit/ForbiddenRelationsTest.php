<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\BlacklistFactory;
use Tactix\Check;
use Tactix\ClassViolationException;
use Tactix\FolderViolationException;
use Tactix\Tests\Data\MyValueObject;
use Tactix\YieldViolations;

final class ForbiddenRelationsTest extends TestCase
{
    #[Test]
    public function folder_with_forbidden_dependency_should_throw(): void
    {
        $folder = __DIR__.'/../Data';

        $check = new Check(new YieldViolations(BlacklistFactory::load()));

        try {
            $check->folder($folder);
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
        $check = new Check(new YieldViolations(BlacklistFactory::load()));

        try {
            $check->className(MyValueObject::class);
            self::fail('Should have thrown before!');
        } catch (ClassViolationException $ex) {
            self::assertSame(sprintf('Class %s has 1 violation(s)!', MyValueObject::class), $ex->getMessage());
            self::assertCount(1, $ex->violations);
            self::assertSame('(MyValueObject)-[consumes]->(MyEntity) is a forbidden relation! ❌', $ex->violations[0]->message);
        }
    }
}

<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Command\Report;
use Tactix\Tests\Data\MyEntity;
use Tactix\Tests\Data\MyException;
use Tactix\Tests\Data\MyInterface;
use Tactix\Tests\Data\MyValueObject;
use Tactix\Tests\Data\MyWithoutTag;

final class ReportTest extends TestCase
{
    #[Test]
    public function initial_report_should_have_empty_arrays(): void
    {
        $report = Report::initial();

        self::assertSame([], $report->aggregateRoots);
        self::assertSame([], $report->entities);
        self::assertSame([], $report->factories);
        self::assertSame([], $report->repositories);
        self::assertSame([], $report->services);
        self::assertSame([], $report->valueObjects);
        self::assertSame([], $report->interfaces);
        self::assertSame([], $report->exceptions);
        self::assertSame([], $report->uncategorized);
    }

    #[Test]
    public function report_should_classify_interface(): void
    {
        $report = Report::initial()->withClassName(MyInterface::class);

        self::assertSame([MyInterface::class], $report->interfaces);
        self::assertSame([], $report->entities);
        self::assertSame([], $report->valueObjects);
        self::assertSame([], $report->exceptions);
        self::assertSame([], $report->uncategorized);
    }

    #[Test]
    public function report_should_classify_exception(): void
    {
        $report = Report::initial()->withClassName(MyException::class);

        self::assertSame([MyException::class], $report->exceptions);
        self::assertSame([], $report->interfaces);
        self::assertSame([], $report->entities);
        self::assertSame([], $report->valueObjects);
        self::assertSame([], $report->uncategorized);
    }

    #[Test]
    public function report_should_classify_entity(): void
    {
        $report = Report::initial()->withClassName(MyEntity::class);

        self::assertSame([MyEntity::class], $report->entities);
        self::assertSame([], $report->interfaces);
        self::assertSame([], $report->valueObjects);
        self::assertSame([], $report->exceptions);
        self::assertSame([], $report->uncategorized);
    }

    #[Test]
    public function report_should_classify_value_object(): void
    {
        $report = Report::initial()->withClassName(MyValueObject::class);

        self::assertSame([MyValueObject::class], $report->valueObjects);
        self::assertSame([], $report->interfaces);
        self::assertSame([], $report->entities);
        self::assertSame([], $report->exceptions);
        self::assertSame([], $report->uncategorized);
    }

    #[Test]
    public function report_should_classify_uncategorized(): void
    {
        $report = Report::initial()->withClassName(MyWithoutTag::class);

        self::assertSame([MyWithoutTag::class], $report->uncategorized);
        self::assertSame([], $report->interfaces);
        self::assertSame([], $report->entities);
        self::assertSame([], $report->valueObjects);
        self::assertSame([], $report->exceptions);
    }

    #[Test]
    public function report_should_accumulate_multiple_classes(): void
    {
        $report = Report::initial()
            ->withClassName(MyInterface::class)
            ->withClassName(MyEntity::class)
            ->withClassName(MyValueObject::class)
            ->withClassName(MyException::class)
            ->withClassName(MyWithoutTag::class);

        self::assertSame([MyInterface::class], $report->interfaces);
        self::assertSame([MyEntity::class], $report->entities);
        self::assertSame([MyValueObject::class], $report->valueObjects);
        self::assertSame([MyException::class], $report->exceptions);
        self::assertSame([MyWithoutTag::class], $report->uncategorized);
    }

    #[Test]
    public function report_should_accumulate_multiple_classes_of_same_type(): void
    {
        $report = Report::initial()
            ->withClassName(MyEntity::class)
            ->withClassName(MyValueObject::class);

        self::assertSame([MyEntity::class], $report->entities);
        self::assertSame([MyValueObject::class], $report->valueObjects);
    }

    #[Test]
    public function report_should_be_immutable(): void
    {
        $initial = Report::initial();
        $withEntity = $initial->withClassName(MyEntity::class);

        self::assertNotSame($initial, $withEntity);
        self::assertSame([], $initial->entities);
        self::assertSame([MyEntity::class], $withEntity->entities);
    }
}

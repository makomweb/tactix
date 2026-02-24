<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Tactix\Analyzer\PhpFileAnalyzer;
use Tactix\Tests\Data\MyTrait;

class TraitTest extends TestCase
{
    #[Test]
    public function trait_is_identified(): void
    {
        $path = __DIR__.'/../Data/MyTrait.php';

        $analyzer = PhpFileAnalyzer::fromFile($path);

        self::assertSame(MyTrait::class, $analyzer->className);
    }
}

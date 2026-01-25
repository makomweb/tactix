<?php

declare(strict_types=1);

namespace Tactix\Tests\Unit;

use PHPUnit\Framework\Attributes\Test;
use PHPUnit\Framework\TestCase;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Tester\CommandTester;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\GetSetMethodNormalizer;
use Symfony\Component\Serializer\Serializer;
use Tactix\Command\ReportCommand;

final class ReportCommandTest extends TestCase
{
    private string $tempDir;

    protected function setUp(): void
    {
        parent::setUp();
        $this->tempDir = sys_get_temp_dir().'/tactix_test_'.uniqid();
        mkdir($this->tempDir, 0777, true);
    }

    protected function tearDown(): void
    {
        parent::tearDown();
        if (is_dir($this->tempDir)) {
            $this->removeDirectory($this->tempDir);
        }
    }

    private function createSerializer(): Serializer
    {
        return new Serializer([new ReportNormalizer(), new GetSetMethodNormalizer()], [new JsonEncoder()]);
    }

    #[Test]
    public function command_should_succeed_with_valid_folder(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        $folder = __DIR__.'/../Data';
        $commandTester->execute([
            'folder' => $folder,
            '--out-dir' => $this->tempDir,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Report for', $output);
        self::assertStringContainsString('Report written to:', $output);
    }

    #[Test]
    public function command_should_generate_report_files(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        $folder = __DIR__.'/../Data';
        $commandTester->execute([
            'folder' => $folder,
            '--out-dir' => $this->tempDir,
        ]);

        $reportDir = $this->tempDir.'/report';
        self::assertFileExists($reportDir.'/index.html');
        self::assertFileExists($reportDir.'/report.js');
        self::assertFileExists($reportDir.'/chart.js');
        self::assertFileExists($reportDir.'/styles.css');
    }

    #[Test]
    public function command_should_generate_valid_report_js(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        $folder = __DIR__.'/../Data';
        $commandTester->execute([
            'folder' => $folder,
            '--out-dir' => $this->tempDir,
        ]);

        $reportJsPath = $this->tempDir.'/report/report.js';
        $content = file_get_contents($reportJsPath);
        self::assertNotFalse($content, 'Failed to read report.js file');

        self::assertStringStartsWith('const reportData = ', $content);
        self::assertStringContainsString('"folder":', $content);
        self::assertStringContainsString('"classes":', $content);
        self::assertStringContainsString('"forbidden":', $content);
    }

    #[Test]
    public function command_should_generate_valid_html_structure(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        $folder = __DIR__.'/../Data';
        $commandTester->execute([
            'folder' => $folder,
            '--out-dir' => $this->tempDir,
        ]);

        $htmlPath = $this->tempDir.'/report/index.html';
        $content = file_get_contents($htmlPath);
        self::assertNotFalse($content, 'Failed to read index.html file');

        self::assertStringContainsString('<!DOCTYPE html>', $content);
        self::assertStringContainsString('<title>Charts Report</title>', $content);
        self::assertStringContainsString('id="pieChart"', $content);
        self::assertStringContainsString('id="barChart"', $content);
        self::assertStringContainsString('id="classesTableContainer"', $content);
        self::assertStringContainsString('<script src="report.js"></script>', $content);
        self::assertStringContainsString('<script src="chart.js"></script>', $content);
    }

    #[Test]
    public function command_should_use_current_directory_when_no_out_dir_specified(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        // Change to temp directory before running command
        $originalDir = getcwd();
        self::assertNotFalse($originalDir, 'Failed to get current working directory');
        chdir($this->tempDir);

        try {
            $folder = __DIR__.'/../Data';
            $commandTester->execute(['folder' => $folder]);

            self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
            self::assertFileExists($this->tempDir.'/report/index.html');
        } finally {
            chdir($originalDir);
        }
    }

    #[Test]
    public function command_should_display_uncategorized_classes_when_present(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        $folder = __DIR__.'/../Data';
        $commandTester->execute([
            'folder' => $folder,
            '--out-dir' => $this->tempDir,
        ]);

        $output = $commandTester->getDisplay();
        self::assertStringContainsString('Uncategorized Classes', $output);
    }

    #[Test]
    public function command_should_complete_successfully_and_show_sections(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        $folder = __DIR__.'/../Data';
        $commandTester->execute([
            'folder' => $folder,
            '--out-dir' => $this->tempDir,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        // The command should always show the report title and table
        self::assertStringContainsString('Report for', $output);
        self::assertStringContainsString('FQCN', $output);
    }

    #[Test]
    public function command_should_create_output_directory_if_not_exists(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        $nonExistentDir = $this->tempDir.'/nested/path/that/does/not/exist';
        self::assertDirectoryDoesNotExist($nonExistentDir);

        $folder = __DIR__.'/../Data';
        $commandTester->execute([
            'folder' => $folder,
            '--out-dir' => $nonExistentDir,
        ]);

        self::assertSame(Command::SUCCESS, $commandTester->getStatusCode());
        self::assertFileExists($nonExistentDir.'/report/index.html');
    }

    #[Test]
    public function command_should_fail_gracefully_with_exception(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        // Use a non-existent folder that will cause an error
        $folder = '/nonexistent/folder/that/does/not/exist';
        $commandTester->execute([
            'folder' => $folder,
            '--out-dir' => $this->tempDir,
        ]);

        self::assertSame(Command::FAILURE, $commandTester->getStatusCode());
        $output = $commandTester->getDisplay();
        self::assertStringContainsString('[ERROR]', $output);
    }

    #[Test]
    public function command_should_include_class_distribution_table(): void
    {
        $command = new ReportCommand($this->createSerializer());
        $commandTester = new CommandTester($command);

        $folder = __DIR__.'/../Data';
        $commandTester->execute([
            'folder' => $folder,
            '--out-dir' => $this->tempDir,
        ]);

        $output = $commandTester->getDisplay();
        // The table should have headers
        self::assertStringContainsString('FQCN', $output);
        self::assertStringContainsString('Tag', $output);
    }

    private function removeDirectory(string $dir): void
    {
        if (!is_dir($dir)) {
            return;
        }

        $files = array_diff(scandir($dir), ['.', '..']);
        foreach ($files as $file) {
            $path = $dir.'/'.$file;
            is_dir($path) ? $this->removeDirectory($path) : unlink($path);
        }
        rmdir($dir);
    }
}

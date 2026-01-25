<?php

declare(strict_types=1);

namespace Tactix\Command;

use Symfony\Component\Console\Attribute\AsCommand;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Table;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Input\InputOption;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Serializer\Encoder\JsonEncode;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\SerializerInterface;
use Tactix\AmbiguityException;
use Tactix\Analyzer\Edge;
use Tactix\Analyzer\Node;
use Tactix\Analyzer\NodeReducer;
use Tactix\Analyzer\Relation;
use Tactix\Analyzer\RelationReducer;
use Tactix\Analyzer\YieldNodes;
use Tactix\Analyzer\YieldRelations;
use Tactix\AttributeNameFactory;
use Tactix\IgnoreableTypes;
use Tactix\TactixException;

#[AsCommand(
    name: 'tactix:report',
    description: 'Create a report for classes in the specified folder',
)]
final class ReportCommand extends Command
{
    public function __construct(private readonly SerializerInterface $serializer)
    {
        parent::__construct();
    }

    protected function configure(): void
    {
        $this
            ->addArgument(
                'folder',
                InputArgument::REQUIRED,
                'The source code folder to be checked'
            )
            ->addOption(
                'out-dir',
                null, InputOption::VALUE_REQUIRED,
                'Base output directory for reports (defaults to project root)'
            )
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            $this->doExecute($input, $output);

            return Command::SUCCESS;
        } catch (TactixException $ex) {
            $io->error($ex->getMessage());

            return Command::FAILURE;
        } catch (\Throwable $ex) {
            $io->error(sprintf('%s', $ex));

            return Command::FAILURE;
        }
    }

    private function doExecute(InputInterface $input, OutputInterface $output): void
    {
        $folder = $input->getArgument('folder');
        assert(is_string($folder));

        // Determine base output directory. When used as a dependency inside another project
        // we default to that project's root directory (if available) by using the
        // current working directory (getcwd()). A caller can override with --out-dir.
        $outDir = $input->getOption('out-dir');
        if (!is_null($outDir) && is_string($outDir) && '' !== $outDir) {
            $baseDir = $outDir;
        } else {
            $baseDir = getcwd() ?: '.';
        }

        $io = new SymfonyStyle($input, $output);
        $io->title(sprintf('Report for "%s"', $folder));

        /** @var Node[] */
        $nodes = array_reduce(
            iterator_to_array(YieldNodes::from($folder)),
            new NodeReducer(
                ignoreTypes: IgnoreableTypes::VALUES,
                shouldNotStartWith: [
                    'App\\Kernel',
                    'App\\CLI\\',
                    'App\\DDD\\',
                    'Doctrine\\',
                    'Symfony\\',
                    'Psr\\',
                    'PhpParser\\',
                    'phpDocumentor\\',
                    'Monolog\\',
                    'OpenTelemetry\\',
                    'Rx\\',
                    'React\\',
                    'InfluxDB2\\',
                    'EasyCorp\\Bundle\\EasyAdminBundle\\',
                ],
                shouldNotContain: [
                    'array<',
                    'array{',
                ]
            ),
            []
        );

        $table = new Table($output);
        $table->setHeaders(['#', 'FQCN', 'Tag']);
        for ($i = 0; $i < count($nodes); ++$i) {
            $node = $nodes[$i];
            $table->addRow(
                [
                    $i + 1,
                    $node->fqcn,
                    self::createTag($node),
                ]
            );
        }

        $table->render();

        $report = array_reduce(
            $nodes,
            static function (Report $report, Node $node): Report {
                /** @var class-string $className */
                $className = $node->fqcn;

                return $report->withClassName($className);
            },
            Report::initial()
        );

        /** @var string[] */
        $forbidden = array_reduce(
            array_reduce(
                iterator_to_array(YieldRelations::fromFolder($folder)),
                new RelationReducer(
                    considerEdges: [Edge::PRODUCES, Edge::CONSUMES, Edge::THROWS],
                    ignoreTypes: IgnoreableTypes::VALUES),
                []
            ),
            static fn (array $descriptions, Relation $relation) => $relation->isForbidden()
                    ? [...$descriptions, $relation->getDescription()]
                    : $descriptions,
            []
        );

        if (count($report->uncategorized) > 0) {
            $io->section('Uncategorized Classes');
            foreach ($report->uncategorized as $className) {
                $io->text($className);
            }
        }

        if (count($forbidden) > 0) {
            $io->section('Forbidden Relations');
            foreach ($forbidden as $desc) {
                $io->text($desc);
            }
        }

        $reportJsPath = rtrim($baseDir, '\\/').DIRECTORY_SEPARATOR.'report'.DIRECTORY_SEPARATOR.'report.js';
        $this->safeReport($folder, $report, $forbidden, $reportJsPath);

        $io->info('Report written to: ./report/index.html');
    }

    private static function createTag(Node $node): string
    {
        /** @var class-string $className */
        $className = $node->fqcn;

        if (interface_exists($className)) {
            return '❓ Interface';
        }

        if (str_contains($className, 'Exception')) {
            return '❌ Exception';
        }

        try {
            $attribute = AttributeNameFactory::fromClassOrNull($className);

            return !is_null($attribute)
                ? sprintf('✅ %s', $attribute->value)
                : '🔥 uncategorized';
        } catch (AmbiguityException) {
            return '🧨 ambiguous';
        }
    }

    /**
     * @param string[] $forbiddenRelations
     */
    private function safeReport(string $folder, Report $report, array $forbiddenRelations, string $reportJsPath): void
    {
        $data = [
            'folder' => $folder,
            'classes' => $report,
            'forbidden' => $forbiddenRelations,
        ];

        $json = $this->serializer->serialize(
            $data,
            JsonEncoder::FORMAT, [
                JsonEncode::OPTIONS => JSON_PRETTY_PRINT,
            ]
        );

        $reportDir = dirname($reportJsPath);
        if (!is_dir($reportDir)) {
            mkdir($reportDir, 0777, true);
        }

        file_put_contents($reportJsPath, 'const reportData = '.$json.';');

        $resourceDir = __DIR__.'/../../resources/report';
        if (is_dir($resourceDir)) {
            $items = scandir($resourceDir);
            foreach ($items as $item) {
                if ('.' === $item || '..' === $item) {
                    continue;
                }

                copy($resourceDir.'/'.$item, $reportDir.'/'.$item);
            }
        }
    }
}

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
            ->addArgument('folder', InputArgument::REQUIRED, 'The source code folder to be checked')
            ->addOption('out-dir', null, InputOption::VALUE_REQUIRED, 'Base output directory for reports (defaults to project root)')
        ;
    }

    protected function execute(InputInterface $input, OutputInterface $output): int
    {
        $io = new SymfonyStyle($input, $output);

        try {
            self::doExecute($input, $output);

            return Command::SUCCESS;
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

        $destination = rtrim($baseDir, '\\/').DIRECTORY_SEPARATOR.'report'.DIRECTORY_SEPARATOR.'report.js';
        $this->safeReport($folder, $report, $forbidden, destination: $destination);

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
    private function safeReport(string $folder, Report $report, array $forbiddenRelations, string $destination): void
    {
        $data = [
            'folder' => $folder,
            'classes' => $report,
            'forbidden' => $forbiddenRelations,
        ];

        $json = $this->serializer->serialize(
            $data,
            JsonEncoder::FORMAT,
            [JsonEncode::OPTIONS => JSON_PRETTY_PRINT]
        );

        // Ensure destination directory exists (destination is typically 'report/report.js')
        $dir = dirname($destination);
        if (!is_dir($dir)) {
            mkdir($dir, 0777, true);
        }

        // Write the JS data file (report.js)
        file_put_contents($destination, 'const reportData = '.$json.';');

        // Ensure static assets (chart.js, styles.css) are available in the report directory.
        // Prefer packaged templates under resources/report, otherwise create sensible defaults.
        $resourceDir = __DIR__.'/../../resources/report';

        // chart.js
        if (is_file($resourceDir.'/chart.js')) {
            copy($resourceDir.'/chart.js', $dir.'/chart.js');
        } else {
            // default minimal chart script inspired by tbone's chart.js using ECharts
            $defaultChartJs = <<<'JS'
// Basic chart renderer expecting global reportData and echarts
document.addEventListener("DOMContentLoaded", function () {
    const { classes, forbidden, folder } = reportData;
    document.getElementById("reportFolder").textContent = `Report for ${folder}`;

    const colors = [
        "#3498db", "#e74c3c", "#1abc9c", "#95a5a6", "#2ecc71", "#f39c12", "#f1c40f", "#9b59b6", "#7f8c8d"
    ];

    const labels = Object.keys(classes).filter(key => Array.isArray(classes[key]));
    const values = labels.map(key => classes[key].length);
    const readableLabels = labels.map(key => key.replace(/_/g, ' '));

    const pieEl = document.getElementById('pieChart');
    const barEl = document.getElementById('barChart');
    if (pieEl && barEl && window.echarts) {
        const pieChart = echarts.init(pieEl);
        const pieData = readableLabels.map((label, index) => ({ name: label, value: values[index] }));
        pieChart.setOption({
            tooltip: { trigger: 'item', formatter: '{a} <br/>{b}: {c} ({d}%)' },
            legend: { orient: 'vertical', left: 'left' },
            series: [{ name: 'Classes', type: 'pie', radius: '50%', data: pieData, color: colors }]
        });

        const barChart = echarts.init(barEl);
        barChart.setOption({
            tooltip: { trigger: 'axis', axisPointer: { type: 'shadow' } },
            xAxis: { type: 'category', data: readableLabels, axisLabel: { rotate: 45 } },
            yAxis: { type: 'value', minInterval: 1 },
            series: [{ name: 'Count', type: 'bar', data: values, itemStyle: { color: params => colors[params.dataIndex % colors.length] } }]
        });

        window.addEventListener('resize', () => { pieChart.resize(); barChart.resize(); });
    }

    // populate classes table if present
    const container = document.getElementById('classesTableContainer');
    if (container) {
        const all = [];
        labels.forEach(category => {
            classes[category].forEach(c => all.push({ name: c, category }));
        });
        all.sort((a,b) => a.name.localeCompare(b.name));
        const table = document.createElement('table');
        table.style.width = '100%';
        table.style.borderCollapse = 'collapse';
        const thead = document.createElement('thead');
        const headerRow = document.createElement('tr');
        ['Class','Category'].forEach(h => {
            const th = document.createElement('th'); th.textContent = h; th.style.border = '1px solid #ddd'; th.style.padding = '8px'; th.style.background = '#f5f5f5'; headerRow.appendChild(th);
        });
        thead.appendChild(headerRow); table.appendChild(thead);
        const tbody = document.createElement('tbody');
        all.forEach((item, idx) => {
            const tr = document.createElement('tr'); if (idx%2===0) tr.style.background='#f9f9f9';
            const tdName = document.createElement('td'); tdName.textContent = item.name; tdName.style.border='1px solid #ddd'; tdName.style.padding='8px';
            const tdCat = document.createElement('td'); tdCat.textContent = item.category; tdCat.style.border='1px solid #ddd'; tdCat.style.padding='8px';
            tr.appendChild(tdName); tr.appendChild(tdCat); tbody.appendChild(tr);
        });
        table.appendChild(tbody); container.appendChild(table);
    }
});
JS;
            file_put_contents($dir.'/chart.js', $defaultChartJs);
        }

        // styles.css
        if (is_file($resourceDir.'/styles.css')) {
            copy($resourceDir.'/styles.css', $dir.'/styles.css');
        } else {
            $defaultCss = <<<'CSS'
body { font-family: system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial; margin: 16px; }
h1 { text-align: center; }
.chart-row { display:flex; gap:20px; justify-content:center; margin:20px 0; }
.chart-container { border:1px solid #ddd; border-radius:8px; padding:6px; }
#classesTableContainer { max-width: 1000px; margin: 0 auto; }
CSS;
            file_put_contents($dir.'/styles.css', $defaultCss);
        }

        // index.html referencing the static assets. Use ECharts CDN and include chart.js and report.js
        $indexHtml = <<<'HTML'
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="UTF-8" />
        <meta name="viewport" content="width=device-width, initial-scale=1.0"/>
        <title>Charts Report</title>

        <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600&display=swap" rel="stylesheet">
        <link rel="stylesheet" href="styles.css">

        <script src="https://cdnjs.cloudflare.com/ajax/libs/echarts/5.4.3/echarts.min.js"></script>
        <script src="report.js"></script>
    </head>
    <body>
        <h1 id="reportFolder" style="text-align: center;"></h1>

        <h2 style="text-align: center;">Class distribution</h2>

        <div class="chart-row" style="display: flex; gap: 20px; justify-content: center; margin: 20px 0;">
            <div class="chart-container" style="width: 45%; height: 400px; border: 1px solid #ddd; border-radius: 8px;">
                <div id="pieChart" style="width: 100%; height: 100%;"></div>
            </div>
            <div class="chart-container" style="width: 45%; height: 400px; border: 1px solid #ddd; border-radius: 8px;">
                <div id="barChart" style="width: 100%; height: 100%;"></div>
            </div>
        </div>

        <h2 style="text-align: center; margin-top: 40px;">All classes</h2>
        <div id="classesTableContainer"></div>

        <script src="chart.js"></script>
    </body>
</html>
HTML;

        file_put_contents($dir.'/index.html', $indexHtml);
    }
}

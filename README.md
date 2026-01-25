# Tactix

![app-ci-workflow](https://github.com/makomweb/tactix/actions/workflows/app-ci.yaml/badge.svg)

Tactical DDD, simplified for PHP: tag your classes (via PHP attributes) and validate missing tags + forbidden relations.

<p align="center">
  <img src="./assets/logo.png" alt="project-logo" width="300"/>
</p>

## Installation

```bash
composer require --dev makomweb/tactix
```

Requirements:
- PHP ^8.2
- your source code being tagged with [PHP Molecules](https://github.com/xmolecules/phpmolecules)

## Usage

### 1. Tag your classes

- install the tags package from PHP Molecules as a regular dependency via:

```bash
composer require xmolecules/phpmolecules
```

- tag your classes with the available attributes:

```php
use PHPMolecules\DDD\Attribute\AggregateRoot;
use PHPMolecules\DDD\Attribute\Entity;
use PHPMolecules\DDD\Attribute\ValueObject;
use PHPMolecules\DDD\Attribute\Service;
use PHPMolecules\DDD\Attribute\Factory;
use PHPMolecules\DDD\Attribute\Repository;

#[Entity]
final class User {}
```

### 2. Either Check your classes or folders manually

```php
use Tactix\Check;

Check::className(User::class);
Check::folder(__DIR__.'/src');
```

`Check` throws on violations:
- `Tactix\ClassViolationException`
- `Tactix\FolderViolationException`

Both exceptions contain a `$violations` property of type `array<Tactix\Violation>` to get further details about whether there are missing tags, ambiguity or forbidden relations.

### 3. Or generate a report for a specific folder

Tactix provides a Symfony Console command `tactix:report` that creates a static HTML report for a source folder.

```bash
# Run the report command for a folder.
bin/console tactix:report <folder>
# or, when installed as a dependency with optional output directory
vendor/bin/console tactix:report <folder> --out-dir=<out-dir>
# Exclude specific namespaces from the report (can be used multiple times)
vendor/bin/console tactix:report <folder> --exclude-namespace="App\\CLI\\" --exclude-namespace="App\\Infrastructure\\"
```

Options:
- `--out-dir`: Base output directory for reports (defaults to project root)
- `--exclude-namespace`: Namespace prefix to exclude from the report (can be used multiple times). By default, `Doctrine\\`, `Symfony\\`, and `Psr\\` namespaces are excluded. When you provide your own exclusions, you replace these defaults.

Notes:
- the output files index.html, report.js, chart.js, styles.css are created
- the command prints discovered classes and forbidden relations and finishes with `Report written to: ./report/index.html`.

## Forbidden relations

Tactix includes a small built-in blacklist (see `Tactix\Forbidden`) and reports violations as:

```
(MyValueObject)-[consumes]->(MyEntity) is a forbidden relation! ❌
```

## Contributing

This repository includes a lightweight container workflow to run tests and analysis in a reproducible environment.

- Build image: `make build` (requires Docker and Docker Compose)
- Open shell in running container: `make shell`

Guidelines for contributing improvements:

- Install dependencies via `composer install` from within the development container
- Run the QA suite locally before opening a PR: `composer qa` (runs PHPStan, php-cs-fixer and PHPUnit).
- Prefer adding unit tests for new features or bug fixes; tests are in `tests/Unit`.
- Follow PHPStan and php-cs-fixer rules. Running `composer cs` will apply fixer changes.
- If you use the container workflow, prefer running tests inside the container to match CI.
- Open pull requests targeting the `master` branch with a clear description of the change and a short test plan.

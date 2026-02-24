# Tactix

![app-ci-workflow](https://github.com/makomweb/tactix/actions/workflows/app-ci.yaml/badge.svg)
[![codecov](https://codecov.io/github/makomweb/tactix/graph/badge.svg?token=LBDBUCWP6A)](https://codecov.io/github/makomweb/tactix)

Tactical DDD, simplified for PHP. Tag your classes via PHP attributes and analyze forbidden relations.

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
use Tactix\Blacklist;
use Tactix\Check;
use Tactix\YieldViolations;

$check = new Check(new YieldViolations(new Blacklist(Blacklist::DEFAULT_DATA)));
$check->className(User::class);

// or:
$check->folder(__DIR__.'/src');
```

`Check` throws on violations:
- `Tactix\ClassViolationException`
- `Tactix\FolderViolationException`

Both exceptions contain a `$violations` property of type `array<Tactix\Violation>` to get further details about whether there are missing tags, ambiguity or forbidden relations.

```
(MyValueObject)-[consumes]->(MyEntity) is a forbidden relation! ❌
```

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

## Framework Integration

Tactix is framework-agnostic. The core package does not depend on any specific full-stack framework and can be integrated into different environments.

### Symfony

Tactix provides a Symfony Bundle that automatically configures the `tactix:report` command and provides a `Blacklist` service via dependency injection.

#### 1. Register the Bundle

The bundle is auto-discovered via Symfony Flex. If you're not using Flex or need manual registration:

```php
// config/bundles.php
return [
    // ...
    Tactix\TactixBundle::class => ['all' => true],
];
```

#### 2. Configure Blacklist Rules

Create `config/packages/tactix.yaml`:

```yaml
tactix:
  blacklist:
    Entity:
      - Factory
      - Service
      - AggregateRoot
    ValueObject:
      - Entity
      - AggregateRoot
      - Repository
      - Factory
      - Service
    AggregateRoot:
      - Factory
    Repository:
      - Factory
      - Service
    Factory:
      - Repository
    Service: []
```

Or inherit from defaults by creating an empty config (uses `Blacklist::DEFAULT_DATA`):

```yaml
tactix: ~
```

#### 3. Use in Your Application

The `Blacklist` service is automatically available via dependency injection:

```php
use Tactix\Blacklist;
use Tactix\YieldViolations;

final class MyArchitectureValidator
{
    public function __construct(private Blacklist $blacklist) {}

    public function validateFolder(string $folder): void
    {
        $violations = new YieldViolations($this->blacklist);
        $results = iterator_to_array($violations->fromFolder($folder));
        
        if (!empty($results)) {
            throw new \RuntimeException('Architecture violations found!');
        }
    }
}
```

Or create a `Check` instance for convenient validation:

```php
$check = new Check(new YieldViolations($this->blacklist));
$check->className(MyClass::class);  // throws on violation
```

### Laravel

Tactix can be integrated into Laravel projects with a custom service provider.

#### 1. Create a Service Provider

```php
<?php
// app/Providers/BlacklistProvider.php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Symfony\Component\Yaml\Yaml;
use Tactix\BlacklistFactory;

class BlacklistProvider extends ServiceProvider
{
    public function register()
    {
        $this->app->singleton(Blacklist::class, function () {
            $configPath = config_path('tactix.yaml');
            
            return file_exists($configPath)
              ? BlacklistFactory::fromYaml($configPath)
              : BlacklistFactory::DEFAULT();
        });
    }
}
```

#### 2. Register the Provider

Add to `config/app.php`:

```php
'providers' => [
    // ...
    App\Providers\BlacklistProvider::class,
],
```

#### 3. Create Configuration File

Create `config/tactix.yaml` (same format as Symfony):

```yaml
tactix:
  blacklist:
    Entity:
      - Factory
      - Service
      - AggregateRoot
    # ... rest of configuration
```

#### 4. Use in Your Application

```php
use Tactix\Blacklist;
use Tactix\YieldViolations;

class ArchitectureCommand extends Command
{
    public function handle(Blacklist $blacklist)
    {
        $violations = new YieldViolations($blacklist);
        $results = iterator_to_array($violations->fromFolder(app_path()));
        
        $this->info(count($results) . ' violations found');
    }
}
```

### Standalone Usage (No Framework)

For CLI scripts or standalone applications:

```php
use Tactix\Blacklist;
use Tactix\Check;
use Tactix\YieldViolations;
use Symfony\Component\Yaml\Yaml;

// Load blacklist from YAML
$yaml = Yaml::parseFile('tactix.yaml');
$blacklist = BlacklistFactory::fromYaml($yaml);

// Create, check and validate
$check = new Check(new YieldViolations($blacklist));
$check->folder('./src');
```

## Contributing

See [CONTRIBUTING.md](./CONTRIBUTING.md) for details.

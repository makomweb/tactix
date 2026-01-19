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

### 2. Check your classes or folders

```php
use Tactix\Check;

Check::className(User::class);
Check::folder(__DIR__.'/src');
```

`Check` throws on violations:
- `Tactix\ClassViolationException`
- `Tactix\FolderViolationException`

Both exceptions contain a `$violations` property of type `array<Tactix\Violation>` to get further details about wether there are missing tags, ambiguity or forbidden relations.

## Forbidden relations

Tactix includes a small built-in blacklist (see `Tactix\Forbidden`) and reports violations as:

```
(MyValueObject)-[consumes]->(MyEntity) is a forbidden relation! ❌
```

## Container-based workflow

This repository includes a lightweight container workflow to run tests and analysis in a reproducible environment.

- Build image: `make build` (requires Docker and Docker Compose v2+)
- Start service: `make up`
- Install dependencies inside container: `docker exec -u 1000 tactix sh -c 'cd /var/www/project && composer install --no-interaction'`
- Run tests in container: `make test` or `docker exec -u 1000 tactix sh -c 'cd /var/www/project && vendor/bin/phpunit --configuration phpunit.xml.dist --testdox'`
- Open shell in running container: `make shell`

Notes:
- The container mounts the repository at `/var/www/project` and runs as UID 1000:GID 1001.
- The Dockerfile used is at `docker/php/Dockerfile` and is based on the official `php:8.4-cli` image. It installs Composer and Xdebug to allow coverage reporting.

## Contributing

Guidelines for contributing improvements:

- Run the QA suite locally before opening a PR: `composer qa` (runs PHPStan, php-cs-fixer and PHPUnit).
- Prefer adding unit tests for new features or bug fixes; tests are in `tests/Unit`.
- Follow PHPStan and php-cs-fixer rules. Running `composer cs` will apply fixer changes.
- If you use the container workflow, prefer running tests inside the container to match CI.
- Open pull requests targeting the `master` branch with a clear description of the change and a short test plan.


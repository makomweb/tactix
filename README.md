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

- install tags via `composer require xmolecules/phpmolecules`

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

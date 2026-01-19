# AGENTS

This document collects concrete, observed information an autonomous agent needs to work effectively with this repository.

It only documents what is present in the repository. Do not assume other commands, tools, or conventions exist unless listed here.

---

## Quick facts

- Project name (composer): makomweb/tactix (composer.json:1-3)
- Language: PHP (composer.json:24)
- Required PHP version: ^8.2 (composer.json:24)
- Type: library (composer.json:4)
- PSR-4 autoload: `Tactix\` => `src/` (composer.json:6-10)
- Dev autoload for tests: `Tactix\Tests\` => `tests/` (composer.json:11-15)
- Key dependencies (composer.json:23-27): `nikic/php-parser`, `xmolecules/phpmolecules`
- Dev dependencies (composer.json:28-31): `phpstan/phpstan`, `phpunit/phpunit`, `friendsofphp/php-cs-fixer`

## Repo layout (observed)

- src/
  - Analyzer/ (many analyzers and helpers)
  - Assert/
  - AmbiguityException.php
  - AttributeName.php
  - AttributeNameFactory.php
  - Check.php
  - ClassViolationException.php
  - FolderViolationException.php
  - Forbidden.php
  - IgnoreableTypes.php
  - Violation.php
  - ViolationException.php
  - YieldViolations.php

- tests/
  - Data/ (fixture classes used by unit tests)
  - Unit/ (PHPUnit tests)
  - bootstrap.php (tests/bootstrap.php:1-7)

- CI: .github/workflows/app-ci.yaml (runs phpstan, php-cs-fixer, phpunit)
- phpstan.neon.dist (phpstan configuration)
- phpunit.xml.dist (phpunit configuration and coverage reporting)
- composer.json / composer.lock
- .php-cs-fixer.dist.php (present)

## How to set up and run the project (observed commands)

1. Install dependencies

- composer install --prefer-dist --no-scripts (used in GitHub Actions: .github/workflows/app-ci.yaml:34-35)
- Locally: `composer install`

2. QA / style / tests (composer scripts defined in composer.json:scripts)

- Run all QA tools: `composer qa` (composer.json:33-38)
  - runs: `vendor/bin/phpstan analyse --memory-limit=1G`, `vendor/bin/php-cs-fixer fix`, `vendor/bin/phpunit`
- Run static analyzer: `composer sa` → `vendor/bin/phpstan analyse --memory-limit=1G` (composer.json:39)
- Run tests: `composer test` → `vendor/bin/phpunit` (composer.json:40)
- Run code style fixer: `composer cs` → `vendor/bin/php-cs-fixer fix` (composer.json:41)

Direct invocations (used by CI):
- `vendor/bin/phpstan analyse` (.github/workflows/app-ci.yaml:37-38)
- `vendor/bin/php-cs-fixer check` (.github/workflows/app-ci.yaml:40-41)
- `vendor/bin/phpunit` (.github/workflows/app-ci.yaml:43-44)

Notes:
- CI sets `PHP_CS_FIXER_IGNORE_ENV=1` and `DATABASE_URL="sqlite:///:memory:"` (.github/workflows/app-ci.yaml:20-22)
- PHPUnit bootstrap: `tests/bootstrap.php` (phpunit.xml.dist:8)

## Testing patterns

- PHPUnit is used (phpunit.xml.dist)
- Tests are under `tests/Unit` and use PHPUnit attributes (e.g. `#[Test]`) (tests/Unit/*.php)
- Test fixtures/live examples are in `tests/Data` and are loaded via composer autoloading (composer.json:11-15)
- Tests expect thrown domain exceptions; e.g. `Check::className(...)` and `Check::folder(...)` throw `ClassViolationException` or `FolderViolationException` with a public `violations` property (see tests/Unit/* and src/Check.php:12-31)

## Static analysis and code-style

- phpstan: configured at max level in phpstan.neon.dist (phpstan.neon.dist:1-4)
  - Paths analysed: src/ and tests/
- php-cs-fixer: config file present (.php-cs-fixer.dist.php)
- GitHub Actions run phpstan, php-cs-fixer check and phpunit (see .github/workflows/app-ci.yaml)

## Key runtime behavior and patterns (observed in source)

- Primary entry points (public API):
  - Tactix\Check::className(string $className) (src/Check.php:12-19)
  - Tactix\Check::folder(string $folder) (src/Check.php:24-31)

- Both methods gather violations from `YieldViolations::fromClassName` / `YieldViolations::fromFolder` and throw exceptions when violations exist.
  - Exceptions thrown: `Tactix\ClassViolationException` and `Tactix\FolderViolationException` (src/Check.php:14-30)
  - Exception objects expose a `violations` property (tests assert on that)

- Forbidden relations are modelled in `Tactix\Forbidden` (src/Forbidden.php). Important observed behavior:
  - `Forbidden::check(AttributeName $from, AttributeName $to): bool` checks against a blacklist (src/Forbidden.php:19-27)
  - If `from` is not present in the hard-coded blacklist, it throws a `LogicException` instructing to add an entry (src/Forbidden.php:27)
  - The blacklist is created by `createBlackList()` and contains `AttributeName` enum-like constants such as `AttributeName::ENTITY`, `AttributeName::VALUE_OBJECT`, etc. (src/Forbidden.php:31-50)

- Attribute / tag handling depends on external `xmolecules/phpmolecules` package (README usage, composer.json:25-26). Tests use attributes on fixture classes in `tests/Data`.

- Analyzer implementation is under `src/Analyzer/` — multiple classes for parsing PHP files and reducing relations. Use these files when modifying or extending analysis.

## Naming conventions and code-style patterns (observed)

- PSR-4 namespaces: `Tactix\` for src/, `Tactix\Tests\` for tests (composer.json:6-14)
- Strict types declaration: files declare `declare(strict_types=1);` (many files)
- Readonly classes: some classes use `final readonly class` (src/Check.php:7)
- Exceptions follow `*Exception` suffix and are placed in project root (e.g. `ClassViolationException.php`)
- Test methods use descriptive names and `#[Test]` attribute (tests/Unit/*)

## Files to inspect when making changes

- Public API: `src/Check.php` (src/Check.php:7-31)
- Violation models & exceptions: `src/Violation.php`, `src/ViolationException.php`, `src/ClassViolationException.php`, `src/FolderViolationException.php`
- Forbidden rules: `src/Forbidden.php` (src/Forbidden.php:19-51)
- Analyzer logic: `src/Analyzer/*` (multiple files)
- Tests & fixtures: `tests/Unit/*`, `tests/Data/*`
- CI workflow: `.github/workflows/app-ci.yaml`
- Static analysis: `phpstan.neon.dist`
- Composer scripts: `composer.json:scripts`

## Gotchas and non-obvious observations (explicit)

- `Forbidden::check` will throw a `LogicException` if a "from" `AttributeName` is not present in the hard-coded blacklist (src/Forbidden.php:27). If you add a new `AttributeName` you must also update the blacklist or handle the exception.

- Composer `qa` script runs the fixer with `vendor/bin/php-cs-fixer fix` which will modify files in-place. CI runs `php-cs-fixer check` (app-ci.yaml:40) which only checks; the `composer qa` usage differs (fix vs check).

- PHPUnit is configured to stop on error (phpunit.xml.dist:9) and has xdebug coverage settings enabled in the configuration file (phpunit.xml.dist:19-20).

- Tests rely on autoloading and the test bootstrap `tests/bootstrap.php` which calls the Composer autoloader (tests/bootstrap.php:3). Ensure composer dependencies are installed before running tests.

## What I searched for (discovery steps performed)

- repo root listing (ls)
- looked for agent/rule files: none found
- composer.json (read for scripts, autoload, requirements)
- phpunit.xml.dist (read for test config)
- phpstan.neon.dist (read for static analysis config)
- README.md (usage notes and examples)
- .github/workflows/app-ci.yaml (CI steps)
- representative source files: src/Check.php, src/Forbidden.php, src/Analyzer/*
- tests: tests/Unit/* and tests/Data/*

---

If you want this file updated (expanded, reorganized, or to include additional file:line references), run this command again and specify what to add.

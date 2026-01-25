# AGENTS

Concise reference for agents working on this repository. Only document facts observed in the repository — do not invent commands or conventions.

---

## Quick facts

- Language: PHP (composer.json)
- PHP requirement: ^8.2 (composer.json)
- Package name: makomweb/tactix (composer.json)
- Package type: library
- Autoload (PSR-4): `Tactix\` -> `src/` (composer.json)
- Test autoload (PSR-4): `Tactix\Tests\` -> `tests/` (composer.json)
- Tests: PHPUnit (vendor/bin/phpunit)
- Static analysis: PHPStan (phpstan.neon.dist)
- Code style: PHP-CS-Fixer (.php-cs-fixer.dist.php)
- CI: GitHub Actions workflow .github/workflows/app-ci.yaml runs phpstan, php-cs-fixer (check) and phpunit

## Repository layout (observed)

- src/ — library code (primary implementation)
  - Analyzer/ — static PHP source analysis helpers
  - DependencyInjection/ — Symfony DI extension & configuration
  - Command/ — console/report command
  - Assert/ — assertion helpers used in tests
  - Several root classes: Check.php, Forbidden.php, AttributeName.php, etc.
- tests/ — test code
  - Unit/ — PHPUnit unit tests
  - Data/ — test fixtures (small PHP files used by analyzers)
- resources/ — static JS/CSS report template (resources/report)
- docker/ — Dockerfile for development image (docker/php/Dockerfile)
- .github/workflows/app-ci.yaml — CI definition
- Makefile — convenience targets for container workflow
- composer.json / composer.lock — dependencies, scripts and QA commands
- phpunit.xml.dist — PHPUnit configuration
- phpstan.neon.dist — PHPStan configuration

## How to run (local and container)

Local (host):
- Install deps: composer install
- Run tests: composer test  (equivalent to vendor/bin/phpunit)
- Run QA (static analysis + style + tests): composer qa
- Run phpstan only: composer sa
- Run php-cs-fixer (will modify files): composer cs

Container (recommended for reproducible env):
- Build image: make build  (Makefile -> `docker compose build`)
- Start service: make up    (Makefile -> `docker compose up -d`)
- Enter shell in running container: make shell  (opens sh)
- Install dependencies inside container: docker exec -u 1000 tactix sh -c 'cd /var/www/project && composer install --no-interaction'
- Run tests inside container: make test (Makefile target runs vendor/bin/phpunit in container)
- Run QA inside container: make qa  (runs composer qa inside container)

Notes:
- Makefile uses `docker compose` (Docker Compose v2+). Service/container name is `tactix` and repository is mounted at `/var/www/project` inside the container.
- Container runs as UID 1000:GID 1001 (image built by docker/php/Dockerfile).
- The container sets XDEBUG_MODE=coverage to enable coverage when xdebug is present.

## CI details

- Workflow: .github/workflows/app-ci.yaml
- Runs on ubuntu-latest and tests matrix contains php-version: 8.4
- Steps: checkout, setup-php (shivammathur/setup-php), composer install (no-scripts), phpstan analyse, php-cs-fixer check, phpunit
- Environment: sets PHP_CS_FIXER_IGNORE_ENV=1 and DATABASE_URL=sqlite:///:memory:

## Code organization and patterns

- Attributes-based tagging: The library works with PHPMolecules attributes (AggregateRoot, Entity, ValueObject, Factory, Service, Repository). See src/AttributeName.php for mapping and helpers.
  - AttributeName enum maps semantic names to actual attribute class FQCNs and provides AttributeName::fromAttributeClass(string) (src/AttributeName.php)
- Static analysis: src/Analyzer contains many classes to parse PHP files, analyze nodes, relations and produce Violation objects.
- Public API: Tactix\Check (src/Check.php) provides helpers to check a single class (Check::className) or a folder (Check::folder).
- Forbidden relations: src/Forbidden.php contains a hard-coded blacklist (createBlackList()). If you add new AttributeName enum values you must update this list or Forbidden::check() will throw a LogicException (see src/Forbidden.php:19-28).
- Reports: resources/report contains a small static HTML/JS/CSS report template used by the Report command (src/Command/*).
- DependencyInjection: Symfony DI extension and configuration present under src/DependencyInjection (Configuration.php, TactixExtension.php) for integration in Symfony apps.

## Testing strategy and patterns

- Tests live in tests/Unit and use PHPUnit. Fixtures for analyzer tests are small PHP files under tests/Data.
- Bootstrap file: tests/bootstrap.php requires vendor/autoload.php and sets umask when APP_DEBUG is true.
- Common test helpers/assertions are available in src/Assert (and used in tests).
- When adding features, add unit tests in tests/Unit and fixtures in tests/Data as needed.

## Composer scripts and QA

- composer test -> vendor/bin/phpunit
- composer qa -> runs phpstan analyse --memory-limit=1G, php-cs-fixer fix, and phpunit (note: php-cs-fixer runs in fix mode here)
- composer sa -> phpstan only
- composer cs -> php-cs-fixer fix

CI runs php-cs-fixer in check mode (see .github/workflows/app-ci.yaml), while composer qa runs the fixer in fix mode locally. Be mindful: composer qa will modify files in-place.

## Important gotchas / notes for agents

- Forbidden blacklist: src/Forbidden.php creates an explicit list for every AttributeName. Forbidden::check() will throw a LogicException if a 'from' AttributeName has no entry — update createBlackList() when adding enum values.
  - Reference: src/Forbidden.php:19-28 and createBlackList() implementation
- PHP version: CI uses PHP 8.4 in the workflow (matrix). composer.json requires ^8.2.
- php-cs-fixer: composer qa runs php-cs-fixer in fix mode. CI runs the fixer in check mode — prefer running QA inside the container to reproduce CI.
- Tests and analyzers rely on small PHP fixtures in tests/Data; maintain their formatting and namespaces when adding new cases.

## Files of interest (quick jump targets)

- src/AttributeName.php
- src/Forbidden.php
- src/Check.php
- src/Analyzer/PhpFileAnalyzer.php
- src/Command/ReportCommand.php
- composer.json
- phpunit.xml.dist
- phpstan.neon.dist
- Makefile
- .github/workflows/app-ci.yaml

---

Update or extend this file only with facts observed in the repository.

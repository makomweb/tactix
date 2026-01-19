# AGENTS

Concise reference for agents working on this repository. Only documents observed, current project facts and commands.

---

## Quick facts

- Language: PHP (composer.json)
- PHP requirement: ^8.2
- Package: makomweb/tactix
- PSR-4 autoload: `Tactix\` -> `src/`; tests autoload `Tactix\Tests\` -> `tests/`
- Tests: PHPUnit (vendor/bin/phpunit)
- Static analysis: PHPStan (phpstan.neon.dist)
- Code style: php-cs-fixer (.php-cs-fixer.dist.php)

## Repo layout (observed)

- src/ (library code)
- tests/Unit (PHPUnit tests) and tests/Data (fixtures)
- .github/workflows/app-ci.yaml (CI runs phpstan, php-cs-fixer, phpunit)
- docker/ (docker/php/Dockerfile) — image built from php:8.4-cli, includes xdebug and composer
- docker-compose.yml — service name: `tactix`, mounts repository at `/var/www/project`
- Makefile — targets: build, up, down, test-container, shell

## Container-based workflow (what to use)

Observed, supported workflow to run tests in a container using standard tooling:

- Build image: make build
- Start service: make up
- Install deps (inside container): docker exec -u 1000 tactix sh -c 'cd /var/www/project && composer install --no-interaction'
- Run tests (inside service): make test-container  OR docker exec -u 1000 tactix sh -c 'cd /var/www/project && vendor/bin/phpunit --configuration phpunit.xml.dist --testdox'
- Open shell in running container: make shell

Makefile runs `docker compose` so use Docker Compose v2+ (command: `docker compose ...`).

Notes about the container setup:

- Service key in docker-compose.yml is `tactix` and container_name is `tactix`.
- Repository is mounted at `/var/www/project` inside the container.
- Container runs as UID 1000:GID 1001 (user: "1000:1001").
- Dockerfile (docker/php/Dockerfile) is based on `php:8.4-cli`, installs xdebug and copies Composer binary from the composer image.
- The compose service sets `XDEBUG_MODE=coverage` so PHPUnit can generate coverage when xdebug is present.

## PHPUnit configuration

- PHPUnit configuration file: `phpunit.xml.dist`.
- PHPUnit is configured to NOT stop on errors (stopOnError="false").
- Tests bootstrap via `tests/bootstrap.php` which requires Composer autoloader; ensure dependencies installed before running tests.

## Commands (summary)

- Local composer install: `composer install`
- Run tests locally (host): `composer test` (vendor/bin/phpunit)
- Run QA: `composer qa` (phpstan, php-cs-fixer, phpunit)
- Container build: `make build`
- Container up: `make up`
- Run tests in container: `make test-container` (invokes `docker compose run --rm tactix vendor/bin/phpunit ...`)
- Shell into running container: `make shell`

## Gotchas / important notes

- If you add new AttributeName values, update the hard-coded blacklist in `src/Forbidden.php` or `Forbidden::check()` will throw a LogicException.
- `composer qa` runs php-cs-fixer in "fix" mode (will modify files in-place); CI uses php-cs-fixer check.
- The container workflow relies on the docker/php/Dockerfile. If you remove or change it, update docker-compose.yml accordingly.

---

Update or extend this file only with facts observed in the repository.

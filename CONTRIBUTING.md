# Contributing

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

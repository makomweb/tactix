CONTAINER_NAME ?= tactix

.PHONY: build up down test shell

build:
	@docker compose build --pull --no-cache

up:
	@docker compose up -d --remove-orphans

down:
	@docker compose down

# Run PHPUnit inside the container (starts a one-off container if not running)
test:
	@docker compose run --rm php vendor/bin/phpunit --configuration phpunit.xml.dist $(TESTARGS)

# Open an interactive shell in the running container
shell:
	@docker exec -it $(CONTAINER_NAME) sh

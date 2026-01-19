CONTAINER_NAME ?= tactix

.PHONY: build up down test shell

build:
	@docker compose build --pull --no-cache

up:
	@docker compose up -d --remove-orphans

down:
	@docker compose down

# Run all QA scripts in the container
qa:
	@docker compose exec tactix composer qa
	
# Run PHPUnit inside the container
test:
	@docker compose exec tactix vendor/bin/phpunit --configuration phpunit.xml.dist $(TESTARGS)

# Open an interactive shell in the running container
shell:
	@docker compose exec -it $(CONTAINER_NAME) sh

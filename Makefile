CONTAINER_NAME ?= tactix

.PHONY: build up down test shell

build:
	@docker compose build --pull --no-cache

up:
	@docker compose up -d --remove-orphans

down:
	@docker compose down

qa:
	@docker compose exec -it $(CONTAINER_NAME) composer qa
	
test:
	@docker compose exec -it $(CONTAINER_NAME) vendor/bin/phpunit --configuration phpunit.xml.dist $(TESTARGS)

shell:
	@docker compose exec -it $(CONTAINER_NAME) sh

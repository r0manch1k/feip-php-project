-include .env.local
export

DOCKER_COMPOSE = docker compose --env-file .env.local
PHP = $(DOCKER_COMPOSE) exec -T php

# CLEANING

rm-volumes:
	$(DOCKER_COMPOSE) down -v
	docker volume prune -a -f

rm-images:
	$(DOCKER_COMPOSE) down --rmi all
	docker image prune -a -f

rm-containers:
	docker container prune -f

rm-networks:
	docker network prune -f

rm-system: 
	docker system prune --volumes -f

rm-all: rm-volumes rm-images rm-containers rm-system rm-networks

# DOCKER

build:
	$(DOCKER_COMPOSE) build

up:
	$(DOCKER_COMPOSE) up -d

up-service:
	$(DOCKER_COMPOSE) up -d --no-deps --build $(SERVICE)

up-logs:
	$(DOCKER_COMPOSE) up

up-logs-service:
	$(DOCKER_COMPOSE) up --no-deps --build $(SERVICE)

down:
	$(DOCKER_COMPOSE) down

# COMPOSER 

composer-install:
	$(PHP) composer install

# PSALM

psalm:
	$(PHP) vendor/bin/psalm

# TESTS

test-all:
	$(PHP) ./vendor/bin/phpunit --bootstrap vendor/autoload.php

test-services:
	$(PHP) ./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/Service

test-controllers:
	$(PHP) ./vendor/bin/phpunit --bootstrap vendor/autoload.php tests/Controller


.PHONY: rm-volumes rm-images rm-containers rm-networks rm-system rm-all build up \
		up-service up-logs up-logs-service down composer-install psalm test-all \
		test-services test-controllers
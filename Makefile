-include .env.local
export

DOCKER_COMPOSE = docker compose --env-file .env.local
PHP = $(DOCKER_COMPOSE) exec -T php

# CLEANING

.PHONY: rm-volumes
rm-volumes:
	$(DOCKER_COMPOSE) down -v
	docker volume prune -a -f

.PHONY: rm-images
rm-images:
	$(DOCKER_COMPOSE) down --rmi all
	docker image prune -a -f

.PHONY: rm-containers
rm-containers:
	docker container prune -f

.PHONY: rm-networks
rm-networks:
	docker network prune -f

.PHONY: rm-system
rm-system: 
	docker system prune --volumes -f

.PHONY: rm-all
rm-all: rm-volumes rm-images rm-containers rm-system rm-networks

# DOCKER

.PHONY: build
build:
	$(DOCKER_COMPOSE) build

.PHONY: up
up:
	$(DOCKER_COMPOSE) up -d

.PHONY: up-service
up-service:
	$(DOCKER_COMPOSE) up -d --no-deps --build $(SERVICE)

.PHONY: up-logs
up-logs:
	$(DOCKER_COMPOSE) up

.PHONY: up-logs-service
up-logs-service:
	$(DOCKER_COMPOSE) up --no-deps --build $(SERVICE)

.PHONY: down
down:
	$(DOCKER_COMPOSE) down

# DOCTRINE

.PHONY: doctrine-create
doctrine-create:
	$(PHP) bin/console doctrine:database:create

.PHONY: doctrine-diff
doctrine-diff:
	$(PHP) bin/console doctrine:migrations:diff

.PHONY: doctrine-migrate
doctrine-migrate:
	$(PHP) bin/console doctrine:migrations:migrate

.PHONY: doctrine-fixtures
doctrine-fixtures:
	$(PHP) bin/console --env=test doctrine:database:create

.PHONY: doctrine-schema
doctrine-schema:
	$(PHP) bin/console --env=test doctrine:schema:create

.PHONY: doctrine-drop
doctrine-drop:
	$(PHP) bin/console --env=test doctrine:database:drop --force

.PHONY: doctrine-create-test
doctrine-create-test:
	$(PHP) bin/console doctrine:database:create --env=test

.PHONY: doctrine-migrate-test
doctrine-migrate-test:
	$(PHP) bin/console doctrine:migrations:migrate -n --env=test

.PHONY: doctrine-drop-test
doctrine-drop-test:
	$(PHP) bin/console doctrine:database:drop --force --env=test

# COMPOSER 

.PHONY: composer-install
composer-install:
	$(PHP) composer install

# PSALM

.PHONY: psalm
psalm:
	$(PHP) vendor/bin/psalm

# TESTS

.PHONY: test-all
test-all:
	$(PHP) ./vendor/bin/phpunit --bootstrap tests/bootstrap.php

.PHONY: test-services
test-services:
	$(PHP) ./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/Service

.PHONY: test-controllers
test-controllers:
	$(PHP) ./vendor/bin/phpunit --bootstrap tests/bootstrap.php tests/Controller

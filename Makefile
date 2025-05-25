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

.PHONY: migrations-clean
migrations-clean:
	rm -f migrations/*.php

.PHONY: doctrine-create
doctrine-create:
	$(PHP) bin/console doctrine:database:create

.PHONY: doctrine-diff
doctrine-diff:
	$(PHP) bin/console doctrine:migrations:diff --formatted

.PHONY: doctrine-migrate
doctrine-migrate:
	$(PHP) bin/console doctrine:migrations:migrate

.PHONY: doctrine-schema
doctrine-schema:
	$(PHP) bin/console doctrine:schema:create

.PHONY: doctrine-drop
doctrine-drop:
	$(PHP) bin/console doctrine:database:drop --force

.PHONY: doctrine-fixtures
doctrine-fixtures-load:
	$(PHP) bin/console --env=test doctrine:fixtures:load

.PHONY: doctrine-create-test
doctrine-create-test:
	$(PHP) bin/console --env=test doctrine:database:create

.PHONY: doctrine-diff-test
doctrine-diff-test:
	$(PHP) bin/console --env=test doctrine:migrations:diff --formatted

.PHONY: doctrine-migrate-test
doctrine-migrate-test:
	$(PHP) bin/console --env=test doctrine:migrations:migrate -n

.PHONY: doctrine-drop-test
doctrine-drop-test:
	$(PHP) bin/console --env=test doctrine:database:drop --force

# COMPOSER 

.PHONY: composer-install
composer-install:
	$(PHP) composer install

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

# PSALM

.PHONY: psalm
psalm:
	$(PHP) ./vendor/bin/psalm

.PHONY: psalm-clear-cache
psalm-clear-cache:
	$(PHP) ./vendor/bin/psalm --clear-cache

# PHPCS

.PHONY: phpcs
phpcs:
	$(PHP) ./vendor/bin/phpcs

.PHONY: phpcbf
phpcbf:
	$(PHP) ./vendor/bin/phpcbf

.PHONY: phpcs-file
phpcs-file:
	$(PHP) ./vendor/bin/phpcs $(FILE)

.PHONY: phpcbf-file
phpcbf-file:
	$(PHP) ./vendor/bin/phpcbf $(FILE)

.PHONY: php-cs-fixer
php-cs-fixer:
	$(PHP) ./vendor/bin/php-cs-fixer --allow-risky=yes fix

# AUTH

.PHONY: generate-keypair
generate-keypair:
	$(PHP) bin/console lexik:jwt:generate-keypair --overwrite

.PHONY: create-admin
create-admin:
	$(PHP) bin/console app:create:admin $(PHONE) $(PASSWORD)

# TELEGRAM

.PHONY: set-webhook
set-webhook:
	$(PHP) bin/console telegram:webhook:set
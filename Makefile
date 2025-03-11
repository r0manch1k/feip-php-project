-include .env.local
export

DOCKER_COMPOSE = docker compose --env-file .env.local

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


.PHONY: rm-volumes rm-images rm-containers rm-networks rm-system rm-all build up up-service up-logs up-logs-service down
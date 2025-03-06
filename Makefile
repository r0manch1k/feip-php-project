DOCKER_COMPOSE_DEV = docker-compose -f ./.docker/docker-compose.dev.yml
# DOCKER_COMPOSE_PROD = docker-compose -f ./.docker/docker-compose.prod.yml -p tye

rm-volumes:
	$(DOCKER_COMPOSE_DEV) down -v
	docker volume prune -a -f

rm-images:
	$(DOCKER_COMPOSE_DEV) down --rmi all
	docker image prune -a -f

rm-containers:
	docker container prune -f

rm-networks:
	docker network prune -f

rm-system: 
	docker system prune --volumes -f

rm-all: rm-volumes rm-images rm-containers rm-system rm-networks

# DEV

build-dev:
	$(DOCKER_COMPOSE_DEV) build

up-dev:
	$(DOCKER_COMPOSE_DEV) up -d

up-service-dev:
	$(DOCKER_COMPOSE_DEV) up -d --no-deps --build $(SERVICE)

up-logs-dev:
	$(DOCKER_COMPOSE_DEV) up

up-logs-service-dev:
	$(DOCKER_COMPOSE_DEV) up --no-deps --build $(SERVICE)

down-dev:
	$(DOCKER_COMPOSE_DEV) down


.PHONY: build-dev up-dev up-logs-dev down-dev
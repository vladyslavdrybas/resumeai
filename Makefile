#!make
include .env

ssh-agent-start:
	 eval `ssh-agent`

echo-env:
	echo ${HOST}
	echo ${APP_NAME}
	echo ${PROXY_NETWORK}
	echo ${APP_NETWORK}

app-run:
	make echo-env
	docker network ls|grep ${PROXY_NETWORK} > /dev/null || docker network create ${PROXY_NETWORK}
	docker network ls|grep ${DATABASE_NETWORK} > /dev/null || docker network create ${DATABASE_NETWORK}
	docker compose -f docker-compose.yml up -d --remove-orphans

app-run-local:
	make echo-env
	docker network ls|grep ${PROXY_NETWORK} > /dev/null || docker network create ${PROXY_NETWORK}
	docker network ls|grep ${DATABASE_NETWORK} > /dev/null || docker network create ${DATABASE_NETWORK}
	docker compose -f docker-compose.yml -f docker-compose.local.yml up -d --remove-orphans

app-check-config-local:
	make echo-env
	docker compose -f docker-compose.yml -f docker-compose.local.yml config

app-stop-local:
	docker compose -f docker-compose.yml -f docker-compose.local.yml down

env-build:
	./env-builder.sh

# run proxy to connect you local env with 3rd party services
proxy-run:
	docker run --net=host -it -e NGROK_AUTHTOKEN=${NGROK_AUTH_TOKEN} ngrok/ngrok:latest http ${LOCALHOST_PUBLIC_PORT}

gateway-start-local:
	@docker container start saas-api-gateway-dev
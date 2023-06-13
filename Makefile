APP_NAME := ollegro_backend

VENV := .venv
PYTHON := $(VENV)/bin/python3
PIP := $(VENV)/bin/pip

BASE_DOCKER := "docker-compose.yml"
DEV_DOCKER := "docker-compose.override.yml"

DEV_NAME := "ollegro_backend-dev"

ifndef VERBOSE
	MAKEFLAGS += --no-print-directory
endif

args = `arg="$(filter-out $@,$(MAKECMDGOALS))" && echo $${arg:-${1}}`


## DEV block
dev: ## [DEV] run dev containers
	docker compose -f $(BASE_DOCKER) -f $(DEV_DOCKER) --profile dev -p $(DEV_NAME) up --build -d $(call args)

dev-restart: ## restart dev containers
	docker compose -f $(BASE_DOCKER) -f $(DEV_DOCKER) --profile dev -p $(DEV_NAME) restart $(call args)

dev-logs: ## show logs dev containers
	docker compose -f $(BASE_DOCKER) -f $(DEV_DOCKER) --profile dev -p $(DEV_NAME) logs -f $(call args)

dev-down: ## down dev containers
	docker compose -f $(BASE_DOCKER) -f $(DEV_DOCKER) --profile dev -p $(DEV_NAME) down $(call args)

dev-cmd: ## show base command for dev containers
	@echo "docker compose -f $(BASE_DOCKER) -f $(DEV_DOCKER) --profile dev -p $(DEV_NAME)"

dev-exec: ## Exec to backend dev container
	docker exec -it ollegro_backend-app-backend sh

help: ## show help
	@egrep '^[a-zA-Z_-]+:.*?## .*$$' $(MAKEFILE_LIST) | awk 'BEGIN {FS = ":.*?## "}; {printf "\033[36m%-20s\033[0m %s\n", $$1, $$2}'

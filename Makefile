COMPOSE ?= docker compose

.PHONY: up down down-v logs restart shell-app shell-db db-init db-fix-encoding test

up:
	$(COMPOSE) up -d --build

down:
	$(COMPOSE) down

down-v:
	$(COMPOSE) down -v

logs:
	$(COMPOSE) logs -f app db mailpit

restart:
	$(COMPOSE) restart app db mailpit

shell-app:
	$(COMPOSE) exec app bash

shell-db:
	$(COMPOSE) exec db bash

db-init:
	$(COMPOSE) exec app bash scripts/docker/init-db.sh

db-fix-encoding:
	$(COMPOSE) exec app bash scripts/docker/repair-db-encoding.sh

test:
	$(COMPOSE) exec app php tests/run.php

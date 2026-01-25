.PHONY: help build start stop restart logs ps ps-all clean backup update deploy-prod deploy-dev down down-all dev-down clean-all clean-volumes

# Docker Compose files
COMPOSE_PROD = docker/docker-compose.prod.yml
COMPOSE_DEV = docker/docker-compose.dev.yml

help: ## Show this help message
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Available targets:'
	@awk 'BEGIN {FS = ":.*?## "} /^[a-zA-Z_-]+:.*?## / {printf "  \033[36m%-15s\033[0m %s\n", $$1, $$2}' $(MAKEFILE_LIST)

# Production commands
build: ## Build production containers
	docker compose -f $(COMPOSE_PROD) build

start: ## Start production containers
	docker compose -f $(COMPOSE_PROD) up -d

stop: ## Stop production containers
	docker compose -f $(COMPOSE_PROD) stop

restart: ## Restart production containers
	docker compose -f $(COMPOSE_PROD) restart

down: ## Stop and remove production containers
	docker compose -f $(COMPOSE_PROD) down

logs: ## Show production logs
	docker compose -f $(COMPOSE_PROD) logs -f

ps: ## Show running containers
	docker compose -f $(COMPOSE_PROD) ps

ps-all: ## Show ALL containers (including stopped)
	@echo "=== Production Containers ==="
	@docker compose -f $(COMPOSE_PROD) ps -a 2>/dev/null || echo "No production containers"
	@echo ""
	@echo "=== Development Containers ==="
	@docker compose -f $(COMPOSE_DEV) ps -a 2>/dev/null || echo "No development containers"
	@echo ""
	@echo "=== All bot_api Containers ==="
	@docker ps -a --filter "name=bot_api" --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null || echo "No containers found"

# Development commands
dev-start: ## Start development environment
	docker compose -f $(COMPOSE_DEV) up -d

dev-stop: ## Stop development environment
	docker compose -f $(COMPOSE_DEV) stop

dev-down: ## Stop and remove development containers
	docker compose -f $(COMPOSE_DEV) down

dev-logs: ## Show development logs
	docker compose -f $(COMPOSE_DEV) logs -f

# Local testing
test-local: ## Run local testing setup (interactive)
	cd scripts && ./test-local.sh

test-dev: ## Quick start development environment locally
	@echo "Starting local development environment..."
	@if [ ! -f .env.local ]; then cp .env.dev.example .env.local; fi
	@if [ ! -f app/.env.local ]; then cp app/.env.example app/.env.local; fi
	docker compose -f $(COMPOSE_DEV) up -d
	@echo "Local dev environment started at http://localhost:8080"

# Database commands
db-migrate: ## Run database migrations
	docker compose -f $(COMPOSE_PROD) exec php php bin/console doctrine:migrations:migrate --no-interaction

db-update: ## Update database schema
	docker compose -f $(COMPOSE_PROD) exec php php bin/console doctrine:schema:update --force

db-backup: ## Backup database
	cd scripts && ./backup-db.sh

db-restore: ## Restore database (usage: make db-restore FILE=backup_20260123_120000.sql.gz)
	@if [ -z "$(FILE)" ]; then echo "Usage: make db-restore FILE=backup_file.sql.gz"; exit 1; fi
	gunzip < backups/$(FILE) | docker compose -f $(COMPOSE_PROD) exec -T database mysql -u root -p$(MYSQL_ROOT_PASSWORD) $(MYSQL_DATABASE)

# Cache commands
cache-clear: ## Clear Symfony cache
	docker compose -f $(COMPOSE_PROD) exec php php bin/console cache:clear

cache-warmup: ## Warmup Symfony cache
	docker compose -f $(COMPOSE_PROD) exec php php bin/console cache:warmup

# Frontend commands
frontend-install: ## Install frontend dependencies
	cd frontend && npm install

frontend-build: ## Build frontend for production
	cd frontend && npm run build

frontend-dev: ## Run frontend in development mode
	cd frontend && npm run dev

# Application commands
deploy-prod: ## Deploy production (full deployment)
	cd scripts && ./deploy-quick.sh

update: ## Update application
	cd scripts && ./update-app.sh

ssh-php: ## SSH into PHP container
	docker compose -f $(COMPOSE_PROD) exec php sh

ssh-nginx: ## SSH into Nginx container
	docker compose -f $(COMPOSE_PROD) exec nginx sh

ssh-db: ## SSH into database container
down-all: ## Stop and remove ALL containers (dev + prod)
	docker compose -f $(COMPOSE_DEV) down 2>/dev/null || true
	docker compose -f $(COMPOSE_PROD) down 2>/dev/null || true
	@echo "All containers stopped and removed"

	docker compose -f $(COMPOSE_PROD) exec database bash

	@echo "Stopping and removing all containers with volumes..."
# Cleanup commands
clean: ## Remove all containers, volumes and images
	@docker ps -a --filter "name=bot_api" -q | xargs -r docker rm -f 2>/dev/null || true
	@docker network prune -f
clean-all: ## Stop and remove ALL containers with volumes (dev + prod)
	docker compose -f $(COMPOSE_DEV) down -v 2>/dev/null || true
	docker compose -f $(COMPOSE_PROD) down -v 2>/dev/null || true
	@echo "All containers, networks, and volumes removed"

clean-volumes: ## Remove all Docker volumes (WARNING: deletes all data!)
	@echo "WARNING: This will delete all Docker volumes including databases!"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
		docker ps -a --filter "name=bot_api" -q | xargs -r docker rm -f 2>/dev/null || true; \
		docker volume ls -q --filter "name=bot_api" | xargs -r docker volume rm 2>/dev/null || true; \
		docker network prune -f; \
		docker compose -f $(COMPOSE_DEV) down -v 2>/dev/null || true; \
		docker compose -f $(COMPOSE_PROD) down -v 2>/dev/null || true; \
		docker volume prune -f; \
		echo "All volumes removed"; \
	else \
		echo "Cancelled"; \
	fi

	docker compose -f $(COMPOSE_PROD) down -v
	docker system prune -f

clean-cache: ## Remove cache and log files
	rm -rf app/var/cache/*
	rm -rf app/var/log/*

# SSL/TLS commands
ssl-generate: ## Generate self-signed SSL certificates
	mkdir -p docker/nginx/ssl
	openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
		-keyout docker/nginx/ssl/key.pem \
		-out docker/nginx/ssl/cert.pem \
		-subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"

# JWT commands
jwt-generate: ## Generate JWT keypair
	docker compose -f $(COMPOSE_PROD) exec php php bin/console lexik:jwt:generate-keypair --skip-if-exists

# Monitoring
stats: ## Show container stats
	docker stats

disk-usage: ## Show Docker disk usage
	docker system df


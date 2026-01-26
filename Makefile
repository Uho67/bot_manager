.PHONY: help build start stop restart logs ps clean deploy-prod prod down-all clean-all ssh-php db-update cache-clear frontend-build cs-check cs-fix phpcs phpcbf

# Docker Compose files
COMPOSE_PROD = docker/docker-compose.prod.yml
COMPOSE_DEV = docker/docker-compose.dev.yml
ENV_FILE = .env

# Help
help: ## Show this help
	@echo 'Usage: make [target]'
	@echo ''
	@echo 'Deployment:'
	@echo '  deploy-prod    Full production deployment (first time)'
	@echo '  prod           Quick update: rebuild PHP + clear cache'
	@echo ''
	@echo 'Container Management:'
	@echo '  build          Build containers'
	@echo '  start          Start containers'
	@echo '  stop           Stop containers'
	@echo '  restart        Restart containers'
	@echo '  down-all       Stop and remove all containers'
	@echo '  logs           View logs'
	@echo '  ps             Show container status'
	@echo ''
	@echo 'Application:'
	@echo '  db-update      Update database schema'
	@echo '  cache-clear    Clear Symfony cache'
	@echo '  ssh-php        SSH into PHP container'
	@echo ''
	@echo 'Code Quality:'
	@echo '  cs-check       Check code style (dry-run)'
	@echo '  cs-fix         Fix code style'
	@echo '  phpcs          Run PHP CodeSniffer'
	@echo '  phpcbf         Fix PHP CodeSniffer issues'
	@echo ''
	@echo 'Cleanup:'
	@echo '  clean-all      Remove all containers and volumes'

# ===================
# DEPLOYMENT
# ===================

deploy-prod: ## Full production deployment
	@chmod +x scripts/deploy.sh && ./scripts/deploy.sh

prod: ## Quick update: rebuild PHP + fix permissions + DB update + clear cache
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) up -d --build php
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) exec php sh -c "mkdir -p config/jwt && [ -f config/jwt/private.pem ] || php bin/console lexik:jwt:generate-keypair && chown -R www-data:www-data config/jwt/"
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) exec php sh -c "mkdir -p public/media && chown -R www-data:www-data public/media var"
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) exec php php bin/console doctrine:schema:update --force || true
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) exec php php bin/console cache:clear --env=prod
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) exec php chown -R www-data:www-data var/

# ===================
# CONTAINER MANAGEMENT
# ===================

build: ## Build production containers
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) build

start: ## Start production containers
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) up -d

stop: ## Stop production containers
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) stop

restart: ## Restart production containers
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) restart

down-all: ## Stop and remove all containers
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) down 2>/dev/null || true
	docker compose -f $(COMPOSE_DEV) down 2>/dev/null || true

logs: ## View logs
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) logs -f

ps: ## Show container status
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) ps

# ===================
# APPLICATION
# ===================

db-update: ## Update database schema
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) exec php php bin/console doctrine:schema:update --force

cache-clear: ## Clear Symfony cache
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) exec php php bin/console cache:clear

ssh-php: ## SSH into PHP container
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) exec php sh

frontend-build: ## Build frontend
	cd frontend && npm install && npm run build

# ===================
# CODE QUALITY
# ===================

cs-check: ## Check code style (dry-run)
	cd app && vendor/bin/php-cs-fixer fix --dry-run --diff

cs-fix: ## Fix code style
	cd app && vendor/bin/php-cs-fixer fix

phpcs: ## Run PHP CodeSniffer
	cd app && vendor/bin/phpcs

phpcbf: ## Fix PHP CodeSniffer issues
	cd app && vendor/bin/phpcbf

# ===================
# CLEANUP
# ===================

clean-all: ## Remove all containers and volumes (WARNING: deletes data!)
	docker compose --env-file $(ENV_FILE) -f $(COMPOSE_PROD) down -v 2>/dev/null || true
	docker compose -f $(COMPOSE_DEV) down -v 2>/dev/null || true
	docker system prune -f


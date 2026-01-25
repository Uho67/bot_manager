# Container Cleanup & Final MariaDB → MySQL Migration

## Date: January 25, 2026

## Issues Found & Resolved

### Issue 1: Old Containers Not Removed
**Problem**: After running `make clean-all` and `make clean-volumes`, old containers were still present:
- bot_api_nginx_dev
- bot_api_php_dev
- bot_api_database_dev (using mariadb:11.3)
- bot_api_redis_dev

**Root Cause**: The old containers were created before the docker-compose files were moved to the `docker/` directory, so they weren't being tracked by the new compose file paths.

**Solution**:
1. Force removed all old containers manually
2. Enhanced `make clean-all`, `make down-all`, and `make clean-volumes` commands to:
   - Force remove any container with "bot_api" in the name
   - Prune Docker networks
   - Remove volumes by name filter

### Issue 2: MariaDB References in Config Files
**Problem**: `.env.dev.example` and `.env.example` still referenced MariaDB 11.3 instead of MySQL 8.0

**Files Updated**:
- `.env.dev.example` → Changed to MySQL 8.0
- `.env.example` → Changed to MySQL 8.0
- `documentation/DEPLOYMENT.md` → Updated DATABASE_URL
- `documentation/DEPLOYMENT_DOCKER.md` → Updated directory reference
- `documentation/README_DOCKER.md` → Updated directory reference
- `documentation/DOCKER_INDEX.md` → Updated directory reference
- `documentation/FILES_SUMMARY.md` → Updated references (2 places)

## Changes Made

### 1. Improved Makefile Commands

#### Enhanced `make down-all`
```makefile
down-all: ## Stop and remove ALL containers (dev + prod)
	@echo "Stopping and removing all containers..."
	docker compose -f $(COMPOSE_DEV) down 2>/dev/null || true
	docker compose -f $(COMPOSE_PROD) down 2>/dev/null || true
	@docker ps -a --filter "name=bot_api" -q | xargs -r docker rm -f 2>/dev/null || true
	@docker network prune -f
	@echo "All containers stopped and removed"
```
**What it does**: Removes ALL containers matching "bot_api" pattern, even orphaned ones.

#### Enhanced `make clean-all`
```makefile
clean-all: ## Stop and remove ALL containers with volumes (dev + prod)
	@echo "Stopping and removing all containers with volumes..."
	docker compose -f $(COMPOSE_DEV) down -v 2>/dev/null || true
	docker compose -f $(COMPOSE_PROD) down -v 2>/dev/null || true
	@docker ps -a --filter "name=bot_api" -q | xargs -r docker rm -f 2>/dev/null || true
	@docker network prune -f
	@echo "All containers, networks, and volumes removed"
```
**What it does**: Same as down-all but also removes volumes.

#### Enhanced `make clean-volumes`
```makefile
clean-volumes: ## Remove all Docker volumes (WARNING: deletes all data!)
	@echo "WARNING: This will delete all Docker volumes including databases!"
	@read -p "Are you sure? [y/N] " -n 1 -r; \
	echo; \
	if [[ $$REPLY =~ ^[Yy]$$ ]]; then \
		docker compose -f $(COMPOSE_DEV) down -v 2>/dev/null || true; \
		docker compose -f $(COMPOSE_PROD) down -v 2>/dev/null || true; \
		docker ps -a --filter "name=bot_api" -q | xargs -r docker rm -f 2>/dev/null || true; \
		docker volume ls -q --filter "name=bot_api" | xargs -r docker volume rm 2>/dev/null || true; \
		docker network prune -f; \
		echo "All volumes removed"; \
	else \
		echo "Cancelled"; \
	fi
```
**What it does**: Interactively removes ALL volumes with confirmation.

#### New `make ps-all` Command
```makefile
ps-all: ## Show ALL containers (including stopped)
	@echo "=== Production Containers ==="
	@docker compose -f $(COMPOSE_PROD) ps -a 2>/dev/null || echo "No production containers"
	@echo ""
	@echo "=== Development Containers ==="
	@docker compose -f $(COMPOSE_DEV) ps -a 2>/dev/null || echo "No development containers"
	@echo ""
	@echo "=== All bot_api Containers ==="
	@docker ps -a --filter "name=bot_api" --format "table {{.Names}}\t{{.Image}}\t{{.Status}}\t{{.Ports}}" 2>/dev/null || echo "No containers found"
```
**What it does**: Shows ALL containers (running and stopped) for easy debugging.

### 2. Updated Environment Files

#### .env.dev.example
**Before**:
```env
# MariaDB Configuration
MARIADB_VERSION=11.3
DATABASE_URL=mysql://app:app@database:3306/app?serverVersion=11.3&charset=utf8mb4
```

**After**:
```env
# MySQL Configuration
MYSQL_VERSION=8.0
DATABASE_URL=mysql://app:app@database:3306/app?serverVersion=8.0&charset=utf8mb4
```

#### .env.example
**Before**:
```env
# MariaDB Configuration
MARIADB_VERSION=11.3
DATABASE_URL=mysql://bot_api_user:change_this_password_in_production@database:3306/bot_api_db?serverVersion=11.3&charset=utf8mb4
```

**After**:
```env
# MySQL Configuration
MYSQL_VERSION=8.0
DATABASE_URL=mysql://bot_api_user:change_this_password_in_production@database:3306/bot_api_db?serverVersion=8.0&charset=utf8mb4
```

### 3. Updated Documentation Files

All references to MariaDB/mariadb changed to MySQL/mysql in:
- `documentation/DEPLOYMENT.md`
- `documentation/DEPLOYMENT_DOCKER.md`
- `documentation/README_DOCKER.md`
- `documentation/DOCKER_INDEX.md`
- `documentation/FILES_SUMMARY.md`

## Verification

### Check for Containers
```bash
make ps-all
# OR
docker ps -a --filter "name=bot_api"
```
**Result**: No containers found ✅

### Check for Volumes
```bash
docker volume ls --filter "name=bot_api"
```
**Result**: No volumes found ✅

## New Commands Available

```bash
make ps-all           # Show all containers (including stopped)
make down-all         # Stop & remove all containers (improved)
make clean-all        # Stop & remove all + volumes (improved)
make clean-volumes    # Interactive volume cleanup (improved)
```

## Complete MariaDB → MySQL Migration Summary

### All Changes Completed:
- ✅ docker-compose.prod.yml → MySQL 8.0
- ✅ docker-compose.dev.yml → MySQL 8.0
- ✅ app/compose.yaml → MySQL 8.0
- ✅ .env.example → MySQL 8.0
- ✅ .env.dev.example → MySQL 8.0
- ✅ /docker/mariadb/ → /docker/mysql/
- ✅ All healthchecks updated
- ✅ All documentation updated
- ✅ Old containers removed
- ✅ Cleanup commands enhanced

## Testing Recommendations

1. **Verify clean state**:
   ```bash
   make ps-all
   ```

2. **Start fresh development environment**:
   ```bash
   make dev-start
   ```

3. **Verify MySQL is running**:
   ```bash
   docker compose -f docker/docker-compose.dev.yml exec database mysql -V
   ```
   Expected: `mysql  Ver 8.0.x`

4. **Test cleanup commands**:
   ```bash
   make down-all      # Should remove all containers
   make ps-all        # Should show no containers
   ```

## Benefits

1. **Robust Cleanup**: Commands now handle orphaned containers
2. **Complete MySQL Migration**: No more MariaDB references anywhere
3. **Better Visibility**: `make ps-all` shows all containers
4. **Safer Operations**: Interactive prompts for destructive operations
5. **Consistent Configuration**: All env files use MySQL 8.0

## Next Steps

1. Test development environment: `make dev-start`
2. Verify MySQL connection works
3. Run migrations if needed: `make db-migrate`
4. Test application functionality

All cleanup complete! ✅


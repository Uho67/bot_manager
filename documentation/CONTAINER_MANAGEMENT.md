# Container Management Commands - Quick Guide

## Overview

Multiple commands have been added to manage Docker containers for both development and production environments.

## Stop/Remove Commands Summary

### 🟢 Safe Commands (Data Preserved)

#### Stop Containers Only
```bash
make stop           # Stop production containers (can restart later)
make dev-stop       # Stop development containers (can restart later)
```
- Containers stopped but not removed
- Can resume with `make start` or `make dev-start`
- All data in volumes is preserved

#### Stop and Remove Containers
```bash
make down           # Remove production containers
make dev-down       # Remove development containers
make down-all       # Remove ALL containers (dev + prod)
```
- Containers removed but volumes preserved
- Database data, Redis data, and uploaded files remain intact
- Next start will recreate containers with existing data

### 🔴 Destructive Commands (Data Loss!)

#### Remove Everything
```bash
make clean-all      # Remove ALL containers + volumes (dev + prod)
make clean-volumes  # Remove ALL volumes (interactive prompt)
```
- ⚠️ **WARNING**: These commands delete all data!
- Database will be empty after next start
- Uploaded files will be lost
- Use only when you want a fresh start

## Usage Examples

### Scenario 1: Restart Development Environment
```bash
make dev-stop    # Stop dev containers
make dev-start   # Start again (data preserved)
```

### Scenario 2: Switch from Dev to Production
```bash
make dev-down    # Remove dev containers
make build       # Build production
make start       # Start production
```

### Scenario 3: Clean Everything and Start Fresh
```bash
make down-all    # Remove all containers
# Containers removed, data still in volumes

# OR for complete reset:
make clean-all   # Remove containers AND volumes
# Everything deleted, fresh start
```

### Scenario 4: Stop Everything Quickly
```bash
make down-all    # Stop and remove all containers immediately
```

## Command Reference Table

| Command | Scope | Stops? | Removes Container? | Removes Volumes? | Data Safe? |
|---------|-------|--------|-------------------|------------------|------------|
| `make stop` | Prod | ✅ | ❌ | ❌ | ✅ Yes |
| `make dev-stop` | Dev | ✅ | ❌ | ❌ | ✅ Yes |
| `make down` | Prod | ✅ | ✅ | ❌ | ✅ Yes |
| `make dev-down` | Dev | ✅ | ✅ | ❌ | ✅ Yes |
| `make down-all` | Both | ✅ | ✅ | ❌ | ✅ Yes |
| `make clean-all` | Both | ✅ | ✅ | ✅ | ❌ **NO** |
| `make clean-volumes` | Both | ✅ | ✅ | ✅ | ❌ **NO** |

## What Gets Deleted?

### With `make down-all` (Safe)
- ✅ Container processes stopped
- ✅ Container instances removed
- ✅ Networks removed
- ❌ Volumes **PRESERVED**
  - Database data intact
  - Redis data intact
  - Uploaded media files intact
  - PHP var/ directory intact

### With `make clean-all` (Destructive)
- ✅ Everything from `down-all` PLUS:
- ❌ All volumes **DELETED**
  - Database completely empty
  - Redis data gone
  - Uploaded files deleted
  - Cache cleared

## Direct Docker Compose Commands

If you prefer to use docker compose directly:

```bash
# Stop all dev containers
docker compose -f docker/docker-compose.dev.yml down

# Stop all prod containers
docker compose -f docker/docker-compose.prod.yml down

# Stop all dev containers + volumes
docker compose -f docker/docker-compose.dev.yml down -v

# Stop all prod containers + volumes
docker compose -f docker/docker-compose.prod.yml down -v

# Stop everything (both environments)
docker compose -f docker/docker-compose.dev.yml down
docker compose -f docker/docker-compose.prod.yml down
```

## Best Practices

### ✅ Do This
- Use `make down-all` to quickly stop everything
- Use `make dev-down` when switching environments
- Backup database before using `clean-all`: `make db-backup`
- Use `make stop` / `make dev-stop` for temporary stops

### ❌ Avoid This
- Don't use `clean-all` without backing up first
- Don't mix manual docker commands with make commands
- Don't delete volumes unless you want a fresh start

## Troubleshooting

### "Containers won't stop"
```bash
# Force stop everything
docker stop $(docker ps -aq)
docker rm $(docker ps -aq)
```

### "Port already in use"
```bash
# See what's running
docker ps

# Stop conflicting containers
make down-all
```

### "Want completely fresh start"
```bash
# Backup first!
make db-backup

# Then clean everything
make clean-all

# Start fresh
make build
make start
```

## Quick Commands

```bash
# Most common use cases:
make down-all          # Stop everything (safe)
make clean-all         # Reset everything (data loss!)
make dev-down          # Remove dev only
make down              # Remove prod only
```

## Related Commands

```bash
make ps                # See what's running
make logs              # View production logs
make dev-logs          # View development logs
make stats             # Monitor resource usage
make disk-usage        # Check Docker disk space
```

For more details, see `documentation/QUICK_REFERENCE.md`


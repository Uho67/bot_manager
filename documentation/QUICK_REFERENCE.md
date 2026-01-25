# Quick Reference Guide - Updated Paths

## Important: Script and File Locations Have Changed!

All scripts and documentation have been reorganized. Use this guide for the updated commands.

## Scripts (now in `scripts/` directory)

### Option 1: Use Make Commands (Recommended)
```bash
make deploy-prod        # Deploy production
make update             # Update application
make db-backup          # Backup database
make test-local         # Local testing setup
```

### Option 2: Run Scripts Directly
```bash
cd scripts
./deploy-quick.sh       # Deploy production
./update-app.sh         # Update application
./backup-db.sh          # Backup database
./health-check.sh       # Check system health
./test-local.sh         # Local testing setup
./start-frontend.sh     # Start frontend dev server
./deploy.sh             # Standard deployment
```

## Docker Compose (now in `docker/` directory)

### Using Make (Recommended)
```bash
make build              # Build production containers
make start              # Start production
make stop               # Stop production
make dev-start          # Start development
make logs               # View logs
```

### Direct Docker Compose Commands
```bash
docker compose -f docker/docker-compose.prod.yml build
docker compose -f docker/docker-compose.prod.yml up -d
docker compose -f docker/docker-compose.prod.yml down
docker compose -f docker/docker-compose.dev.yml up -d
```

## Documentation (now in `documentation/` directory)

```bash
documentation/START_HERE.md          # Getting started
documentation/DEPLOYMENT.md          # Deployment guide
documentation/LOCAL_TESTING.md       # Local testing
documentation/DEPLOYMENT_DOCKER.md   # Docker deployment
documentation/README_DOCKER.md       # Docker reference
documentation/REORGANIZATION_SUMMARY.md  # This reorganization
```

## Database Changes

**MySQL 8.0** is now used instead of MariaDB 11.3 for better Symfony compatibility.

```bash
# Connection string format
DATABASE_URL=mysql://user:pass@host:3306/dbname?serverVersion=8.0
```

## Stop/Remove Containers - All Options

### Stop Containers Only (data preserved)
```bash
make stop           # Stop production containers
make dev-stop       # Stop development containers
```

### Stop and Remove Containers (data preserved in volumes)
```bash
make down           # Remove production containers
make dev-down       # Remove development containers
make down-all       # Remove ALL containers (dev + prod)
```

### Remove Containers AND Volumes (⚠️ deletes all data!)
```bash
make clean-all      # Remove all containers + volumes (dev + prod)
make clean-volumes  # Interactive - removes ALL Docker volumes
```

### Quick Reference
| Command | Action | Data Lost? |
|---------|--------|-----------|
| `make stop` | Stop prod containers | ❌ No |
| `make dev-stop` | Stop dev containers | ❌ No |
| `make down` | Remove prod containers | ❌ No |
| `make dev-down` | Remove dev containers | ❌ No |
| `make down-all` | Remove all containers | ❌ No |
| `make clean-all` | Remove all + volumes | ⚠️ **YES** |
| `make clean-volumes` | Remove all volumes | ⚠️ **YES** |

## Key Changes Summary

| Old Path | New Path |
|----------|----------|
| `./deploy-quick.sh` | `cd scripts && ./deploy-quick.sh` or `make deploy-prod` |
| `./backup-db.sh` | `cd scripts && ./backup-db.sh` or `make db-backup` |
| `./test-local.sh` | `cd scripts && ./test-local.sh` or `make test-local` |
| `docker-compose.prod.yml` | `docker/docker-compose.prod.yml` or use `make` |
| `docker-compose.dev.yml` | `docker/docker-compose.dev.yml` or use `make` |
| `DEPLOYMENT.md` | `documentation/DEPLOYMENT.md` |
| `START_HERE.md` | `documentation/START_HERE.md` |

## Updating Old Documentation References

If you see commands like:
```bash
./deploy-quick.sh
```

Replace with:
```bash
make deploy-prod
# OR
cd scripts && ./deploy-quick.sh
```

If you see:
```bash
docker compose -f docker-compose.prod.yml
```

Replace with:
```bash
make <command>
# OR
docker compose -f docker/docker-compose.prod.yml
```

## Environment Files (unchanged)

```
.env                    # Local development (Warden, etc.)
.env.prod               # Production environment (use this on server!)
.env.example            # Root environment template
app/.env.local          # App environment
frontend/.env.local     # Frontend environment
```

**Important:** Production deployments use `.env.prod` instead of `.env` to avoid conflicts with local development tools. See `documentation/ENV_PROD_USAGE.md` for details.

## Quick Commands Cheat Sheet

```bash
# Development
make dev-start          # Start dev environment
make dev-logs           # View dev logs
make dev-stop           # Stop dev environment
make dev-down           # Stop and remove dev containers

# Production
make build              # Build containers
make start              # Start services
make stop               # Stop services
make restart            # Restart services
make down               # Stop and remove containers
make logs               # View logs

# Stop/Remove ALL Containers
make down-all           # Stop & remove ALL containers (dev + prod)
make clean-all          # Stop & remove ALL with volumes
make clean-volumes      # Remove ALL volumes (WARNING: deletes data!)

# Database
make db-migrate         # Run migrations
make db-backup          # Backup database
make db-restore FILE=x  # Restore backup

# Frontend
make frontend-build     # Build frontend
make frontend-dev       # Dev server

# Maintenance
make cache-clear        # Clear Symfony cache
make update             # Update application
make clean              # Clean production containers & images
```

## Need Help?

- Full documentation: `documentation/` directory
- Make commands: `make help`
- Project structure: See `README.md`
- Reorganization details: `documentation/REORGANIZATION_SUMMARY.md`


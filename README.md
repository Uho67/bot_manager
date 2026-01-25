# Bot API - Symfony Application

A production-ready Symfony API application with Docker containerization, Vue.js frontend, and comprehensive deployment scripts.

## Project Structure

```
bot_api/
├── app/                    # Symfony application
├── frontend/               # Vue.js frontend application
├── docker/                 # Docker configuration files
│   ├── docker-compose.dev.yml    # Development environment
│   ├── docker-compose.prod.yml   # Production environment
│   ├── mysql/              # MySQL initialization scripts
│   ├── nginx/              # Nginx configuration
│   ├── php/                # PHP-FPM Dockerfile
│   └── .dockerignore       # Docker ignore file
├── scripts/                # Shell scripts for deployment and maintenance
│   ├── backup-db.sh        # Database backup script
│   ├── deploy-quick.sh     # Quick deployment script
│   ├── deploy.sh           # Standard deployment script
│   ├── health-check.sh     # Health check script
│   ├── start-frontend.sh   # Frontend development server
│   ├── test-local.sh       # Local testing setup
│   └── update-app.sh       # Application update script
├── documentation/          # Project documentation
│   ├── DEPLOYMENT.md       # Deployment guide
│   ├── DEPLOYMENT_DOCKER.md # Docker deployment guide
│   ├── DOCKER_INDEX.md     # Docker index
│   ├── FILES_SUMMARY.md    # Files summary
│   ├── LOCAL_TESTING.md    # Local testing guide
│   ├── README_DOCKER.md    # Docker README
│   ├── START_HERE.md       # Getting started guide
│   └── SUCCESS.md          # Success metrics
├── Makefile                # Make commands for common tasks
└── README.md               # This file
```

## Quick Start

### Development Environment

```bash
# Start development environment
make dev-start

# Or use the interactive test script
make test-local
```

### Production Deployment

```bash
# Quick deployment (recommended)
make deploy-prod

# Or step by step
make build
make start
```

## Available Make Commands

Run `make help` to see all available commands:

- **Production**: `build`, `start`, `stop`, `restart`, `down`, `logs`, `ps`
- **Development**: `dev-start`, `dev-stop`, `dev-down`, `dev-logs`
- **Cleanup**: `down-all`, `clean-all`, `clean-volumes`, `clean`
- **Database**: `db-migrate`, `db-update`, `db-backup`, `db-restore`
- **Cache**: `cache-clear`, `cache-warmup`
- **Frontend**: `frontend-install`, `frontend-build`, `frontend-dev`
- **Deployment**: `deploy-prod`, `update`
- **Utilities**: `ssh-php`, `ssh-nginx`, `ssh-db`, `ssl-generate`, `jwt-generate`, `stats`

## Technology Stack

- **Backend**: Symfony 7.2 (PHP 8.3)
- **Database**: MySQL 8.0
- **Cache**: Redis 7
- **Frontend**: Vue.js 3 + TypeScript + Vite
- **Web Server**: Nginx
- **Containerization**: Docker & Docker Compose

## Documentation

All documentation is available in the `documentation/` directory:

- [Getting Started](documentation/START_HERE.md) - Begin here
- [Local Testing Guide](documentation/LOCAL_TESTING.md) - Local development setup
- [Deployment Guide](documentation/DEPLOYMENT.md) - Production deployment
- [Docker Guide](documentation/DEPLOYMENT_DOCKER.md) - Docker-specific deployment

## Scripts

All maintenance and deployment scripts are located in the `scripts/` directory:

```bash
# Backup database
cd scripts && ./backup-db.sh

# Deploy application
cd scripts && ./deploy-quick.sh

# Update application
cd scripts && ./update-app.sh

# Run health checks
cd scripts && ./health-check.sh

# Start frontend dev server
cd scripts && ./start-frontend.sh
```

## Environment Configuration

Copy the example environment files and configure:

```bash
# Local development (Warden, Docker Desktop, etc.)
cp .env.example .env

# Production deployment (use .env.prod to avoid conflicts)
cp .env.example .env.prod

# Application environment
cp app/.env.example app/.env.local

# Frontend environment
cp frontend/.env.example frontend/.env.local
```

**Note for Production:** Use `.env.prod` instead of `.env` on production servers to avoid conflicts with local development tools. The production docker-compose file (`docker/docker-compose.prod.yml`) is configured to use `.env.prod`. See `documentation/ENV_PROD_USAGE.md` for details.

# Application environment
cp app/.env.example app/.env.local

# Frontend environment
cp frontend/.env.example frontend/.env.local
```

## Database

The project uses **MySQL 8.0** for optimal Symfony compatibility.

### Migrations

```bash
# Run migrations
make db-migrate

# Or manually
docker compose -f docker/docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate
```

### Backup & Restore

```bash
# Backup
make db-backup

# Restore
make db-restore FILE=backup_20260125_120000.sql.gz
```

## Support

For detailed information, please refer to the documentation in the `documentation/` directory.

## License

Proprietary - All rights reserved


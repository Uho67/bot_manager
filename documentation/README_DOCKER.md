# Bot API - Docker Deployment Quick Start

This is a quick reference guide. For full deployment instructions, see [DEPLOYMENT_DOCKER.md](DEPLOYMENT_DOCKER.md).

## Project Stack

- **Backend**: Symfony 8.0 (PHP 8.4)
- **Frontend**: Vue.js 3
- **Database**: MariaDB 11.3
- **Cache**: Redis 7
- **Web Server**: Nginx
- **Future**: NestJS (ready to add)

## Test Locally First

Before deploying to production, test everything on your local machine:

```bash
./test-local.sh
```

See [LOCAL_TESTING.md](LOCAL_TESTING.md) for detailed local testing guide.

---

## Quick Start

### 1. Initial Setup

```bash
# Copy environment files
cp .env.example .env
cp app/.env.example app/.env.local
cp frontend/.env.example frontend/.env.production

# Edit .env files with your actual values
nano .env
nano app/.env.local
nano frontend/.env.production
```

### 2. Deploy

```bash
# Quick deployment (automated)
./deploy-quick.sh

# Or using Makefile
make deploy-prod
```

### 3. Access Application

- **Frontend**: https://localhost (or your domain)
- **API**: https://localhost/api

## Common Commands

### Using Makefile (Recommended)

```bash
make help              # Show all available commands
make build             # Build containers
make start             # Start services
make stop              # Stop services
make restart           # Restart services
make logs              # View logs
make db-backup         # Backup database
make cache-clear       # Clear Symfony cache
make update            # Update application
```

### Using Docker Compose Directly

```bash
# Start services
docker compose -f docker-compose.prod.yml up -d

# View logs
docker compose -f docker-compose.prod.yml logs -f

# Stop services
docker compose -f docker-compose.prod.yml stop

# Restart a service
docker compose -f docker-compose.prod.yml restart php
```

## Maintenance Scripts

- `./deploy-quick.sh` - Quick deployment script
- `./update-app.sh` - Update application (pull, build, deploy)
- `./backup-db.sh` - Backup database

## Important Files

- `docker-compose.prod.yml` - Production Docker Compose configuration
- `.env` - Root environment variables
- `app/.env.local` - Symfony backend configuration
- `frontend/.env.production` - Vue.js frontend configuration
- `docker/nginx/conf.d/default.conf` - Nginx configuration
- `docker/php/Dockerfile` - PHP container configuration

## Directory Structure

```
bot_api/
├── app/                          # Symfony Backend
│   ├── config/                   # Configuration files
│   ├── src/                      # Source code
│   ├── public/                   # Public files & entry point
│   └── .env.local                # Backend environment
├── frontend/                     # Vue.js Frontend
│   ├── src/                      # Frontend source
│   ├── dist/                     # Built files (generated)
│   └── .env.production           # Frontend environment
├── docker/                       # Docker configuration
│   ├── nginx/                    # Nginx config & SSL
│   ├── php/                      # PHP Dockerfile & config
│   └── mysql/                  # Database init scripts
├── docker-compose.prod.yml       # Production compose file
├── .env                          # Root environment
└── Makefile                      # Command shortcuts
```

## Environment Variables

### Root .env
- Database credentials
- Redis password
- Domain configuration

### app/.env.local (Backend)
- `APP_ENV` - Environment (prod/dev)
- `APP_SECRET` - Application secret
- `DATABASE_URL` - Database connection
- `REDIS_URL` - Redis connection
- `JWT_PASSPHRASE` - JWT encryption key
- `TELEGRAM_BOT_TOKEN` - Bot token

### frontend/.env.production (Frontend)
- `VITE_API_URL` - Backend API URL

## Troubleshooting

### Port already in use
```bash
sudo lsof -i :80
sudo lsof -i :443
# Stop conflicting service
```

### Permission issues
```bash
docker compose -f docker-compose.prod.yml exec php chown -R www-data:www-data /var/www/html/var
```

### Database connection failed
```bash
# Check database status
docker compose -f docker-compose.prod.yml ps database
# View logs
docker compose -f docker-compose.prod.yml logs database
```

### 502 Bad Gateway
```bash
# Restart PHP-FPM
docker compose -f docker-compose.prod.yml restart php
```

### Clear all caches
```bash
make cache-clear
# Or
docker compose -f docker-compose.prod.yml exec php php bin/console cache:clear
```

## Security Checklist

Before deploying to production:

- [ ] Change all default passwords in `.env`
- [ ] Generate strong `APP_SECRET` (32+ chars)
- [ ] Set strong `JWT_PASSPHRASE`
- [ ] Install real SSL certificates (Let's Encrypt)
- [ ] Update `CORS_ALLOW_ORIGIN` to your domain
- [ ] Set up firewall (allow only 22, 80, 443)
- [ ] Configure automated backups
- [ ] Review and update Nginx security headers
- [ ] Disable debug mode (`APP_ENV=prod`)

## Backup & Restore

### Backup
```bash
make db-backup
# Or
./backup-db.sh
```

### Restore
```bash
make db-restore FILE=backup_20260123_120000.sql.gz
```

## Monitoring

```bash
# View container stats
make stats

# View disk usage
make disk-usage

# View logs
make logs
```

## Full Documentation

For complete deployment instructions, SSL setup, performance tuning, and more, see:
- [DEPLOYMENT_DOCKER.md](DEPLOYMENT_DOCKER.md) - Complete deployment guide

## Support

For issues or questions, please check the documentation or create an issue in the repository.


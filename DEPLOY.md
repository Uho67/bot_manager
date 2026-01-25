# Docker Deployment Guide

One file. Everything you need to deploy.

---

## Prerequisites

- Linux server with Docker installed
- Ports 80 and 443 open
- Git access to this repository

---

## Quick Deploy (5 minutes)

```bash
# 1. Clone repository
git clone <your-repo> /var/www/bot_api
cd /var/www/bot_api

# 2. Create environment file
cp .env.example .env.prod
nano .env.prod   # Edit with your values

# 3. Deploy
make deploy-prod
```

**Done!** Your app is running at `http://your-server-ip`

---

## Environment Configuration

### Required: `.env.prod` (root directory)

Create this file with your production values:

```env
# Database
MYSQL_VERSION=8.0
MYSQL_DATABASE=bot_api
MYSQL_USER=app
MYSQL_PASSWORD=your_secure_password
MYSQL_ROOT_PASSWORD=your_root_password

# Redis
REDIS_PASSWORD=your_redis_password

# Symfony
APP_ENV=prod
APP_SECRET=your_32_char_secret_here
DATABASE_URL=mysql://app:your_secure_password@database:3306/bot_api?serverVersion=8.0
REDIS_URL=redis://:your_redis_password@redis:6379

# JWT (generate with: openssl rand -base64 32)
JWT_PASSPHRASE=your_jwt_passphrase

# CORS
CORS_ALLOW_ORIGIN=*

# Domain (optional)
DOMAIN=localhost
HTTP_PORT=80
HTTPS_PORT=443
```

### Required: `app/.env.prod.local`

Create this file for Symfony-specific settings:

```env
APP_ENV=prod
APP_SECRET=your_32_char_secret_here
DATABASE_URL=mysql://app:your_secure_password@database:3306/bot_api?serverVersion=8.0
```

---

## What `make deploy-prod` Does

1. ✅ Checks prerequisites
2. ✅ Builds frontend (Vue.js)
3. ✅ Generates SSL certificate (self-signed)
4. ✅ Builds Docker images
5. ✅ Starts all containers
6. ✅ Runs database migrations
7. ✅ Clears Symfony cache

---

## Manual Commands

```bash
# Start containers
make start

# Stop containers
make stop

# Restart containers
make restart

# View logs
make logs

# Container status
make ps

# SSH into PHP container
make ssh-php

# Update database schema
make db-update

# Clear cache
make cache-clear

# Stop and remove all containers
make down-all

# Remove everything including data (DANGER!)
make clean-all
```

---

## Architecture

```
Internet (port 80/443)
        ↓
   Nginx Container
        ↓
   ├── /        → Vue.js frontend (static files)
   └── /api     → PHP-FPM Container (Symfony)
                        ↓
                   ├── MySQL Container
                   └── Redis Container

Internet (port 8080)
        ↓
   phpMyAdmin Container → MySQL Container
```

**Containers:**
- `bot_api_nginx` - Web server (ports 80, 443)
- `bot_api_php` - Symfony API
- `bot_api_database` - MySQL database
- `bot_api_redis` - Cache
- `bot_api_phpmyadmin` - Database management (port 8080)

---

## phpMyAdmin

Access phpMyAdmin at: `http://your-server-ip:8080`

**Login credentials:**
- Server: `database` (auto-filled)
- Username: `root`
- Password: Your `MYSQL_ROOT_PASSWORD` from `.env.prod`

**Change port:** Add to `.env.prod`:
```env
PMA_PORT=8080
```

**Security warning:** In production, consider:
- Restricting access by IP (firewall)
- Disabling phpMyAdmin after use
- Using SSH tunnel instead of exposing port

---

## Troubleshooting

### App not loading in browser

**Check 1: Containers running?**
```bash
make ps
# All containers should show "Up"
```

**Check 2: Frontend built correctly?**
```bash
# Rebuild frontend with correct API URL
cd frontend
rm -rf dist
VITE_API_URL="" npm run build
cd ..
make restart
```

**Check 3: Check logs**
```bash
make logs
# Look for errors
```

### API calls failing

**Check 1: PHP container working?**
```bash
docker compose --env-file .env -f docker/docker-compose.prod.yml exec php php -v
```

**Check 2: Database connection?**
```bash
make ssh-php
php bin/console doctrine:query:sql "SELECT 1"
```

### Database connection error

**Check 1: MySQL running?**
```bash
docker compose --env-file .env -f docker/docker-compose.prod.yml logs database
```

**Check 2: Credentials correct?**
- Compare `DATABASE_URL` in `app/.env.prod.local` with MySQL credentials in `.env.prod`

### Permission errors

```bash
# Fix var directory permissions
docker compose --env-file .env -f docker/docker-compose.prod.yml exec php chown -R www-data:www-data /var/www/html/var
```

---

## SSL Certificate

The deploy script creates a self-signed certificate. For production with a domain:

**Option 1: Let's Encrypt (recommended)**
```bash
# Install certbot on host
sudo apt install certbot

# Get certificate
sudo certbot certonly --standalone -d yourdomain.com

# Copy to nginx ssl directory
sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/key.pem

# Restart nginx
make restart
```

**Option 2: Cloudflare**
- Use Cloudflare as proxy
- Cloudflare handles SSL
- Your server only needs HTTP (port 80)

---

## Updating the Application

```bash
cd /var/www/bot_api

# Pull latest code
git pull origin main

# Rebuild and restart
make deploy-prod
```

---

## Backup Database

```bash
# Backup
docker compose --env-file .env -f docker/docker-compose.prod.yml exec database mysqldump -u root -p bot_api > backup.sql

# Restore
docker compose --env-file .env -f docker/docker-compose.prod.yml exec -T database mysql -u root -p bot_api < backup.sql
```

---

## File Locations

| What | Where |
|------|-------|
| Environment config | `.env.prod` |
| Symfony env | `app/.env.prod.local` |
| Docker Compose | `docker/docker-compose.prod.yml` |
| Nginx config | `docker/nginx/conf.d/default.conf` |
| PHP Dockerfile | `docker/php/Dockerfile` |
| Frontend build | `frontend/dist/` |
| SSL certificates | `docker/nginx/ssl/` |
| Deploy script | `scripts/deploy.sh` |

---

## Common Issues & Fixes

| Problem | Solution |
|---------|----------|
| "Connection refused" | Check if containers are running: `make ps` |
| "502 Bad Gateway" | PHP container crashed. Check: `make logs` |
| "API URL is localhost" | Rebuild frontend: `cd frontend && VITE_API_URL="" npm run build` |
| "Database not found" | Run: `make db-update` |
| "Permission denied" | Fix permissions: see Troubleshooting section |
| "SSL error" | Certificate missing. Check `docker/nginx/ssl/` |

---

## That's It!

For 99% of deployments, you only need:

1. `cp .env.example .env.prod` and edit
2. `make deploy-prod`

Everything else is for troubleshooting.

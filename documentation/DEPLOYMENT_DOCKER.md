# Docker Compose Production Deployment Guide

This guide provides complete instructions for deploying the Bot API project (Symfony + Vue.js + Redis) using Docker Compose.

## Table of Contents
1. [Prerequisites](#prerequisites)
2. [Project Architecture](#project-architecture)
3. [Initial Server Setup](#initial-server-setup)
4. [Environment Configuration](#environment-configuration)
5. [Building and Deployment](#building-and-deployment)
6. [SSL/TLS Configuration](#ssltls-configuration)
7. [Database Management](#database-management)
8. [Monitoring and Maintenance](#monitoring-and-maintenance)
9. [Troubleshooting](#troubleshooting)

---

## Prerequisites

### Server Requirements
- **OS**: Ubuntu 20.04 LTS or later (recommended) / Debian / CentOS
- **RAM**: Minimum 2GB, recommended 4GB+
- **CPU**: 2+ cores
- **Disk**: 20GB+ free space
- **Network**: Public IP address with ports 80 and 443 accessible

### Required Software
- Docker (version 20.10+)
- Docker Compose (version 2.0+)
- Git
- Node.js 18+ (for building frontend)

---

## Project Architecture

```
┌─────────────────────────────────────────────────┐
│                    Nginx                        │
│         (Reverse Proxy + SSL/TLS)              │
│  Port 80 (HTTP) → 443 (HTTPS Redirect)         │
│  Port 443 (HTTPS) → Frontend + API             │
└─────────┬───────────────────────┬───────────────┘
          │                       │
          │ Static Files          │ /api/*
          │ (Vue.js)              │ (Symfony)
          ▼                       ▼
┌──────────────────┐    ┌──────────────────┐
│   Frontend       │    │   PHP-FPM        │
│   (dist/)        │    │   (Symfony 8)    │
└──────────────────┘    └────────┬─────────┘
                                 │
                    ┌────────────┼────────────┐
                    ▼            ▼            ▼
              ┌─────────┐  ┌─────────┐  ┌─────────┐
              │ MariaDB │  │  Redis  │  │  Media  │
              │   DB    │  │  Cache  │  │ Storage │
              └─────────┘  └─────────┘  └─────────┘
```

---

## Initial Server Setup

### 1. Install Docker and Docker Compose

```bash
# Update package index
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common

# Add Docker's official GPG key
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /usr/share/keyrings/docker-archive-keyring.gpg

# Add Docker repository
echo "deb [arch=$(dpkg --print-architecture) signed-by=/usr/share/keyrings/docker-archive-keyring.gpg] https://download.docker.com/linux/ubuntu $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Install Docker
sudo apt update
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-compose-plugin

# Add current user to docker group
sudo usermod -aG docker $USER

# Start and enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Verify installation
docker --version
docker compose version
```

### 2. Install Node.js (for building frontend)

```bash
# Install Node.js 18.x
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Verify installation
node --version
npm --version
```

### 3. Create Project Directory

```bash
# Create directory
sudo mkdir -p /var/www/bot_api
sudo chown $USER:$USER /var/www/bot_api

# Navigate to directory
cd /var/www/bot_api
```

### 4. Clone Repository

```bash
# Clone your repository
git clone <your-repository-url> .

# Or if using SSH
git clone git@github.com:username/bot_api.git .
```

---

## Environment Configuration

### 1. Root Environment File

```bash
# Copy example file
cp .env.example .env

# Edit with your actual values
nano .env
```

**Important variables to update:**
- `MYSQL_PASSWORD` - Strong database password
- `MYSQL_ROOT_PASSWORD` - Strong root password
- `REDIS_PASSWORD` - Strong Redis password
- `APP_SECRET` - Random 32+ character string
- `JWT_PASSPHRASE` - Strong passphrase for JWT
- `DOMAIN` - Your actual domain name

### 2. Backend Environment File

```bash
# Copy example file
cp app/.env.example app/.env.local

# Edit with your values
nano app/.env.local
```

**Key configurations:**
- Match `DATABASE_URL` with Docker Compose database credentials
- Match `REDIS_URL` with Redis password
- Set `CORS_ALLOW_ORIGIN` to your domain
- Update `JWT_PASSPHRASE` to match root .env
- Add your `TELEGRAM_BOT_TOKEN` if applicable

### 3. Frontend Environment File

```bash
# Copy example file
cp frontend/.env.example frontend/.env.production

# Edit with your values
nano frontend/.env.production
```

**Update:**
- `VITE_API_URL` - Your production domain (e.g., https://yourdomain.com)

---

## Building and Deployment

### 1. Build Frontend

```bash
cd frontend

# Install dependencies
npm install

# Build for production (uses .env.production)
npm run build

# Go back to root
cd ..
```

### 2. Generate SSL Certificates

**Option A: Self-signed (for testing only)**
```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"
```

**Option B: Let's Encrypt (recommended for production)**
```bash
# Stop any service on port 80
sudo systemctl stop nginx 2>/dev/null || true

# Install certbot
sudo apt install -y certbot

# Generate certificates
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com

# Copy to project
sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/key.pem
sudo chown $USER:$USER docker/nginx/ssl/*.pem

# Set up auto-renewal
echo "0 0 * * * certbot renew --quiet && docker compose -f /var/www/bot_api/docker-compose.prod.yml restart nginx" | sudo crontab -
```

### 3. Generate JWT Keys

```bash
cd app

# Generate JWT keypair
php bin/console lexik:jwt:generate-keypair --skip-if-exists

# Or manually
mkdir -p config/jwt
openssl genpkey -out config/jwt/private.pem -aes256 -algorithm rsa -pkeyopt rsa_keygen_bits:4096
openssl pkey -in config/jwt/private.pem -out config/jwt/public.pem -pubout

cd ..
```

### 4. Build and Start Containers

```bash
# Build images
docker compose -f docker-compose.prod.yml build

# Start services
docker compose -f docker-compose.prod.yml up -d

# Check status
docker compose -f docker-compose.prod.yml ps

# View logs
docker compose -f docker-compose.prod.yml logs -f
```

### 5. Initialize Database

```bash
# Run migrations (if using migrations)
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction

# OR update schema (if not using migrations)
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:schema:update --force

# Create admin user (if applicable)
docker compose -f docker-compose.prod.yml exec php php bin/console app:create-admin
```

---

## SSL/TLS Configuration

### Automatic SSL Renewal

For Let's Encrypt certificates, set up automatic renewal:

```bash
# Create renewal script
sudo tee /usr/local/bin/renew-ssl.sh > /dev/null <<'EOF'
#!/bin/bash
certbot renew --quiet
cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem /var/www/bot_api/docker/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/yourdomain.com/privkey.pem /var/www/bot_api/docker/nginx/ssl/key.pem
docker compose -f /var/www/bot_api/docker-compose.prod.yml restart nginx
EOF

# Make executable
sudo chmod +x /usr/local/bin/renew-ssl.sh

# Add to crontab
echo "0 0 * * * /usr/local/bin/renew-ssl.sh" | sudo crontab -
```

---

## Database Management

### Backup Database

```bash
# Create backup directory
mkdir -p backups

# Backup database
docker compose -f docker-compose.prod.yml exec database \
  mysqldump -u root -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} > backups/backup_$(date +%Y%m%d_%H%M%S).sql

# Or using docker exec
docker exec bot_api_database \
  mysqldump -u root -pYOUR_ROOT_PASSWORD bot_api_db > backups/backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database

```bash
# Restore from backup
docker compose -f docker-compose.prod.yml exec -T database \
  mysql -u root -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} < backups/backup_20260123_120000.sql
```

### Automated Backups

```bash
# Create backup script
tee backup-db.sh > /dev/null <<'EOF'
#!/bin/bash
BACKUP_DIR="/var/www/bot_api/backups"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

docker exec bot_api_database \
  mysqldump -u root -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} \
  | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

# Keep only last 7 days of backups
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +7 -delete
EOF

chmod +x backup-db.sh

# Add to crontab (daily at 2 AM)
echo "0 2 * * * /var/www/bot_api/backup-db.sh" | crontab -
```

---

## Monitoring and Maintenance

### View Logs

```bash
# All services
docker compose -f docker-compose.prod.yml logs -f

# Specific service
docker compose -f docker-compose.prod.yml logs -f php
docker compose -f docker-compose.prod.yml logs -f nginx
docker compose -f docker-compose.prod.yml logs -f database

# Last 100 lines
docker compose -f docker-compose.prod.yml logs --tail=100 php
```

### Container Management

```bash
# Restart all services
docker compose -f docker-compose.prod.yml restart

# Restart specific service
docker compose -f docker-compose.prod.yml restart php

# Stop all services
docker compose -f docker-compose.prod.yml stop

# Start all services
docker compose -f docker-compose.prod.yml start

# Rebuild and restart
docker compose -f docker-compose.prod.yml up -d --build
```

### Update Application

```bash
# Pull latest code
git pull origin main

# Rebuild frontend
cd frontend
npm install
npm run build
cd ..

# Rebuild backend container
docker compose -f docker-compose.prod.yml build php

# Restart services
docker compose -f docker-compose.prod.yml up -d

# Run migrations if needed
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction

# Clear cache
docker compose -f docker-compose.prod.yml exec php php bin/console cache:clear
```

### Resource Monitoring

```bash
# View container resource usage
docker stats

# View disk usage
docker system df

# Clean up unused resources
docker system prune -a
```

---

## Troubleshooting

### Common Issues

#### 1. Port Already in Use

```bash
# Check what's using port 80/443
sudo lsof -i :80
sudo lsof -i :443

# Stop conflicting service (e.g., Apache)
sudo systemctl stop apache2
sudo systemctl disable apache2
```

#### 2. Permission Issues

```bash
# Fix Symfony var directory permissions
docker compose -f docker-compose.prod.yml exec php chown -R www-data:www-data /var/www/html/var
docker compose -f docker-compose.prod.yml exec php chmod -R 775 /var/www/html/var

# Fix media directory permissions
docker compose -f docker-compose.prod.yml exec php chown -R www-data:www-data /var/www/html/public/media
```

#### 3. Database Connection Issues

```bash
# Check database is running
docker compose -f docker-compose.prod.yml ps database

# Test database connection
docker compose -f docker-compose.prod.yml exec database mysql -u root -p${MYSQL_ROOT_PASSWORD} -e "SELECT 1;"

# Check PHP can connect
docker compose -f docker-compose.prod.yml exec php php bin/console doctrine:query:sql "SELECT 1"
```

#### 4. Redis Connection Issues

```bash
# Test Redis connection
docker compose -f docker-compose.prod.yml exec redis redis-cli -a ${REDIS_PASSWORD} ping

# Should return: PONG
```

#### 5. 502 Bad Gateway

```bash
# Check PHP-FPM is running
docker compose -f docker-compose.prod.yml ps php

# Check PHP-FPM logs
docker compose -f docker-compose.prod.yml logs php

# Restart PHP service
docker compose -f docker-compose.prod.yml restart php
```

#### 6. Nginx Configuration Errors

```bash
# Test nginx configuration
docker compose -f docker-compose.prod.yml exec nginx nginx -t

# Reload nginx
docker compose -f docker-compose.prod.yml exec nginx nginx -s reload
```

### Debug Mode

To enable debug mode temporarily:

```bash
# Set APP_ENV to dev in app/.env.local
docker compose -f docker-compose.prod.yml exec php sh -c 'echo "APP_ENV=dev" >> .env.local'

# Clear cache
docker compose -f docker-compose.prod.yml exec php php bin/console cache:clear

# Remember to switch back to prod!
```

### Access Container Shell

```bash
# PHP container
docker compose -f docker-compose.prod.yml exec php sh

# Nginx container
docker compose -f docker-compose.prod.yml exec nginx sh

# Database container
docker compose -f docker-compose.prod.yml exec database bash
```

---

## Security Checklist

- [ ] Changed all default passwords in `.env`
- [ ] Generated strong `APP_SECRET`
- [ ] Generated strong `JWT_PASSPHRASE`
- [ ] Configured SSL/TLS certificates
- [ ] Set up firewall (UFW)
- [ ] Disabled SSH root login
- [ ] Set up automated backups
- [ ] Configured log rotation
- [ ] Updated `CORS_ALLOW_ORIGIN` to specific domain
- [ ] Removed debug/development tools from production

### Firewall Setup

```bash
# Enable UFW
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
sudo ufw status
```

---

## Performance Tuning

### PHP-FPM Optimization

Edit `docker/php/php-prod.ini` and adjust:
- `memory_limit`
- `opcache.memory_consumption`
- `realpath_cache_size`

### Database Optimization

Create `docker/mysql/conf.d/custom.cnf`:

```ini
[mysqld]
max_connections = 150
innodb_buffer_pool_size = 1G
innodb_log_file_size = 256M
```

### Redis Optimization

Add to docker-compose.prod.yml under redis service:

```yaml
command: redis-server --maxmemory 256mb --maxmemory-policy allkeys-lru --appendonly yes
```

---

## Additional Resources

- [Symfony Documentation](https://symfony.com/doc/current/index.html)
- [Docker Documentation](https://docs.docker.com/)
- [Nginx Documentation](https://nginx.org/en/docs/)
- [Let's Encrypt](https://letsencrypt.org/)

---

## Support

For issues or questions, please create an issue in the project repository.


# Docker Compose Production Deployment Guide

This guide provides complete instructions for deploying the Bot API project (Symfony + Vue.js + Redis) using Docker Compose.

## Deployment Scenarios

Choose your deployment scenario:

| Scenario | SSL | Browser Warnings | Setup Complexity | Cost |
|----------|-----|------------------|------------------|------|
| **A: Domain with SSL (Let's Encrypt)** | ✅ Free, Trusted | ❌ None | Medium | $10-15/year (domain) |
| **B: Domain without SSL (HTTP only)** | ❌ No encryption | ⚠️ Not secure warning | Easy | $10-15/year (domain) |
| **C: IP Address (Self-signed SSL)** | ⚠️ Self-signed | ⚠️ Security warning | Easy | Free |

**Recommended for:**
- **Option A**: Production deployments (best security)
- **Option B**: Testing with domain, non-sensitive data
- **Option C**: Testing, development, internal tools

💡 **Using IP Address?** You're using Option C - Continue with this guide.

---

## 🚀 Quick Deployment (Recommended)

**One-command deployment after configuration:**

```bash
make deploy-prod
```

This runs `scripts/deploy-quick.sh` which automatically:
- ✅ Builds frontend
- ✅ Generates SSL certificates (if missing)
- ✅ Builds & starts Docker containers
- ✅ Generates JWT keys
- ✅ Runs database migrations
- ✅ Shows deployment status

**Useful Make Commands:**
```bash
make help          # Show all commands
make build         # Build containers only
make start         # Start services
make stop          # Stop services
make logs          # View logs
make db-migrate    # Run migrations
make ps            # Container status
```

**For manual step-by-step deployment**, continue reading below.

---

## Architecture Overview

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
              │ Mysql   │  │  Redis  │  │  Media  │
              │   DB    │  │  Cache  │  │ Storage │
              └─────────┘  └─────────┘  └─────────┘
```

---

## Initial Server Setup

### 1. Install Docker and Docker Compose

**Method 1: Using Docker's Official Installation Script (Recommended)**

This is the easiest and most reliable method that works on all modern Linux distributions:

```bash
# Update system packages
sudo apt update && sudo apt upgrade -y

# Remove any old Docker installations
sudo apt remove -y docker docker-engine docker.io containerd runc 2>/dev/null || true

# Download and run Docker's official installation script
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh

# Install Docker Compose plugin
sudo apt update
sudo apt install -y docker-compose-plugin

# Add current user to docker group
sudo usermod -aG docker $USER

# Start and enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Apply group changes (or log out and back in)
newgrp docker

# Verify installation
docker --version
docker compose version

# Test Docker
docker run hello-world
```

**Expected output:**
- Docker version 24.0+ or later
- Docker Compose version v2.x.x or later
- "Hello from Docker!" message

---

**Method 2: Manual Repository Installation (Alternative)**

If the automated script doesn't work, use this manual method:

<details>
<summary>Click to expand manual installation steps</summary>

```bash
# Update package index
sudo apt update && sudo apt upgrade -y

# Install required packages
sudo apt install -y apt-transport-https ca-certificates curl software-properties-common gnupg lsb-release

# Remove any old Docker installations
sudo apt remove -y docker docker-engine docker.io containerd runc 2>/dev/null || true

# Add Docker's official GPG key
sudo mkdir -p /etc/apt/keyrings
curl -fsSL https://download.docker.com/linux/ubuntu/gpg | sudo gpg --dearmor -o /etc/apt/keyrings/docker.gpg
sudo chmod a+r /etc/apt/keyrings/docker.gpg

# Add Docker repository (Ubuntu)
echo \
  "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/ubuntu \
  $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# For Debian, use this instead:
# echo \
#   "deb [arch=$(dpkg --print-architecture) signed-by=/etc/apt/keyrings/docker.gpg] https://download.docker.com/linux/debian \
#   $(lsb_release -cs) stable" | sudo tee /etc/apt/sources.list.d/docker.list > /dev/null

# Update package index with Docker repository
sudo apt update

# Install Docker Engine and Docker Compose plugin
sudo apt install -y docker-ce docker-ce-cli containerd.io docker-buildx-plugin docker-compose-plugin

# Add current user to docker group
sudo usermod -aG docker $USER

# Start and enable Docker
sudo systemctl start docker
sudo systemctl enable docker

# Verify installation
docker --version
docker compose version

# Test Docker (may need to log out and back in for group changes)
sudo docker run hello-world
```

</details>

---

**Troubleshooting Docker Installation:**

If you encounter errors like "Package 'docker-ce' has no installation candidate":

```bash
# Check your OS version
lsb_release -a

# Use the official script (most reliable)
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt install -y docker-compose-plugin

# Verify
docker --version
docker compose version
```

For more installation help, see: `documentation/DOCKER_INSTALLATION_FIX.md`

### 2. Install Node.js (for building frontend)

```bash
# Install Node.js 22.x
curl -fsSL https://deb.nodesource.com/setup_22.x | sudo -E bash -
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

**Note:** Production uses `.env.prod` to avoid conflicts with local development `.env` files (e.g., Warden, Docker Desktop, etc.). See `documentation/ENV_PROD_USAGE.md` for details.

### 1. Root Environment File


**Configure based on your deployment option:**

**Option A & B (Domain):**
```dotenv
DOMAIN=yourdomain.com
CORS_ALLOW_ORIGIN=^https?://(localhost|yourdomain\.com)(:[0-9]+)?$
```

**Option C (IP Address - e.g., 46.175.145.84):**
```dotenv
DOMAIN=46.175.145.84
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$
```
⚠️ **Important for IP:** Escape dots with backslash: `46\.175\.145\.84`
cp .env.example .env.prod

# Edit with your actual values
nano .env.prod

# Create symlink so docker-compose uses .env.prod
ln -sf .env.prod .env
```

**Important variables to update:**
- `MYSQL_PASSWORD` - Strong database password
- `MYSQL_ROOT_PASSWORD` - Strong root password
- `REDIS_PASSWORD` - Strong Redis password
- `APP_SECRET` - Random 32+ character string
- `JWT_PASSPHRASE` - Strong passphrase for JWT
- `DOMAIN` - Your actual domain name **OR server IP address** (e.g., `45.76.123.45`)
- `CORS_ALLOW_ORIGIN` - Update with your domain or IP (escape dots: `192\.168\.1\.100`)

**📝 Note:** Don't have a domain? You can use your server's IP address! See `documentation/DEPLOYMENT_WITHOUT_DOMAIN.md` for complete IP-based deployment guide.
```bash
# Copy example file (using .env.prod to avoid conflicts with local .env)
TELEGRAM_WEBHOOK_URL=https://yourdomain.com/api/telegram/webhook
```

**Option B (Domain without SSL):**
```dotenv
CORS_ALLOW_ORIGIN=^https?://(localhost|yourdomain\.com)(:[0-9]+)?$
TELEGRAM_WEBHOOK_URL=http://yourdomain.com/api/telegram/webhook
```

**Option C (IP Address - 46.175.145.84):**
```dotenv
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$
TELEGRAM_WEBHOOK_URL=http://46.175.145.84/api/telegram/webhook
```
⚠️ **Remember:** Escape dots in IP: `46\.175\.145\.84`

**Why `.env.prod.local`?**
- Symfony **skips** `.env.local` when `APP_ENV=prod`
- In production, Symfony loads: `.env` → `.env.prod` → **`.env.prod.local`**
- `.env.prod.local` is gitignored (safe for production credentials)

**Key configurations:**
- Match `DATABASE_URL` with Docker Compose database credentials
**Configure API URL based on your deployment option:**

**Option A (Domain with SSL - Let's Encrypt):**
```dotenv
VITE_API_URL=https://yourdomain.com
VITE_DEBUG=false
```

**Option B (Domain without SSL - HTTP only):**
```dotenv
VITE_API_URL=http://yourdomain.com
VITE_DEBUG=false
```

**Option C (IP Address - 46.175.145.84):**
```dotenv
VITE_API_URL=http://46.175.145.84
VITE_DEBUG=false
```

💡 **Tip:** If using self-signed SSL with IP, use `https://46.175.145.84` but users will need to accept browser security warnings.
- Update `JWT_PASSPHRASE` to match root `.env.prod`
- Update `TELEGRAM_BOT_TOKEN` if applicable
- **Important:** Update `serverVersion=11.3` to `serverVersion=8.0` for MySQL

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
### 2. SSL/TLS Certificate Setup
```
Choose the option that matches your deployment scenario:

# Note: Replace 46.175.145.84 with your actual IP or use localhost
```
#### **Option A: Domain with SSL (Let's Encrypt) - Recommended for Production**
⚠️ **Note:** Self-signed certificates will show browser security warnings. Users must manually accept the certificate.
✅ **Best for:** Production deployments with a domain name
✅ **SSL:** Free, automatically trusted by browsers
✅ **Security:** Full HTTPS encryption
---

**Option B: Let's Encrypt (ONLY for domain names - NOT for IP addresses)**

⚠️ **Important:** Let's Encrypt **CANNOT** issue certificates for IP addresses. You **MUST** have a domain name.

**Steps:**

**Requirements:**
# 1. Stop any service on port 80
- Domain DNS A record points to your server IP
- Port 80 is accessible from the internet
# 2. Install certbot
```bash
# Stop any service on port 80
# 3. Generate certificates (replace with your actual domain)

# Install certbot
# 4. Create SSL directory
mkdir -p docker/nginx/ssl

# 5. Copy certificates to project

# Generate certificates (replace with your actual domain)
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com

# 6. Set up automatic renewal (see SSL Configuration section below)


**Access your app:** `https://yourdomain.com`
# Copy to project
sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/key.pem
#### **Option B: Domain without SSL (HTTP Only) - Testing Only**

⚠️ **Best for:** Testing, non-sensitive data
❌ **SSL:** None - unencrypted HTTP only
⚠️ **Security:** Data transmitted in plain text

**Requirements:**
- You own a domain name
- Domain DNS A record points to your server IP

**Steps:**

```bash
# 1. Create dummy SSL files (nginx still needs them in config)
mkdir -p docker/nginx/ssl

# 2. Generate self-signed cert (won't be used, but nginx config requires files)
openssl req -x509 -nodes -days 1 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/CN=localhost"

# 3. Update nginx config to only listen on port 80 (optional)
# Edit docker/nginx/conf.d/default.conf and remove SSL server block
```

**Access your app:** `http://yourdomain.com`
```
⚠️ **Warning:** Not recommended for production! No encryption means credentials and data are visible to network observers.

For testing with IP addresses, you can disable HTTPS:
- Access via: `http://YOUR_IP` (no SSL)
#### **Option C: IP Address with Self-Signed SSL - What You're Using**
- Only use for development/testing
✅ **Best for:** Testing, development, internal tools
⚠️ **SSL:** Self-signed - browser will show security warnings
✅ **Security:** Encrypted, but users must manually accept certificate
| Have domain name | Option B (Let's Encrypt) | ✅ Free, trusted SSL |
**Requirements:**
- Server with public IP address
- Ports 80 and 443 accessible

**Steps:**

```bash
# 1. Create SSL directory
mkdir -p docker/nginx/ssl

# 2. Generate self-signed certificate for your IP (e.g., 46.175.145.84)
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/C=US/ST=State/L=City/O=YourOrg/CN=46.175.145.84"

# Note: Replace 46.175.145.84 with your actual IP address
```

**Access your app:**
- HTTPS: `https://46.175.145.84` (will show security warning - click "Advanced" and "Proceed")
- HTTP: `http://46.175.145.84` (if you want to avoid warnings, but less secure)

⚠️ **Browser Warning:** Users will see "Your connection is not private" or similar. This is normal for self-signed certificates. Click "Advanced" → "Proceed to 46.175.145.84 (unsafe)" to continue.

💡 **For production:** Consider getting a cheap domain ($10/year) to use Let's Encrypt free SSL without warnings.

---

#### **Quick Comparison**

| Option | Command | Access URL | Browser Warning? | Data Encrypted? |
|--------|---------|------------|------------------|-----------------|
| **A: Domain + Let's Encrypt** | `certbot certonly --standalone -d yourdomain.com` | `https://yourdomain.com` | ❌ No | ✅ Yes |
| **B: Domain HTTP only** | No SSL setup needed | `http://yourdomain.com` | ⚠️ "Not secure" | ❌ No |
| **C: IP + Self-signed** | `openssl req -x509 ... -subj "/CN=46.175.145.84"` | `https://46.175.145.84` | ⚠️ "Not private" | ✅ Yes |

---

#### **Why Let's Encrypt doesn't work with IP addresses**

Let's Encrypt **requires a domain name** because:
- Certificates are tied to domain names in the DNS system
- IP addresses can change or be reassigned
- Domain validation is done via DNS/HTTP challenges
- This is a security feature, not a limitation

**Solution:** If you need trusted SSL certificates, get a domain name (very affordable: $10-15/year from Namecheap, Google Domains, etc.)
| Local testing | Option A (Self-signed) | ⚠️ Works but browser warnings |
| Internal network | Option A (Self-signed) | ⚠️ Works but browser warnings |
| Production with IP | Get a domain first! | 🛑 Self-signed not ideal |

💡 **Tip:** Domains are cheap ($10-15/year). Consider getting one for production deployments to enable proper SSL.

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

**Option 1: Using Make Commands (Recommended - Simpler)**

```bash
# Build images
make build

# Start services
make start

# Check status
make ps

# View logs
make logs
```

**Option 2: Using Docker Compose Directly**

**Using Make Commands (Recommended):**

```bash
# Run migrations
make db-migrate

# OR update schema (if not using migrations)
make db-update
```

**Using Docker Compose Directly:**

```bash
# Run migrations
docker compose -f docker/docker-compose.prod.yml build

# OR update schema
docker compose -f docker/docker-compose.prod.yml up -d

# Check status
docker compose -f docker/docker-compose.prod.yml ps

# View logs
docker compose -f docker/docker-compose.prod.yml logs -f
```

💡 **Tip:** Use `make help` to see all available commands!

**Note:** The `.env` symlink created earlier points to `.env.prod`, so docker-compose automatically uses production settings.

### 5. Initialize Database

```bash
# Run migrations (if using migrations)
docker compose -f docker/docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction

# OR update schema (if not using migrations)
docker compose -f docker/docker-compose.prod.yml exec php php bin/console doctrine:schema:update --force

# Create admin user (if applicable)
docker compose -f docker/docker-compose.prod.yml exec php php bin/console app:create-admin
```

---

## SSL/TLS Configuration

### Automatic SSL Renewal (Option A Only - Let's Encrypt with Domain)

⚠️ **Note:** This section **ONLY applies to Option A** (Domain with Let's Encrypt).

If you're using:
- **Option B** (Domain without SSL) - Skip this section
- **Option C** (IP with self-signed SSL) - Skip this section, self-signed certs are valid for 365 days

---

**For Option A users:**

Set up automatic certificate renewal to prevent expiration:

```bash
# Create renewal script
sudo tee /usr/local/bin/renew-ssl.sh > /dev/null <<'EOF'
#!/bin/bash
certbot renew --quiet
cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem /var/www/bot_api/docker/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/yourdomain.com/privkey.pem /var/www/bot_api/docker/nginx/ssl/key.pem
docker compose -f /var/www/bot_api/docker/docker-compose.prod.yml restart nginx
EOF

# Make executable
sudo chmod +x /usr/local/bin/renew-ssl.sh

# Add to crontab (runs daily at midnight)
echo "0 0 * * * /usr/local/bin/renew-ssl.sh" | sudo crontab -

# Test renewal (dry run)
sudo certbot renew --dry-run
```

Let's Encrypt certificates are valid for 90 days. The cron job will automatically renew them when they're close to expiration.
#!/bin/bash
certbot renew --quiet
cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem /var/www/bot_api/docker/nginx/ssl/cert.pem
cp /etc/letsencrypt/live/yourdomain.com/privkey.pem /var/www/bot_api/docker/nginx/ssl/key.pem
docker compose -f /var/www/bot_api/docker/docker-compose.prod.yml restart nginx
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
docker compose -f docker/docker-compose.prod.yml exec database \
  mysqldump -u root -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} > backups/backup_$(date +%Y%m%d_%H%M%S).sql

# Or using docker exec
docker exec bot_api_database \
  mysqldump -u root -pYOUR_ROOT_PASSWORD bot_api_db > backups/backup_$(date +%Y%m%d_%H%M%S).sql
```

### Restore Database

```bash
# Restore from backup
docker compose -f docker/docker-compose.prod.yml exec -T database \
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
docker compose -f docker/docker-compose.prod.yml logs -f

# Specific service
docker compose -f docker/docker-compose.prod.yml logs -f php
docker compose -f docker/docker-compose.prod.yml logs -f nginx
docker compose -f docker/docker-compose.prod.yml logs -f database

# Last 100 lines
docker compose -f docker/docker-compose.prod.yml logs --tail=100 php
```

### Container Management

```bash
# Restart all services
docker compose -f docker/docker-compose.prod.yml restart

# Restart specific service
docker compose -f docker/docker-compose.prod.yml restart php

# Stop all services
docker compose -f docker/docker-compose.prod.yml stop

# Start all services
docker compose -f docker/docker-compose.prod.yml start

# Rebuild and restart
docker compose -f docker/docker-compose.prod.yml up -d --build
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
docker compose -f docker/docker-compose.prod.yml build php

# Restart services
docker compose -f docker/docker-compose.prod.yml up -d

# Run migrations if needed
docker compose -f docker/docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction

# Clear cache
docker compose -f docker/docker-compose.prod.yml exec php php bin/console cache:clear
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
docker compose -f docker/docker-compose.prod.yml ps database

# Test database connection
docker compose -f docker/docker-compose.prod.yml exec database mysql -u root -p${MYSQL_ROOT_PASSWORD} -e "SELECT 1;"

# Check PHP can connect
docker compose -f docker/docker-compose.prod.yml exec php php bin/console doctrine:query:sql "SELECT 1"
```

#### 4. Redis Connection Issues

```bash
# Test Redis connection
docker compose -f docker/docker-compose.prod.yml exec redis redis-cli -a ${REDIS_PASSWORD} ping

# Should return: PONG
```

#### 5. 502 Bad Gateway

```bash
# Check PHP-FPM is running
docker compose -f docker/docker-compose.prod.yml ps php

# Check PHP-FPM logs
docker compose -f docker/docker-compose.prod.yml logs php

# Restart PHP service
docker compose -f docker/docker-compose.prod.yml restart php
```

#### 6. Nginx Configuration Errors

```bash
# Test nginx configuration
docker compose -f docker/docker-compose.prod.yml exec nginx nginx -t

# Reload nginx
docker compose -f docker/docker-compose.prod.yml exec nginx nginx -s reload
```

### Debug Mode

To enable debug mode temporarily:

```bash
# Set APP_ENV to dev in app/.env.local
docker compose -f docker/docker-compose.prod.yml exec php sh -c 'echo "APP_ENV=dev" >> .env.local'

# Clear cache
docker compose -f docker/docker-compose.prod.yml exec php php bin/console cache:clear

# Remember to switch back to prod!
```

### Access Container Shell

```bash
# PHP container
docker compose -f docker/docker-compose.prod.yml exec php sh

# Nginx container
docker compose -f docker/docker-compose.prod.yml exec nginx sh

# Database container
docker compose -f docker/docker-compose.prod.yml exec database bash
```

---

## Security Checklist

- [ ] Changed all default passwords in `.env.prod`
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


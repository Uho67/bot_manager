# Deployment Checklist for Server 46.175.145.84

## Your Server Configuration

**Server IP:** `46.175.145.84`
**CORS Pattern:** `^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$`
**Access URL:** `http://46.175.145.84`

---

## Quick Deployment Steps

### 1. On Your Production Server (46.175.145.84)

```bash
# Install Docker
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt install -y docker-compose-plugin
sudo usermod -aG docker $USER
newgrp docker

# Verify Docker
docker --version
docker compose version

# Install Node.js
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs

# Create project directory
sudo mkdir -p /var/www/bot_api
sudo chown $USER:$USER /var/www/bot_api
cd /var/www/bot_api

# Clone your repository
git clone <your-repo-url> .
```

### 2. Configure Environment

```bash
# Copy the template file
cp .env.prod.TEMPLATE_46.175.145.84 .env.prod

# Edit with your actual passwords
nano .env.prod
```

**✅ Your CORS is already configured correctly:**
```dotenv
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$
DOMAIN=46.175.145.84
```

**⚠️ MUST CHANGE these values:**
- `MYSQL_PASSWORD` → Use a strong password
- `MYSQL_ROOT_PASSWORD` → Use a different strong password
- `REDIS_PASSWORD` → Use a strong password
- `APP_SECRET` → Generate 32+ random characters
- `JWT_PASSPHRASE` → Use a strong passphrase
- Update `DATABASE_URL` with your MySQL password

**💡 Generate strong passwords:**
```bash
# Generate random passwords
openssl rand -base64 32
```

### 3. Create Symlink

```bash
# Create symlink so docker-compose uses .env.prod
ln -sf .env.prod .env
```

### 4. Configure Backend Environment

```bash
cp app/.env.example app/.env.prod.local
nano app/.env.prod.local
```

**Important:** Use `.env.prod.local` not `.env.local` - Symfony skips `.env.local` when APP_ENV=prod!

Update to match your .env.prod passwords:
- `DATABASE_URL` - Use same password as MYSQL_PASSWORD in .env.prod
- `REDIS_URL` - Use same password as REDIS_PASSWORD in .env.prod
- `CORS_ALLOW_ORIGIN` - Set to `^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$`
- `JWT_PASSPHRASE` - Use same as in .env.prod
- `serverVersion=8.0` - Make sure it's MySQL 8.0, not 11.3

### 5. Build Frontend

```bash
cd frontend
npm install
npm run build
cd ..
```

Update `frontend/.env.production.local`:
```dotenv
VITE_API_URL=http://46.175.145.84
```

### 6. Generate JWT Keys

```bash
mkdir -p app/config/jwt
# Will be generated automatically by Docker
```

### 7. Generate Self-Signed SSL (Optional)

```bash
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/CN=46.175.145.84"
```

### 8. Configure Firewall

```bash
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp
sudo ufw enable
sudo ufw status
```

### 9. Deploy

**Quick Deploy (Recommended):**
```bash
# One command deployment
make deploy-prod
```

**Manual Deploy:**
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

### 10. Initialize Database

```bash
# Run migrations
make db-migrate

# Or update schema
make db-update
```

### 11. Test Your Application

```bash
# From your local machine
curl http://46.175.145.84/api

# Or open in browser
# http://46.175.145.84
```

---

## Verification Checklist

- [ ] Docker installed and running
- [ ] Node.js installed
- [ ] Project cloned to `/var/www/bot_api`
- [ ] `.env.prod` created with strong passwords
- [ ] CORS configured: `46\.175\.145\.84` (with backslashes!)
- [ ] Symlink created: `.env → .env.prod`
- [ ] Frontend built
- [ ] Firewall configured (ports 80, 443, 22)
- [ ] Docker containers running
- [ ] Database initialized
- [ ] Application accessible at `http://46.175.145.84`

---

## Your Specific Configuration

### CORS Pattern (Already Correct)
```dotenv
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$
```

**Why the backslashes?**
- In regex, a plain dot `.` matches ANY character
- We want to match a literal dot in the IP address
- `\.` escapes the dot to match it literally
- So `46\.175\.145\.84` matches exactly that IP

### Access URLs
- **HTTP**: `http://46.175.145.84`
- **HTTPS**: `https://46.175.145.84` (if you generated SSL certificate)
- **API**: `http://46.175.145.84/api`

---

## Common Issues & Solutions

### CORS Errors
**Problem:** "CORS policy: No 'Access-Control-Allow-Origin' header"

**Check:**
```bash
# Verify CORS in .env.prod has backslashes
grep CORS_ALLOW_ORIGIN .env.prod
# Should show: CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$
```

### Can't Access from External Network
```bash
# Check firewall
sudo ufw status

# Check if nginx is listening
sudo netstat -tlnp | grep :80

# Check container logs
docker compose -f docker/docker-compose.prod.yml logs nginx
```

### Database Connection Issues
```bash
# Test database
docker compose -f docker/docker-compose.prod.yml exec database mysql -u root -p

# Check PHP can connect
docker compose -f docker/docker-compose.prod.yml exec php php bin/console doctrine:query:sql "SELECT 1"
```
make logs
---

make restart

```bash
make stop

# Start again
make start
docker compose -f docker/docker-compose.prod.yml logs -f

make build
make start
docker compose -f docker/docker-compose.prod.yml restart

make ssh-php

# See all available commands
make help
docker compose -f docker/docker-compose.prod.yml down

# Rebuild and restart
docker compose -f docker/docker-compose.prod.yml up -d --build

# Access PHP container
docker compose -f docker/docker-compose.prod.yml exec php sh
```

---

## Support & Documentation

- **IP Deployment Guide**: `documentation/DEPLOYMENT_WITHOUT_DOMAIN.md`
- **Full Deployment**: `documentation/DEPLOYMENT_DOCKER.md`
- **Docker Installation**: `documentation/DOCKER_INSTALLATION_FIX.md`
- **Quick Reference**: `documentation/QUICK_REFERENCE.md`

---

**Ready to deploy? Start with Step 1!** 🚀


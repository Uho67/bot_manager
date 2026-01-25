# Quick Deployment Guide - Option C (IP Address)

## Your Setup
- **IP Address:** 46.175.145.84
- **SSL:** Self-signed certificate
- **Access:** `http://46.175.145.84` or `https://46.175.145.84` (with browser warning)

## Quick Steps

### 1. Install Docker
```bash
curl -fsSL https://get.docker.com -o get-docker.sh
sudo sh get-docker.sh
sudo apt install -y docker-compose-plugin
sudo usermod -aG docker $USER
newgrp docker
```

### 2. Install Node.js
```bash
curl -fsSL https://deb.nodesource.com/setup_18.x | sudo -E bash -
sudo apt install -y nodejs
```

### 3. Setup Project
```bash
sudo mkdir -p /var/www/bot_api
sudo chown $USER:$USER /var/www/bot_api
cd /var/www/bot_api
git clone <your-repo> .
```

### 4. Configure Root Environment
```bash
cp .env.example .env.prod
nano .env.prod
```

**Set these values:**
```dotenv
DOMAIN=46.175.145.84
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$
MYSQL_PASSWORD=your_strong_password
MYSQL_ROOT_PASSWORD=your_strong_root_password
REDIS_PASSWORD=your_strong_redis_password
APP_SECRET=your_random_32_character_secret
JWT_PASSPHRASE=your_strong_jwt_passphrase
```

```bash
ln -sf .env.prod .env
```

### 5. Configure Backend
```bash
cp app/.env.example app/.env.prod.local
nano app/.env.prod.local
```

**Update:**
```dotenv
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$
DATABASE_URL="mysql://bot_api_user:your_strong_password@database:3306/bot_api_db?serverVersion=8.0&charset=utf8mb4"
REDIS_URL=redis://:your_strong_redis_password@redis:6379
JWT_PASSPHRASE=your_strong_jwt_passphrase
```

### 6. Configure Frontend
```bash
cp frontend/.env.example frontend/.env.production
nano frontend/.env.production
```

**Set:**
```dotenv
VITE_API_URL=http://46.175.145.84
```

### 7. Build Frontend
```bash
cd frontend
npm install
npm run build
cd ..
```

### 8. Generate Self-Signed SSL
```bash
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/CN=46.175.145.84"
```

### 9. Setup Firewall
```bash
sudo ufw allow 22/tcp
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### 10. Deploy
```bash
docker compose -f docker/docker-compose.prod.yml build
docker compose -f docker/docker-compose.prod.yml up -d
```

### 11. Initialize Database
```bash
docker compose -f docker/docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction
```

### 12. Check Status
```bash
docker compose -f docker/docker-compose.prod.yml ps
```

## Access Your Application

**HTTP (no warnings):**
```
http://46.175.145.84
```

**HTTPS (with browser warning - click Advanced → Proceed):**
```
https://46.175.145.84
```

## Useful Commands

```bash
# View logs
make logs

# Restart services
make restart

# Stop all
make stop

# Start again
make start

# Remove everything
make down

# Access PHP container
make ssh-php

# Clear Symfony cache
make cache-clear

# See all available commands
make help
```

## Checklist

- [ ] Docker installed
- [ ] Node.js installed
- [ ] .env.prod configured with IP: `46.175.145.84`
- [ ] CORS has escaped dots: `46\.175\.145\.84`
- [ ] app/.env.prod.local configured
- [ ] frontend/.env.production has `VITE_API_URL=http://46.175.145.84`
- [ ] SSL certificate generated
- [ ] Firewall configured
- [ ] Containers running
- [ ] Can access: `http://46.175.145.84`

## Next Steps

When you get a domain:
1. Point DNS A record to 46.175.145.84
2. Update DOMAIN in .env.prod
3. Get Let's Encrypt certificate
4. Update CORS and frontend API URL
5. Restart services

---

**Full documentation:** `documentation/DEPLOYMENT_DOCKER.md`


# 🧪 Local Testing Guide

## Quick Start - Test Locally in 5 Minutes

You can test the entire Docker setup on your local machine (macOS) before deploying to production.

---

## Prerequisites

✅ Docker Desktop installed
✅ Node.js installed
✅ Your current macOS setup

---

## Option 1: Quick Local Test (Recommended)

### 1. Run the Local Test Script

```bash
./test-local.sh
```

This automated script will:
- Set up local environment files
- Build the frontend
- Generate self-signed SSL certificates
- Start all Docker containers
- Run health checks
- Show you how to access the app

---

## Option 2: Manual Local Setup

### Step 1: Configure Environment for Local Testing

```bash
# Use development environment
cp .env.dev.example .env.local

# Backend - use local settings
cp app/.env.example app/.env.local

# Frontend - use local API URL
cat > frontend/.env.local << EOF
VITE_API_URL=http://localhost:8080
VITE_DEBUG=true
EOF
```

### Step 2: Build Frontend

```bash
cd frontend
npm install
npm run build
cd ..
```

### Step 3: Start Development Environment

```bash
# Use development compose file (no SSL needed)
docker compose -f docker-compose.dev.yml up -d

# Or using Makefile
make dev-start
```

### Step 4: Access Your Application

**Backend API:**
- URL: http://localhost:8080
- API Docs: http://localhost:8080/api

**Frontend Development:**
```bash
cd frontend
npm run dev
# Opens at http://localhost:5173
```

**Database:**
- Host: localhost
- Port: 3306
- User: app
- Password: app
- Database: app

**Redis:**
- Host: localhost
- Port: 6379
- Password: redis

---

## Option 3: Test Production Setup Locally

If you want to test the full production setup with SSL:

### Step 1: Configure for Local Production

```bash
# Copy environment files
cp .env.example .env.local
cp app/.env.example app/.env.local
cp frontend/.env.example frontend/.env.production.local

# Edit frontend/.env.production.local
echo "VITE_API_URL=https://localhost" > frontend/.env.production.local
```

### Step 2: Generate Self-Signed SSL Certificates

```bash
make ssl-generate
# Or manually:
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/C=US/ST=State/L=City/O=Test/CN=localhost"
```

### Step 3: Build Frontend

```bash
cd frontend
npm install
npm run build
cd ..
```

### Step 4: Deploy Locally

```bash
./deploy-quick.sh
# Or
make deploy-prod
```

### Step 5: Access Application

- **Frontend**: https://localhost (accept self-signed certificate warning)
- **API**: https://localhost/api

---

## Local Testing Commands

### Start Services

```bash
# Development mode (recommended for local)
make dev-start

# Production mode
make start
```

### View Logs

```bash
# All services
make dev-logs   # or make logs

# Specific service
docker compose -f docker-compose.dev.yml logs -f php
```

### Stop Services

```bash
make dev-stop   # or make stop
```

### Run Health Check

```bash
./health-check.sh
```

### Access Database

```bash
# Via command line
docker compose -f docker-compose.dev.yml exec database mysql -u app -papp app

# Or use a GUI client:
# - Sequel Ace (macOS)
# - TablePlus
# - DBeaver
# Host: localhost, Port: 3306, User: app, Pass: app
```

### Access Redis

```bash
docker compose -f docker-compose.dev.yml exec redis redis-cli -a redis
```

### Clear Everything and Start Fresh

```bash
# Stop and remove everything
make clean

# Start again
make dev-start
```

---

## Recommended Local Workflow

### For Frontend Development:

```bash
# Terminal 1: Run backend in Docker
make dev-start

# Terminal 2: Run frontend with hot reload
cd frontend
npm run dev
```

This gives you:
- ✅ Hot reload for Vue.js
- ✅ Real backend API in Docker
- ✅ Fast development cycle

### For Backend Development:

```bash
# Run everything in Docker
make dev-start

# Watch logs
make dev-logs

# Execute Symfony commands
docker compose -f docker-compose.dev.yml exec php php bin/console cache:clear
```

---

## Testing Checklist

After starting services, verify:

- [ ] Containers are running: `make ps`
- [ ] Database is accessible: `make ssh-db` then `mysql -u app -papp`
- [ ] Redis is accessible: Test with redis-cli
- [ ] API responds: `curl http://localhost:8080/api`
- [ ] Frontend loads (if using dev server): `http://localhost:5173`
- [ ] No errors in logs: `make dev-logs`

---

## Common Local Issues & Fixes

### Port Already in Use

If port 8080 is in use:

```bash
# Edit .env.local and change:
HTTP_PORT=8081

# Then restart
make dev-stop
make dev-start
```

### Database Connection Failed

```bash
# Wait for database to fully start
docker compose -f docker-compose.dev.yml ps

# Check database logs
docker compose -f docker-compose.dev.yml logs database

# Restart database
docker compose -f docker-compose.dev.yml restart database
```

### Permission Issues on macOS

```bash
# Fix permissions
docker compose -f docker-compose.dev.yml exec php chown -R www-data:www-data /var/www/html/var
```

### Clear All Docker Data

```bash
# Nuclear option - removes everything
docker compose -f docker-compose.dev.yml down -v
docker system prune -a --volumes

# Then start fresh
make dev-start
```

---

## Local vs Production Differences

| Feature | Local Dev | Production |
|---------|-----------|------------|
| SSL/TLS | Not needed | Required |
| Port | 8080 | 80, 443 |
| Xdebug | Enabled | Disabled |
| Opcache | Dynamic | Static |
| Error Display | Verbose | Hidden |
| Frontend | Dev server | Built static |
| Volumes | Bind mounts | Named volumes |

---

## Quick Local Test Script

The `test-local.sh` script does everything for you:

```bash
./test-local.sh
```

It will:
1. ✅ Check prerequisites (Docker, Node.js)
2. ✅ Set up environment files
3. ✅ Install and build frontend
4. ✅ Start Docker containers
5. ✅ Wait for services to be ready
6. ✅ Run health checks
7. ✅ Display access URLs and credentials
8. ✅ Show useful commands

---

## Local Development Tips

### 1. Use Development Mode

Development mode is optimized for local testing:
- Port 8080 (no conflicts with system services)
- Xdebug enabled
- Hot reload friendly
- Detailed error messages

### 2. Frontend Hot Reload

Instead of building frontend each time:

```bash
cd frontend
npm run dev
```

Point your browser to `http://localhost:5173` for instant updates.

### 3. Watch Backend Logs

Keep logs visible while developing:

```bash
make dev-logs
```

### 4. Quick Cache Clear

```bash
docker compose -f docker-compose.dev.yml exec php php bin/console cache:clear
```

### 5. Database Migrations

```bash
docker compose -f docker-compose.dev.yml exec php php bin/console doctrine:migrations:migrate
```

---

## Next Steps After Local Testing

Once everything works locally:

1. ✅ Push code to repository
2. ✅ Set up production server
3. ✅ Get real SSL certificates
4. ✅ Update production environment files
5. ✅ Deploy using `./deploy-quick.sh` on server

---

## Useful Local Commands

```bash
# Start local testing
./test-local.sh

# View all containers
docker ps

# View logs
make dev-logs

# Stop everything
make dev-stop

# Clean everything
make clean

# Restart a service
docker compose -f docker-compose.dev.yml restart php

# Execute commands in containers
docker compose -f docker-compose.dev.yml exec php sh
docker compose -f docker-compose.dev.yml exec database mysql -u app -papp

# Check resource usage
docker stats
```

---

## Browser Access

### Development Mode (HTTP):
- Frontend Dev Server: http://localhost:5173
- Backend API: http://localhost:8080/api
- Accept frontend's local dev server

### Production Mode Locally (HTTPS):
- Everything: https://localhost
- You'll need to accept the self-signed certificate warning

---

## Summary

**Easiest Way**: Run `./test-local.sh` 🚀

**Manual Way**: Use `make dev-start` for development mode

**Full Production Test**: Use `./deploy-quick.sh` with self-signed SSL

---

**Ready to test?** Run: `./test-local.sh`


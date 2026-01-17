# Production Deployment Guide

This guide explains how to deploy the Symfony API backend and Vue.js frontend for this project in a simple, production-ready way.

---

## Prerequisites
- Linux server (Ubuntu recommended)
- Docker and Docker Compose installed
- Node.js (for building frontend assets)
- Git

---

## 0. Recommended Directory Structure

Store your project in `/var/www/bot_api` (or similar):

```bash
sudo mkdir -p /var/www/bot_api
sudo chown $USER:$USER /var/www/bot_api
cd /var/www/bot_api
```

---

## 1. Clone the Repository

```bash
git clone <your-repo-url> .
```

---

## 2. Environment Variables

### Backend (Symfony)
- Symfony env files live in `app/`.
- In production, prefer **`app/.env.local`** (not committed) for secrets.
- Your database host **inside docker-compose network** should be the compose service name: `database`.

Example `DATABASE_URL` for MariaDB (Doctrine):

```dotenv
DATABASE_URL="mariadb://app:!ChangeMe!@database:3306/app?serverVersion=11.3"
```

### Frontend (Vue)
Set the API base URL in `frontend/.env`:

```dotenv
VITE_API_URL=https://your-domain.com
```

(Your Vue app calls `${VITE_API_URL}/api/...`.)

---

## 3. Start MariaDB (Docker Compose)

From repo root:

```bash
docker compose -f app/compose.yaml -f app/compose.override.yaml up -d
```

Check it’s healthy:

```bash
docker compose -f app/compose.yaml -f app/compose.override.yaml ps
```

---

## 4. Backend Dependencies (Symfony)

```bash
cd app
composer install --no-dev --optimize-autoloader
```

---

## 5. Run Migrations (after DB is up)

How you run migrations depends on **how Symfony is running** in production.

### Option A (Symfony runs on the host)

```bash
cd app
php bin/console doctrine:migrations:migrate --no-interaction
```

### Option B (Symfony runs in a container)
Run the same command inside your Symfony/PHP container:

```bash
docker compose exec <symfony-service> php bin/console doctrine:migrations:migrate --no-interaction
```

> Note: your current `app/compose.yaml` defines **only the `database` service**. If you want Symfony to run in Docker too, you must add a PHP/Symfony service to the compose file.

---

## 6. Build the Frontend (Vue)

```bash
cd ../frontend
npm ci
npm run build
```

This creates `frontend/dist/`.

---

## 7. Nginx (serve frontend + proxy API)

### Where files live
- Frontend static files: `/var/www/bot_api/frontend/dist`
- Symfony code: `/var/www/bot_api/app`

### Nginx config (simple)
This version serves the SPA and proxies `/api` to a backend HTTP server.

If you run Symfony behind an HTTP server on **localhost:8000** (example), use:

```nginx
server {
    listen 80;
    server_name your-domain.com;

    root /var/www/bot_api/frontend/dist;
    index index.html;

    location /api/ {
        proxy_pass http://127.0.0.1:8000;
        proxy_set_header Host $host;
        proxy_set_header X-Real-IP $remote_addr;
        proxy_set_header X-Forwarded-For $proxy_add_x_forwarded_for;
        proxy_set_header X-Forwarded-Proto $scheme;
    }

    location / {
        try_files $uri $uri/ /index.html;
    }
}
```

Important:
- `proxy_pass http://127.0.0.1:8000;` assumes Symfony is reachable there (for example via Caddy/Apache, or a Docker container port mapped to localhost).
- If you run PHP-FPM directly with Nginx (more typical), the Nginx config is different (fastcgi). Keep it simple: proxy to an HTTP backend.

---

## 8. Permissions

Ensure Symfony writeable dirs are writable by the process user (web server / PHP):
- `app/var/`

---

## 9. Health Check

- Open `https://your-domain.com/` (frontend)
- Call `https://your-domain.com/api/admin-users`

---

## 10. SSL

Use Let’s Encrypt (Certbot) or your hosting provider.

---

## Notes / Common Mistakes
- Don’t use `bot_api-db-1` in `DATABASE_URL`. That’s a container name that can change. Use the compose service name `database`.
- Don’t expose DB to the internet. The override file binds MariaDB to `127.0.0.1:3306` for local access only.

---

## Done

Your project should now be running in production

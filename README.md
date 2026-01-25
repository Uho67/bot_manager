# Bot API

Symfony 7.2 + Vue.js 3 + Docker

## Quick Deployment

### First Time Setup

```bash
# 1. Clone repository
git clone <repo-url> /var/www/bot_api
cd /var/www/bot_api

# 2. Create environment files
cp .env.example .env.prod
nano .env.prod  # Configure your settings

cp app/.env.example app/.env.prod.local
nano app/.env.prod.local  # Match settings with .env.prod

# 3. Deploy
make deploy-prod
```

### Update Deployment

```bash
cd /var/www/bot_api
git pull
make deploy-prod
```

## Environment Configuration

### .env.prod (Required values)

```dotenv
DOMAIN=your-server-ip-or-domain
MYSQL_PASSWORD=your_strong_password
MYSQL_ROOT_PASSWORD=your_root_password
REDIS_PASSWORD=your_redis_password
APP_SECRET=random_32_character_string
DATABASE_URL=mysql://bot_api_user:your_strong_password@database:3306/bot_api_db?serverVersion=8.0&charset=utf8mb4
JWT_PASSPHRASE=your_jwt_passphrase
CORS_ALLOW_ORIGIN=^https?://(localhost|your-server-ip)(:[0-9]+)?$
```

### app/.env.prod.local

Match the passwords from .env.prod.

## Commands

```bash
make deploy-prod   # Full deployment
make logs          # View logs
make ps            # Container status
make restart       # Restart services
make db-update     # Update database
make cache-clear   # Clear cache
make clean-all     # Remove everything
```

## Troubleshooting

### Git conflicts on server

```bash
git restore .
git pull
```

### View logs

```bash
make logs
```

### Rebuild from scratch

```bash
make clean-all
make deploy-prod
```


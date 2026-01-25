# Production Environment Configuration (.env.prod)

This file should be used for production deployments to avoid conflicts with local development `.env` files (e.g., Warden setup).

## Why .env.prod?

- `.env` is commonly used by local development tools (Warden, Docker Desktop, etc.)
- Using `.env.prod` prevents conflicts and accidental overwrites
- Keeps production and development configurations separate

## Setup

```bash
# On production server
cp .env.example .env.prod
nano .env.prod  # Edit with production values

# Create symlink so docker-compose uses .env.prod
ln -sf .env.prod .env
```

This approach:
- Keeps actual values in `.env.prod` (clear production file)
- Creates `.env` symlink pointing to `.env.prod`
- Docker Compose automatically reads `.env` (which points to `.env.prod`)
- Your local `.env` (for Warden, etc.) remains untouched

## Docker Compose Configuration

The `docker/docker-compose.prod.yml` file is configured to use `.env.prod`:

```yaml
version: '3.8'

env_file:
  - ../.env.prod

services:
  # ... services configuration
```

## Important Variables

Make sure to update these critical variables in `.env.prod`:

### Database
- `MYSQL_VERSION` - MySQL version (default: 8.0)
- `MYSQL_DATABASE` - Database name
- `MYSQL_USER` - Database user
- `MYSQL_PASSWORD` - **Strong password required!**
- `MYSQL_ROOT_PASSWORD` - **Strong password required!**

### Redis
- `REDIS_PASSWORD` - **Strong password required!**

### Application
- `APP_ENV` - Set to `prod`
- `APP_SECRET` - **32+ character random string required!**
- `JWT_PASSPHRASE` - **Strong passphrase required!**

### Domain
- `DOMAIN` - Your production domain (e.g., example.com)
- `CORS_ALLOW_ORIGIN` - Your domain pattern

## Security Notes

1. **Never commit `.env.prod` to version control**
2. Use strong, unique passwords for all services
3. Keep `.env.prod` permissions restrictive: `chmod 600 .env.prod`
4. Backup `.env.prod` securely (encrypted)

## Deployment Commands

Since `.env` is a symlink to `.env.prod`, all docker-compose commands work normally:

```bash
# Build
docker compose -f docker/docker-compose.prod.yml build

# Start
docker compose -f docker/docker-compose.prod.yml up -d

# Stop
docker compose -f docker/docker-compose.prod.yml down
```

Docker Compose automatically reads `.env` file, which points to `.env.prod`.

## Local Development

For local development, continue using your existing `.env` file with Warden or other tools. The production setup won't interfere.

```bash
# Local development uses .env (not tracked here)
# Production uses .env.prod
```

## Migration from Existing Setup

If you previously used `.env` for production:

```bash
# Backup existing .env
cp .env .env.backup

# Copy to .env.prod
cp .env .env.prod

# Restore local .env if needed
# (or keep .env.backup for reference)
```


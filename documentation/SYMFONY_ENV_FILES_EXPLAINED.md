# Symfony Environment File Loading Explained

## The Problem with `.env.local` in Production

### TL;DR
**DO NOT use `.env.local` for production!** Use `.env.prod.local` instead.

## Why `.env.local` Doesn't Work in Production

Symfony has a special rule: **`.env.local` is ignored when `APP_ENV=prod`**

This is by design for security and performance reasons.

## Symfony Environment File Loading Order

### Development (APP_ENV=dev)
```
.env                  ← Base file (committed to git)
.env.local           ← Local overrides (gitignored) ✅ LOADED
.env.dev             ← Dev defaults (committed to git)
.env.dev.local       ← Dev local overrides (gitignored)
```

### Production (APP_ENV=prod)
```
.env                  ← Base file (committed to git)
.env.local           ← ❌ SKIPPED in production!
.env.prod            ← Prod defaults (committed to git)
.env.prod.local      ← Prod overrides (gitignored) ✅ USE THIS!
```

## Correct Production Setup

### Step 1: Create `.env.prod.local`
```bash
cp app/.env.example app/.env.prod.local
nano app/.env.prod.local
```

### Step 2: Set Production Values
```dotenv
APP_ENV=prod
APP_SECRET=your_random_32_character_secret
DATABASE_URL="mysql://bot_api_user:STRONG_PASSWORD@database:3306/bot_api_db?serverVersion=8.0&charset=utf8mb4"
REDIS_URL=redis://:REDIS_PASSWORD@redis:6379
CORS_ALLOW_ORIGIN=^https?://(localhost|46\.175\.145\.84)(:[0-9]+)?$
JWT_PASSPHRASE=your_strong_passphrase
TELEGRAM_BOT_TOKEN=your_bot_token
```

### Step 3: Verify It's Gitignored
Check `app/.gitignore` contains:
```gitignore
/.env.*.local
```

This pattern covers `.env.prod.local`, `.env.dev.local`, etc.

## Why Symfony Does This

1. **Performance**: In production, Symfony doesn't need to check multiple override files
2. **Security**: Explicit production config reduces risk of dev settings leaking to prod
3. **Clarity**: Makes it obvious which file is for production

## File Purpose Summary

| File | Committed? | Used in Dev? | Used in Prod? | Purpose |
|------|------------|--------------|---------------|---------|
| `.env` | ✅ Yes | ✅ Yes | ✅ Yes | Base configuration for all environments |
| `.env.local` | ❌ No | ✅ Yes | ❌ **NO** | Local dev overrides (SKIPPED in prod!) |
| `.env.prod` | ✅ Yes | ❌ No | ✅ Yes | Production defaults |
| `.env.prod.local` | ❌ No | ❌ No | ✅ Yes | **Production secrets (USE THIS!)** |
| `.env.dev` | ✅ Yes | ✅ Yes | ❌ No | Development defaults |
| `.env.dev.local` | ❌ No | ✅ Yes | ❌ No | Dev local overrides |

## Common Mistake

❌ **WRONG** (will NOT work in production):
```bash
cp app/.env.example app/.env.local
# This file is IGNORED when APP_ENV=prod!
```

✅ **CORRECT** (will work in production):
```bash
cp app/.env.example app/.env.prod.local
# This file is loaded when APP_ENV=prod
```

## How to Verify

### Check What Files Symfony is Loading

```bash
# In your PHP container
docker compose -f docker/docker-compose.prod.yml exec php sh

# Inside container, check environment
php bin/console debug:container --env-vars

# Or check what files exist
ls -la .env*
```

### Expected Files in Production Container
```
.env              ← From git (base config)
.env.prod         ← From git (if exists)
.env.prod.local   ← Your production secrets (mounted or copied)
```

### NOT Expected in Production
```
.env.local        ← Will be ignored even if present!
.env.dev          ← Dev only
.env.dev.local    ← Dev only
```

## Docker Volume Considerations

When using Docker volumes, you can:

**Option 1: Mount the file (development/testing)**
```yaml
volumes:
  - ./app:/var/www/html
  # .env.prod.local is included
```

**Option 2: Copy during build (production)**
```dockerfile
COPY app/.env.prod.local /var/www/html/.env.prod.local
```

**Option 3: Use Docker secrets (advanced)**
```yaml
secrets:
  - app_env_prod_local
```

## Real-World Example

Your production server (46.175.145.84):

```bash
# Create production config
cat > app/.env.prod.local << 'EOF'
APP_ENV=prod
APP_SECRET=a8f5e9c2b1d4f7e3a6c9b2d5e8f1a4c7b0d3e6f9a2c5b8d1e4f7a0c3b6
DATABASE_URL="mysql://bot_api_user:MyStr0ng_Pa$$w0rd123@database:3306/bot_api_db?serverVersion=8.0&charset=utf8mb4"
REDIS_URL=redis://:Red1s_Pa$$w0rd789@redis:6379
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$
JWT_PASSPHRASE=JWT_Str0ng_Passphrase_XYZ
TELEGRAM_BOT_TOKEN=1234567890:ABCdefGHIjklMNOpqrsTUVwxyz
EOF

# Verify it's gitignored
git status  # Should NOT show .env.prod.local

# Deploy
docker compose -f docker/docker-compose.prod.yml up -d
```

## Troubleshooting

### Symptom: Environment variables not loading
**Check:**
```bash
docker compose -f docker/docker-compose.prod.yml exec php env | grep APP_ENV
# Should show: APP_ENV=prod

docker compose -f docker/docker-compose.prod.yml exec php ls -la .env*
# Should list .env.prod.local
```

### Symptom: Using .env.local but values not working
**Solution:**
```bash
# Rename .env.local to .env.prod.local
mv app/.env.local app/.env.prod.local

# Restart containers
docker compose -f docker/docker-compose.prod.yml restart php
```

## References

- [Symfony Environment Variables Documentation](https://symfony.com/doc/current/configuration.html#selecting-the-active-environment)
- [Symfony DotEnv Component](https://symfony.com/doc/current/components/dotenv.html)

## Summary

✅ **Production: Use `.env.prod.local`**
❌ **Production: Don't use `.env.local`** (it's ignored!)
✅ **Development: `.env.local` works fine**
✅ **All environments: `.env` is always loaded first**

---

**Key Takeaway:** In production Docker deployment, always use `app/.env.prod.local` for your secrets and configuration overrides. The file `.env.local` is specifically excluded when `APP_ENV=prod`.


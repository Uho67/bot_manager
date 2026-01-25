# ✅ FIXED: Backend Environment File Issue

## The Problem

The documentation incorrectly instructed to use `app/.env.local` for production deployment.

**This doesn't work!** Symfony **specifically excludes** `.env.local` when `APP_ENV=prod`.

## The Fix

Changed from:
```bash
cp app/.env.example app/.env.local  # ❌ WRONG - ignored in production!
```

To:
```bash
cp app/.env.example app/.env.prod.local  # ✅ CORRECT - loaded in production!
```

## Why This Matters

### Symfony's Environment File Loading

**In Production (APP_ENV=prod):**
- `.env` → Loaded ✅
- `.env.local` → **SKIPPED** ❌
- `.env.prod` → Loaded ✅
- `.env.prod.local` → Loaded ✅

**In Development (APP_ENV=dev):**
- `.env` → Loaded ✅
- `.env.local` → Loaded ✅
- `.env.dev` → Loaded ✅
- `.env.dev.local` → Loaded ✅

## Files Updated

1. **`documentation/DEPLOYMENT_DOCKER.md`**
   - Fixed backend environment file section
   - Added explanation of why `.env.prod.local` is needed
   - Added reminder about MySQL 8.0 serverVersion

2. **`app/.env.example`**
   - Changed `serverVersion=11.3` to `serverVersion=8.0`
   - Updated comments to reflect MySQL instead of MariaDB

3. **`DEPLOYMENT_CHECKLIST_46.175.145.84.md`**
   - Updated to use `.env.prod.local`
   - Added warning about not using `.env.local`

4. **`documentation/SYMFONY_ENV_FILES_EXPLAINED.md`** (NEW)
   - Complete explanation of Symfony environment file loading
   - Shows the loading order for each environment
   - Common mistakes and how to avoid them
   - Troubleshooting guide

## What You Should Do

### On Production Server (46.175.145.84)

```bash
# CORRECT way:
cp app/.env.example app/.env.prod.local
nano app/.env.prod.local
```

Update these values:
```dotenv
APP_ENV=prod
DATABASE_URL="mysql://bot_api_user:YOUR_PASSWORD@database:3306/bot_api_db?serverVersion=8.0&charset=utf8mb4"
REDIS_URL=redis://:YOUR_REDIS_PASSWORD@redis:6379
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|46\.175\.145\.84)(:[0-9]+)?$
JWT_PASSPHRASE=YOUR_JWT_PASSPHRASE
```

### Important Notes

1. ✅ `.env.prod.local` is already gitignored (pattern: `/.env.*.local`)
2. ✅ Use `serverVersion=8.0` for MySQL (not 11.3)
3. ✅ Match passwords from root `.env.prod`
4. ✅ Escape dots in CORS IP: `46\.175\.145\.84`

## Verification

After deployment, verify the file is loaded:

```bash
# Check if file exists
docker compose -f docker/docker-compose.prod.yml exec php ls -la .env*

# Should show:
# .env
# .env.prod.local

# Check environment
docker compose -f docker/docker-compose.prod.yml exec php env | grep DATABASE_URL
```

## Summary

**The Issue:** Documentation said to use `app/.env.local` for production
**The Problem:** Symfony ignores `.env.local` when `APP_ENV=prod`
**The Solution:** Use `app/.env.prod.local` instead
**Status:** ✅ FIXED in all documentation

---

**Date:** January 25, 2026
**Files Changed:** 4
**New Documentation:** 1
**Impact:** Critical fix for production deployment


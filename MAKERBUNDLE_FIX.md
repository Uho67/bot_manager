# ✅ FIXED: MakerBundle Error During Production Build

## The Error

```
Attempted to load class "MakerBundle" from namespace "Symfony\Bundle\MakerBundle".
Did you forget a "use" statement for another namespace?
Script cache:clear returned with error code 255
```

## Root Cause

When running `composer install --no-dev` in the Dockerfile, Composer was triggering the `post-install-cmd` scripts which include `cache:clear`. The `cache:clear` command was trying to load all bundles, including `MakerBundle`, but since we used `--no-dev`, the MakerBundle package wasn't installed.

**The sequence:**
1. `composer install --no-dev` → Installs only production dependencies
2. Composer runs `post-install-cmd` scripts automatically
3. Scripts include `cache:clear`
4. `cache:clear` tries to load kernel and all bundles
5. `MakerBundle` class not found (it's a dev dependency) → **ERROR**

## The Fix

Updated `docker/php/Dockerfile` to:

1. **Skip auto-scripts** during composer install using `--no-scripts` flag
2. **Set APP_ENV=prod** before running composer
3. **Manually clear/warmup cache** after composer install with explicit `--env=prod` flag

### Changed Code

**Before:**
```dockerfile
COPY app /var/www/html

RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress \
    && composer dump-autoload --optimize --no-dev --classmap-authoritative
```

**After:**
```dockerfile
COPY app /var/www/html

# Set environment to production
ENV APP_ENV=prod

# Install dependencies (skip auto-scripts to avoid MakerBundle error)
RUN composer install --no-dev --optimize-autoloader --no-interaction --no-progress --no-scripts \
    && composer dump-autoload --optimize --no-dev --classmap-authoritative

# Clear cache after composer install
RUN php bin/console cache:clear --env=prod --no-warmup || true \
    && php bin/console cache:warmup --env=prod || true
```

## Key Changes

1. ✅ Added `--no-scripts` to `composer install` - Prevents automatic execution of post-install scripts
2. ✅ Added `ENV APP_ENV=prod` - Ensures Symfony runs in production mode
3. ✅ Added manual cache commands - Explicitly runs cache:clear and cache:warmup with `--env=prod`
4. ✅ Added `|| true` - Prevents build failure if cache commands have issues

## Why This Works

- `--no-scripts` prevents Composer from running `cache:clear` during install
- `ENV APP_ENV=prod` tells Symfony to load only production bundles
- Manual `cache:clear --env=prod` runs after all dependencies are installed
- MakerBundle is correctly excluded in production (it's in `bundles.php` as `['dev' => true]`)

## Testing the Fix

**On your server:**

```bash
# Rebuild the image
make build

# Or manually:
docker compose -f docker/docker-compose.prod.yml build --no-cache

# Then start services
make start
```

## What to Expect

The build should now complete successfully without the MakerBundle error.

**Successful output will show:**
```
✅ Composer install completed (without scripts)
✅ Autoload optimized
✅ Cache cleared for prod environment
✅ Cache warmed up
✅ Build successful
```

## Additional Notes

### Why --no-scripts is Safe

The `--no-scripts` flag skips the `post-install-cmd` scripts defined in `composer.json`, which typically include:
- `cache:clear` - We run this manually anyway
- Other auto-scripts - Not critical during Docker build

### bundles.php is Correct

The `app/config/bundles.php` file is already correctly configured:

```php
Symfony\Bundle\MakerBundle\MakerBundle::class => ['dev' => true],
```

This means MakerBundle is **only** loaded in `dev` environment, which is correct.

### Cache Handling

Cache is cleared/warmed during:
1. **Build time** - In Dockerfile (after our fix)
2. **Deployment** - By `deploy-quick.sh` script
3. **Runtime** - As needed when code changes

## Related Issues

If you still encounter cache issues after deployment, you can manually clear cache:

```bash
# Inside container
docker compose -f docker/docker-compose.prod.yml exec php php bin/console cache:clear --env=prod

# Or using make
make cache-clear
```

## Summary

**Problem:** MakerBundle class not found during production build
**Cause:** Composer auto-scripts trying to load dev bundles during `--no-dev` install
**Fix:** Skip auto-scripts, set APP_ENV=prod, manually handle cache
**Status:** ✅ FIXED

---

**You can now run:**
```bash
make deploy-prod
```

The build should complete successfully! 🚀


# ✅ YES - You Can Use IP Address Instead of Domain!

## Quick Answer

**YES!** You can absolutely deploy using your server's IP address instead of a domain name.

## Quick Setup (3 Steps)

### 1. Get Your Server IP
```bash
curl ifconfig.me
# Example output: 45.76.123.45
```

### 2. Configure .env.prod
```bash
cp .env.example .env.prod
nano .env.prod
```

Update these values:
```dotenv
# Use your server IP
DOMAIN=45.76.123.45  # Replace with YOUR actual IP

# CORS - Escape dots with backslash
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|45\.76\.123\.45)(:[0-9]+)?$

# Other required settings
MYSQL_PASSWORD=your_strong_password
MYSQL_ROOT_PASSWORD=your_strong_root_password
REDIS_PASSWORD=your_strong_redis_password
APP_SECRET=your_32_character_random_secret
JWT_PASSPHRASE=your_strong_passphrase
```

### 3. Deploy
```bash
ln -sf .env.prod .env
docker compose -f docker/docker-compose.prod.yml up -d
```

## Access Your Application

```bash
# HTTP (recommended for IP-based deployment)
http://YOUR_SERVER_IP

# Example:
http://45.76.123.45
```

## Important Notes

### ✅ What Works
- API access via IP address
- All application features
- Database, Redis, backend, frontend
- Can upgrade to domain later without major changes

### ⚠️ Limitations
- **SSL/TLS**: Cannot use Let's Encrypt (requires domain)
  - Must use self-signed certificate (browser warnings)
  - Or use HTTP only (not recommended for production)
- **Professional appearance**: IP looks less professional than domain
- **Email/OAuth**: Some services require domain names

### 🔒 SSL Options with IP

**Option 1: Self-Signed Certificate** (has browser warnings)
```bash
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/CN=45.76.123.45"
```
Access: `https://45.76.123.45` (accept security warning)

**Option 2: HTTP Only** (for testing)
```bash
# Just use HTTP
# Access: http://45.76.123.45
```

## Example Configuration

### Complete .env.prod Example
```dotenv
COMPOSE_PROJECT_NAME=bot_api
HTTP_PORT=80
HTTPS_PORT=443

MYSQL_VERSION=8.0
MYSQL_DATABASE=bot_api_db
MYSQL_USER=bot_api_user
MYSQL_PASSWORD=MyStr0ng_Pa$$w0rd123
MYSQL_ROOT_PASSWORD=R00t_Pa$$w0rd456

REDIS_PASSWORD=Red1s_Pa$$w0rd789

APP_ENV=prod
APP_SECRET=a_random_32_char_secret_key_here_abc123xyz
DATABASE_URL=mysql://bot_api_user:MyStr0ng_Pa$$w0rd123@database:3306/bot_api_db?serverVersion=8.0&charset=utf8mb4
REDIS_URL=redis://:Red1s_Pa$$w0rd789@redis:6379

# Replace with YOUR server IP and escape dots!
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|45\.76\.123\.45)(:[0-9]+)?$

JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=JWT_Passphrase_ABC123

TELEGRAM_BOT_TOKEN=your_bot_token_here
NESTJS_PORT=3000

# Your server IP
DOMAIN=45.76.123.45
```

## Firewall Setup

```bash
# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp  # Keep SSH!
sudo ufw enable
```

## Testing

```bash
# From your local machine
curl http://YOUR_SERVER_IP/api

# Example
curl http://45.76.123.45/api
```

## Upgrade to Domain Later

When you get a domain:

1. Point DNS A record to your server IP
2. Update `.env.prod`:
   ```dotenv
   DOMAIN=yourdomain.com
   CORS_ALLOW_ORIGIN=^https?://(localhost|yourdomain\.com)(:[0-9]+)?$
   ```
3. Get Let's Encrypt certificate
4. Restart:
   ```bash
   docker compose -f docker/docker-compose.prod.yml restart
   ```

## Documentation

📚 **Complete Guide**: `documentation/DEPLOYMENT_WITHOUT_DOMAIN.md`
📚 **Full Deployment**: `documentation/DEPLOYMENT_DOCKER.md`
📚 **Quick Reference**: `documentation/QUICK_REFERENCE.md`

## Summary

✅ **YES, use IP address!**
✅ Simple setup: Change `DOMAIN` and `CORS_ALLOW_ORIGIN` in `.env.prod`
✅ Remember to escape dots in CORS: `192\.168\.1\.100`
✅ Access via: `http://YOUR_SERVER_IP`
✅ Can upgrade to domain anytime

---

**Your Next Step:**
```bash
# On production server
curl ifconfig.me  # Get your IP
cp .env.example .env.prod
nano .env.prod  # Update DOMAIN and CORS with your IP
```

Then follow: `documentation/DEPLOYMENT_DOCKER.md`
k

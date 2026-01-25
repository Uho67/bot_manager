# Deployment Without Domain - Using IP Address

## Overview

You can deploy the application using your server's IP address instead of a domain name. This is perfect for:
- Testing and development
- Internal networks
- Before domain is ready
- Cost savings (no domain needed immediately)

## Configuration

### 1. Get Your Server IP

```bash
# On your production server, find your public IP
curl ifconfig.me

# Or
curl icanhazip.com

# For local network IP
ip addr show
```

Example output: `192.168.1.100` or `45.76.123.45`

### 2. Configure .env.prod

```bash
# Copy and edit
cp .env.example .env.prod
nano .env.prod
```

**Update these values with your server IP:**

```dotenv
# Use your server IP instead of domain
DOMAIN=45.76.123.45  # Replace with your actual IP

# CORS - Allow your server IP
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1|45\.76\.123\.45)(:[0-9]+)?$

# Other settings
APP_ENV=prod
MYSQL_PASSWORD=your_strong_password
MYSQL_ROOT_PASSWORD=your_strong_root_password
REDIS_PASSWORD=your_strong_redis_password
APP_SECRET=your_32_character_random_secret_here
JWT_PASSPHRASE=your_strong_jwt_passphrase
```

**Important:**
- Escape dots in IP for CORS regex: `192\.168\.1\.100` (use backslash before dots)
- For multiple IPs: `^https?://(localhost|192\.168\.1\.100|45\.76\.123\.45)(:[0-9]+)?$`

### 3. SSL/TLS Considerations

#### Option A: Self-Signed Certificate (Testing)

```bash
# Generate self-signed certificate
mkdir -p docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/C=US/ST=State/L=City/O=Organization/CN=45.76.123.45"
```

**Note:** Browsers will show security warning (you'll need to accept it)

#### Option B: Let's Encrypt with IP (Not Recommended)

Let's Encrypt requires a domain name. Cannot issue certificates for IP addresses.

#### Option C: Use HTTP Only (Not Recommended for Production)

For testing only, you can disable HTTPS:

```bash
# Access via: http://45.76.123.45
# Update nginx configuration to listen on port 80 only
```

### 4. Nginx Configuration for IP

The existing nginx configuration works with IP addresses. No changes needed.

### 5. Frontend Configuration

Update frontend environment for IP-based API:

```bash
# frontend/.env.production.local
VITE_API_URL=https://45.76.123.45  # or http:// if no SSL
```

Or if using a different port:
```bash
VITE_API_URL=https://45.76.123.45:443
```

### 6. Firewall Configuration

Make sure ports are open:

```bash
# Allow HTTP and HTTPS
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw allow 22/tcp  # Keep SSH open!
sudo ufw enable
```

### 7. Deploy

```bash
# Create symlink
ln -sf .env.prod .env

# Deploy
cd /var/www/bot_api
docker compose -f docker/docker-compose.prod.yml up -d
```

### 8. Access Your Application

```bash
# API
http://YOUR_SERVER_IP
https://YOUR_SERVER_IP  # If using SSL

# Test
curl http://YOUR_SERVER_IP/api
curl https://YOUR_SERVER_IP/api  # If using SSL
```

## Complete Example Configuration

### Example 1: Public Server with HTTP Only

```dotenv
# .env.prod
DOMAIN=45.76.123.45
HTTP_PORT=80
HTTPS_PORT=443
CORS_ALLOW_ORIGIN=^https?://(localhost|45\.76\.123\.45)(:[0-9]+)?$
APP_ENV=prod
# ... other settings
```

Access: `http://45.76.123.45`

### Example 2: Local Network Server

```dotenv
# .env.prod
DOMAIN=192.168.1.100
HTTP_PORT=80
HTTPS_PORT=443
CORS_ALLOW_ORIGIN=^https?://(localhost|192\.168\.1\.100)(:[0-9]+)?$
APP_ENV=prod
# ... other settings
```

Access: `http://192.168.1.100`

### Example 3: Custom Port

```dotenv
# .env.prod
DOMAIN=45.76.123.45
HTTP_PORT=8080
HTTPS_PORT=8443
CORS_ALLOW_ORIGIN=^https?://(localhost|45\.76\.123\.45)(:[0-9]+)?$
APP_ENV=prod
# ... other settings
```

Access: `http://45.76.123.45:8080`

## Upgrading to Domain Later

When you get a domain, simply:

1. Point domain DNS A record to your server IP
2. Update `.env.prod`:
   ```dotenv
   DOMAIN=yourdomain.com
   CORS_ALLOW_ORIGIN=^https?://(localhost|yourdomain\.com)(:[0-9]+)?$
   ```
3. Get real SSL certificate with Let's Encrypt
4. Restart services

```bash
docker compose -f docker/docker-compose.prod.yml restart
```

## Security Considerations

### ⚠️ Important for IP-Only Deployment

1. **Self-signed SSL certificates** - Browsers show warnings
2. **No automatic SSL** - Can't use Let's Encrypt without domain
3. **CORS must include your IP** - Don't use `*` in production
4. **Firewall is critical** - Only expose necessary ports
5. **Consider VPN** - For sensitive applications

### Recommendations

**For Testing:**
- ✅ IP with self-signed certificate is fine
- ✅ HTTP only acceptable for internal testing

**For Production:**
- ❌ Don't use HTTP only (no encryption)
- ❌ Self-signed certs not ideal (warning messages)
- ✅ Get a cheap domain ($10/year)
- ✅ Use Let's Encrypt for free SSL

## Troubleshooting

### Can't Access from External Network

```bash
# Check if service is listening
sudo netstat -tlnp | grep :80
sudo netstat -tlnp | grep :443

# Check firewall
sudo ufw status

# Test from server itself
curl http://localhost
```

### CORS Errors

```bash
# Make sure IP is properly escaped in CORS_ALLOW_ORIGIN
# Wrong: 192.168.1.100
# Right: 192\.168\.1\.100

# Check nginx logs
docker compose -f docker/docker-compose.prod.yml logs nginx
```

### SSL Certificate Issues

```bash
# Verify certificate
openssl s_client -connect YOUR_IP:443

# Regenerate if needed
cd docker/nginx/ssl
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout key.pem -out cert.pem \
  -subj "/CN=YOUR_IP"
```

## Summary

✅ **Yes, you can use IP address!**
- Update `DOMAIN=YOUR_SERVER_IP` in `.env.prod`
- Escape dots in CORS: `192\.168\.1\.100`
- Use self-signed SSL (with browser warnings) or HTTP only
- Firewall must allow ports 80/443
- Can upgrade to domain later without major changes

**Quick Start:**
```bash
# Get your IP
curl ifconfig.me

# Configure
cp .env.example .env.prod
nano .env.prod  # Set DOMAIN to your IP

# Deploy
ln -sf .env.prod .env
docker compose -f docker/docker-compose.prod.yml up -d
```

Access your app at: `http://YOUR_SERVER_IP`


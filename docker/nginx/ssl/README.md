# Self-signed SSL certificates for development/testing
# For production, replace with real SSL certificates from Let's Encrypt or your provider

This directory should contain:
- cert.pem (SSL certificate)
- key.pem (Private key)

## Generate self-signed certificates for testing:

```bash
openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
  -keyout docker/nginx/ssl/key.pem \
  -out docker/nginx/ssl/cert.pem \
  -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"
```

## For production with Let's Encrypt:

Use certbot to generate real SSL certificates:

```bash
# Install certbot
sudo apt-get install certbot

# Generate certificates
sudo certbot certonly --standalone -d yourdomain.com -d www.yourdomain.com

# Copy certificates to this directory
sudo cp /etc/letsencrypt/live/yourdomain.com/fullchain.pem docker/nginx/ssl/cert.pem
sudo cp /etc/letsencrypt/live/yourdomain.com/privkey.pem docker/nginx/ssl/key.pem
```

## Auto-renewal setup:

```bash
# Add to crontab
0 0 * * * certbot renew --quiet && docker-compose -f docker-compose.prod.yml restart nginx
```


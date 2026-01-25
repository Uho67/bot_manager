#!/bin/bash
set -e

# ===========================================
# Bot API - Production Deployment Script
# ===========================================

cd "$(dirname "$0")/.."

echo "========================================"
echo "  Bot API - Production Deployment"
echo "========================================"
echo ""

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
RED='\033[0;31m'
NC='\033[0m'

# Step 1: Check prerequisites
echo "Checking prerequisites..."

if [ ! -f .env.prod ]; then
    echo -e "${RED}ERROR: .env.prod not found${NC}"
    echo "Create it: cp .env.example .env.prod && nano .env.prod"
    exit 1
fi

if [ ! -f app/.env.prod.local ]; then
    echo -e "${YELLOW}Creating app/.env.prod.local from example...${NC}"
    cp app/.env.example app/.env.prod.local
    echo -e "${YELLOW}Please configure app/.env.prod.local with your settings${NC}"
fi

# Create .env symlink
ln -sf .env.prod .env
echo -e "${GREEN}✓ Environment configured${NC}"

# Step 2: Build frontend
echo ""
echo "Building frontend..."
cd frontend
[ ! -d node_modules ] && npm install
npm run build
cd ..
echo -e "${GREEN}✓ Frontend built${NC}"

# Step 3: Generate SSL certificate if missing
if [ ! -f docker/nginx/ssl/cert.pem ]; then
    echo ""
    echo "Generating SSL certificate..."
    mkdir -p docker/nginx/ssl
    DOMAIN=$(grep "^DOMAIN=" .env.prod | cut -d'=' -f2 || echo "localhost")
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
        -keyout docker/nginx/ssl/key.pem \
        -out docker/nginx/ssl/cert.pem \
        -subj "/CN=${DOMAIN}" 2>/dev/null
    echo -e "${GREEN}✓ SSL certificate created${NC}"
fi

# Step 4: Build and start containers
echo ""
echo "Building containers..."
docker compose --env-file .env -f docker/docker-compose.prod.yml build

echo ""
echo "Starting containers..."
docker compose --env-file .env -f docker/docker-compose.prod.yml up -d

# Step 5: Wait for services
echo ""
echo "Waiting for services to start (30s)..."
sleep 30

# Step 6: Show status
echo ""
echo "Container status:"
docker compose --env-file .env -f docker/docker-compose.prod.yml ps

# Step 7: Initialize database
echo ""
echo "Initializing database..."
docker compose --env-file .env -f docker/docker-compose.prod.yml exec -T php php bin/console doctrine:schema:update --force || true

# Step 8: Clear cache
echo ""
echo "Clearing cache..."
docker compose --env-file .env -f docker/docker-compose.prod.yml exec -T php php bin/console cache:clear || true

# Done
DOMAIN=$(grep "^DOMAIN=" .env.prod | cut -d'=' -f2 || echo "localhost")
echo ""
echo "========================================"
echo -e "${GREEN}  ✓ Deployment Complete!${NC}"
echo "========================================"
echo ""
echo "Application: http://${DOMAIN}"
echo ""
echo "Commands:"
echo "  make logs        View logs"
echo "  make ps          Container status"
echo "  make restart     Restart services"
echo ""


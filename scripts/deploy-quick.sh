#!/bin/bash

# Quick Deployment Script for Bot API
# This script automates the deployment process

set -e

echo "==================================="
echo "Bot API - Quick Deployment Script"
echo "==================================="

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if .env exists
if [ ! -f ../.env ]; then
    echo -e "${RED}Error: .env file not found!${NC}"
    echo "Please copy .env.example to .env and configure it first."
    exit 1
fi

# Check if frontend is built
if [ ! -d "../frontend/dist" ]; then
    echo -e "${YELLOW}Frontend not built. Building now...${NC}"
    cd ../frontend
    npm install
    npm run build
    cd ../scripts
    echo -e "${GREEN}Frontend built successfully!${NC}"
fi

# Check if SSL certificates exist
if [ ! -f "../docker/nginx/ssl/cert.pem" ] || [ ! -f "../docker/nginx/ssl/key.pem" ]; then
    echo -e "${YELLOW}SSL certificates not found!${NC}"
    echo "Generating self-signed certificates for testing..."
    mkdir -p ../docker/nginx/ssl
    openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
      -keyout ../docker/nginx/ssl/key.pem \
      -out ../docker/nginx/ssl/cert.pem \
      -subj "/C=US/ST=State/L=City/O=Organization/CN=localhost"
    echo -e "${GREEN}Self-signed certificates generated!${NC}"
    echo -e "${YELLOW}For production, replace with real SSL certificates!${NC}"
fi

# Check if JWT keys exist
if [ ! -f "../app/config/jwt/private.pem" ] || [ ! -f "../app/config/jwt/public.pem" ]; then
    echo -e "${YELLOW}JWT keys not found. Generating...${NC}"
    mkdir -p ../app/config/jwt
    # Note: This requires PHP to be installed on host or will be done in container
    echo "JWT keys will be generated in the PHP container after start."
fi

# Build and start containers
echo -e "${GREEN}Building Docker containers...${NC}"
docker compose -f ../docker/docker-compose.prod.yml build

echo -e "${GREEN}Starting services...${NC}"
docker compose -f ../docker/docker-compose.prod.yml up -d

# Wait for database to be ready
echo -e "${YELLOW}Waiting for database to be ready...${NC}"
sleep 10

# Generate JWT keys if needed
if [ ! -f "../app/config/jwt/private.pem" ]; then
    echo -e "${GREEN}Generating JWT keys...${NC}"
    docker compose -f ../docker/docker-compose.prod.yml exec -T php php bin/console lexik:jwt:generate-keypair --skip-if-exists
fi

# Run database migrations/updates
echo -e "${GREEN}Updating database schema...${NC}"
docker compose -f ../docker/docker-compose.prod.yml exec -T php php bin/console doctrine:migrations:migrate --no-interaction || \
docker compose -f ../docker/docker-compose.prod.yml exec -T php php bin/console doctrine:schema:update --force

# Clear cache
echo -e "${GREEN}Clearing cache...${NC}"
docker compose -f ../docker/docker-compose.prod.yml exec -T php php bin/console cache:clear

# Show status
echo ""
echo -e "${GREEN}==================================="
echo "Deployment completed successfully!"
echo "===================================${NC}"
echo ""
echo "Services status:"
docker compose -f ../docker/docker-compose.prod.yml ps
echo ""
echo -e "${GREEN}Application is running at:${NC}"
echo "  HTTP:  http://localhost"
echo "  HTTPS: https://localhost"
echo ""
echo -e "${YELLOW}Useful commands:${NC}"
echo "  View logs:     docker compose -f ../docker/docker-compose.prod.yml logs -f"
echo "  Stop services: docker compose -f ../docker/docker-compose.prod.yml stop"
echo "  Restart:       docker compose -f ../docker/docker-compose.prod.yml restart"
echo ""


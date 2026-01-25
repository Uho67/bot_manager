#!/bin/bash

# Local Testing Script for Bot API
# This script sets up everything for local testing on macOS

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
CYAN='\033[0;36m'
NC='\033[0m' # No Color

echo -e "${BLUE}"
echo "╔════════════════════════════════════════╗"
echo "║   Bot API - Local Testing Setup       ║"
echo "║   macOS Development Environment        ║"
echo "╚════════════════════════════════════════╝"
echo -e "${NC}\n"

# Check prerequisites
echo -e "${YELLOW}Checking prerequisites...${NC}"

# Check Docker
if ! command -v docker &> /dev/null; then
    echo -e "${RED}✗ Docker is not installed${NC}"
    echo "Please install Docker Desktop from: https://www.docker.com/products/docker-desktop"
    exit 1
fi
echo -e "${GREEN}✓ Docker is installed${NC}"

# Check if Docker is running
if ! docker info &> /dev/null; then
    echo -e "${RED}✗ Docker is not running${NC}"
    echo "Please start Docker Desktop"
    exit 1
fi
echo -e "${GREEN}✓ Docker is running${NC}"

# Check Node.js
if ! command -v node &> /dev/null; then
    echo -e "${RED}✗ Node.js is not installed${NC}"
    echo "Please install Node.js from: https://nodejs.org/"
    exit 1
fi
echo -e "${GREEN}✓ Node.js is installed ($(node --version))${NC}"

# Check npm
if ! command -v npm &> /dev/null; then
    echo -e "${RED}✗ npm is not installed${NC}"
    exit 1
fi
echo -e "${GREEN}✓ npm is installed ($(npm --version))${NC}"

echo ""

# Ask user which mode to test
echo -e "${CYAN}Select testing mode:${NC}"
echo "1) Development mode (HTTP, port 8080) - Recommended"
echo "2) Production mode (HTTPS, port 443) - Full test with SSL"
echo ""
read -p "Enter choice [1-2]: " mode_choice

if [ "$mode_choice" = "2" ]; then
    TEST_MODE="production"
    COMPOSE_FILE="../docker/docker-compose.prod.yml"
    echo -e "${YELLOW}Using Production mode${NC}\n"
else
    TEST_MODE="development"
    COMPOSE_FILE="../docker/docker-compose.dev.yml"
    echo -e "${YELLOW}Using Development mode${NC}\n"
fi

# Stop any running containers
echo -e "${YELLOW}Stopping any existing containers...${NC}"
docker compose -f ../docker/docker-compose.dev.yml down 2>/dev/null || true
docker compose -f ../docker/docker-compose.prod.yml down 2>/dev/null || true
echo -e "${GREEN}✓ Cleanup complete${NC}\n"

# Setup environment files
echo -e "${YELLOW}Setting up environment files...${NC}"

if [ "$TEST_MODE" = "development" ]; then
    # Development mode
    if [ ! -f ../.env.local ]; then
        cp ../.env.dev.example ../.env.local
        echo -e "${GREEN}✓ Created .env.local${NC}"
    else
        echo -e "${CYAN}  .env.local already exists, keeping it${NC}"
    fi

    if [ ! -f ../app/.env.local ]; then
        cat > ../app/.env.local << 'EOF'
APP_ENV=dev
APP_SECRET=dev_secret_for_local_testing_only
DATABASE_URL=mysql://app:app@database:3306/app?serverVersion=11.3&charset=utf8mb4
REDIS_URL=redis://:redis@redis:6379
CORS_ALLOW_ORIGIN=^https?://(localhost|127\.0\.0\.1)(:[0-9]+)?$
JWT_SECRET_KEY=%kernel.project_dir%/config/jwt/private.pem
JWT_PUBLIC_KEY=%kernel.project_dir%/config/jwt/public.pem
JWT_PASSPHRASE=dev_jwt_passphrase
TELEGRAM_BOT_TOKEN=test_bot_token
EOF
        echo -e "${GREEN}✓ Created app/.env.local${NC}"
    else
        echo -e "${CYAN}  app/.env.local already exists, keeping it${NC}"
    fi

    cat > ../frontend/.env.local << 'EOF'
VITE_API_URL=http://localhost:8080
VITE_DEBUG=true
EOF
    echo -e "${GREEN}✓ Created frontend/.env.local${NC}"

else
    # Production mode
    if [ ! -f ../.env ]; then
        cp ../.env.example ../.env
        echo -e "${GREEN}✓ Created .env${NC}"
    else
        echo -e "${CYAN}  .env already exists, keeping it${NC}"
    fi

    if [ ! -f ../app/.env.local ]; then
        cp ../app/.env.example ../app/.env.local
        # Update for local testing
        sed -i '' 's/yourdomain\.com/localhost/g' ../app/.env.local 2>/dev/null || true
        echo -e "${GREEN}✓ Created app/.env.local${NC}"
    else
        echo -e "${CYAN}  app/.env.local already exists, keeping it${NC}"
    fi

    cat > ../frontend/.env.production.local << 'EOF'
VITE_API_URL=https://localhost
VITE_DEBUG=false
EOF
    echo -e "${GREEN}✓ Created frontend/.env.production.local${NC}"
fi

echo ""

# Install and build frontend
echo -e "${YELLOW}Installing frontend dependencies...${NC}"
cd ../frontend
npm install
echo -e "${GREEN}✓ Frontend dependencies installed${NC}"

echo -e "${YELLOW}Building frontend...${NC}"
npm run build
echo -e "${GREEN}✓ Frontend built successfully${NC}"
cd ../scripts

echo ""

# Generate SSL certificates if production mode
if [ "$TEST_MODE" = "production" ]; then
    if [ ! -f "../docker/nginx/ssl/cert.pem" ] || [ ! -f "../docker/nginx/ssl/key.pem" ]; then
        echo -e "${YELLOW}Generating self-signed SSL certificates...${NC}"
        mkdir -p ../docker/nginx/ssl
        openssl req -x509 -nodes -days 365 -newkey rsa:2048 \
          -keyout ../docker/nginx/ssl/key.pem \
          -out ../docker/nginx/ssl/cert.pem \
          -subj "/C=US/ST=State/L=City/O=Local-Test/CN=localhost" \
          2>/dev/null
        echo -e "${GREEN}✓ SSL certificates generated${NC}"
    else
        echo -e "${CYAN}SSL certificates already exist${NC}"
    fi
    echo ""
fi

# Build Docker images
echo -e "${YELLOW}Building Docker images...${NC}"
docker compose -f $COMPOSE_FILE build
echo -e "${GREEN}✓ Docker images built${NC}\n"

# Start containers
echo -e "${YELLOW}Starting Docker containers...${NC}"
docker compose -f $COMPOSE_FILE up -d
echo -e "${GREEN}✓ Containers started${NC}\n"

# Wait for services to be ready
echo -e "${YELLOW}Waiting for services to be ready...${NC}"
echo -e "${CYAN}This may take 30-60 seconds...${NC}"
sleep 10

# Wait for database
MAX_TRIES=30
COUNTER=0
echo -n "Checking database"
while [ $COUNTER -lt $MAX_TRIES ]; do
    if docker compose -f $COMPOSE_FILE exec -T database mysql -u app -papp -e "SELECT 1;" &> /dev/null; then
        echo ""
        echo -e "${GREEN}✓ Database is ready${NC}"
        break
    fi
    COUNTER=$((COUNTER+1))
    sleep 2
    echo -n "."
done

if [ $COUNTER -eq $MAX_TRIES ]; then
    echo ""
    echo -e "${RED}Database took too long to start, but may still be initializing${NC}"
    echo -e "${YELLOW}Continuing anyway...${NC}"
fi

echo ""

# Generate JWT keys if needed
if [ ! -f "../app/config/jwt/private.pem" ]; then
    echo -e "${YELLOW}Generating JWT keys...${NC}"
    docker compose -f $COMPOSE_FILE exec -T php php bin/console lexik:jwt:generate-keypair --skip-if-exists || true
    echo -e "${GREEN}✓ JWT keys generated${NC}"
fi

# Run database migrations/updates
echo -e "${YELLOW}Setting up database...${NC}"
docker compose -f $COMPOSE_FILE exec -T php php bin/console doctrine:migrations:migrate --no-interaction 2>/dev/null || \
docker compose -f $COMPOSE_FILE exec -T php php bin/console doctrine:schema:update --force
echo -e "${GREEN}✓ Database schema updated${NC}\n"

# Clear cache
echo -e "${YELLOW}Clearing Symfony cache...${NC}"
docker compose -f $COMPOSE_FILE exec -T php php bin/console cache:clear
echo -e "${GREEN}✓ Cache cleared${NC}\n"

# Show container status
echo -e "${YELLOW}Container status:${NC}"
docker compose -f $COMPOSE_FILE ps
echo ""

# Run quick health check
echo -e "${YELLOW}Running health checks...${NC}"

# Check database
if docker compose -f $COMPOSE_FILE exec -T database mysql -u root -proot -e "SELECT 1;" &> /dev/null; then
    echo -e "${GREEN}✓ Database connection: OK${NC}"
else
    echo -e "${RED}✗ Database connection: FAILED${NC}"
fi

# Check Redis
if docker compose -f $COMPOSE_FILE exec -T redis redis-cli -a redis ping 2>/dev/null | grep -q "PONG"; then
    echo -e "${GREEN}✓ Redis connection: OK${NC}"
else
    echo -e "${RED}✗ Redis connection: FAILED${NC}"
fi

# Check PHP
if docker compose -f $COMPOSE_FILE exec -T php php -v &> /dev/null; then
    echo -e "${GREEN}✓ PHP-FPM: OK${NC}"
else
    echo -e "${RED}✗ PHP-FPM: FAILED${NC}"
fi

# Check Nginx
if docker compose -f $COMPOSE_FILE exec -T nginx nginx -t &> /dev/null; then
    echo -e "${GREEN}✓ Nginx: OK${NC}"
else
    echo -e "${RED}✗ Nginx: FAILED${NC}"
fi

echo ""

# Display access information
echo -e "${GREEN}"
echo "╔════════════════════════════════════════╗"
echo "║   🎉 Local Testing Setup Complete!    ║"
echo "╚════════════════════════════════════════╝"
echo -e "${NC}\n"

if [ "$TEST_MODE" = "development" ]; then
    echo -e "${CYAN}Access your application:${NC}"
    echo -e "${GREEN}Backend API:${NC}     http://localhost:8080"
    echo -e "${GREEN}API Endpoints:${NC}   http://localhost:8080/api"
    echo ""
    echo -e "${CYAN}Frontend Development Server:${NC}"
    echo "Run in a new terminal:"
    echo -e "${YELLOW}  cd frontend && npm run dev${NC}"
    echo "Then access: http://localhost:5173"
else
    echo -e "${CYAN}Access your application:${NC}"
    echo -e "${GREEN}Frontend:${NC}        https://localhost"
    echo -e "${GREEN}Backend API:${NC}     https://localhost/api"
    echo ""
    echo -e "${YELLOW}⚠️  You'll see a security warning (self-signed certificate)${NC}"
    echo -e "${YELLOW}   Click 'Advanced' and 'Proceed to localhost'${NC}"
fi

echo ""
echo -e "${CYAN}Database Access:${NC}"
echo -e "  Host:     ${GREEN}localhost${NC}"
echo -e "  Port:     ${GREEN}3306${NC} (dev) or internal (prod)"
echo -e "  User:     ${GREEN}app${NC}"
echo -e "  Password: ${GREEN}app${NC}"
echo -e "  Database: ${GREEN}app${NC}"

echo ""
echo -e "${CYAN}Redis Access:${NC}"
echo -e "  Host:     ${GREEN}localhost${NC}"
echo -e "  Port:     ${GREEN}6379${NC} (dev) or internal (prod)"
echo -e "  Password: ${GREEN}redis${NC}"

echo ""
echo -e "${CYAN}Useful Commands:${NC}"
echo -e "  View logs:          ${YELLOW}docker compose -f $COMPOSE_FILE logs -f${NC}"
echo -e "  Stop services:      ${YELLOW}docker compose -f $COMPOSE_FILE stop${NC}"
echo -e "  Restart services:   ${YELLOW}docker compose -f $COMPOSE_FILE restart${NC}"
echo -e "  Health check:       ${YELLOW}./health-check.sh${NC}"
echo -e "  Clear everything:   ${YELLOW}docker compose -f $COMPOSE_FILE down -v${NC}"

echo ""
echo -e "${CYAN}Documentation:${NC}"
echo -e "  Full guide:         ${YELLOW}cat LOCAL_TESTING.md${NC}"
echo -e "  Deployment guide:   ${YELLOW}cat DEPLOYMENT_DOCKER.md${NC}"
echo -e "  Quick reference:    ${YELLOW}cat README_DOCKER.md${NC}"

echo ""
echo -e "${GREEN}Happy testing! 🚀${NC}\n"


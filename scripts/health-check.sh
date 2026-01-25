#!/bin/bash

# Health Check Script for Bot API
# This script checks the health of all services

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

COMPOSE_FILE="../docker/docker-compose.prod.yml"

echo -e "${BLUE}==================================="
echo "Bot API - Health Check"
echo "===================================${NC}\n"

# Check if containers are running
echo -e "${YELLOW}Checking container status...${NC}"
docker compose -f $COMPOSE_FILE ps

echo ""

# Function to check service health
check_service() {
    local service=$1
    local name=$2

    if docker compose -f $COMPOSE_FILE ps | grep -q "$service.*running"; then
        echo -e "${GREEN}✓${NC} $name is running"
        return 0
    else
        echo -e "${RED}✗${NC} $name is not running"
        return 1
    fi
}

# Check all services
echo -e "${YELLOW}Checking services...${NC}"
check_service "database" "MySQL"
check_service "redis" "Redis"
check_service "php" "PHP-FPM"
check_service "nginx" "Nginx"

echo ""

# Check database connection
echo -e "${YELLOW}Testing database connection...${NC}"
if docker compose -f $COMPOSE_FILE exec -T database mysql -u root -p${MYSQL_ROOT_PASSWORD:-root} -e "SELECT 1;" > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Database connection successful"
else
    echo -e "${RED}✗${NC} Database connection failed"
fi

# Check Redis connection
echo -e "${YELLOW}Testing Redis connection...${NC}"
if docker compose -f $COMPOSE_FILE exec -T redis redis-cli -a ${REDIS_PASSWORD:-redis_password} ping 2>/dev/null | grep -q "PONG"; then
    echo -e "${GREEN}✓${NC} Redis connection successful"
else
    echo -e "${RED}✗${NC} Redis connection failed"
fi

# Check PHP-FPM
echo -e "${YELLOW}Testing PHP-FPM...${NC}"
if docker compose -f $COMPOSE_FILE exec -T php php -v > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} PHP-FPM is responding"
else
    echo -e "${RED}✗${NC} PHP-FPM is not responding"
fi

# Check Nginx configuration
echo -e "${YELLOW}Testing Nginx configuration...${NC}"
if docker compose -f $COMPOSE_FILE exec -T nginx nginx -t > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Nginx configuration is valid"
else
    echo -e "${RED}✗${NC} Nginx configuration has errors"
fi

# Check HTTP endpoint
echo -e "${YELLOW}Testing HTTP endpoint...${NC}"
HTTP_CODE=$(curl -s -o /dev/null -w "%{http_code}" http://localhost 2>/dev/null || echo "000")
if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "301" ] || [ "$HTTP_CODE" = "302" ]; then
    echo -e "${GREEN}✓${NC} HTTP endpoint is accessible (HTTP $HTTP_CODE)"
else
    echo -e "${RED}✗${NC} HTTP endpoint returned HTTP $HTTP_CODE"
fi

# Check disk usage
echo ""
echo -e "${YELLOW}Disk usage:${NC}"
docker system df

# Check logs for errors (last 50 lines)
echo ""
echo -e "${YELLOW}Recent errors in logs (if any):${NC}"
docker compose -f $COMPOSE_FILE logs --tail=50 | grep -i error || echo -e "${GREEN}No recent errors found${NC}"

echo ""
echo -e "${BLUE}==================================="
echo "Health check completed"
echo "===================================${NC}"


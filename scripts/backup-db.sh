#!/bin/bash

# Database Backup Script for Bot API
# Run this script to create database backups

set -e

# Configuration
BACKUP_DIR="../backups"
DATE=$(date +%Y%m%d_%H%M%S)
RETENTION_DAYS=7

# Colors
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Create backup directory
mkdir -p $BACKUP_DIR

echo -e "${GREEN}Starting database backup...${NC}"

# Load environment variables
if [ -f ../.env ]; then
    export $(cat ../.env | grep -v '^#' | xargs)
fi

# Backup database
docker compose -f ../docker/docker-compose.prod.yml exec -T database \
    mysqldump -u root -p${MYSQL_ROOT_PASSWORD} ${MYSQL_DATABASE} \
    | gzip > $BACKUP_DIR/backup_$DATE.sql.gz

echo -e "${GREEN}Backup created: backup_$DATE.sql.gz${NC}"

# Remove old backups
echo -e "${YELLOW}Cleaning up old backups (older than $RETENTION_DAYS days)...${NC}"
find $BACKUP_DIR -name "backup_*.sql.gz" -mtime +$RETENTION_DAYS -delete

# List current backups
echo ""
echo "Current backups:"
ls -lh $BACKUP_DIR/backup_*.sql.gz 2>/dev/null || echo "No backups found"
echo ""
echo -e "${GREEN}Backup completed successfully!${NC}"


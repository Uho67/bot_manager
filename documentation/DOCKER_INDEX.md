# Bot API - Docker Deployment Package

Complete Docker Compose deployment setup for Bot API project (Symfony + Vue.js + Redis + MariaDB).

## 📦 What's Included

This deployment package includes everything needed to deploy your application in production or development:

- ✅ **Docker Compose configurations** (production & development)
- ✅ **Dockerfiles** for all services (PHP, Nginx, NestJS ready)
- ✅ **Environment templates** for all components
- ✅ **Automation scripts** (deploy, update, backup)
- ✅ **Comprehensive documentation** (deployment guides & references)
- ✅ **Nginx configurations** with SSL/TLS support
- ✅ **Database initialization** scripts
- ✅ **Makefile** for easy command management

## 🧪 Test Locally First (Recommended)

```bash
# One command to test everything locally on your Mac
./test-local.sh
```

This will set up and test the entire stack on your local machine before deploying to production.

See **[LOCAL_TESTING.md](LOCAL_TESTING.md)** for details.

---

## 🚀 Production Deployment (3 Steps)

```bash
# 1. Configure environment
cp .env.example .env
cp app/.env.example app/.env.local
cp frontend/.env.example frontend/.env.production
# Edit these files with your actual values

# 2. Deploy
./deploy-quick.sh

# 3. Access your app
# Frontend: https://yourdomain.com
# API: https://yourdomain.com/api
```

## 📚 Documentation

### Primary Documents
- **[README_DOCKER.md](README_DOCKER.md)** - Quick reference guide (START HERE)
- **[DEPLOYMENT_DOCKER.md](DEPLOYMENT_DOCKER.md)** - Complete deployment guide
- **[FILES_SUMMARY.md](FILES_SUMMARY.md)** - All files overview

### Configuration Files
- `.env.example` - Root environment variables
- `app/.env.example` - Symfony backend configuration
- `frontend/.env.example` - Vue.js frontend configuration
- `.env.dev.example` - Development environment

## 🛠️ Available Commands

### Using Makefile (Recommended)
```bash
make help              # Show all commands
make deploy-prod       # Deploy to production
make start             # Start all services
make stop              # Stop all services
make restart           # Restart services
make logs              # View logs
make db-backup         # Backup database
make cache-clear       # Clear Symfony cache
make update            # Update application
```

### Using Scripts
```bash
./deploy-quick.sh      # Quick deployment
./update-app.sh        # Update application
./backup-db.sh         # Backup database
./health-check.sh      # Check system health
```

## 🏗️ Architecture

```
┌─────────────────────────────────────────────────┐
│                  Nginx (Port 443)               │
│          Reverse Proxy + SSL/TLS                │
└─────────┬───────────────────────┬───────────────┘
          │                       │
    Frontend (Vue)           API (Symfony)
          │                       │
          │              ┌────────┴────────┐
          │              │                 │
          │         PHP-FPM           ┌────┴────┐
          │              │            │         │
          │              │        MariaDB   Redis
          └──────────────┴────────────┴─────────┘
```

## 📋 Services Included

| Service | Technology | Port | Description |
|---------|-----------|------|-------------|
| Nginx | 1.25 Alpine | 80, 443 | Web server & reverse proxy |
| PHP-FPM | PHP 8.4 | 9000 | Symfony backend |
| MariaDB | 11.3 | 3306 | Database |
| Redis | 7 Alpine | 6379 | Cache & sessions |
| NestJS* | Node 18 | 3000 | *Future microservice |

## 🔒 Security Features

- ✅ SSL/TLS encryption (Let's Encrypt ready)
- ✅ Environment-based secrets management
- ✅ Security headers in Nginx
- ✅ CORS configuration
- ✅ JWT authentication ready
- ✅ Non-root containers
- ✅ Network isolation
- ✅ Health checks

## 📁 Project Structure

```
bot_api/
├── 📄 docker-compose.prod.yml    # Production setup
├── 📄 docker-compose.dev.yml     # Development setup
├── 📄 Makefile                   # Command shortcuts
├── 🔧 .env.example               # Environment template
│
├── 📜 Scripts/
│   ├── deploy-quick.sh           # Quick deployment
│   ├── update-app.sh             # Update app
│   ├── backup-db.sh              # Backup database
│   └── health-check.sh           # Health check
│
├── 📚 Documentation/
│   ├── README_DOCKER.md          # Quick reference
│   ├── DEPLOYMENT_DOCKER.md      # Full guide
│   └── FILES_SUMMARY.md          # Files overview
│
├── 🐳 docker/                    # Docker configs
│   ├── nginx/                    # Nginx container
│   ├── php/                      # PHP container
│   ├── mysql/                  # DB init scripts
│   └── nestjs/                   # NestJS (future)
│
├── 💻 app/                       # Symfony backend
│   └── .env.example              # Backend env
│
└── 🎨 frontend/                  # Vue.js frontend
    └── .env.example              # Frontend env
```

## 🎯 Common Tasks

### First Deployment
```bash
./deploy-quick.sh
```

### Update Application
```bash
./update-app.sh
```

### Backup Database
```bash
./backup-db.sh
# Or scheduled
make db-backup
```

### View Logs
```bash
make logs
# Or specific service
docker compose -f docker-compose.prod.yml logs -f php
```

### Restart Service
```bash
make restart
# Or specific service
docker compose -f docker-compose.prod.yml restart php
```

### Health Check
```bash
./health-check.sh
```

## 🔧 Environment Variables

### Must Change for Production

**Root .env:**
- `MYSQL_PASSWORD` - Strong password
- `MYSQL_ROOT_PASSWORD` - Strong password
- `REDIS_PASSWORD` - Strong password
- `DOMAIN` - Your actual domain

**app/.env.local:**
- `APP_SECRET` - Random 32+ characters
- `JWT_PASSPHRASE` - Strong passphrase
- `TELEGRAM_BOT_TOKEN` - Your bot token

**frontend/.env.production:**
- `VITE_API_URL` - Your production URL

## 🆘 Troubleshooting

### Port Already in Use
```bash
sudo lsof -i :80
sudo lsof -i :443
```

### 502 Bad Gateway
```bash
make restart
# Or
docker compose -f docker-compose.prod.yml restart php
```

### Permission Issues
```bash
docker compose -f docker-compose.prod.yml exec php \
  chown -R www-data:www-data /var/www/html/var
```

### Check Health
```bash
./health-check.sh
```

## 📊 Monitoring

```bash
# Container stats
make stats

# Disk usage
make disk-usage

# Service status
make ps
```

## 🔄 Update Workflow

1. Backup database: `./backup-db.sh`
2. Pull changes: `git pull`
3. Update: `./update-app.sh`
4. Verify: `./health-check.sh`

## 🌐 Production Checklist

Before going live:

- [ ] Changed all default passwords
- [ ] Generated strong APP_SECRET
- [ ] Configured real SSL certificates
- [ ] Set up firewall (UFW)
- [ ] Configured CORS for your domain
- [ ] Set up automated backups
- [ ] Tested all endpoints
- [ ] Configured monitoring/alerts
- [ ] Updated documentation with project specifics

## 🔗 Additional Resources

- [Symfony Documentation](https://symfony.com/doc)
- [Vue.js Documentation](https://vuejs.org)
- [Docker Documentation](https://docs.docker.com)
- [Nginx Documentation](https://nginx.org/en/docs)
- [Let's Encrypt](https://letsencrypt.org)

## 💡 Tips

1. **Use Makefile**: It's easier than remembering Docker Compose commands
2. **Regular Backups**: Set up cron job for daily backups
3. **Monitor Logs**: Check logs regularly for issues
4. **Health Checks**: Run health-check.sh before and after updates
5. **Test First**: Use dev environment before deploying to production

## 📞 Support

For detailed instructions, see:
- Quick Start: [README_DOCKER.md](README_DOCKER.md)
- Full Guide: [DEPLOYMENT_DOCKER.md](DEPLOYMENT_DOCKER.md)
- Files Overview: [FILES_SUMMARY.md](FILES_SUMMARY.md)

## 📝 Version

- **Created**: January 23, 2026
- **Docker**: 20.10+
- **Docker Compose**: 2.0+
- **PHP**: 8.4
- **Symfony**: 8.0
- **Vue.js**: 3.4
- **MariaDB**: 11.3
- **Redis**: 7
- **Nginx**: 1.25

---

**Ready to deploy?** Run `./deploy-quick.sh` or `make deploy-prod` 🚀


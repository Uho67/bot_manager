# 🚀 Quick Start Guide

## ⚡ Get Started in Minutes

### Local Development Setup:
```bash
./test-local.sh
```
Choose option 1 (Development mode) for local testing.

---

## 📋 Choose Your Path

### 1️⃣ Local Development (Recommended First)
```bash
./test-local.sh
```
- Sets up complete local environment
- Backend + Database + Redis
- Takes ~5 minutes
- No server needed

**See:** [LOCAL_TESTING.md](LOCAL_TESTING.md)

---

### 2️⃣ Production Deployment
```bash
./deploy-quick.sh  # Run on your server
```
- Full production deployment
- SSL/TLS support
- Automated setup

**See:** [DEPLOYMENT_DOCKER.md](DEPLOYMENT_DOCKER.md)

---

### 3️⃣ Command Reference
```bash
make help  # See all available commands
```

**See:** [README_DOCKER.md](README_DOCKER.md)

---

## 📚 Documentation Structure

```
START HERE   → START_HERE.md (Quick start guide)
             ↓
LOCAL SETUP  → LOCAL_TESTING.md (Local development)
             ↓
PRODUCTION   → DEPLOYMENT_DOCKER.md (Server deployment)
             ↓
REFERENCE    → README_DOCKER.md (Commands & tips)
             ↓
DETAILS      → FILES_SUMMARY.md (File documentation)
```

---

## 🎯 Typical Workflow

### For New Users:
1. **Read**: `START_HERE.md` (you are here!)
2. **Setup**: Run `./test-local.sh` locally
3. **Learn**: Read `LOCAL_TESTING.md` for details
4. **Deploy**: Follow `DEPLOYMENT_DOCKER.md` for production
5. **Use**: Keep `README_DOCKER.md` handy for commands

### For Experienced Users:
1. **Local**: `./test-local.sh`
2. **Deploy**: `./deploy-quick.sh` (on server)
3. **Done!** ✅

---

## 💡 Most Common Commands

```bash
# Local Testing
./test-local.sh              # Full local setup
make test-local              # Same as above
make test-dev                # Quick dev start

# Production Deployment
./deploy-quick.sh            # Full deployment
make deploy-prod             # Same as above

# Day-to-Day
make start                   # Start services
make stop                    # Stop services
make logs                    # View logs
make restart                 # Restart services

# Maintenance
./update-app.sh              # Update application
./backup-db.sh               # Backup database
./health-check.sh            # Check system health

# Help
make help                    # See all commands
```

---

## 🔥 Common Questions

### Q: Can I test locally?
**A:** YES! Run `./test-local.sh`

### Q: What do I need installed?
**A:** Docker Desktop and Node.js

### Q: Which mode for local development?
**A:** Development mode (option 1) - easier, no SSL needed

### Q: How do I access my app locally?
**A:**
- Dev mode: http://localhost:8080 (backend) + http://localhost:5173 (frontend)
- Prod mode: https://localhost (accept self-signed SSL warning)

### Q: Where do I configure settings?
**A:** Edit `.env` files (use `.env.example` as template)

### Q: How do I deploy to production?
**A:** Run `./deploy-quick.sh` on your server

### Q: Need to start fresh?
**A:**
```bash
docker compose -f docker-compose.dev.yml down -v
./test-local.sh
```


---

## 📂 Important Files

| File | Purpose |
|------|---------|
| `test-local.sh` | Test everything locally |
| `deploy-quick.sh` | Deploy to production |
| `update-app.sh` | Update application |
| `backup-db.sh` | Backup database |
| `health-check.sh` | Check system health |
| `Makefile` | Command shortcuts |
| `LOCAL_TESTING.md` | Local testing guide |
| `DEPLOYMENT_DOCKER.md` | Production deployment guide |
| `README_DOCKER.md` | Quick reference |

---

## ⚡ TL;DR

**Want to test locally?**
```bash
./test-local.sh
```

**Want to deploy?**
```bash
./deploy-quick.sh
```

**Want to see all commands?**
```bash
make help
```

**That's it!** 🎉

---

## 🆘 Need Help?

1. **Local Testing Issues**: See [LOCAL_TESTING.md](LOCAL_TESTING.md)
2. **Deployment Issues**: See [DEPLOYMENT_DOCKER.md](DEPLOYMENT_DOCKER.md)
3. **Command Reference**: See [README_DOCKER.md](README_DOCKER.md)
4. **File Details**: See [FILES_SUMMARY.md](FILES_SUMMARY.md)
5. **Overview**: See [DOCKER_INDEX.md](DOCKER_INDEX.md)

---

**Ready?** Run: `./test-local.sh` 🚀


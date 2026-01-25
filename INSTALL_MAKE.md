# Quick Fix: Install Make on Your Server

## The Issue
```bash
root@vps321ql:/var/www/bot_api# make deploy-prod
Command 'make' not found, but can be installed with:
apt install make
```

## Quick Solution

**On your production server (root@vps321ql), run:**

```bash
# Install make
sudo apt install -y make

# Verify installation
make --version

# Now you can use make commands
make deploy-prod
```

## What is Make?

`make` is a build automation tool that reads the `Makefile` and executes commands. It's installed by default on most development systems but may be missing on minimal server installations.

## Alternative: Use Scripts Directly

If you prefer not to install make, you can run the deployment script directly:

```bash
# Instead of: make deploy-prod
# Use:
cd /var/www/bot_api/scripts
./deploy-quick.sh
```

## All Make Commands Have Script/Docker Alternatives

| Make Command | Alternative Command |
|--------------|---------------------|
| `make deploy-prod` | `cd scripts && ./deploy-quick.sh` |
| `make build` | `docker compose -f docker/docker-compose.prod.yml build` |
| `make start` | `docker compose -f docker/docker-compose.prod.yml up -d` |
| `make stop` | `docker compose -f docker/docker-compose.prod.yml stop` |
| `make logs` | `docker compose -f docker/docker-compose.prod.yml logs -f` |
| `make ps` | `docker compose -f docker/docker-compose.prod.yml ps` |
| `make db-migrate` | `docker compose -f docker/docker-compose.prod.yml exec php php bin/console doctrine:migrations:migrate --no-interaction` |
| `make ssh-php` | `docker compose -f docker/docker-compose.prod.yml exec php sh` |

## Recommended: Install Make

It's **highly recommended** to install make because:
- ✅ Much shorter commands
- ✅ Easier to remember
- ✅ Less typing
- ✅ Only 2MB installed size
- ✅ Standard tool on all Linux systems

**Install command:**
```bash
sudo apt install -y make
```

## Your Next Steps

**On your server (root@vps321ql):**

```bash
# 1. Install make
sudo apt install -y make

# 2. Verify
make --version

# 3. Deploy
make deploy-prod
```

**That's it!** ✅

---

**Alternative if you don't want to install make:**
```bash
cd /var/www/bot_api/scripts
./deploy-quick.sh
```

Both work the same way!


# Admin Guide

Quick reference for admin tasks.

**Important:** All PHP commands must run inside the container!

---

## phpMyAdmin Access

phpMyAdmin is bound to localhost only (security). Access via SSH tunnel.

### Step 1: Create SSH Tunnel

**On your local machine:**
```bash
ssh -L 8080:localhost:8080 root@your-server-ip
```
Keep this terminal open.

### Step 2: Open Browser

Go to: `http://localhost:8080`

### Step 3: Login

- **Username:** `root`
- **Password:** Your `MYSQL_ROOT_PASSWORD` from `.env.prod`

---

## Create Admin User (CLI)

### Option 1: Enter Container First (Recommended)

```bash
# On your server
cd /var/www/bot_api

# Enter the PHP container
make ssh-php

# Now you're inside the container - run commands here
php bin/console app:create-admin-user uho67 zDdr4nji --role=SUPER_ADMIN

# Exit container when done
exit
```

### Option 2: Run Directly from Host (One-Liner)

```bash
# On your server (without entering container)
cd /var/www/bot_api
docker compose --env-file .env -f docker/docker-compose.prod.yml exec php php bin/console app:create-admin-user john secret123 --role=SUPER_ADMIN
```

### Command Syntax

```bash
php bin/console app:create-admin-user <username> <password> [bot_code] [--role=ADMIN|SUPER_ADMIN]
```

**Arguments:**
- `username` - Admin username (required)
- `password` - Admin password (required)
- `bot_code` - Bot code (optional)

**Options:**
- `--role=ADMIN` - Regular admin (default)
- `--role=SUPER_ADMIN` - Super admin with full access

**Examples:**

```bash
# Create regular admin
php bin/console app:create-admin-user john secret123

# Create admin with bot code
php bin/console app:create-admin-user john secret123 BOT001

# Create super admin
php bin/console app:create-admin-user john secret123 --role=SUPER_ADMIN

# Create super admin with bot code
php bin/console app:create-admin-user john secret123 BOT001 --role=SUPER_ADMIN
```

---

## Other Useful Commands

Commands that work **from the host** (no need to enter container):

```bash
# Clear cache
make cache-clear

# Update database schema
make db-update

# View logs
make logs

# Container status
make ps

# Restart containers
make restart
```

Commands that need to run **inside the container**:

```bash
# First enter the container
make ssh-php

# Then run any Symfony command
php bin/console list                    # List all commands
php bin/console doctrine:query:sql "SELECT * FROM admin_user"
php bin/console cache:clear

# Exit when done
exit
```

---

## Quick Reference

| Task | Where | Command |
|------|-------|---------|
| Enter PHP container | Host | `make ssh-php` |
| Create admin | Inside container | `php bin/console app:create-admin-user <user> <pass>` |
| Create super admin | Inside container | `php bin/console app:create-admin-user <user> <pass> --role=SUPER_ADMIN` |
| Clear cache | Host | `make cache-clear` |
| Update DB | Host | `make db-update` |
| View logs | Host | `make logs` |
| phpMyAdmin | Local machine | SSH tunnel + `http://localhost:8080` |

**Host** = Your server terminal
**Inside container** = After running `make ssh-php`

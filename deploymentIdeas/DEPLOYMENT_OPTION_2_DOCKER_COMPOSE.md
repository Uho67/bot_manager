# Deployment Option 2: Docker Compose (Recommended)

## Overview
Run everything in containers on a single server using Docker Compose.

## How It Works
- Create Dockerfiles for PHP and Nginx
- Define all services (PHP, Nginx, MariaDB, Redis) in `docker-compose.yml`
- Build frontend, copy `dist/` folder into Nginx container
- Containers communicate via Docker network
- Deploy by pulling code and running `docker compose up`

## Architecture
```
Internet → Nginx Container → PHP-FPM Container → Symfony
                              ↓
                         MariaDB Container
                         Redis Container
```

## Pros
✅ **Consistent environments** - Same setup in dev and production  
✅ **Easy to manage** - Start/stop services with one command  
✅ **Isolated services** - Problems in one container don't affect others  
✅ **Easy scaling** - Scale individual services independently  
✅ **Easy rollbacks** - Switch to previous image version quickly  
✅ **Portable** - Works on any server with Docker  

## Cons
❌ **Docker knowledge required** - Need to understand containers  
❌ **More memory usage** - Each container has overhead  
❌ **Slightly complex networking** - Container networking can be tricky  
❌ **Debugging** - Need to know Docker commands for logs  

## Best For
- Most projects (recommended default)
- Teams working together
- Projects that need consistency
- When you want easy deployment
- Medium to large projects

## Setup Complexity
**Time:** 4-6 hours  
**Difficulty:** Medium  
**Knowledge Required:** Docker, Docker Compose basics  

## Cost Estimate
- Server: $10-30/month (VPS with more RAM for containers)
- Total: ~$10-30/month

## Deployment Process
1. Install Docker and Docker Compose on server
2. Create Dockerfiles for PHP and Nginx
3. Create `docker-compose.yml` with all services
4. Build frontend and include in Nginx container
5. Set environment variables in `.env` file
6. Run `docker compose up -d`
7. Set up auto-deploy via GitHub Actions

## Maintenance
- Update Docker images regularly
- Monitor container resources
- Database backups (volume backups)
- Update application code via git pull + docker compose restart

## Scaling
- Vertical: Upgrade server resources
- Horizontal: Add more PHP containers behind load balancer
- Easy to scale individual services (e.g., add more PHP workers)

## When to Choose This
Choose Docker Compose if:
- You want the best balance of simplicity and flexibility
- You're working in a team
- You want dev/prod consistency
- You plan to scale later
- You want modern deployment practices

## Migration Path
Easy to migrate to Kubernetes later if you need orchestration.

## Key Files Needed
- `Dockerfile` (PHP container)
- `docker-compose.yml` (all services)
- `nginx.conf` (Nginx configuration)
- `.env` (environment variables)

---

## My Recommendations & Thoughts

### Why This is My Top Recommendation
- **The "Goldilocks" option** - Not too simple, not too complex
- **Industry standard** - Most companies use Docker for deployment
- **Future-proof** - Easy path to Kubernetes if you grow
- **Developer happiness** - Same environment everywhere eliminates "works on my machine"

### Hidden Challenges to Consider
- **Docker learning curve** - First-time setup takes longer
- **Volume permissions** - File permissions inside containers can be tricky
- **Memory overhead** - Containers use more RAM than native processes
- **Debugging inside containers** - Requires learning Docker commands

### My Honest Opinion
This is **the sweet spot for 90% of projects**. The initial investment in learning Docker pays off quickly. Your future self will thank you for the consistency and ease of deployment.

### Tips for Success
- Keep images small - Use Alpine-based images where possible
- Don't store data in containers - Use volumes for databases and uploads
- Use multi-stage builds to reduce image size
- Create separate compose files for dev and production
- Learn to read Docker logs (`docker compose logs -f`)

### Common Mistakes to Avoid
- Running containers as root (use non-root user)
- Storing secrets in Dockerfile (use environment variables)
- Not setting resource limits (containers can eat all RAM)
- Forgetting to backup volumes (volumes are not backed up by default)

### For Your Project Specifically
Your Symfony + Vue.js + MariaDB + Redis stack is **perfect for Docker Compose**:
- PHP-FPM container for Symfony
- Nginx container for frontend + reverse proxy
- MariaDB container for database
- Redis container for caching
- All communicate via Docker network

---

## Deep Dive: How Docker Compose Works

### Can We Have One File With Everything?

**Yes!** That's exactly what `docker-compose.yml` does. One file defines:
- All your services (PHP, Nginx, MariaDB, Redis)
- The network they share
- Environment variables and credentials
- Volumes for persistent data
- Port mappings to the outside world

**How services connect inside Docker:**
- Docker Compose creates a private network automatically
- Containers talk to each other using service names (not IP addresses)
- Example: PHP connects to database using hostname `database`, not `localhost`
- Example: PHP connects to Redis using hostname `redis`, not `127.0.0.1`
- Credentials are passed via environment variables (never hardcoded)

### How Credentials Work

**The `.env` file approach:**
- Create a `.env` file with all secrets (DB password, Redis password, etc.)
- Docker Compose reads this file automatically
- Values are injected into containers as environment variables
- Symfony reads these via `$_ENV` or `%env()%` syntax
- Never commit `.env` to git - use `.env.example` as template

**What goes in `.env`:**
- DATABASE_URL (includes host, user, password, database name)
- REDIS_URL (includes host, port, password if any)
- APP_SECRET (Symfony secret)
- JWT keys path
- Any other secrets

### What to Configure on the Server

**Minimal server setup needed:**

1. **Install Docker and Docker Compose**
   - Just two packages to install
   - Works on Ubuntu, Debian, CentOS, any Linux

2. **Open firewall ports**
   - Port 80 (HTTP)
   - Port 443 (HTTPS)
   - That's it - database and Redis stay internal

3. **Clone your repository**
   - Pull your code to the server
   - Create `.env` file with production values

4. **Run `docker compose up -d`**
   - Docker downloads images
   - Creates containers
   - Starts everything
   - Done!

**What you DON'T need to install:**
- ❌ PHP
- ❌ Nginx
- ❌ MariaDB
- ❌ Redis
- ❌ Composer
- ❌ Node.js

Everything runs inside containers!

### How Requests Reach Your Application

**The request flow:**

```
User's Browser
      ↓
Internet (port 80/443)
      ↓
Server's Firewall (allows 80/443)
      ↓
Docker Port Mapping (80 → Nginx container)
      ↓
Nginx Container (receives request)
      ↓
├─ Static files (Vue.js) → Served directly
└─ /api requests → Forwarded to PHP container
      ↓
PHP-FPM Container (Symfony processes request)
      ↓
├─ Database query → MariaDB container
└─ Cache lookup → Redis container
      ↓
Response flows back the same path
```

**Port mapping explained:**
- Only Nginx exposes ports to the outside world (80, 443)
- PHP, MariaDB, Redis have NO external ports
- They only communicate on the internal Docker network
- This is more secure - database can't be accessed from internet

**How Nginx knows where to send requests:**
- Nginx config defines rules
- Requests to `/` serve Vue.js static files
- Requests to `/api` are proxied to PHP container
- PHP container is referenced by name (e.g., `php:9000`)

### The Container Structure for Your Project

**You need these containers:**

1. **nginx** - Web server and reverse proxy
   - Serves Vue.js frontend (static files)
   - Proxies `/api` requests to PHP
   - Handles SSL certificates
   - Exposes ports 80 and 443

2. **php** - PHP-FPM with Symfony
   - Runs your Symfony application
   - Connects to database and Redis
   - No external ports (internal only)

3. **database** - MariaDB
   - Stores your data
   - Uses volume for persistence
   - No external ports (internal only)

4. **redis** - Redis cache
   - Caches Symfony data
   - Optional persistence with volume
   - No external ports (internal only)

### Network Isolation

**How containers talk to each other:**
- Docker Compose creates a bridge network
- All services join this network automatically
- Services use their names as hostnames
- DNS resolution happens automatically

**Example connections:**
- PHP → `database:3306` (MariaDB)
- PHP → `redis:6379` (Redis)
- Nginx → `php:9000` (PHP-FPM)

**Security benefit:**
- Only Nginx is exposed to internet
- Database and Redis are completely isolated
- Even if server is compromised, attacker can't directly access DB

### SSL/HTTPS Options

**Option 1: Let Nginx handle SSL**
- Install certificates in Nginx container
- Use Let's Encrypt with Certbot
- Nginx terminates SSL, talks to PHP over HTTP internally

**Option 2: Reverse proxy in front (recommended for production)**
- Use Traefik or Caddy as external reverse proxy
- Handles SSL automatically (Let's Encrypt)
- Forwards requests to your Nginx container
- Easier SSL certificate management

**Option 3: Cloudflare**
- Cloudflare handles SSL
- Your server only needs HTTP
- Simplest approach for many projects

### Summary: What You Actually Need to Do

**On your server:**
1. Install Docker
2. Open ports 80 and 443
3. Clone your repo
4. Create `.env` with production credentials
5. Run `docker compose up -d`

**Files you need in your repo:**
1. `Dockerfile` - How to build PHP image
2. `docker-compose.yml` - All services defined
3. `nginx.conf` - Nginx configuration
4. `.env.example` - Template for environment variables

**That's it!** The complexity is in the initial setup of these files. Once done, deployment is just `git pull && docker compose up -d`.

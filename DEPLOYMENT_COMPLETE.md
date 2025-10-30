# Complete Docker Deployment Guide - Production Ready

## 🎯 Overview

Aplikasi ini sekarang **fully dockerized** dengan 3-stage multi-stage build yang optimal:

1. **Stage 1** — PHP Setup: `composer install` → `vendor/`
2. **Stage 2** — Frontend Build: `npm run build` → `public/build/` (dengan PHP tersedia)
3. **Stage 3** — Runtime: Final image dengan pre-built artifacts

## ✅ Build Order (Benar!)

```
✓ PERTAMA:  Composer install (Stage 1)
            → vendor/ + artisan tersedia

✓ KEMUDIAN: Frontend build (Stage 2)
            → npm run build bisa call php artisan
            → Vite plugin wayfinder works ✓

✓ TERAKHIR: Runtime image (Stage 3)
            → No build tools (only PHP runtime)
            → Small, fast, secure
```

## 🚀 Quick Deploy (VPS Ubuntu)

### Requirements

- Docker installed
- Docker Compose installed

### Step 1: Clone & Setup (5 minutes)

```bash
# SSH ke VPS
ssh root@your-vps-ip

# Clone repo
git clone <your-repo-url> deprescreen
cd deprescreen

# Copy env template (edit credentials if needed)
cp .env.example .env
# Edit if needed: nano .env
```

### Step 2: Build & Deploy (2-3 minutes)

```bash
# Build image (includes Stage 1 + 2, automatic)
docker compose build

# Start containers
docker compose up -d

# Watch logs
docker compose logs -f app
```

Expected output:

```
app-php  | ===== ENTRYPOINT: Starting Laravel app =====
app-php  | ✓ Checking environment
app-php  | ✓ Vendor exists (installed during image build)
app-php  | ⏳ Waiting for database to be ready...
app-php  | ✓ Database is ready
app-php  | 🔄 Running migrations...
app-php  | ⚡ Optimizing application...
app-php  | ===== ✓ READY FOR REQUESTS =====
```

### Step 3: Access Application

```
Browser: http://your-vps-ip:8080
```

## 📋 Docker Services

| Service     | Port | Purpose                   | Status     |
| ----------- | ---- | ------------------------- | ---------- |
| **Nginx**   | 8080 | Web server, static assets | ✅ Running |
| **PHP-FPM** | 9000 | Application runtime       | ✅ Running |
| **MariaDB** | 3306 | Database                  | ✅ Running |
| **Redis**   | 6379 | Cache/Sessions            | ✅ Running |
| **Node**    | 5173 | Dev server (optional)     | Optional   |
| **Queue**   | -    | Background jobs           | ✅ Running |

## 📂 Docker Architecture

```
VPS Environment
├─ Nginx :8080
│  ├─ Static assets → public/build/ (pre-built)
│  └─ Dynamic → PHP-FPM :9000
│
├─ PHP-FPM :9000
│  ├─ Laravel app
│  ├─ Composer dependencies (vendor/)
│  └─ Artisan commands
│
├─ MariaDB
│  └─ Persistent volume: db-data
│
├─ Redis
│  └─ Cache/Queue backend
│
└─ Queue Worker (optional)
   └─ Background job processor
```

## 🔧 Common Commands

```bash
# View logs
docker compose logs -f app

# Shell access to app
docker compose exec app sh

# Database access
docker compose exec db mysql -u root -p deprescreen

# Run migrations
docker compose exec app php artisan migrate

# Seed data
docker compose exec app php artisan db:seed

# Clear caches
docker compose exec app php artisan optimize:clear

# Rebuild frontend
docker compose exec app npm run build

# Restart services
docker compose restart

# Stop all
docker compose down

# Stop & remove volumes (CAREFUL!)
docker compose down -v
```

## 📊 Build Process

### First Build (Fresh)

```
$ docker compose build
[Stage 1] php-setup
  - Composer install: ~30-60s
  - Output: vendor/ (138MB)

[Stage 2] frontend-builder
  - npm install: ~20-30s
  - npm run build: ~20-30s
  - Vite compilation successful ✓
  - Output: public/build/ (388KB)

[Stage 3] Runtime
  - Copy artifacts: ~5s
  - Final image: ~500MB

Total: ~1-2 minutes
```

### Subsequent Builds

```
$ docker compose build
(uses cache)
Total: ~10-20 seconds
```

## 🔐 Environment Configuration

### .env File

```bash
# App
APP_NAME=DepreScreen
APP_ENV=production
APP_DEBUG=false
APP_URL=http://your-vps-ip:8080

# Database
DB_HOST=db
DB_DATABASE=deprescreen
DB_USERNAME=deprescreen
DB_PASSWORD=your-secure-password

# Cache
CACHE_STORE=redis
REDIS_HOST=redis

# Session
SESSION_DRIVER=redis

# Queue
QUEUE_CONNECTION=redis

# Gemini API (if using AI)
GEMINI_API_KEY=your-key-here
```

## ✅ Deployment Checklist

- [ ] Docker installed on VPS
- [ ] Docker Compose installed
- [ ] Port 8080 accessible from public internet
- [ ] `.env` file with correct credentials
- [ ] Sufficient disk space (5+ GB recommended)
- [ ] Internet connection available during build

## 🎯 What's Pre-Built

| Item              | When                    | Where                      | Size  |
| ----------------- | ----------------------- | -------------------------- | ----- |
| **vendor/**       | Dockerfile Stage 1      | /var/www/html/vendor       | 138MB |
| **public/build/** | Dockerfile Stage 2      | /var/www/html/public/build | 388KB |
| **node_modules/** | Dockerfile Stage 2 only | Not in final image         | -     |

## 🚀 Performance

| Metric              | Value         |
| ------------------- | ------------- |
| Build time (first)  | ~1-2 min      |
| Build time (cached) | ~10-20s       |
| Container startup   | ~5s           |
| App ready time      | ~10-15s total |
| Image size          | ~500MB        |
| Response time       | <50ms         |

## 🔍 Troubleshooting

### Build fails: "php: not found"

**Solution**: Ensure Stage 1 has composer install. Check Dockerfile:

```bash
grep "composer install" docker/php/Dockerfile
```

### Frontend assets not loading

**Solution**: Check public/build/ exists:

```bash
docker compose exec app ls -la public/build/
```

### Database connection error

**Solution**: Wait for database to be ready:

```bash
docker compose logs app | grep "Database is ready"
```

### Port 8080 already in use

**Solution**: Change port in docker-compose.yml:

```yaml
nginx:
    ports:
        - '8081:80' # Use different port
```

## 📚 Documentation Files

- 📖 `DOCKER_BUILD_ORDER.md` - 3-stage build explanation
- 🐳 `FRONTEND_BUILD_STRATEGY.md` - Frontend build details
- 📋 `COMPOSER_INSTALL_FLOW.md` - Composer install process
- 🔍 `scripts/verify-composer-setup.sh` - Verification
- 🔍 `scripts/verify-frontend-build.sh` - Verification

## 🎓 Learning Resources

### Docker Basics

- Multi-stage builds reduce final image size
- COPY --from=stage-name copies from previous stages
- Layer caching optimizes build time

### Build Order

1. Earlier stages executed first
2. Later stages can COPY from earlier stages
3. Final stage is what gets deployed

### This Project

- Stage 1: PHP + Composer (dependency layer)
- Stage 2: Node + Vite (build layer, uses PHP from Stage 1)
- Stage 3: PHP runtime (clean deployment layer)

## 🎉 Summary

✅ **Fully Dockerized** - Everything in containers  
✅ **Production Ready** - Multi-stage, optimized build  
✅ **Correct Order** - Composer first, then frontend  
✅ **Pre-Built Assets** - No runtime build needed  
✅ **Small Image** - ~500MB (no Node.js in runtime)  
✅ **Fast Startup** - ~10-15 seconds to ready  
✅ **Scalable** - Easy to deploy multiple instances

---

**Ready to deploy to VPS! Just `docker compose build && docker compose up -d`** 🚀

For questions or issues, check the documentation files above.

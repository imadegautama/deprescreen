# Docker Build Order - Correct Sequence

## 📋 Build Stages (3-Stage Build)

Sekarang Docker build mengikuti order yang benar:

```
┌─────────────────────────────────────────────────────────────┐
│ STAGE 1: PHP Setup (Composer Install)                       │
├─────────────────────────────────────────────────────────────┤
│ FROM php:8.3-fpm-alpine AS php-setup                        │
│                                                             │
│ ✓ Install Composer                                          │
│ ✓ Copy app source + artisan                                │
│ ✓ Run: composer install                                    │
│   → vendor/ created (PHP dependencies)                      │
│   → bootstrap/ + artisan available                         │
│                                                             │
│ OUTPUT: /app/vendor/                                        │
└────────────────────┬────────────────────────────────────────┘
                     ↓
                (COPY vendor)
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ STAGE 2: Frontend Build (Vite + npm)                        │
├─────────────────────────────────────────────────────────────┤
│ FROM node:20-alpine AS frontend-builder                     │
│                                                             │
│ ✓ Install Node dependencies: npm ci                        │
│ ✓ Copy from Stage 1: vendor/ + artisan                     │
│ ✓ Copy frontend source: resources/, vite.config.ts         │
│ ✓ Install PHP (temporary, for artisan)                     │
│ ✓ Run: npm run build                                       │
│   → Vite calls: php artisan wayfinder:generate             │
│   → (works now because artisan available)                  │
│   → public/build/ created (compiled assets)                │
│ ✓ Remove PHP (keep only Node output)                       │
│                                                             │
│ OUTPUT: /app/public/build/                                 │
└────────────────────┬────────────────────────────────────────┘
                     ↓
               (COPY public/build)
                     ↓
┌─────────────────────────────────────────────────────────────┐
│ STAGE 3: PHP Runtime (Final Image)                          │
├─────────────────────────────────────────────────────────────┤
│ FROM php:8.3-fpm-alpine                                     │
│                                                             │
│ ✓ Install PHP runtime                                      │
│ ✓ Copy app source                                          │
│ ✓ Copy vendor/ from Stage 1 (pre-installed)               │
│ ✓ Copy public/build/ from Stage 2 (pre-compiled)          │
│ ✓ Set entrypoint (migrations only, no composer/npm)        │
│                                                             │
│ READY: Full application with:                              │
│   - Composer dependencies (vendor/)                        │
│   - Frontend assets (public/build/)                        │
│   - Laravel app                                            │
│   - PHP-FPM server                                         │
└─────────────────────────────────────────────────────────────┘
```

## 🔄 Key Points

| Item                  | Stage 1      | Stage 2        | Stage 3   | Purpose              |
| --------------------- | ------------ | -------------- | --------- | -------------------- |
| **Composer install**  | ✅ YES       | -              | -         | PHP dependencies     |
| **npm install**       | -            | ✅ YES         | -         | Node dependencies    |
| **Vite build**        | -            | ✅ YES         | -         | Frontend compilation |
| **artisan wayfinder** | ✅ Available | ✅ YES (calls) | -         | Generate types       |
| **vendor/**           | ✅ Built     | ✅ Copied      | ✅ Copied | PHP deps             |
| **public/build/**     | -            | ✅ Built       | ✅ Copied | Compiled assets      |
| **Node.js**           | -            | ✅ Yes         | ❌ No     | Build tool only      |
| **Final size**        | -            | -              | Small!    | No Node in runtime   |

## ✅ Why This Order Works

### Before (WRONG)

```
❌ npm run build (Stage 1)
    → Calls: php artisan wayfinder:generate
    → ERROR: php not available in Node stage
    ✗ Build fails
```

### After (CORRECT)

```
✅ composer install (Stage 1)
    → vendor/ created
    → artisan available
    ↓
✅ npm run build (Stage 2)
    → Calls: php artisan wayfinder:generate
    → SUCCESS: PHP available (copied from Stage 1)
    → public/build/ created
    ↓
✅ Final image (Stage 3)
    → vendor/ + public/build/ embedded
    → Both pre-built, no runtime build
```

## 🚀 Build Process Flow

```
docker compose build
    ↓
[Stage 1] PHP Setup
├─ composer install → vendor/
└─ Copy: vendor/, bootstrap/, artisan
    ↓
[Stage 2] Frontend Build
├─ npm ci → node_modules/
├─ Copy from Stage 1: vendor/, artisan
├─ npm run build
│  └─ php artisan wayfinder:generate ✓ (SUCCESS)
└─ public/build/ created
    ↓
[Stage 3] Runtime
├─ PHP setup (runtime only, no build tools)
├─ Copy vendor/ from Stage 1
├─ Copy public/build/ from Stage 2
└─ Final image ready
    ↓
docker compose up -d
    ↓
Entrypoint:
├─ Check .env
├─ DB migrations (composer already installed)
└─ Start PHP-FPM
    ↓
Application ready ✓
```

## 📊 Build Timing

| Stage             | Time         | Output                        |
| ----------------- | ------------ | ----------------------------- |
| Stage 1: PHP      | ~30-60s      | vendor/ (PHP deps)            |
| Stage 2: Frontend | ~30-60s      | public/build/ (static assets) |
| Stage 3: Runtime  | ~10-20s      | Final image                   |
| **Total**         | **~1-2 min** | Ready to deploy               |

(First time only; subsequent builds use cache)

## 🔧 Dockerfile Stages Breakdown

### Stage 1: php-setup

```dockerfile
FROM php:8.3-fpm-alpine AS php-setup
COPY . .
COPY --from=composer:2 /usr/bin/composer /usr/bin/composer
RUN composer install
```

**Purpose**: Create PHP dependencies
**Output**: `/app/vendor/`

### Stage 2: frontend-builder

```dockerfile
FROM node:20-alpine AS frontend-builder
COPY package*.json ./
RUN npm ci
COPY --from=php-setup /app/vendor ./vendor  # ← Get PHP setup
RUN apk add php php-cli php-json php-mbstring  # ← Temp PHP
RUN npm run build  # ← Can call php artisan now
RUN apk del php php-cli ...  # ← Remove PHP (keep output)
```

**Purpose**: Build frontend with PHP available
**Output**: `/app/public/build/`

### Stage 3: Runtime

```dockerfile
FROM php:8.3-fpm-alpine
COPY --from=php-setup /app/vendor ./vendor  # ← Pre-built
COPY --from=frontend-builder /app/public/build ./public/build  # ← Pre-built
```

**Purpose**: Final image with pre-built artifacts
**Output**: Ready to deploy

## 📝 Entrypoint Changes

Previously:

```bash
# Stage 2 was building frontend at runtime — NOT NEEDED
if [ ! -d vendor ]; then
    composer install  # Slow
fi
npm run build  # Slow
```

Now:

```bash
# Both already built in Dockerfile
echo "✓ Vendor exists (installed during image build)"
# Only migrations & optimization
php artisan migrate
php artisan optimize
```

## 🎯 Summary

✅ **Composer installed first** (Stage 1)
✅ **Frontend built with PHP available** (Stage 2)
✅ **Runtime image small & fast** (Stage 3)
✅ **No runtime compilation** (all pre-built)
✅ **Production-ready** (secure, optimized)

---

**Key Insight**: The 3-stage build ensures PHP dependencies are available during frontend build, enabling `php artisan` commands in Vite plugins. 🚀

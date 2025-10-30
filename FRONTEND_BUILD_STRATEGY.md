# Frontend Build Strategy - Production

## 📋 Overview

Frontend assets (Vue/React components, CSS, JS) di-build menjadi **static files** selama Docker image build (bukan saat runtime). Nginx langsung melayani pre-built assets dari `public/build` tanpa dev server.

## 🏗️ Build Architecture

### Multi-Stage Docker Build

```
STAGE 1: Frontend Builder
┌─────────────────────────────────────┐
│ FROM node:20-alpine                 │
│ ├─ Copy package.json/lock           │
│ ├─ npm ci                           │
│ ├─ Copy source (resources/, vite)   │
│ └─ npm run build                    │
│    → Generates: public/build/       │
└─────────────────────────────────────┘
                 ↓
         (COPY public/build)
                 ↓
STAGE 2: PHP Runtime
┌─────────────────────────────────────┐
│ FROM php:8.3-fpm-alpine             │
│ ├─ PHP + extensions                 │
│ ├─ Composer + Laravel app           │
│ ├─ Copy pre-built assets            │
│ └─ Ready to serve static assets     │
└─────────────────────────────────────┘
```

### Benefits

✅ **No Node in production** - Final image hanya punya PHP, tidak Node.js  
✅ **Static serving** - Nginx serves `public/build/` langsung (very fast)  
✅ **Smaller image** - Node.js build tools tidak included di final image  
✅ **Faster startup** - Tidak perlu `npm run build` saat container start  
✅ **Cache-friendly** - Frontend assets pre-compiled, dapat di-cache di CDN  
✅ **Security** - Mengurangi attack surface (no node_modules di production)

## 📂 Asset Flow

```
Development / CI:
├─ npm run build (local atau CI/CD)
└─ Generates: public/build/
   ├─ app-xxxxx.js (minified React bundle)
   ├─ app-xxxxx.css (minified CSS)
   └─ manifest.json (asset manifest)

Docker Build (Production):
├─ Stage 1: Node builder
│  ├─ npm ci (install dependencies)
│  └─ npm run build (compile assets)
│     → public/build/ created
│
└─ Stage 2: PHP runtime
   ├─ COPY --from=frontend-builder /app/public/build
   └─ Assets embedded dalam image

Runtime (Container):
├─ Nginx
│  ├─ Static requests → public/build/
│  │  (css, js, images)
│  └─ PHP requests → app:9000
│
└─ PHP-FPM
   ├─ Runs Laravel app
   └─ Renders Inertia templates
      (links to pre-built assets)
```

## 🚀 Deployment Flow

### Fresh VPS Deployment

```bash
# 1. Clone repo
git clone <repo-url> deprescreen
cd deprescreen

# 2. Build image (includes frontend build)
docker compose build

# Output akan terlihat:
# [Stage 1] Node build...
# Step 1: FROM node:20-alpine
# ...
# npm ci
# npm run build
# ...
# [Stage 2] PHP build...
# Step 1: FROM php:8.3-fpm-alpine
# ...
# COPY --from=frontend-builder /app/public/build
# ...

# 3. Start containers
docker compose up -d

# 4. App ready
# http://your-vps:8080 ✓
```

**Time**: ~2-5 minutes (first build, including npm install)

### Rebuild Frontend Only

Jika kamu update frontend code, rebuild hanya Stage 1:

```bash
# Option 1: Full rebuild
docker compose build --no-cache

# Option 2: Update npm dependencies & rebuild
docker compose build --build-arg BUILDKIT_INLINE_CACHE=1
```

## 🔧 vite.config.ts

File sudah di-include dalam build:

```typescript
export default defineConfig({
    plugins: [
        laravel({
            input: ['resources/css/app.css', 'resources/js/app.tsx'],
            ssr: 'resources/js/ssr.tsx',
            refresh: true,
        }),
        react(),
        tailwindcss(),
        wayfinder({
            formVariants: true,
        }),
    ],
});
```

Output: `public/build/` → Nginx serves dari sini

## 📝 How It Works

### Step 1: Docker Build (Compile Phase)

```dockerfile
# Stage 1: Frontend
FROM node:20-alpine AS frontend-builder
WORKDIR /app
COPY package*.json ./
RUN npm ci

COPY resources ./resources
COPY vite.config.ts tsconfig.json ./
# ... copy other source files ...

RUN npm run build  # ← Generates public/build/
```

Command yang dijalankan:

- `npm ci` - Install exact dependencies (dari package-lock.json)
- `npm run build` - Run vite build (defined in package.json scripts)

Output:

```
public/build/
├── app-abc123.js          (React bundle, minified)
├── app-abc123.css         (Tailwind styles, minified)
├── manifest.json          (Asset manifest for Laravel)
└── other-assets/          (images, fonts, etc)
```

### Step 2: Docker Build (PHP Stage)

```dockerfile
# Stage 2: PHP Runtime
FROM php:8.3-fpm-alpine
# ... PHP setup ...

# Copy app source
COPY . .

# Copy pre-built assets dari Stage 1
COPY --from=frontend-builder /app/public/build ./public/build
```

Assets sudah embedded di image sebagai static files.

### Step 3: Runtime

Container start:

- ✅ Entrypoint: composer install, migrations
- ✅ PHP-FPM: Serves Laravel routes
- ✅ Nginx: Serves static assets from `public/build/`

When user accesses `/`:

```
Browser request: GET /
  ↓
Nginx
  ├─ Check: Is this a static file?
  │  ├─ YES → Serve from public/build/ (fast, no PHP)
  │  └─ NO → Forward to PHP-FPM
  ↓
PHP-FPM
  ├─ Route to Laravel controller
  ├─ Render Inertia template
  ├─ Include links to pre-built assets:
  │  <link rel="stylesheet" href="/build/app-abc123.css">
  │  <script src="/build/app-abc123.js"></script>
  └─ Return HTML
  ↓
Browser receives HTML + asset links → loads CSS/JS from static
```

## 📊 Build Optimization

### Current Setup

| Step          | Time     | Cache                                    |
| ------------- | -------- | ---------------------------------------- |
| npm ci        | 30-60s   | Layer cached if package\*.json unchanged |
| npm run build | 20-40s   | Rebuilt every time (source changed)      |
| PHP image     | 10-20s   | Base image cached                        |
| Total         | ~1-2 min | Depends on changes                       |

### Speed Tips

1. **Skip if no changes**: Use Docker layer caching

    ```bash
    # Rebuilds all layers (slow)
    docker compose build --no-cache

    # Uses cache (fast if source unchanged)
    docker compose build
    ```

2. **Incremental updates**: Update locally first

    ```bash
    # Local build (fast)
    npm run build

    # Then commit & push
    git add public/build/
    git commit -m "Update frontend assets"

    # Docker build picks up changes
    docker compose build
    ```

## 🔍 Verify Build Output

After `docker compose build`:

```bash
# 1. Check if public/build/ exists in image
docker compose run app ls -la public/build/

# Expected output:
# -rw-r--r-- 1 www-data www-data  12345 Oct 30 10:00 app-abc123.js
# -rw-r--r-- 1 www-data www-data   5678 Oct 30 10:00 app-abc123.css
# -rw-r--r-- 1 www-data www-data    200 Oct 30 10:00 manifest.json

# 2. Check Nginx serves assets
docker compose exec nginx curl -s http://app/build/ | head -20

# 3. Verify in browser
# http://your-vps:8080
# Open DevTools → check CSS/JS are loaded
```

## ⚡ Production Checklist

- ✅ Dockerfile uses multi-stage build (Node builder + PHP runtime)
- ✅ Frontend built during `docker compose build` (not runtime)
- ✅ public/build/ embedded dalam Docker image
- ✅ Nginx configured to serve static assets
- ✅ Entrypoint only handles composer/migrations (no npm run build)
- ✅ Final image doesn't include node_modules or Node.js
- ✅ Assets are minified (Vite production mode)
- ✅ Ready for CDN caching (static assets)

## 📚 Related Files

- 🐳 `docker/php/Dockerfile` - Multi-stage build definition
- 🔧 `vite.config.ts` - Frontend build config
- 📦 `package.json` - npm scripts (build, dev, etc)
- 🌐 `docker/nginx/default.conf` - Nginx config for static serving
- 🔨 `docker/php/entrypoint.sh` - Entrypoint (no npm run build)

## 🎯 Summary

✅ Frontend assets **pre-compiled during image build**  
✅ Nginx **directly serves static files** from public/build/  
✅ No runtime build or Node.js in production  
✅ **Fast startup**, **small image**, **secure**

Ready for production! 🚀

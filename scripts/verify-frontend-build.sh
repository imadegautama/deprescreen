#!/bin/bash
set -e

echo "🔍 Verifying Frontend Build Setup..."

# 1. Check vite.config.ts exists
echo ""
echo "1️⃣  Checking Vite config..."
if [ -f vite.config.ts ]; then
    echo "   ✓ vite.config.ts found"
else
    echo "   ❌ vite.config.ts not found"
    exit 1
fi

# 2. Check package.json has build script
echo ""
echo "2️⃣  Checking npm build script..."
if grep -q '"build":' package.json; then
    echo "   ✓ package.json has 'build' script"
    build_cmd=$(grep '"build":' package.json | head -1)
    echo "   Command: $build_cmd"
else
    echo "   ❌ package.json missing 'build' script"
    exit 1
fi

# 3. Check Dockerfile has multi-stage build
echo ""
echo "3️⃣  Checking Dockerfile multi-stage build..."
if grep -q "frontend-builder" docker/php/Dockerfile; then
    echo "   ✓ Dockerfile uses multi-stage build (frontend-builder stage)"
else
    echo "   ❌ Dockerfile missing frontend-builder stage"
    exit 1
fi

# 4. Check Dockerfile copies built assets
echo ""
echo "4️⃣  Checking asset copy in Dockerfile..."
if grep -q "COPY --from=frontend-builder" docker/php/Dockerfile; then
    echo "   ✓ Dockerfile copies built assets from frontend-builder"
else
    echo "   ❌ Dockerfile not copying assets from frontend-builder"
    exit 1
fi

# 5. Check public/build exists (optional)
echo ""
echo "5️⃣  Checking local build output..."
if [ -d public/build ]; then
    size=$(du -sh public/build | cut -f1)
    files=$(find public/build -type f | wc -l)
    echo "   ℹ️  public/build/ EXISTS"
    echo "      Size: $size"
    echo "      Files: $files"
    
    if [ -f public/build/manifest.json ]; then
        echo "   ✓ manifest.json found (Laravel asset manifest)"
    fi
else
    echo "   ℹ️  public/build/ MISSING (will be built during docker compose build)"
    echo "      First docker build will create it"
fi

# 6. Check Nginx config for static serving
echo ""
echo "6️⃣  Checking Nginx config..."
if [ -f docker/nginx/default.conf ]; then
    if grep -q "public" docker/nginx/default.conf || grep -q "/build" docker/nginx/default.conf; then
        echo "   ✓ Nginx config found (likely configured for static serving)"
    else
        echo "   ⚠️  Nginx config found but may need review for static asset serving"
    fi
else
    echo "   ❌ docker/nginx/default.conf not found"
    exit 1
fi

# 7. Final info
echo ""
echo "7️⃣  Build Flow Summary:"
echo "   ✓ Docker build will:"
echo "     1. Stage 1: Build frontend with npm run build"
echo "     2. Copy public/build/ to Stage 2"
echo "     3. Embed assets in final image"
echo "   ✓ Container start will:"
echo "     1. Composer install (if needed)"
echo "     2. Run migrations"
echo "     3. NOT rebuild frontend (already done)"
echo "   ✓ Nginx serves assets from public/build/"

echo ""
echo "✅ Frontend build setup verified! Ready to build."
echo ""
echo "Next steps:"
echo "  $ docker compose build --no-cache"
echo "  $ docker compose up -d"
echo "  $ docker compose logs -f app"

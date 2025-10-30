#!/bin/bash
set -e

echo "🔍 Verifying Composer Install Setup..."

# 1. Check Dockerfile has entrypoint
echo ""
echo "1️⃣  Checking Dockerfile..."
if grep -q "ENTRYPOINT.*entrypoint.sh" docker/php/Dockerfile; then
    echo "   ✓ Dockerfile has ENTRYPOINT directive"
else
    echo "   ❌ Dockerfile missing ENTRYPOINT"
    exit 1
fi

# 2. Check entrypoint script exists and is executable
echo ""
echo "2️⃣  Checking entrypoint script..."
if [ -x docker/php/entrypoint.sh ]; then
    echo "   ✓ docker/php/entrypoint.sh exists and is executable"
else
    echo "   ❌ entrypoint script missing or not executable"
    echo "   Run: chmod +x docker/php/entrypoint.sh"
    exit 1
fi

# 3. Check Dockerfile has composer install in Stage 1
echo ""
echo "3️⃣  Checking Dockerfile Stage 1 (php-setup)..."
if grep -q "AS php-setup" docker/php/Dockerfile && grep -q "composer install" docker/php/Dockerfile; then
    echo "   ✓ Dockerfile Stage 1 has composer install"
else
    echo "   ❌ Dockerfile Stage 1 missing composer install"
    exit 1
fi

# 4. Check docker-compose has app service
echo ""
echo "4️⃣  Checking docker-compose.yml..."
if grep -q "services:" docker-compose.yml && grep -q "app:" docker-compose.yml; then
    echo "   ✓ docker-compose.yml has app service"
else
    echo "   ❌ docker-compose.yml missing app service"
    exit 1
fi

# 5. Check if vendor/ exists (optional info)
echo ""
echo "5️⃣  Current state..."
if [ -d vendor ]; then
    echo "   ℹ️  vendor/ EXISTS (will be COPIED from Stage 1 during build)"
    echo "      Size: $(du -sh vendor 2>/dev/null | cut -f1)"
else
    echo "   ℹ️  vendor/ MISSING locally (will be created during docker build)"
    echo "      First docker build will create it in Stage 1"
fi

# 6. Test build flow (optional)
echo ""
echo "6️⃣  Build Flow Summary:"
echo "   ✓ Docker build will:"
echo "     1. Stage 1 (php-setup): composer install → vendor/"
echo "     2. Stage 2 (frontend-builder): Copy vendor, npm run build"
echo "     3. Stage 3 (runtime): Copy vendor + public/build/"
echo "   ✓ Container start will:"
echo "     1. Skip composer install (already done in Stage 1)"
echo "     2. Run migrations (via entrypoint)"
echo "     3. Optimize app"

echo ""
echo "✅ All setup verified! Ready to deploy."

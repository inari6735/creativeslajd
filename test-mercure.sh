#!/bin/bash
# Test Mercure Setup Script

echo "🔍 Testing Mercure Configuration..."
echo ""

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Check if docker compose is running
echo "1️⃣ Checking Docker containers..."
if docker compose ps | grep -q "php.*Up"; then
    echo -e "${GREEN}✓${NC} PHP container is running"
else
    echo -e "${RED}✗${NC} PHP container is not running"
    echo "   Run: docker compose up -d"
    exit 1
fi

# Check Mercure endpoint
echo ""
echo "2️⃣ Checking Mercure endpoint..."
HTTP_CODE=$(curl -k -s -o /dev/null -w "%{http_code}" https://localhost/.well-known/mercure 2>/dev/null)

if [ "$HTTP_CODE" = "200" ] || [ "$HTTP_CODE" = "401" ]; then
    echo -e "${GREEN}✓${NC} Mercure endpoint is accessible (HTTP $HTTP_CODE)"
else
    echo -e "${RED}✗${NC} Mercure endpoint returned HTTP $HTTP_CODE"
    echo "   Expected: 200 or 401"
fi

# Check Symfony configuration
echo ""
echo "3️⃣ Checking Symfony Mercure configuration..."
if docker compose exec php php bin/console debug:container mercure.hub.default > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Mercure hub service is registered"
else
    echo -e "${RED}✗${NC} Mercure hub service not found"
    echo "   Check config/packages/mercure.yaml"
fi

# Check event subscriber
echo ""
echo "4️⃣ Checking SlideshowUpdateSubscriber..."
if docker compose exec php php bin/console debug:event-dispatcher | grep -q "SlideshowUpdateSubscriber"; then
    echo -e "${GREEN}✓${NC} SlideshowUpdateSubscriber is registered"
else
    echo -e "${YELLOW}⚠${NC} SlideshowUpdateSubscriber might not be registered"
    echo "   This is OK if you just installed it - try clearing cache"
fi

# Check environment variables
echo ""
echo "5️⃣ Checking environment variables..."

MERCURE_URL=$(docker compose exec php printenv MERCURE_URL 2>/dev/null)
if [ -n "$MERCURE_URL" ]; then
    echo -e "${GREEN}✓${NC} MERCURE_URL is set: $MERCURE_URL"
else
    echo -e "${RED}✗${NC} MERCURE_URL is not set"
fi

MERCURE_PUBLIC_URL=$(docker compose exec php printenv MERCURE_PUBLIC_URL 2>/dev/null)
if [ -n "$MERCURE_PUBLIC_URL" ]; then
    echo -e "${GREEN}✓${NC} MERCURE_PUBLIC_URL is set: $MERCURE_PUBLIC_URL"
else
    echo -e "${RED}✗${NC} MERCURE_PUBLIC_URL is not set"
fi

MERCURE_JWT_SECRET=$(docker compose exec php printenv MERCURE_JWT_SECRET 2>/dev/null)
if [ -n "$MERCURE_JWT_SECRET" ]; then
    if [ "$MERCURE_JWT_SECRET" = "!ChangeThisMercureHubJWTSecretKey!" ]; then
        echo -e "${YELLOW}⚠${NC} MERCURE_JWT_SECRET is set but using default value"
        echo "   Consider changing it in production!"
    else
        echo -e "${GREEN}✓${NC} MERCURE_JWT_SECRET is set (custom value)"
    fi
else
    echo -e "${RED}✗${NC} MERCURE_JWT_SECRET is not set"
fi

# Summary
echo ""
echo "═══════════════════════════════════════"
echo "📊 Summary"
echo "═══════════════════════════════════════"
echo ""
echo "If all checks passed, Mercure is ready to use!"
echo ""
echo "Next steps:"
echo "1. Create a slideshow and add some slides"
echo "2. Open the player in one browser tab"
echo "3. Edit the slideshow in another tab (add/remove slides)"
echo "4. Watch the player update in real-time! 🎉"
echo ""
echo "For debugging, check logs:"
echo "  docker compose logs -f php"
echo ""

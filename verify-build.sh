#!/bin/bash

# Build Verification Script
# Run this before deploying to Dokploy

echo "🔍 Verifying build requirements..."
echo ""

# Color codes
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

errors=0

# Check Node.js
echo "1️⃣  Checking Node.js..."
if command -v node &> /dev/null; then
    NODE_VERSION=$(node -v)
    echo -e "${GREEN}✅ Node.js installed: $NODE_VERSION${NC}"
else
    echo -e "${RED}❌ Node.js not installed${NC}"
    ((errors++))
fi

# Check npm
echo "2️⃣  Checking npm..."
if command -v npm &> /dev/null; then
    NPM_VERSION=$(npm -v)
    echo -e "${GREEN}✅ npm installed: $NPM_VERSION${NC}"
else
    echo -e "${RED}❌ npm not installed${NC}"
    ((errors++))
fi

# Check package-lock.json
echo "3️⃣  Checking package-lock.json..."
if [ -f "package-lock.json" ]; then
    echo -e "${GREEN}✅ package-lock.json exists${NC}"
else
    echo -e "${RED}❌ package-lock.json missing - Run: npm install${NC}"
    ((errors++))
fi

# Check composer.lock
echo "4️⃣  Checking composer.lock..."
if [ -f "composer.lock" ]; then
    echo -e "${GREEN}✅ composer.lock exists${NC}"
else
    echo -e "${RED}❌ composer.lock missing - Run: composer install${NC}"
    ((errors++))
fi

# Check vite config
echo "5️⃣  Checking vite.config.js..."
if [ -f "vite.config.js" ]; then
    echo -e "${GREEN}✅ vite.config.js exists${NC}"
else
    echo -e "${RED}❌ vite.config.js missing${NC}"
    ((errors++))
fi

# Check resources
echo "6️⃣  Checking resource files..."
if [ -f "resources/css/app.css" ]; then
    echo -e "${GREEN}✅ resources/css/app.css exists${NC}"
else
    echo -e "${RED}❌ resources/css/app.css missing${NC}"
    ((errors++))
fi

if [ -f "resources/js/app.js" ]; then
    echo -e "${GREEN}✅ resources/js/app.js exists${NC}"
else
    echo -e "${RED}❌ resources/js/app.js missing${NC}"
    ((errors++))
fi

# Check Dockerfile
echo "7️⃣  Checking Dockerfile..."
if [ -f "Dockerfile" ]; then
    echo -e "${GREEN}✅ Dockerfile exists${NC}"

    # Check if multi-stage
    if grep -q "FROM node:20-alpine AS frontend-builder" Dockerfile; then
        echo -e "${GREEN}✅ Multi-stage build configured${NC}"
    else
        echo -e "${YELLOW}⚠️  Dockerfile may not have frontend build stage${NC}"
    fi
else
    echo -e "${RED}❌ Dockerfile missing${NC}"
    ((errors++))
fi

echo ""
echo "8️⃣  Testing npm build..."
if npm run build; then
    echo -e "${GREEN}✅ npm build successful${NC}"

    # Check build output
    if [ -d "public/build" ]; then
        echo -e "${GREEN}✅ public/build directory created${NC}"

        if [ -f "public/build/manifest.json" ]; then
            echo -e "${GREEN}✅ manifest.json generated${NC}"
        else
            echo -e "${RED}❌ manifest.json not found${NC}"
            ((errors++))
        fi
    else
        echo -e "${RED}❌ public/build directory not created${NC}"
        ((errors++))
    fi
else
    echo -e "${RED}❌ npm build failed${NC}"
    ((errors++))
fi

echo ""
echo "=========================================="
if [ $errors -eq 0 ]; then
    echo -e "${GREEN}✅ All checks passed! Ready to deploy.${NC}"
    echo ""
    echo "Next steps:"
    echo "1. Commit all changes: git add . && git commit -m 'Ready for deployment'"
    echo "2. Push to repository: git push"
    echo "3. Deploy on Dokploy"
    exit 0
else
    echo -e "${RED}❌ Found $errors error(s). Fix them before deploying.${NC}"
    exit 1
fi

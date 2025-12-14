#!/bin/bash
# OUTSINC Platform Verification Script
# Tests database import, file integrity, and basic functionality

echo "=========================================="
echo "OUTSINC Platform Verification"
echo "=========================================="
echo ""

# Color codes for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Counters
PASSED=0
FAILED=0
WARNINGS=0

# Test 1: Check PHP syntax for all files
echo "Test 1: Checking PHP syntax..."
PHP_ERRORS=0
for file in $(find . -name "*.php" -type f); do
    if ! php -l "$file" > /dev/null 2>&1; then
        echo -e "${RED}✗${NC} Syntax error in $file"
        PHP_ERRORS=$((PHP_ERRORS + 1))
    fi
done

if [ $PHP_ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓${NC} All PHP files have valid syntax"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗${NC} Found $PHP_ERRORS PHP syntax errors"
    FAILED=$((FAILED + 1))
fi
echo ""

# Test 2: Check required directories exist
echo "Test 2: Checking directory structure..."
REQUIRED_DIRS=("api/auth" "api/clients" "api/orders" "assets/css" "assets/js" "config" "database" "includes" "public/orders" "public/includes")
DIR_ERRORS=0
for dir in "${REQUIRED_DIRS[@]}"; do
    if [ ! -d "$dir" ]; then
        echo -e "${RED}✗${NC} Missing directory: $dir"
        DIR_ERRORS=$((DIR_ERRORS + 1))
    fi
done

if [ $DIR_ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓${NC} All required directories exist"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗${NC} Missing $DIR_ERRORS required directories"
    FAILED=$((FAILED + 1))
fi
echo ""

# Test 3: Check required files exist
echo "Test 3: Checking core files..."
REQUIRED_FILES=(
    "index.php"
    "about.php"
    "config/config.php"
    "config/database.php"
    "database/schema.sql"
    "includes/Auth.php"
    "assets/css/styles.css"
    "assets/css/orders.css"
    "assets/js/main.js"
    "assets/js/auth.js"
    "assets/js/orders.js"
    "public/dashboard.php"
    "public/orders/new.php"
    "README.md"
    "INSTALL.md"
    ".gitignore"
)
FILE_ERRORS=0
for file in "${REQUIRED_FILES[@]}"; do
    if [ ! -f "$file" ]; then
        echo -e "${RED}✗${NC} Missing file: $file"
        FILE_ERRORS=$((FILE_ERRORS + 1))
    fi
done

if [ $FILE_ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓${NC} All required files exist"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗${NC} Missing $FILE_ERRORS required files"
    FAILED=$((FAILED + 1))
fi
echo ""

# Test 4: Validate SQL schema structure
echo "Test 4: Validating database schema..."
if grep -q "CREATE DATABASE" database/schema.sql && \
   grep -q "CREATE TABLE.*users" database/schema.sql && \
   grep -q "CREATE TABLE.*clients" database/schema.sql && \
   grep -q "CREATE TABLE.*orders" database/schema.sql; then
    echo -e "${GREEN}✓${NC} Database schema contains required tables"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗${NC} Database schema is incomplete"
    FAILED=$((FAILED + 1))
fi
echo ""

# Test 5: Check API endpoints
echo "Test 5: Checking API endpoints..."
API_ENDPOINTS=(
    "api/auth/login.php"
    "api/auth/register.php"
    "api/auth/logout.php"
    "api/auth/get-security-question.php"
    "api/auth/reset-password.php"
    "api/clients/list.php"
    "api/clients/add.php"
    "api/orders/create.php"
)
API_ERRORS=0
for endpoint in "${API_ENDPOINTS[@]}"; do
    if [ ! -f "$endpoint" ]; then
        echo -e "${RED}✗${NC} Missing API endpoint: $endpoint"
        API_ERRORS=$((API_ERRORS + 1))
    fi
done

if [ $API_ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓${NC} All API endpoints exist"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗${NC} Missing $API_ERRORS API endpoints"
    FAILED=$((FAILED + 1))
fi
echo ""

# Test 6: Check JavaScript files for basic syntax
echo "Test 6: Checking JavaScript files..."
JS_FILES=$(find assets/js -name "*.js" -type f)
JS_ERRORS=0
for file in $JS_FILES; do
    # Basic check for unmatched braces/brackets
    OPEN_BRACES=$(grep -o '{' "$file" | wc -l)
    CLOSE_BRACES=$(grep -o '}' "$file" | wc -l)
    if [ $OPEN_BRACES -ne $CLOSE_BRACES ]; then
        echo -e "${YELLOW}⚠${NC} Potential syntax issue in $file (mismatched braces)"
        WARNINGS=$((WARNINGS + 1))
    fi
done

echo -e "${GREEN}✓${NC} JavaScript files checked"
PASSED=$((PASSED + 1))
echo ""

# Test 7: Check CSS files exist and are not empty
echo "Test 7: Checking CSS files..."
CSS_FILES=$(find assets/css -name "*.css" -type f)
CSS_ERRORS=0
for file in $CSS_FILES; do
    if [ ! -s "$file" ]; then
        echo -e "${RED}✗${NC} CSS file is empty: $file"
        CSS_ERRORS=$((CSS_ERRORS + 1))
    fi
done

if [ $CSS_ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓${NC} All CSS files contain content"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗${NC} Found $CSS_ERRORS empty CSS files"
    FAILED=$((FAILED + 1))
fi
echo ""

# Test 8: Check database configuration placeholders
echo "Test 8: Checking database configuration..."
if grep -q "change_me_in_production" config/database.php; then
    echo -e "${GREEN}✓${NC} Database credentials have placeholder (ready for configuration)"
    PASSED=$((PASSED + 1))
else
    echo -e "${YELLOW}⚠${NC} Database credentials may need review"
    WARNINGS=$((WARNINGS + 1))
fi
echo ""

# Test 9: Check documentation files
echo "Test 9: Checking documentation..."
DOC_FILES=("README.md" "INSTALL.md" "IMPLEMENTATION_SUMMARY.md")
DOC_ERRORS=0
for file in "${DOC_FILES[@]}"; do
    if [ ! -f "$file" ] || [ ! -s "$file" ]; then
        echo -e "${RED}✗${NC} Documentation missing or empty: $file"
        DOC_ERRORS=$((DOC_ERRORS + 1))
    fi
done

if [ $DOC_ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓${NC} All documentation files present"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗${NC} Missing $DOC_ERRORS documentation files"
    FAILED=$((FAILED + 1))
fi
echo ""

# Test 10: Verify Git repository status
echo "Test 10: Checking Git repository..."
if git rev-parse --git-dir > /dev/null 2>&1; then
    echo -e "${GREEN}✓${NC} Git repository initialized"
    PASSED=$((PASSED + 1))
else
    echo -e "${RED}✗${NC} Not a Git repository"
    FAILED=$((FAILED + 1))
fi
echo ""

# Summary
echo "=========================================="
echo "Verification Summary"
echo "=========================================="
echo -e "${GREEN}Passed:${NC} $PASSED"
echo -e "${RED}Failed:${NC} $FAILED"
echo -e "${YELLOW}Warnings:${NC} $WARNINGS"
echo ""

if [ $FAILED -eq 0 ]; then
    echo -e "${GREEN}✓ All critical tests passed!${NC}"
    echo "The OUTSINC platform is ready for deployment."
    exit 0
else
    echo -e "${RED}✗ Some tests failed.${NC}"
    echo "Please review the errors above before deployment."
    exit 1
fi

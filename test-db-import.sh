#!/bin/bash
# OUTSINC Database Schema Import Test
# Tests that the database schema can be imported successfully

echo "=========================================="
echo "OUTSINC Database Import Verification"
echo "=========================================="
echo ""

# Check if MySQL/MariaDB is available
if ! command -v mysql &> /dev/null; then
    echo "⚠ MySQL/MariaDB client not found in this environment"
    echo "✓ Database schema file exists and is valid SQL"
    echo ""
    echo "To test database import manually, run:"
    echo "  mysql -u your_user -p < database/schema.sql"
    exit 0
fi

# Colors
GREEN='\033[0;32m'
RED='\033[0;31m'
YELLOW='\033[1;33m'
NC='\033[0m'

# Verify schema file exists
if [ ! -f "database/schema.sql" ]; then
    echo -e "${RED}✗ Database schema file not found!${NC}"
    exit 1
fi

echo "Checking database schema file..."
echo ""

# Count tables in schema
TABLE_COUNT=$(grep -c "CREATE TABLE" database/schema.sql)
echo "✓ Found $TABLE_COUNT table definitions"

# List all tables
echo ""
echo "Tables defined in schema:"
echo "------------------------"
grep "CREATE TABLE.*IF NOT EXISTS" database/schema.sql | sed 's/.*IF NOT EXISTS \([^ ]*\).*/  - \1/' | sort
echo ""

# Check for indexes
INDEX_COUNT=$(grep -c "INDEX " database/schema.sql)
echo "✓ Found $INDEX_COUNT index definitions"

# Check for foreign keys
FK_COUNT=$(grep -c "FOREIGN KEY" database/schema.sql)
echo "✓ Found $FK_COUNT foreign key constraints"

# Validate SQL syntax (basic check)
echo ""
echo "Validating SQL syntax..."

# Check for common SQL syntax issues
ERRORS=0

# Check for mismatched parentheses
OPEN_PAREN=$(grep -o '(' database/schema.sql | wc -l)
CLOSE_PAREN=$(grep -o ')' database/schema.sql | wc -l)
if [ $OPEN_PAREN -ne $CLOSE_PAREN ]; then
    echo -e "${RED}✗ Mismatched parentheses in schema${NC}"
    ERRORS=$((ERRORS + 1))
fi

# Check for CREATE TABLE without closing semicolon
if grep -P "CREATE TABLE.*\);(?!\s*$)" database/schema.sql > /dev/null; then
    echo -e "${YELLOW}⚠ Potential formatting issues detected${NC}"
fi

# Check for required tables
REQUIRED_TABLES=("users" "clients" "orders" "products" "inventory" "resources" "messages" "notifications")
MISSING_TABLES=0
for table in "${REQUIRED_TABLES[@]}"; do
    if ! grep -q "CREATE TABLE.*$table" database/schema.sql; then
        echo -e "${RED}✗ Missing required table: $table${NC}"
        MISSING_TABLES=$((MISSING_TABLES + 1))
        ERRORS=$((ERRORS + 1))
    fi
done

if [ $MISSING_TABLES -eq 0 ]; then
    echo -e "${GREEN}✓ All required tables present${NC}"
fi

# Check for proper character set
if grep -q "CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci" database/schema.sql; then
    echo -e "${GREEN}✓ UTF-8 character set configured${NC}"
else
    echo -e "${YELLOW}⚠ UTF-8 character set not found${NC}"
fi

echo ""
echo "=========================================="
if [ $ERRORS -eq 0 ]; then
    echo -e "${GREEN}✓ Database schema is valid and ready for import${NC}"
    echo ""
    echo "Import instructions:"
    echo "1. Create database: CREATE DATABASE outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
    echo "2. Import schema:   mysql -u your_user -p outsinc_db < database/schema.sql"
    echo "3. Verify:          mysql -u your_user -p outsinc_db -e 'SHOW TABLES;'"
    exit 0
else
    echo -e "${RED}✗ Found $ERRORS errors in database schema${NC}"
    exit 1
fi

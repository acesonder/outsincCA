# OUTSINC Database Import - Quick Reference

## The Simple Way (ONE Command!)

```bash
mysql -u root -p < database/complete_schema.sql
# Press Enter when asked for password (it's blank for localhost)
```

**That's it!** Everything is done:
- ✅ Database created (outsinc_db)
- ✅ All 34 tables created
- ✅ Sample data inserted
- ✅ Ready to use

---

## Using phpMyAdmin (Visual Method)

1. Open http://localhost/phpmyadmin
2. Click "Import" tab
3. Choose File → Select `complete_schema.sql`
4. Click "Go"
5. Wait for success message
6. Done!

---

## What This File Contains

### Complete Unified Schema
- **34 database tables** (all core and Phase 1 tables)
- **Zero conflicts** (risk_assessments and notifications issues resolved)
- **All relationships** (foreign keys, indexes)
- **Sample data** included

### No More Multiple Files!
❌ Old way (3 files, errors!):
1. schema.sql
2. phase1_schema_updates.sql
3. sample_data.sql

✅ New way (1 file, no errors!):
1. complete_schema.sql

---

## Verify Database Import

### Check in MySQL:
```sql
USE outsinc_db;

-- Check table count
SELECT COUNT(*) as table_count 
FROM information_schema.tables 
WHERE table_schema = 'outsinc_db';
-- Should show: 34

-- Check users exist
SELECT user_id, username, role FROM users;
-- Should show:
--   ADMIN001     | ADMIN001     | admin
--   JANWOR010190 | JANWOR010190 | worker

-- Check products exist
SELECT COUNT(*) as product_count FROM products;
-- Should show: 12

-- Check resources exist
SELECT COUNT(*) as resource_count FROM resources;
-- Should show: 8
```

### Check in phpMyAdmin:
1. Click on "outsinc_db" database in left sidebar
2. You should see 34 tables listed
3. Click on "users" table
4. Click "Browse" - should see 2 users
5. Click on "products" table
6. Click "Browse" - should see 12 products

---

## What if I Already Have the Old Database?

### Drop and Recreate:
```sql
DROP DATABASE IF EXISTS outsinc_db;
```

Then import the new complete_schema.sql:
```bash
mysql -u root -p < database/complete_schema.sql
```

### Or Keep Old Data:
If you have important data in the old database:
1. Export your data first:
   ```bash
   mysqldump -u root -p outsinc_db > backup.sql
   ```
2. Then drop and import new schema
3. Manually migrate your important data

---

## Troubleshooting

### "Access denied for user 'root'"
- Username should be: `root`
- Password should be: blank (just press Enter)
- Make sure MySQL is running

### "Database exists"
- Either drop the old database first: `DROP DATABASE outsinc_db;`
- Or the import already succeeded - check the tables!

### "Can't find file"
- Make sure you're in the outsincCA directory
- Full path: `database/complete_schema.sql`
- Try absolute path: `mysql -u root -p < /full/path/to/database/complete_schema.sql`

### Import Takes Long Time
- This is normal! The file creates 34 tables plus inserts data
- Wait for it to complete (usually 10-30 seconds)
- Don't interrupt the process

---

## Default Login Credentials

### Admin Account
```
Username: ADMIN001
Password: Admin123!
```

### Worker Account
```
Username: JANWOR010190
Password: Worker123!
```

⚠️ **CHANGE THESE PASSWORDS IMMEDIATELY AFTER FIRST LOGIN!**

---

## Next Steps

1. ✅ Database imported
2. 🌐 Open http://localhost/outsincCA/
3. 🔐 Login with ADMIN001
4. 🎨 Verify CSS loads properly
5. 🧪 Test basic functionality
6. 📝 Change default passwords
7. 🚀 Start using OUTSINC!

---

## Files Reference

### Use This:
- ✅ `database/complete_schema.sql` - One file, no errors!

### Don't Use (Deprecated):
- ❌ `database/schema.sql` - Causes conflicts with phase1
- ❌ `database/phase1_schema_updates.sql` - Causes conflicts with schema
- ❌ `database/sample_data.sql` - Now included in complete_schema

### Read These:
- 📖 `INSTALL_LOCALHOST.md` - Full installation guide
- 📖 `SETUP_VALIDATION_GUIDE.md` - Complete setup and validation
- 📖 `PHASE2_QUESTIONNAIRE.md` - Plan your Phase 2

---

## Database Configuration

Database credentials are in `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'outsinc_db';
private $username = 'root';
private $password = '';  // Blank for localhost
```

**Already configured for localhost!** No changes needed for XAMPP/WAMP/MAMP.

---

## Quick Commands Cheatsheet

```bash
# Import database
mysql -u root -p < database/complete_schema.sql

# Access MySQL
mysql -u root -p

# Show databases
SHOW DATABASES;

# Use OUTSINC database
USE outsinc_db;

# Show all tables
SHOW TABLES;

# Show table structure
DESCRIBE users;

# Check user accounts
SELECT * FROM users;

# Count products
SELECT COUNT(*) FROM products;

# Export database (backup)
mysqldump -u root -p outsinc_db > backup.sql

# Drop database (careful!)
DROP DATABASE outsinc_db;
```

---

## Support

- 📖 Full docs: `INSTALL_LOCALHOST.md`
- ✅ Validation: `SETUP_VALIDATION_GUIDE.md`
- 🐛 Issues: https://github.com/acesonder/outsincCA/issues
- 📧 Email: support@outsinc.org

---

**Status: READY FOR DEPLOYMENT** ✨

*One file. No conflicts. No errors. Just works.*

# OUTSINC - Complete Setup and Validation Guide

## What Was Fixed

### Problem
The original database setup had three separate SQL files that caused errors when imported:
- `schema.sql` - Base schema with 23 tables
- `phase1_schema_updates.sql` - Enhanced schema with 11 additional tables
- `sample_data.sql` - Demo data

**Issues:**
- `risk_assessments` table defined in BOTH schema.sql and phase1_schema_updates.sql with different structures
- `notifications` table defined in BOTH files with different structures
- Importing all three files caused conflicts and errors

### Solution
Created a single unified file `database/complete_schema.sql` that:
- ✅ Merges all 34 tables without conflicts
- ✅ Uses the enhanced Phase 1 versions of duplicate tables
- ✅ Includes all sample data
- ✅ Can be imported in ONE step with ZERO errors
- ✅ Works with localhost/outsincCA path
- ✅ Configured for root user with blank password

### Path Configuration Fixed
Updated all asset paths to work with subdirectory installation:
- ✅ CSS files now load correctly at localhost/outsincCA/
- ✅ JavaScript files use correct paths
- ✅ Navigation links work properly
- ✅ Auto-detection of BASE_URL for flexible deployment

---

## Quick Setup Guide

### Step 1: Place Files
1. Clone or download the repository to your web server directory
2. The folder should be named `outsincCA` 
3. Example paths:
   - Windows XAMPP: `C:\xampp\htdocs\outsincCA`
   - Windows WAMP: `C:\wamp64\www\outsincCA`
   - Mac MAMP: `/Applications/MAMP/htdocs/outsincCA`
   - Linux: `/var/www/html/outsincCA`

### Step 2: Import Database (ONE FILE!)
#### Using phpMyAdmin (Recommended):
1. Open http://localhost/phpmyadmin
2. Login: Username `root`, Password (blank)
3. Click "Import" tab
4. Choose File: Select `database/complete_schema.sql`
5. Click "Go"
6. ✅ Done! Database created with all tables and sample data

#### Using Command Line:
```bash
mysql -u root -p < database/complete_schema.sql
# Press Enter when asked for password (it's blank)
```

### Step 3: Access OUTSINC
Open your browser and navigate to:
```
http://localhost/outsincCA/
```

You should see the OUTSINC welcome page!

### Step 4: Login
**Admin Account:**
- Username: `ADMIN001`
- Password: `Admin123!`

**Worker Account:**
- Username: `JANWOR010190`
- Password: `Worker123!`

⚠️ **IMPORTANT**: Change these passwords immediately after first login!

---

## Validation Checklist

### ✅ Database Validation

Test that the database was imported correctly:

1. **Check Database Exists:**
   ```sql
   SHOW DATABASES LIKE 'outsinc_db';
   ```
   Should return: `outsinc_db`

2. **Check Table Count:**
   ```sql
   USE outsinc_db;
   SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = 'outsinc_db';
   ```
   Should return: `34`

3. **Check Sample Data:**
   ```sql
   SELECT COUNT(*) FROM products;  -- Should be 12
   SELECT COUNT(*) FROM resources;  -- Should be 8
   SELECT COUNT(*) FROM users;  -- Should be 2
   ```

4. **Check Users Can Login:**
   ```sql
   SELECT user_id, username, role FROM users;
   ```
   Should show:
   - ADMIN001 (admin role)
   - JANWOR010190 (worker role)

### ✅ CSS and Assets Validation

1. **Check Homepage Loads:**
   - Navigate to `http://localhost/outsincCA/`
   - Page should be styled with purple gradients and 3D buttons
   - No broken styling or "unstyled" look

2. **Check CSS Files Load:**
   - Open browser Developer Tools (F12)
   - Go to Network tab
   - Refresh the page
   - Check for `styles.css` - should return 200 OK, not 404

3. **Check Navigation:**
   - Click "About" in the navigation
   - Should navigate to About page with proper styling
   - URL should be `http://localhost/outsincCA/about.php`

4. **Check Images:**
   - Logo should display (if present)
   - No broken image icons

### ✅ Functionality Validation

1. **Test Registration:**
   - Click "Get Support / Create Account"
   - Fill in the registration form:
     - First Name: Test
     - Last Name: User
     - Date of Birth: 1990-01-01
     - Security Question: "What is your favorite color?"
     - Security Answer: Blue
   - Click "Create Account"
   - Note the generated User ID (e.g., TESUSE010190)
   - Try logging in with that User ID and the username displayed

2. **Test Admin Login:**
   - Go back to login page
   - Enter Username: `ADMIN001`
   - Enter Password: `Admin123!`
   - Click "Sign In"
   - Should redirect to admin dashboard

3. **Test Dashboard:**
   - Dashboard should load with proper styling
   - Navigation menu should be visible
   - No PHP errors should be displayed

4. **Test About Page:**
   - Click "About" in navigation
   - Page should load with full styling
   - Mission, vision, and features should be visible

### ✅ Advanced Validation (Optional)

If you want to test deeper functionality:

1. **Test Harm Reduction Orders (TweakEasy):**
   - Login as worker or admin
   - Navigate to Orders section
   - View sample products
   - Try creating a test order

2. **Test Resource Directory:**
   - Navigate to Resources
   - Should see 8 sample resources
   - Click on a resource to view details

3. **Test User Preferences:**
   - Click on username in top right
   - Go to Settings
   - Try changing theme (light/dark)
   - Try changing font size
   - Changes should persist

---

## Troubleshooting

### Problem: "Can't connect to database"

**Solution:**
1. Make sure MySQL is running (check XAMPP/WAMP/MAMP control panel)
2. Verify database credentials in `config/database.php`:
   ```php
   private $username = 'root';
   private $password = '';
   ```
3. Test MySQL connection:
   ```bash
   mysql -u root -p
   # Press Enter for blank password
   SHOW DATABASES;
   ```

### Problem: "Table doesn't exist" errors

**Solution:**
1. Database wasn't imported correctly
2. Drop and recreate:
   ```sql
   DROP DATABASE IF EXISTS outsinc_db;
   ```
3. Re-import `complete_schema.sql`:
   ```bash
   mysql -u root -p < database/complete_schema.sql
   ```

### Problem: "Page not found" - http://localhost/outsincCA/ doesn't work

**Solution:**
1. Check the folder is named exactly `outsincCA` (case-sensitive on Linux/Mac)
2. Make sure it's in the web server document root:
   - XAMPP: `C:\xampp\htdocs\`
   - WAMP: `C:\wamp64\www\`
   - MAMP: `/Applications/MAMP/htdocs/`
3. Restart your web server (Apache)
4. Try `http://127.0.0.1/outsincCA/` instead

### Problem: CSS not loading - page looks unstyled

**Symptoms:**
- White background instead of purple gradient
- Plain text links instead of styled buttons
- No card designs

**Solution:**
1. Open browser Developer Tools (F12)
2. Check Console for errors
3. Check Network tab - look for 404 errors on CSS files
4. Verify BASE_URL auto-detection is working:
   - Check `config/config.php` was updated
   - The asset_url() function should be defined
5. Clear browser cache (Ctrl+F5 or Cmd+Shift+R)
6. Check file permissions:
   ```bash
   chmod 644 assets/css/styles.css
   ```

### Problem: "Invalid username or password"

**Solutions:**
1. Username is case-sensitive: use `ADMIN001` not `admin001`
2. Password is case-sensitive: use `Admin123!` exactly
3. Check users table has data:
   ```sql
   SELECT * FROM users;
   ```
4. If no users, re-import database
5. Clear browser cookies/cache

### Problem: PHP errors displayed

**Solutions:**
1. Check PHP version: `php -v` (must be 7.4+)
2. Check PHP error log
3. Verify all required extensions are installed:
   ```bash
   php -m | grep -E "pdo|mysql|mbstring|json"
   ```
4. Check file permissions

---

## What's Included in complete_schema.sql

### Database Tables (34 total)

**User Management:**
- users
- user_preferences

**Client Management:**
- clients
- intake_sessions
- intake_documents

**Consent Management:**
- consents
- consent_categories
- consent_history

**Assessments:**
- needs_assessments
- assessment_templates
- assessment_responses
- risk_assessments
- safety_plans
- qol_tracking
- qol_assessments

**Case Management:**
- case_notes
- goals
- tasks

**Harm Reduction:**
- products
- inventory
- orders
- order_items

**Resources:**
- resources
- referrals

**Communication:**
- messages
- notifications
- scheduled_tasks

**Public/Community:**
- public_reports
- events
- news_posts
- learning_content

**Audit:**
- audit_log

### Sample Data Included

- **12 Harm Reduction Products**: Needles, naloxone kits, condoms, pipes, cookers, etc.
- **17 Inventory Entries**: Stock levels for main office and outreach van
- **8 Community Resources**: Shelters, clinics, legal services, food banks
- **2 Demo User Accounts**: Admin and Worker accounts for testing
- **3 Events**: Upcoming community events and training sessions
- **2 News Posts**: Welcome message and updates
- **3 Learning Content Items**: Educational materials on harm reduction
- **6 Consent Categories**: Pre-configured consent types
- **1 Assessment Template**: Standard needs assessment

---

## Next Steps

Now that your system is validated and working:

### 1. Change Default Passwords
- Login as ADMIN001
- Go to Account Settings
- Change password to something secure
- Do the same for JANWOR010190

### 2. Customize Your Installation
- Add your own products to the harm reduction catalog
- Add local resources to the directory
- Create additional user accounts for your team
- Customize colors/branding (in assets/css/styles.css)

### 3. Plan Phase 2
- Review `PHASE2_QUESTIONNAIRE.md`
- Answer the 100 yes/no questions
- Share responses with development team
- Receive customized Phase 2 implementation plan

### 4. Set Up for Production (When Ready)
- Get a domain name and hosting
- Set up SSL/HTTPS certificate
- Change database credentials to use strong password
- Disable error display in php.ini
- Set up automated backups
- Configure email for notifications
- Review security settings

---

## Files You Should Use

### ✅ Use These Files:

- `database/complete_schema.sql` - **Use this for database import!**
- `INSTALL_LOCALHOST.md` - Detailed localhost installation guide
- `PHASE2_QUESTIONNAIRE.md` - Phase 2 planning document
- `README.md` - Platform overview
- `QUICK_REFERENCE.md` - Common operations
- `TESTING_CHECKLIST.md` - Complete testing guide

### ❌ Don't Use These (Superseded):

- `database/schema.sql` - Use complete_schema.sql instead
- `database/phase1_schema_updates.sql` - Merged into complete_schema.sql
- `database/sample_data.sql` - Included in complete_schema.sql

---

## Support

If you encounter any issues:

1. **Check this guide** - Most common issues are covered above
2. **Check the documentation**:
   - `INSTALL_LOCALHOST.md` for setup details
   - `TESTING_CHECKLIST.md` for validation steps
   - `QUICK_REFERENCE.md` for common commands
3. **Check logs**:
   - PHP error log
   - Apache error log
   - Browser console (F12)
4. **Get help**:
   - GitHub Issues: https://github.com/acesonder/outsincCA/issues
   - Email: support@outsinc.org

---

## Summary

**What Changed:**
- ✅ Single unified database file eliminates import errors
- ✅ All asset paths work with localhost/outsincCA
- ✅ Database configured for root user with blank password
- ✅ Complete validation checklist provided
- ✅ Phase 2 questionnaire created

**Result:**
You can now set up OUTSINC locally with:
1. One database import (no errors!)
2. Working CSS and styling
3. Functional login and navigation
4. Sample data for testing
5. Clear path to Phase 2

**Status: READY FOR TESTING ON LOCALHOST/OUTSINCA** 🚀

---

*Document Version: 1.0*  
*Created: 2025-12-15*  
*OUTSINC - Meeting people where they're at, walking with them where they want to go.*

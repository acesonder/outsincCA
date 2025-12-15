# OUTSINC Installation Guide - Complete Setup

This guide will walk you through setting up OUTSINC on your local development server (localhost/outsincCA).

## Prerequisites

Before you begin, ensure you have:

- **PHP 7.4 or higher** with the following extensions:
  - PDO
  - PDO_MySQL
  - mbstring
  - openssl
  - json

- **MySQL 5.7+ or MariaDB 10.3+** (phpMyAdmin recommended for local development)

- **Web Server** (Apache with XAMPP, WAMP, MAMP, or standalone Apache/Nginx)

- **Git** (for cloning the repository)

## Quick Start for localhost/outsincCA

### Step 1: Clone the Repository

```bash
cd /your/web/server/document/root  # e.g., C:\xampp\htdocs or /var/www/html
git clone https://github.com/acesonder/outsincCA.git
cd outsincCA
```

**Note**: The repository folder should be named `outsincCA` so the URL will be `http://localhost/outsincCA/`

### Step 2: Import Database (SINGLE FILE - NO ERRORS!)

We've consolidated all database schema and sample data into ONE file to eliminate import errors!

#### Option A: Using phpMyAdmin (Recommended for localhost)

1. Open phpMyAdmin at `http://localhost/phpmyadmin`
2. Log in with:
   - **Username**: `root`
   - **Password**: (leave blank)
3. Click "Import" tab
4. Click "Choose File" and select `database/complete_schema.sql` from your outsincCA folder
5. Click "Go" at the bottom
6. Wait for the import to complete (you'll see a success message)

#### Option B: Using MySQL Command Line

```bash
# Make sure MySQL is running
mysql -u root -p < database/complete_schema.sql
# (Press Enter when prompted for password - it's blank)
```

**That's it!** The complete_schema.sql file will:
- Create the `outsinc_db` database
- Create all 34 tables with proper relationships
- Insert sample data including products, resources, and demo accounts
- Set up everything you need to start using OUTSINC

### Step 3: Database Configuration (Already Done!)

The `config/database.php` file is already configured for localhost with root user and blank password:

```php
private $host = 'localhost';
private $db_name = 'outsinc_db';
private $username = 'root';
private $password = '';
```

No changes needed for standard XAMPP/WAMP/MAMP setups!

### Step 4: Start Your Web Server

**XAMPP Users**:
1. Open XAMPP Control Panel
2. Start Apache
3. Start MySQL
4. Check that both show "Running" status

**WAMP Users**:
1. Start WAMP
2. Wait for the icon to turn green
3. Left-click the icon and ensure all services are started

**MAMP Users**:
1. Start MAMP
2. Click "Start Servers"
3. Wait for Apache and MySQL to show green

### Step 5: Access OUTSINC

Open your web browser and navigate to:

```
http://localhost/outsincCA/
```

You should see the OUTSINC welcome page with login/registration options!

### Step 6: Login with Demo Accounts

**Admin Account**:
- **Username**: `ADMIN001`
- **Password**: `Admin123!`
- ⚠️ **IMPORTANT**: Change this password immediately after first login!

**Worker Account**:
- **Username**: `JANWOR010190`
- **Password**: `Worker123!`
- ⚠️ **IMPORTANT**: Change this password after first login!

## What's Included in complete_schema.sql?

✅ **34 Database Tables**:
- User management (users, user_preferences)
- Client management (clients, intake_sessions, intake_documents)
- Consent management (consents, consent_categories, consent_history)
- Assessments (needs_assessments, risk_assessments, qol_assessments, safety_plans)
- Case management (case_notes, goals, tasks)
- Harm reduction (products, inventory, orders, order_items)
- Resources (resources, referrals)
- Communication (messages, notifications, scheduled_tasks)
- Community (public_reports, events, news_posts, learning_content)
- Audit (audit_log)

✅ **Sample Data**:
- 12 harm reduction products
- 17 inventory entries
- 8 community resources
- 2 demo user accounts (admin & worker)
- 3 upcoming events
- 2 news posts
- 3 learning content items
- 6 consent categories
- 1 assessment template

## Troubleshooting

### Can't Access localhost/outsincCA

**Problem**: Page not found or doesn't load

**Solutions**:
1. Make sure Apache is running (check XAMPP/WAMP/MAMP control panel)
2. Verify the folder is named exactly `outsincCA` (case-sensitive on some systems)
3. Try `http://127.0.0.1/outsincCA/` instead
4. Clear your browser cache and try again

### phpMyAdmin Login Issues

**Problem**: Can't log into phpMyAdmin

**Solutions**:
1. Username: `root`, Password: (blank - just click Go)
2. If that doesn't work, check your XAMPP/WAMP/MAMP documentation for default credentials
3. Make sure MySQL is running

### Database Import Errors

**Problem**: Errors during SQL import

**Solutions**:
1. Make sure you're using `complete_schema.sql` (the unified file)
2. Drop the database first if it exists: `DROP DATABASE IF EXISTS outsinc_db;`
3. Check that your MySQL version is 5.7 or higher
4. Try importing via command line instead of phpMyAdmin
5. Check the phpMyAdmin error log for specific issues

### White Screen / PHP Errors

**Problem**: Blank page or PHP errors displayed

**Solutions**:
1. Check PHP error log (usually in XAMPP/logs or WAMP/logs)
2. Verify PHP version is 7.4 or higher: run `php -v` in command line
3. Ensure all required PHP extensions are enabled
4. Check file permissions (folders should be readable)

### CSS Not Loading

**Problem**: Page loads but looks broken / unstyled

**Solutions**:
1. Check browser console for 404 errors (F12 in most browsers)
2. Verify the path is `http://localhost/outsincCA/` (with trailing slash)
3. Hard refresh the page: Ctrl+F5 (Windows) or Cmd+Shift+R (Mac)
4. Check that `assets/css/styles.css` exists in your outsincCA folder

### Can't Login

**Problem**: Login credentials don't work

**Solutions**:
1. Double-check username (case-sensitive): `ADMIN001`
2. Double-check password (case-sensitive): `Admin123!`
3. Verify database was imported successfully
4. Check that users table has data: `SELECT * FROM users;` in phpMyAdmin
5. Clear browser cookies and try again

## File Permissions

For XAMPP/WAMP/MAMP on Windows:
- Usually no permission changes needed

For Linux/Mac:
```bash
chmod -R 755 /path/to/outsincCA
chmod -R 775 /path/to/outsincCA/uploads
```

## What Database Files to Use

❌ **DO NOT USE** (these cause conflicts when imported separately):
- `database/schema.sql`
- `database/phase1_schema_updates.sql`
- `database/sample_data.sql`

✅ **USE THIS** (all-in-one, no conflicts):
- `database/complete_schema.sql`

The complete_schema.sql file consolidates everything and resolves table conflicts between the original files.

## Next Steps After Installation

1. **Login as Admin** (`ADMIN001` / `Admin123!`)
2. **Change Admin Password**:
   - Click your username in the top right
   - Select "Account Settings" or "Change Password"
   - Set a strong new password
3. **Explore the System**:
   - View the dashboard
   - Check out the sample data (products, resources)
   - Test the harm reduction order system (TweakEasy)
   - Browse available resources
4. **Customize Your Installation**:
   - Add real products and resources
   - Create additional user accounts
   - Customize colors/branding if desired
5. **Read the Documentation**:
   - `README.md` - Platform overview
   - `QUICK_REFERENCE.md` - Common operations
   - `PHASE2_QUESTIONNAIRE.md` - Plan your Phase 2 features

## Security Recommendations for Production

⚠️ **The current setup is for LOCAL DEVELOPMENT ONLY!**

When moving to production:
1. **Change database credentials** - Use a strong password, not blank!
2. **Enable HTTPS** - Get an SSL certificate
3. **Disable error display** - Set `display_errors = Off` in php.ini
4. **Restrict database access** - Don't use root user in production
5. **Set up backups** - Automated daily database backups
6. **Enable firewall** - Restrict unnecessary ports
7. **Update regularly** - Keep PHP, MySQL, and the application updated

## Getting Help

- **Documentation**: Check all .md files in the root directory
- **Issues**: https://github.com/acesonder/outsincCA/issues
- **Email**: support@outsinc.org
- **Testing Guide**: See `TESTING_CHECKLIST.md` for comprehensive tests

## Verification

Run the verification script to check your installation:

```bash
cd /path/to/outsincCA
./verify.sh
```

This will check:
- PHP files are valid
- Database connection works
- Required tables exist
- File structure is correct

---

**Congratulations!** You now have OUTSINC running on `http://localhost/outsincCA/`

**Thank you for installing OUTSINC!**

*Meeting people where they're at, walking with them where they want to go.*

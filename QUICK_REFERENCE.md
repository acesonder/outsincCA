# OUTSINC Quick Reference Guide

Quick commands and reference for working with the OUTSINC platform.

## Quick Start

### 1. Verify Installation
```bash
./verify.sh
```
This runs 10 automated tests to verify all files are in place and valid.

### 2. Test Database Schema
```bash
./test-db-import.sh
```
Validates the database schema can be imported successfully.

### 3. Import Database
```bash
# Create database
mysql -u root -p -e "CREATE DATABASE outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"

# Import schema
mysql -u root -p outsinc_db < database/schema.sql

# Import sample data (optional, for testing)
mysql -u root -p outsinc_db < database/sample_data.sql
```

### 4. Configure Database
Edit `config/database.php`:
```php
private $host = 'localhost';
private $db_name = 'outsinc_db';
private $username = 'your_db_user';
private $password = 'your_db_password';
```

### 5. Access the Platform
Navigate to: `http://localhost` (or your configured domain)

## Common Tasks

### Create Admin User (Method 1: Web Interface)
1. Register through web interface
2. Note your generated user_id
3. Update role in database:
```bash
mysql -u root -p outsinc_db -e "UPDATE users SET role = 'admin' WHERE user_id = 'YOUR_USER_ID';"
```

### Create Admin User (Method 2: Direct Insert)
```bash
# First, generate password hash
php -r "echo password_hash('YourPassword', PASSWORD_DEFAULT);"

# Copy the hash, then:
mysql -u root -p outsinc_db
```

```sql
INSERT INTO users (user_id, username, password_hash, role, first_name, last_name, date_of_birth, status)
VALUES ('ADMIN001', 'ADMIN001', 'YOUR_HASH_HERE', 'admin', 'Admin', 'User', '1990-01-01', 'active');

INSERT INTO user_preferences (user_id) 
SELECT id FROM users WHERE user_id = 'ADMIN001';
```

### Check PHP Syntax
```bash
php -l path/to/file.php
```

### Check All PHP Files
```bash
find . -name "*.php" -type f -exec php -l {} \; | grep -v "No syntax errors"
```

### Reset User Password
```bash
mysql -u root -p outsinc_db
```

```sql
-- Generate new hash first with: php -r "echo password_hash('NewPassword', PASSWORD_DEFAULT);"
UPDATE users SET password_hash = 'NEW_HASH_HERE' WHERE user_id = 'USER_ID';
```

### Unlock Locked Account
```sql
UPDATE users SET failed_login_attempts = 0, locked_until = NULL WHERE user_id = 'USER_ID';
```

## File Locations

### Configuration
- `config/config.php` - Main configuration
- `config/database.php` - Database credentials

### Database
- `database/schema.sql` - Database structure (23 tables)
- `database/sample_data.sql` - Sample data for testing

### Frontend
- `index.php` - Landing/login page
- `about.php` - About page
- `public/dashboard.php` - Main dashboard
- `public/orders/new.php` - Order creation page

### Assets
- `assets/css/styles.css` - Main stylesheet (21KB)
- `assets/css/orders.css` - Order page styles (9KB)
- `assets/js/main.js` - Core JavaScript (18KB)
- `assets/js/auth.js` - Authentication (6KB)
- `assets/js/orders.js` - Ordering system (14KB)

### API Endpoints
- `api/auth/*.php` - Authentication endpoints
- `api/clients/*.php` - Client management
- `api/orders/*.php` - Order management

### Documentation
- `README.md` - Platform overview
- `INSTALL.md` - Installation guide
- `IMPLEMENTATION_SUMMARY.md` - Feature inventory
- `TESTING_CHECKLIST.md` - QA checklist
- `QUICK_REFERENCE.md` - This file

## Database Tables

### Core Tables
- `users` - All system users
- `user_preferences` - User settings
- `clients` - Client profiles
- `audit_log` - Activity tracking

### Assessments
- `needs_assessments` - Client needs
- `risk_assessments` - Risk evaluation
- `qol_tracking` - Quality of life

### Case Management
- `case_notes` - Contact logs
- `goals` - Client goals
- `tasks` - Action items
- `consents` - Privacy consents

### Harm Reduction
- `products` - Supply inventory
- `inventory` - Stock levels
- `orders` - Order requests
- `order_items` - Order contents

### Resources
- `resources` - Service directory
- `referrals` - Client referrals
- `public_reports` - Community reports

### Communication
- `messages` - Internal messaging
- `notifications` - User notifications
- `events` - Community events
- `news_posts` - Announcements

### Learning
- `learning_content` - Educational materials

## Useful SQL Queries

### Check User Count by Role
```sql
SELECT role, COUNT(*) as count 
FROM users 
GROUP BY role;
```

### List All Orders
```sql
SELECT o.order_number, o.order_date, o.status, 
       u.first_name, u.last_name
FROM orders o
JOIN users u ON o.client_id = u.id
ORDER BY o.order_date DESC
LIMIT 10;
```

### Check Inventory Levels
```sql
SELECT p.product_name, i.location, i.quantity, i.min_threshold
FROM inventory i
JOIN products p ON i.product_id = p.id
WHERE i.quantity < i.min_threshold;
```

### Recent Activity Log
```sql
SELECT a.created_at, a.action, u.username
FROM audit_log a
LEFT JOIN users u ON a.user_id = u.id
ORDER BY a.created_at DESC
LIMIT 20;
```

## JavaScript Console Commands

### Theme Management
```javascript
// Switch to dark mode
OUTSINC.setTheme('dark');

// Switch to light mode
OUTSINC.setTheme('light');

// Auto-detect system preference
OUTSINC.setTheme('auto');
```

### Accessibility
```javascript
// Enable high contrast
OUTSINC.setContrast('high');

// Change font size
OUTSINC.setFontSize('large'); // or 'small', 'medium'

// Enable dyslexia-friendly font
OUTSINC.setDyslexiaFriendly(true);
```

### Utilities
```javascript
// Show toast notification
OUTSINC.showToast('Message here', 'success'); // or 'error', 'warning', 'info'

// Open modal
OUTSINC.showModal('modal-id');

// Close modal
OUTSINC.closeModal('modal-id');
```

## Troubleshooting

### Can't Connect to Database
1. Check MySQL is running: `systemctl status mysql`
2. Verify credentials in `config/database.php`
3. Test connection:
```bash
mysql -u your_user -p -h localhost outsinc_db
```

### Permission Denied Errors
```bash
# Fix permissions
chmod -R 755 .
chmod -R 775 uploads/
chown -R www-data:www-data .  # Ubuntu/Debian
# OR
chown -R apache:apache .      # CentOS/RHEL
```

### White Screen / PHP Errors
1. Check PHP error log: `tail -f /var/log/php_errors.log`
2. Enable error display temporarily in `config/config.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### 404 Errors on API Endpoints
1. Check `.htaccess` exists and mod_rewrite is enabled (Apache)
2. For Nginx, verify rewrite rules in config

### Session Issues
1. Check session directory is writable
2. Verify session settings in `php.ini`
3. Clear browser cookies

## Performance Tips

### Enable PHP OPcache
Edit `php.ini`:
```ini
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000
```

### Database Indexing
The schema includes 51 indexes. To verify:
```sql
SELECT TABLE_NAME, INDEX_NAME 
FROM information_schema.STATISTICS 
WHERE TABLE_SCHEMA = 'outsinc_db';
```

### Enable Compression
Add to `.htaccess`:
```apache
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/html text/plain text/xml text/css text/javascript application/javascript
</IfModule>
```

## Backup & Restore

### Backup Database
```bash
mysqldump -u root -p outsinc_db | gzip > backup_$(date +%Y%m%d).sql.gz
```

### Restore Database
```bash
gunzip < backup_20240101.sql.gz | mysql -u root -p outsinc_db
```

### Backup Files
```bash
tar -czf outsinc_backup_$(date +%Y%m%d).tar.gz \
  --exclude='uploads/*' \
  --exclude='.git' \
  .
```

## Security Checklist

- [ ] Change default database credentials
- [ ] Enable SSL/HTTPS
- [ ] Set strong admin password
- [ ] Disable PHP error display in production
- [ ] Configure firewall (allow only 80/443)
- [ ] Set up regular backups
- [ ] Review user accounts regularly
- [ ] Keep PHP and MySQL updated
- [ ] Monitor audit logs for suspicious activity

## Support

- **Documentation**: See README.md, INSTALL.md, IMPLEMENTATION_SUMMARY.md
- **Testing Guide**: See TESTING_CHECKLIST.md
- **Issues**: https://github.com/acesonder/outsincCA/issues
- **Email**: support@outsinc.org

---

**OUTSINC - Lived experience. Real support. No wrong door.**

# OUTSINC Installation Guide

This guide will walk you through setting up OUTSINC on your server.

## Prerequisites

Before you begin, ensure you have:

- **PHP 7.4 or higher** with the following extensions:
  - PDO
  - PDO_MySQL
  - mbstring
  - openssl
  - json

- **MySQL 5.7+ or MariaDB 10.3+**

- **Web Server** (Apache or Nginx)

- **Git** (for cloning the repository)

## Step 1: Clone the Repository

```bash
cd /var/www  # or your web server's document root
git clone https://github.com/acesonder/outsincCA.git
cd outsincCA
```

## Step 2: Set Permissions

```bash
# Set general permissions
chmod -R 755 .

# Create uploads directory if it doesn't exist
mkdir -p uploads
chmod -R 775 uploads

# Ensure web server can write to uploads
chown -R www-data:www-data uploads  # On Ubuntu/Debian
# OR
chown -R apache:apache uploads      # On CentOS/RHEL
```

## Step 3: Create the Database

```bash
# Log into MySQL
mysql -u root -p

# In MySQL prompt, run:
CREATE DATABASE outsinc_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'outsinc_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON outsinc_db.* TO 'outsinc_user'@'localhost';
FLUSH PRIVILEGES;
EXIT;
```

## Step 4: Import Database Schema

```bash
mysql -u outsinc_user -p outsinc_db < database/schema.sql
```

## Step 5: Configure Database Connection

Edit `config/database.php`:

```php
private $host = 'localhost';
private $db_name = 'outsinc_db';
private $username = 'outsinc_user';
private $password = 'your_secure_password';
```

## Step 6: Configure Web Server

### For Apache

Create or edit `.htaccess` in the root directory:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteBase /

    # Redirect to index.php if not a file or directory
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header set X-Content-Type-Options "nosniff"
    Header set X-Frame-Options "SAMEORIGIN"
    Header set X-XSS-Protection "1; mode=block"
</IfModule>

# Disable directory browsing
Options -Indexes

# Protect config files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>
```

### For Nginx

Add to your Nginx configuration:

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/outsincCA;
    index index.php;

    # Security headers
    add_header X-Content-Type-Options "nosniff" always;
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php7.4-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Deny access to hidden files
    location ~ /\. {
        deny all;
    }

    # Deny access to config files
    location ~* /config/ {
        deny all;
    }
}
```

## Step 7: Create Initial Admin User

### Method 1: Through Web Interface (Recommended for testing)

1. Navigate to `http://your-domain.com`
2. Click "Get Support / Create Account"
3. Fill in the registration form
4. Note your generated User ID
5. Update the user role in the database:

```sql
mysql -u outsinc_user -p outsinc_db
UPDATE users SET role = 'admin' WHERE user_id = 'YOUR_USER_ID';
EXIT;
```

### Method 2: Direct Database Insert

```sql
mysql -u outsinc_user -p outsinc_db

INSERT INTO users (
    user_id, username, password_hash, role, 
    first_name, last_name, date_of_birth, status
) VALUES (
    'ADMIN001', 
    'ADMIN001', 
    '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi',  # password is "password"
    'admin', 
    'System', 
    'Administrator', 
    '1990-01-01', 
    'active'
);

INSERT INTO user_preferences (user_id) 
SELECT id FROM users WHERE user_id = 'ADMIN001';

EXIT;
```

**Important:** Change the password after first login!

## Step 8: Test the Installation

1. Navigate to `http://your-domain.com`
2. You should see the OUTSINC welcome page
3. Click "Sign In"
4. Log in with your admin credentials
5. You should be redirected to the dashboard

## Step 9: Security Hardening (Production)

### Update PHP Settings

Edit `php.ini`:

```ini
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log
session.cookie_httponly = On
session.cookie_secure = On  # If using HTTPS
expose_php = Off
```

### Set Up SSL/HTTPS

Use Let's Encrypt for free SSL:

```bash
sudo apt-get install certbot python3-certbot-apache  # For Apache
# OR
sudo apt-get install certbot python3-certbot-nginx   # For Nginx

sudo certbot --apache  # For Apache
# OR
sudo certbot --nginx   # For Nginx
```

### Enable Firewall

```bash
# UFW (Ubuntu/Debian)
sudo ufw allow 80/tcp
sudo ufw allow 443/tcp
sudo ufw enable
```

### Update Configuration Settings

Edit `config/config.php`:

```php
// Set to 0 in production
error_reporting(0);
ini_set('display_errors', 0);

// Update base URL
define('BASE_URL', 'https://your-domain.com');
```

## Step 10: Set Up Backups

### Database Backup Script

Create `scripts/backup.sh`:

```bash
#!/bin/bash
BACKUP_DIR="/var/backups/outsinc"
DATE=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_DIR

# Backup database
mysqldump -u outsinc_user -p'your_password' outsinc_db | gzip > "$BACKUP_DIR/outsinc_db_$DATE.sql.gz"

# Keep only last 30 days
find $BACKUP_DIR -name "*.sql.gz" -mtime +30 -delete
```

### Set Up Cron Job

```bash
crontab -e

# Add this line to backup daily at 2 AM
0 2 * * * /var/www/outsincCA/scripts/backup.sh
```

## Troubleshooting

### Database Connection Errors

- Check MySQL is running: `sudo systemctl status mysql`
- Verify credentials in `config/database.php`
- Ensure database exists: `mysql -u root -p -e "SHOW DATABASES;"`

### Permission Errors

```bash
# Reset permissions
sudo chown -R www-data:www-data /var/www/outsincCA
sudo chmod -R 755 /var/www/outsincCA
sudo chmod -R 775 /var/www/outsincCA/uploads
```

### PHP Errors

- Check PHP error log: `tail -f /var/log/php_errors.log`
- Verify PHP version: `php -v`
- Check loaded modules: `php -m`

### Web Server Not Starting

**Apache:**
```bash
sudo apache2ctl configtest
sudo systemctl status apache2
sudo tail -f /var/log/apache2/error.log
```

**Nginx:**
```bash
sudo nginx -t
sudo systemctl status nginx
sudo tail -f /var/log/nginx/error.log
```

## Next Steps

1. **Configure Email** (for notifications and password resets)
2. **Add Initial Data** (resources, products, etc.)
3. **Train Staff** on using the system
4. **Set Up Monitoring** (uptime monitoring, error tracking)
5. **Create Backups Strategy**
6. **Document Customizations**

## Getting Help

- **Documentation:** [Link to full docs]
- **Issues:** https://github.com/acesonder/outsincCA/issues
- **Email:** support@outsinc.org

## License

[To be determined]

---

**Thank you for installing OUTSINC!**

*Meeting people where they're at, walking with them where they want to go.*

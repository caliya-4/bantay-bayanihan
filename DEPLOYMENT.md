# Bantay Bayanihan - Deployment Guide

## 🚀 Quick Start for Production Deployment

This guide will walk you through deploying the Bantay Bayanihan emergency response system to production.

---

## Prerequisites

Before you begin, ensure you have:
- ✅ PHP 7.4 or higher (8.0+ recommended)
- ✅ MariaDB 10.3+ or MySQL 5.7+
- ✅ Apache 2.4+ or Nginx web server
- ✅ Composer installed
- ✅ SSL certificate (for HTTPS)
- ✅ Git (optional, for version control)

---

## Step 1: Prepare Your Environment

### 1.1 Copy Project Files
```bash
# If using Git
git clone <repository-url>
cd cbantay-bayanihan

# Or upload files directly to your server
```

### 1.2 Install Dependencies
```bash
composer install --no-dev --optimize-autoloader
```

This will install PHPMailer and other required packages.

### 1.3 Configure Environment Variables
```bash
# Copy the example environment file
cp .env.example .env

# Edit the .env file with your production values
nano .env  # or use your preferred editor
```

**Required Environment Variables:**

```env
# Database Configuration
DB_HOST=your_database_host
DB_NAME=bantay_bayanihan
DB_USERNAME=your_db_username
DB_PASSWORD=your_secure_password

# Application Settings
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_SECRET=generate_random_32_char_string

# Google Gemini AI (for chatbot)
GEMINI_API_KEY=your_gemini_api_key
GEMINI_MODEL=gemini-2.5-flash

# Email (optional - for drill confirmations)
SMTP_ENABLED=false
SMTP_HOST=smtp.gmail.com
SMTP_PORT=587
SMTP_USERNAME=your_email@gmail.com
SMTP_PASSWORD=your_app_password
```

**Generate APP_SECRET:**
```bash
openssl rand -base64 32
```

---

## Step 2: Database Setup

### 2.1 Create Database
```sql
CREATE DATABASE bantay_bayanihan CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'bantay_user'@'localhost' IDENTIFIED BY 'your_secure_password';
GRANT ALL PRIVILEGES ON bantay_bayanihan.* TO 'bantay_user'@'localhost';
FLUSH PRIVILEGES;
```

### 2.2 Import Schema
```bash
mysql -u bantay_user -p bantay_bayanihan < db/bantay_bayanihan.sql
```

### 2.3 Migrate Existing Passwords (if applicable)
If you have existing users with plaintext passwords, run this SQL script:

```sql
-- Create a migration script to hash existing passwords
-- This will be handled automatically on first login
-- See login.php for the password migration logic
```

---

## Step 3: File Permissions

### Linux/Unix Systems
```bash
# Set proper ownership
chown -R www-data:www-data /var/www/bantay-bayanihan

# Set directory permissions
find /var/www/bantay-bayanihan -type d -exec chmod 755 {} \;

# Set file permissions
find /var/www/bantay-bayanihan -type f -exec chmod 644 {} \;

# Make uploads directory writable
chmod -R 775 /var/www/bantay-bayanihan/uploads
```

### Windows (XAMPP)
```cmd
# Navigate to your project folder
cd C:\xampp\htdocs\cbantay-bayanihan

# Ensure the folder is readable by Apache
# Usually no additional configuration needed for XAMPP
```

---

## Step 4: Web Server Configuration

### Apache Configuration

Create or edit your Apache virtual host:

```apache
<VirtualHost *:80>
    ServerName yourdomain.com
    DocumentRoot "/var/www/bantay-bayanihan"
    
    <Directory "/var/www/bantay-bayanihan">
        AllowOverride All
        Require all granted
    </Directory>
    
    ErrorLog ${APACHE_LOG_DIR}/bantay_error.log
    CustomLog ${APACHE_LOG_DIR}/bantay_access.log combined
</VirtualHost>
```

### Enable Required Apache Modules
```bash
a2enmod rewrite
a2enmod headers
a2enmod ssl
systemctl restart apache2
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name yourdomain.com;
    root /var/www/bantay-bayanihan;
    index index.php;

    client_max_body_size 10M;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.0-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_index index.php;
    }

    location ~ /\.env {
        deny all;
    }

    location ~ /test-files {
        deny all;
    }

    location /uploads {
        autoindex off;
    }
}
```

---

## Step 5: SSL/HTTPS Setup (REQUIRED for Production)

### Using Let's Encrypt (Free)
```bash
# Install Certbot
sudo apt install certbot python3-certbot-apache

# Get certificate
sudo certbot --apache -d yourdomain.com

# Auto-renewal is configured automatically
# Test renewal with:
sudo certbot renew --dry-run
```

### Manual SSL Certificate
1. Purchase SSL certificate from your provider
2. Install certificate files to your server
3. Configure Apache/Nginx to use SSL
4. Redirect HTTP to HTTPS

---

## Step 6: Security Hardening

### 6.1 Protect Sensitive Files

Add to your `.htaccess` (already included):

```apache
# Block access to .env files
<FilesMatch "^\.env">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block access to hidden files
<FilesMatch "^\.">
    Order allow,deny
    Deny from all
</FilesMatch>

# Block access to test files
<DirectoryMatch "test-files">
    Order allow,deny
    Deny from all
</DirectoryMatch>
```

### 6.2 PHP Configuration (php.ini)

```ini
# Disable error display in production
display_errors = Off
log_errors = On
error_log = /var/log/php_errors.log

# Security settings
expose_php = Off
allow_url_fopen = Off
allow_url_include = Off

# Upload settings
upload_max_filesize = 5M
post_max_size = 10M
max_file_uploads = 3
```

### 6.3 Database Security

```sql
-- Remove anonymous users
DELETE FROM mysql.user WHERE User='';

-- Disable remote root access
DROP USER 'root'@'%';

-- Remove test database
DROP DATABASE IF EXISTS test;
```

---

## Step 7: Testing

### 7.1 Pre-Deployment Checklist

- [ ] Database connection works
- [ ] User registration works
- [ ] User login works
- [ ] Password hashing is active
- [ ] Admin dashboard accessible
- [ ] Responder dashboard accessible
- [ ] Emergency reporting works
- [ ] File uploads work
- [ ] Gemini chatbot responds
- [ ] Email notifications send (if enabled)
- [ ] CSRF tokens validate
- [ ] HTTPS is working
- [ ] .env file is not accessible via browser

### 7.2 Manual Testing

```bash
# Test database connection
php -r "require 'db_connect.php'; echo 'DB Connected';"

# Test environment loading
php -r "require 'config.php'; echo env('DB_HOST');"

# Check file permissions
ls -la uploads/
```

### 7.3 Security Testing

1. Try accessing `https://yourdomain.com/.env` - should return 403
2. Try accessing `https://yourdomain.com/test-files/` - should return 403
3. Check for XSS vulnerabilities in forms
4. Test SQL injection points
5. Verify password hashing in database

---

## Step 8: Go Live! 🎉

### 8.1 Final Steps

1. **Clear any caches:**
   ```bash
   rm -rf cache/*
   ```

2. **Enable production mode:**
   - Verify `APP_ENV=production` in `.env`
   - Verify `APP_DEBUG=false` in `.env`

3. **Monitor logs:**
   ```bash
   tail -f /var/log/apache2/bantay_error.log
   tail -f /var/log/php_errors.log
   ```

4. **Set up monitoring (optional):**
   - Uptime monitoring: UptimeRobot, Pingdom
   - Error tracking: Sentry, Bugsnag
   - Performance: New Relic, Blackfire

### 8.2 Backup Strategy

```bash
# Database backup (daily cron)
0 2 * * * mysqldump -u bantay_user -p'password' bantay_bayanihan | gzip > /backups/db_$(date +\%Y\%m\%d).sql.gz

# File backup (weekly cron)
0 3 * * 0 tar -czf /backups/files_$(date +\%Y\%m\%d).tar.gz /var/www/bantay-bayanihan
```

---

## Troubleshooting

### Database Connection Failed
```
Error: SQLSTATE[HY000] [1045] Access denied
```
**Solution:** Check database credentials in `.env` file

### Permission Denied
```
Error: Permission denied on uploads
```
**Solution:** 
```bash
chmod -R 775 uploads/
chown -R www-data:www-data uploads/
```

### 500 Internal Server Error
**Solution:** 
- Check Apache error logs: `tail -f /var/log/apache2/error.log`
- Check PHP error logs: `tail -f /var/log/php_errors.log`
- Enable debug mode temporarily: `APP_DEBUG=true`

### Gemini AI Not Working
**Solution:**
- Verify `GEMINI_API_KEY` is set in `.env`
- Check if `curl` extension is enabled: `php -m | grep curl`
- Test API key at: https://ai.google.dev/

### Email Not Sending
**Solution:**
- Set `SMTP_ENABLED=true` in `.env`
- Use Gmail App Password, not regular password
- Check SMTP credentials
- Test with: `telnet smtp.gmail.com 587`

---

## Free Hosting Options

### Recommended: Oracle Cloud Free Tier

**Benefits:**
- Always Free: 2 VMs (1/8 OCPU, 1GB RAM each)
- 200 GB block storage
- 10 TB/month outbound data transfer
- Full root access

**Setup:**
1. Sign up at https://www.oracle.com/cloud/free/
2. Create Ubuntu instance
3. Install LAMP stack
4. Deploy application

### Alternative: Heroku

**Benefits:**
- Free tier available
- Easy deployment with Git
- Automatic HTTPS

**Limitations:**
- Dyno sleeps after 30 min inactivity
- No persistent file storage (need S3 for uploads)
- Requires add-on for database (limited free tier)

### Alternative: Railway.app

**Benefits:**
- $5 free credit monthly
- Easy deployment
- Built-in database

**Setup:**
1. Connect GitHub repository
2. Add MariaDB service
3. Deploy PHP application

### Alternative: InfinityFree

**Benefits:**
- Completely free
- PHP and MySQL included
- No ads

**Limitations:**
- Shared hosting
- Limited resources
- No SSH access

---

## Post-Deployment Support

### Monitoring
- Set up uptime monitoring
- Monitor error logs daily
- Check database performance weekly
- Review user feedback

### Maintenance
- Update dependencies monthly: `composer update`
- Backup database daily
- Review and rotate credentials quarterly
- Test disaster recovery plan

### Security Updates
- Monitor PHP security advisories
- Update PHP version regularly
- Patch application vulnerabilities
- Review and update firewall rules

---

## Need Help?

- Check `REQUIREMENTS.md` for system requirements
- Review code documentation
- Check application logs
- Test in development environment first

---

**Congratulations! Your Bantay Bayanihan system is now live and ready to serve your community! 🛡️**

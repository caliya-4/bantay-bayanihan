# InfinityFree Deployment Checklist

## ✅ Pre-Upload Checklist

1. **Create .env file for production:**
   - Copy `.env.example` to `.env`
   - Update these values:
     ```
     APP_ENV=production
     APP_DEBUG=false
     APP_URL=http://your-domain.infinityfreeapp.com
     ```

2. **Install Composer dependencies:**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

3. **Verify these files exist:**
   - ✅ `db/bantay_bayanihan.sql` (database schema)
   - ✅ `.htaccess` (security rules)
   - ✅ `config.php` (configuration loader)
   - ✅ `db_connect.php` (database connection)

---

## 📤 Upload Steps

### Method 1: File Manager (Easiest)
1. Open File Manager in InfinityFree
2. Navigate to `htdocs/`
3. Upload all files from your project
4. **Exclude these:**
   - `.env` (create it on server instead)
   - `test-files/` folder
   - `node_modules/` folder
   - `.git/` folder

### Method 2: FTP Client (Recommended for large files)
1. Download FileZilla: https://filezilla-project.org/
2. Get FTP credentials from InfinityFree:
   - Hostname: `ftpupload.net` or similar
   - Username: (from your account)
   - Password: (from your account)
   - Port: `21`
3. Connect and upload to `/htdocs/`

---

## 🗄️ Database Setup

1. Go to **Control Panel** → **MySQL Databases**
2. Create new database (usually auto-created)
3. Click **"Access phpMyAdmin"**
4. In phpMyAdmin:
   - Click **"Import"** tab
   - Choose file: `db/bantay_bayanihan.sql`
   - Click **"Go"**
   - Wait for import to complete

5. **Note your database credentials:**
   - Host: `sql123.infinityfree.com` (will be shown)
   - Database name: `epiz_12345678_dbname`
   - Username: `epiz_12345678`
   - Password: (shown when you create database)

---

## 🔧 Post-Upload Configuration

1. **Create .env file on server:**
   - Use File Manager to create `.env` in root directory
   - Add these values:
   ```
   DB_HOST=your_db_host (e.g., sql123.infinityfree.com)
   DB_NAME=your_db_name (e.g., epiz_12345678_bantay)
   DB_USERNAME=your_db_user
   DB_PASSWORD=your_db_password
   
   APP_ENV=production
   APP_DEBUG=false
   APP_URL=http://your-domain.infinityfreeapp.com
   
   GEMINI_API_KEY=AIzaSyDxYqeQ2TzrBW10xr5JiN5aA50xVb-Fsh
   GEMINI_MODEL=gemini-2.5-flash
   
   SMTP_ENABLED=true
   SMTP_HOST=smtp.gmail.com
   SMTP_PORT=587
   SMTP_USERNAME=czaile2404@gmail.com
   SMTP_PASSWORD=qlqpegaojkwiwjrp
   SMTP_FROM_EMAIL=czaile2404@gmail.com
   SMTP_FROM_NAME=Bantay Bayanihan
   
   APP_SECRET=change-this-to-random-string
   ```

2. **Set file permissions:**
   - Make `uploads/` directory writable (755 or 777)
   - Use File Manager to set permissions

3. **Test your site:**
   - Visit: `http://your-domain.infinityfreeapp.com`
   - Test login
   - Test registration
   - Test emergency reporting

---

## ⚠️ Known InfinityFree Limitations

1. **No SSH access** - Use File Manager or FTP only
2. **cURL may be limited** - Gemini API might need testing
3. **Max file size: 10MB** - Upload in chunks if needed
4. **No composer on server** - Upload `vendor/` folder
5. **Email sending may be blocked** - SMTP might not work

---

## 🔄 Updating After Deployment

1. Edit files locally on your computer
2. Test on XAMPP
3. Upload changed files via FTP/File Manager
4. No need to re-upload everything!

---

## 🐛 Troubleshooting

**500 Internal Server Error:**
- Check if `.htaccess` uploaded correctly
- Verify `config.php` exists
- Check PHP version (should be 7.4+)

**Database Connection Failed:**
- Verify credentials in `.env`
- Check if database was imported
- Contact InfinityFree support

**Gemini API Not Working:**
- cURL might be disabled
- Check PHP error logs
- Contact support to enable cURL

---

## 📞 Support

- InfinityFree Forum: https://forum.infinityfree.com/
- Knowledge Base: https://infinityfree.net/support
- Email: support@infinityfree.com

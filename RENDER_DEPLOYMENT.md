# Render Deployment Guide for Bantay Bayanihan

## 🚀 Deployment Steps

### Option 1: Deploy Using Render Blueprint (Recommended)

This is the easiest method using the `render.yaml` file included in your repository.

1. **Go to Render Dashboard**
   - Visit https://dashboard.render.com
   - Log in or create an account

2. **Create a New Blueprint Instance**
   - Click "New +" → "Blueprint Instance"
   - Connect your GitHub repository
   - Render will automatically detect the `render.yaml` file

3. **Configure Environment Variables**
   - Render will auto-populate database credentials from the MySQL database
   - You may need to manually set:
     - `APP_SECRET` (Render can generate this)
     - `GEMINI_API_KEY` (if using the chatbot feature)

4. **Deploy**
   - Click "Apply"
   - Render will build and deploy both the web service and MySQL database
   - This may take 5-10 minutes

### Option 2: Manual Docker Deployment

1. **Create a MySQL Database on Render**
   - Go to https://dashboard.render.com
   - Click "New +" → "MySQL"
   - Choose the free plan
   - Note the connection details (host, database name, username, password)

2. **Create a Web Service**
   - Click "New +" → "Web Service"
   - Connect your GitHub repository
   - Set the following:
     - **Build Command**: Leave blank (using Docker)
     - **Start Command**: Leave blank (using Docker)
     - **Environment**: Select "Docker"
     - **Dockerfile Path**: `Dockerfile`

3. **Add Environment Variables**
   ```
   DB_HOST=<from your MySQL service>
   DB_NAME=<from your MySQL service>
   DB_USERNAME=<from your MySQL service>
   DB_PASSWORD=<from your MySQL service>
   APP_ENV=production
   APP_DEBUG=false
   APP_SECRET=<generate a random 32+ character string>
   GEMINI_API_KEY=<your_gemini_api_key> (optional)
   ```

4. **Add Persistent Disk** (for uploads)
   - Go to your web service settings
   - Add a disk with mount path: `/var/www/html/uploads`
   - Size: 1GB (free tier)

5. **Deploy**
   - Click "Create Web Service"
   - Wait for build and deployment (5-10 minutes)

---

## 🔧 Database Setup

### After Deployment: Import Database Schema

Once your MySQL database is deployed, you need to import the schema:

1. **Get Database Connection Details**
   - From your Render MySQL dashboard, note:
     - Host
     - Database name
     - Username
     - Password
     - Port (usually 3306)

2. **Import Schema Using MySQL Client**
   ```bash
   mysql -h <render-mysql-host> -u <username> -p <database-name> < db/bantay_bayanihan.sql
   ```

3. **Or Using MySQL Workbench/PHPMyAdmin**
   - Connect to your Render MySQL database
   - Import the `db/bantay_bayanihan.sql` file

---

## ⚙️ Environment Variables

All required environment variables for Render:

```env
# Database (auto-populated if using render.yaml)
DB_HOST=<mysql-host>
DB_NAME=<database-name>
DB_USERNAME=<username>
DB_PASSWORD=<password>

# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://your-app.onrender.com
APP_SECRET=<random-32-char-string>

# Optional: Google Gemini AI
GEMINI_API_KEY=<your-api-key>
GEMINI_MODEL=gemini-2.5-flash

# Optional: Email (SMTP)
SMTP_ENABLED=false
```

---

## 🐛 Troubleshooting

### Build Fails

**Problem**: Build timeout or fails during Composer install
**Solution**: 
- The Dockerfile has been optimized to use Apache instead of Nginx
- Make sure `.dockerignore` is in place to reduce build size
- Check Render build logs for specific errors

**Problem**: PHP extension installation fails
**Solution**:
- The Dockerfile now includes all necessary extensions: `pdo_mysql`, `mbstring`, `gd`, etc.
- If issues persist, check Render's Docker build logs

### App Crashes on Start

**Problem**: Container exits immediately
**Solution**:
- The Dockerfile now uses `apache2-foreground` command which runs properly
- Check logs in Render dashboard for specific errors

**Problem**: Database connection fails
**Solution**:
- Verify environment variables are set correctly
- Ensure the database schema has been imported
- Check that the MySQL service is running

### 500 Internal Server Error

**Problem**: App loads but returns 500 errors
**Solution**:
- Enable debug mode temporarily: `APP_DEBUG=true`
- Check Render logs for PHP errors
- Verify database connection
- Ensure `.env` file is being loaded (it's auto-generated from env vars)

### 404 Not Found

**Problem**: Routes return 404
**Solution**:
- The `.htaccess` file handles URL rewriting
- Make sure Apache's `mod_rewrite` is enabled (it is in the Dockerfile)

### File Uploads Don't Work

**Problem**: Can't upload files
**Solution**:
- Add a persistent disk in Render dashboard mounted at `/var/www/html/uploads`
- Without a disk, uploads will be lost on redeploy

---

## 📝 Post-Deployment Checklist

- [ ] Database schema imported successfully
- [ ] Environment variables configured
- [ ] App loads at your Render URL
- [ ] User registration works
- [ ] Login works
- [ ] File uploads work (if disk added)
- [ ] Database queries work
- [ ] Admin dashboard accessible
- [ ] Gemini chatbot responds (if API key set)

---

## 🔐 Security Notes

1. **Never commit `.env` file** - Use Render's environment variables
2. **APP_SECRET should be unique** - Generate with: `openssl rand -base64 32`
3. **Use strong database passwords** - Render auto-generates these
4. **Enable HTTPS** - Render provides free SSL automatically
5. **Remove debug mode** - Always set `APP_DEBUG=false` in production

---

## 💾 Free Tier Limitations

Render's free tier has these limitations:
- Web services spin down after 15 minutes of inactivity (causes slow first load)
- 750 hours/month free (enough for one always-on service)
- 1GB disk storage limit
- MySQL database has limited storage and connections

**Workarounds:**
- Use a free external MySQL/MariaDB service (e.g., db4free.net for testing)
- Upgrade to paid plan for production use

---

## 🔄 Updating Your Deployment

After pushing changes to GitHub:

1. **Auto-Deploy** (if enabled)
   - Render will automatically deploy on push to main branch

2. **Manual Deploy**
   - Go to Render dashboard
   - Click "Manual Deploy" → "Deploy latest commit"

3. **Clear Cache** (if needed)
   - SSH into your service or redeploy

---

## 📞 Support

If you encounter issues:
- Check Render build logs in the dashboard
- Review application logs in Render dashboard
- Test database connection independently
- Verify all environment variables are set

---

**Good luck with your deployment! 🛡️**

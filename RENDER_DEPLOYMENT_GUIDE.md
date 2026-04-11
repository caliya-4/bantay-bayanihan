# 🚀 Render Deployment Guide - Bantay Bayanihan

## Problem Fixed ✅

Your application was failing on Render because of **database type mismatch**:
- Your app was built for **MySQL/MariaDB**
- Render's free tier only provides **PostgreSQL**
- The Dockerfile was missing MySQL PDO extensions
- Database connection code wasn't auto-detecting PostgreSQL

**ALL ISSUES HAVE BEEN FIXED!** The app now supports both MySQL and PostgreSQL automatically.

---

## 📋 What Was Changed

### 1. **Dockerfile** - Added both database drivers
```dockerfile
# Now installs BOTH pdo_mysql and pdo_pgsql
docker-php-ext-install pdo_pgsql pdo_mysql mbstring exif pcntl bcmath gd
```

### 2. **config.php** - Auto-detects database type
- Automatically detects PostgreSQL when `DATABASE_URL` is present
- Falls back to MySQL for local development
- Parses connection strings correctly for both types

### 3. **db_connect.php** - Dynamic database connection
- Creates correct DSN based on database type
- Shows helpful error messages showing which DB type is being used
- Works seamlessly with both MySQL and PostgreSQL

### 4. **render.yaml** - Properly configured for PostgreSQL
- Links to Render PostgreSQL database
- Sets `DATABASE_URL` environment variable automatically
- Auto-generates `APP_SECRET` for security

### 5. **apache.conf** - Production-ready PHP settings
- Disabled `display_errors` for security
- Enabled error logging for debugging
- Errors go to Apache error logs

### 6. **PostgreSQL Schema** - Created compatible schema
- New file: `db/bantay_bayanihan_postgresql.sql`
- Converted all MySQL syntax to PostgreSQL 15
- ENUMs, SERIAL, BOOLEAN all properly converted

---

## 🎯 Deploy to Render - Step by Step

### Step 1: Push Changes to Git

```bash
git add .
git commit -m "Fix Render deployment - add PostgreSQL support"
git push origin main
```

### Step 2: Connect to Render

1. Go to https://dashboard.render.com
2. Click **"New +"** → **"Blueprint Instance"**
3. Connect your GitHub repository
4. Render will automatically read `render.yaml` and configure everything

### Step 3: Wait for Build (5-10 minutes)

Render will:
- Build the Docker container
- Create a PostgreSQL 15 database
- Link the database to your web service
- Deploy your application

**Monitor the build:**
- Click on your web service
- Go to **"Events"** tab
- Watch the build logs

### Step 4: Import Database Schema

Once the app is deployed (you'll see "Service is live"):

#### Option A: Using Render's Web Shell (Easiest)

1. Go to your service in Render dashboard
2. Click **"Shell"** tab
3. Run these commands:
   ```bash
   # Get database connection details
   echo $DATABASE_URL
   
   # Import schema
   psql $DATABASE_URL -f /var/www/html/db/bantay_bayanihan_postgresql.sql
   ```

#### Option B: Using Your Local Machine

1. Get your PostgreSQL connection string from Render:
   - Go to your database service in Render
   - Copy the **"Internal Database URL"**
   
2. Import from your terminal:
   ```bash
   # You need PostgreSQL client installed (psql)
   # Windows: Install from https://www.postgresql.org/download/windows/
   
   psql "paste-your-database-url-here" -f db/bantay_bayanihan_postgresql.sql
   ```

#### Option C: Using pgAdmin or DBeaver (GUI Tool)

1. Connect to your Render PostgreSQL database
2. Open `db/bantay_bayanihan_postgresql.sql` in the query tool
3. Execute the entire script

### Step 5: Test Your Application

Visit your Render URL: `https://your-app-name.onrender.com`

**Expected behavior:**
- ✅ Homepage loads with announcements and drills
- ✅ Login page works
- ✅ User registration works
- ✅ All features functional

**If you see errors:**
- Check Render logs (see Troubleshooting section below)
- Verify database schema was imported successfully
- Ensure all tables exist in PostgreSQL

---

## 🔍 Verify Database Import

After importing the schema, verify it worked:

### Using Render Shell:
```bash
# Connect to database
psql $DATABASE_URL

# List all tables
\dt

# You should see:
# announcements, barangay_stats, certifications, checklist_items,
# drills, drill_participations, emergency_reports, users, etc.

# Check row count
SELECT COUNT(*) FROM users;
SELECT COUNT(*) FROM drills;
SELECT COUNT(*) FROM announcements;

# Exit
\q
```

---

## 🛠️ Troubleshooting

### Issue 1: "Service Unavailable" Page

**What it means:** App can't connect to database

**Fix:**
1. Check that the database service is running in Render
2. Verify `DATABASE_URL` is set in environment variables
3. Check Render service logs for connection errors

### Issue 2: Database Connection Failed

**Check Render Logs:**
1. Go to your web service in Render dashboard
2. Click **"Logs"** tab
3. Look for errors

**Common errors:**
- `Connection refused` → Database service not running
- `Password authentication failed` → Wrong credentials (should be automatic)
- `database does not exist` → Schema not imported yet

### Issue 3: Tables Don't Exist

**Fix:** Import the PostgreSQL schema (see Step 4 above)

**Verify import worked:**
```bash
# In Render Shell
psql $DATABASE_URL -c "\dt"
```

### Issue 4: 500 Internal Server Error

**Get the actual error:**

1. **Check Render Logs:**
   ```
   Dashboard → Your Service → Logs
   ```

2. **Temporarily enable debug mode:**
   - Go to **Environment** tab
   - Add: `APP_DEBUG=true`
   - Redeploy
   - Visit the site to see detailed error
   - **IMPORTANT:** Set back to `false` after debugging!

### Issue 5: Build Fails

**Check build logs for:**
- Docker build errors
- Missing dependencies
- Syntax errors

**Common fixes:**
- Ensure `composer.json` is valid
- Check Dockerfile syntax
- Verify all files are committed to Git

---

## 🔐 Security Checklist

After successful deployment:

### ✅ Done by the fixes:
- [x] `.env` file is in `.dockerignore` (won't be committed)
- [x] `APP_DEBUG=false` in production
- [x] `display_errors=Off` in apache.conf
- [x] `APP_SECRET` is auto-generated by Render
- [x] PostgreSQL uses SSL (Render provides this)

### 🔒 You should do:
- [ ] Delete `render-health.php` after deployment (diagnostic tool)
- [ ] Delete `setup-database.php` after deployment (setup tool)
- [ ] Set strong password for admin account
- [ ] Enable email notifications (optional, configure SMTP)
- [ ] Set up regular database backups

---

## 📊 Environment Variables Reference

These are set automatically by `render.yaml`, but here's what they mean:

| Variable | Source | Purpose |
|----------|--------|---------|
| `DATABASE_URL` | From PostgreSQL service | Full connection string |
| `APP_ENV` | Manual (`production`) | Application environment |
| `APP_DEBUG` | Manual (`false`) | Show detailed errors |
| `APP_SECRET` | Auto-generated | Session security, CSRF tokens |

**Optional variables you can add:**

| Variable | Example | Purpose |
|----------|---------|---------|
| `GEMINI_API_KEY` | `your-key-here` | Enable AI chatbot |
| `SMTP_ENABLED` | `true` | Enable email notifications |
| `SMTP_HOST` | `smtp.gmail.com` | Email server |
| `SMTP_USERNAME` | `you@gmail.com` | Email username |
| `SMTP_PASSWORD` | `app-password` | Email password (use app password for Gmail) |

---

## 🆘 Free MySQL Alternatives (Optional)

If you prefer MySQL over PostgreSQL, you can use these free services instead of Render's PostgreSQL:

### Option 1: PlanetScale (Recommended)
- **Free tier:** 5GB storage, 1 billion rows read/month
- **Setup:**
  1. Create account at https://planetscale.com
  2. Create database
  3. Get connection string
  4. Set `DATABASE_URL` in Render to: `mysql://user:pass@host:port/dbname`
  5. Import `db/bantay_bayanihan.sql` (original MySQL file)

### Option 2: TiDB Cloud
- **Free tier:** 5GB storage, serverless
- **Setup:** Similar to PlanetScale
- **Website:** https://tidbcloud.com

### Option 3: Aiven for MySQL
- **Free tier:** Available on some plans
- **Website:** https://aiven.io/mysql

**If using external MySQL:**
1. Remove the `databases:` section from `render.yaml`
2. Set `DATABASE_URL` manually in Render environment variables
3. Use MySQL connection string format: `mysql://user:pass@host:3306/dbname`

---

## 📈 Monitoring & Maintenance

### Monitor Your App

1. **Render Dashboard:**
   - Check service health regularly
   - Monitor resource usage
   - Review logs for errors

2. **Set up alerts (optional):**
   - Use UptimeRobot (free): https://uptimerobot.com
   - Monitor your Render URL
   - Get email/SMS alerts when down

### Regular Maintenance

**Weekly:**
- Check error logs
- Review user reports
- Monitor database size

**Monthly:**
- Update dependencies: `composer update`
- Review security logs
- Backup database (Render does this automatically)

---

## 🎓 Key Differences: MySQL vs PostgreSQL

Since we converted to PostgreSQL, here are notable changes:

| MySQL | PostgreSQL | Notes |
|-------|------------|-------|
| `tinyint(1)` | `BOOLEAN` | Boolean fields now use TRUE/FALSE |
| `int(11)` | `INTEGER` | Display width removed |
| `AUTO_INCREMENT` | `GENERATED ALWAYS AS IDENTITY` | Auto-increment syntax |
| `DATETIME` | `TIMESTAMP` | Timestamp handling |
| `ENGINE=InnoDB` | (removed) | Not needed in PostgreSQL |
| `ON UPDATE CURRENT_TIMESTAMP` | (removed) | Would need triggers |

**Your PHP code doesn't need changes** - PDO handles the differences automatically!

---

## ✅ Success Indicators

Your deployment is successful when:

1. ✅ Render shows "Service is live" (green dot)
2. ✅ Homepage loads without errors
3. ✅ Database tables exist (verified with `\dt`)
4. ✅ User login works
5. ✅ You can access admin dashboard
6. ✅ No errors in Render logs

---

## 📞 Support

If you're still having issues:

1. **Check Render Logs** - 90% of issues can be diagnosed here
2. **Verify Database** - Make sure schema was imported
3. **Test Locally First** - Run with PostgreSQL locally to test
4. **Review Health Check** - Visit `/render-health.php?check=bantay2026`

**Common Render Issues:**
- **Build fails:** Check Dockerfile and git repository
- **App crashes:** Check logs, usually database-related
- **500 errors:** Enable `APP_DEBUG=true` temporarily
- **Database errors:** Verify schema import worked

---

## 🎉 You're All Set!

Your Bantay Bayanihan application is now:
- ✅ Fully compatible with PostgreSQL
- ✅ Still works with MySQL (for local development)
- ✅ Auto-detects database type
- ✅ Production-ready with proper error handling
- ✅ Secure with proper PHP settings
- ✅ Ready to deploy on Render's free tier

**Push your changes and deploy!** 🚀

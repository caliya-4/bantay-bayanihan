# Render 500 Error Troubleshooting Guide

## 🔍 What's Happening

You're getting a **500 Internal Server Error**, which means:
- ✅ Docker build succeeded
- ✅ Apache is running
- ❌ Something in your PHP code is crashing

## 🛠️ Step-by-Step Fix

### Step 1: View the Actual Error (CRITICAL)

The generic Apache error hides the real PHP issue. Do this:

1. **Go to Render Dashboard**: https://dashboard.render.com
2. **Click on your web service**
3. **Click "Logs" tab**
4. **Look for PHP errors** - they'll show something like:
   - `PHP Fatal error: Uncaught PDOException: SQLSTATE[HY000]...`
   - `PHP Warning: require_once(): Failed opening...`
   - `Database connection failed...`

**OR** use the health check tool I created:

1. Visit: `https://your-app.onrender.com/render-health.php?check=bantay2026`
2. This will show you exactly what's wrong
3. It checks:
   - PHP version and extensions
   - Environment variables
   - Database connection
   - File permissions
   - Missing tables

---

## 🎯 Most Common Causes & Fixes

### Issue #1: Database Not Connected (90% likely)

**Symptoms:**
- Error mentions `PDO`, `SQLSTATE`, or `database`
- Health check shows "Database credentials not set"

**Fix:**

1. **Check Environment Variables in Render:**
   - Go to Render Dashboard → Your Service → Environment
   - Make sure these are set:
     ```
     DB_HOST=<your-mysql-host>
     DB_NAME=<your-database-name>
     DB_USERNAME=<your-db-user>
     DB_PASSWORD=<your-db-password>
     ```

2. **If using render.yaml blueprint:**
   - The database should be auto-created
   - Wait 2-3 minutes after deployment for MySQL to initialize
   - Check the database service status in Render dashboard

3. **If database credentials are correct but still failing:**
   - The database might not have imported yet
   - Visit: `https://your-app.onrender.com/setup-database.php?setup=true`
   - Click "Import Database Schema"

---

### Issue #2: Missing PHP Extensions

**Symptoms:**
- Error mentions `call to undefined function`
- Health check shows extensions missing

**Fix:**
- The Dockerfile has been updated to include all required extensions
- Redeploy to apply the changes

---

### Issue #3: .htaccess Causing Redirect Loop

**Symptoms:**
- Error happens immediately, even before PHP loads
- Logs mention "redirect loop" or "too many redirects"

**Fix:**
- The `.htaccess` has been updated to prevent this
- Redeploy with the new changes

---

### Issue #4: File Permissions

**Symptoms:**
- Error mentions `Permission denied`
- Logs show `fopen()` or `require_once()` failures

**Fix:**
- The Dockerfile now sets proper permissions
- Redeploy to apply

---

## 📋 Quick Diagnostic Steps

### Option A: Use the Health Check Tool

```
https://your-app.onrender.com/render-health.php?check=bantay2026
```

This will tell you:
- ✓ If PHP is working
- ✓ If environment variables are set
- ✓ If database connection works
- ✓ If tables exist
- ✓ File permissions status

### Option B: Check Render Logs

1. Dashboard → Your Service → **Logs** tab
2. Look for lines starting with:
   - `PHP Fatal error:`
   - `PHP Warning:`
   - `PDOException:`
   - `Database Connection Error:`

### Option C: Test Database Connection Manually

If you have MySQL client installed locally:

```bash
mysql -h <render-mysql-host> -u <username> -p <database-name>
```

If this fails, your database credentials are wrong or the database isn't running.

---

## 🔄 After Making Fixes

### To Redeploy:

1. **Commit your changes:**
   ```bash
   git add .
   git commit -m "Fix Render 500 error - update Dockerfile and configs"
   git push origin main
   ```

2. **Render will auto-deploy** (if auto-deploy is enabled)
   - Or manually trigger: Dashboard → Manual Deploy → Deploy latest commit

3. **Wait 3-5 minutes** for build and deployment

4. **Test again:**
   - Visit `https://your-app.onrender.com/`
   - Or check health: `https://your-app.onrender.com/render-health.php?check=bantay2026`

---

## 🚨 Still Not Working?

### Collect This Information:

1. **From Render Logs** (copy the full error):
   - Dashboard → Logs tab
   - Copy any PHP errors or stack traces

2. **From Health Check** (screenshot or copy):
   - Visit `https://your-app.onrender.com/render-health.php?check=bantay2026`
   - Share what it shows

3. **Environment Variables** (confirm these are set):
   - DB_HOST: ?
   - DB_NAME: ?
   - DB_USERNAME: ?
   - DB_PASSWORD: ?

4. **Database Status:**
   - Is the MySQL service running in Render?
   - Has the schema been imported?

---

## 🎯 Emergency Fallback: Simple Test Page

If you want to verify Apache + PHP are working (without database):

Create a file called `test.php`:

```php
<?php
phpinfo();
?>
```

Commit and deploy. Then visit `https://your-app.onrender.com/test.php`

If this works, Apache + PHP are fine - the issue is definitely the database connection.

**DELETE this file after testing** - it exposes sensitive server information!

---

## ✅ What I've Fixed in This Update

1. **Enabled PHP error display** - Now you'll see actual errors instead of generic 500
2. **Fixed .htaccess redirect loop** - Added localhost exception
3. **Created health check tool** - `render-health.php` diagnoses all issues
4. **Improved error messages** - Database errors now log properly

---

## 📞 Next Steps

1. **Check Render logs** for the actual PHP error
2. **Run the health check**: `https://your-app.onrender.com/render-health.php?check=bantay2026`
3. **Verify database credentials** are set in Render environment variables
4. **Import database schema** if tables don't exist
5. **Redeploy** with the updated Dockerfile and .htaccess

Share the specific error you find, and I can help you fix it!

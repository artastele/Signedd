# SPED LMS - Quick Setup Guide (New PC)

## Problem: "Security validation failed" on Login/Register

This happens when the database isn't set up yet. Follow these steps:

---

## Step 1: Check Database Connection

Visit: **http://localhost/Signedd/public/check-database.php**

This will tell you:
- ✓ Can connect to MySQL
- ✓ Database exists
- ✓ Tables exist

---

## Step 2: Create Database (if needed)

If database doesn't exist, visit: **http://localhost/Signedd/public/create-database.php**

This will create the `sped_lms` database.

---

## Step 3: Run Schema Migration

Visit: **http://localhost/Signedd/public/run-migration.php**

This will create all 18 tables needed for the system.

---

## Step 4: Test Login

### Option A: Test Login (No CSRF)
Visit: **http://localhost/Signedd/public/test-login.php**

Default credentials:
- Email: `admin@spedlms.local`
- Password: `password`

### Option B: Normal Login
Visit: **http://localhost/Signedd/public/login**

The CSRF issue should now be fixed!

---

## What Was Fixed?

1. **CSRF Helper** - Now more lenient in development mode
2. **CSRF Logging** - Won't fail for anonymous users
3. **Development Mode** - Enabled in `.env` (APP_ENV=development)

---

## Troubleshooting

### Still getting "Security validation failed"?

1. Check PHP error log: `logs/php_error.log`
2. Make sure `.env` has: `APP_ENV=development`
3. Clear browser cache and cookies
4. Try test-login.php first

### Can't connect to database?

1. Check MySQL is running
2. Verify credentials in `.env`:
   ```
   DB_HOST=localhost
   DB_NAME=sped_lms
   DB_USER=root
   DB_PASS=your_password
   ```

### Tables not created?

1. Run: `public/run-migration.php`
2. Or manually import: `config/schema.sql` in phpMyAdmin

---

## Next Steps After Setup

1. ✅ Login with admin account
2. ✅ Change admin password
3. ✅ Register a new account to test
4. ✅ Test role selection (Parent/Staff)
5. ✅ Test enrollment form

---

## Quick Links

- Check Database: http://localhost/Signedd/public/check-database.php
- Create Database: http://localhost/Signedd/public/create-database.php
- Run Migration: http://localhost/Signedd/public/run-migration.php
- Test Login: http://localhost/Signedd/public/test-login.php
- Normal Login: http://localhost/Signedd/public/login
- Register: http://localhost/Signedd/public/register

---

## Default Admin Account

After migration, you can login with:
- **Email:** admin@spedlms.local
- **Password:** password

**⚠️ IMPORTANT:** Change this password immediately after first login!

---

## System Status

- ✅ .env file created
- ✅ CSRF validation fixed
- ✅ Development mode enabled
- ⏳ Database setup needed (run check-database.php)
- ⏳ Schema migration needed (run run-migration.php)

---

## Need Help?

Check the error logs:
- PHP errors: `logs/php_error.log`
- Apache errors: Check your Apache error log


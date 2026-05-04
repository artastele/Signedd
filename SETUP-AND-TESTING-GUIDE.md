# SPED LMS - Setup and Testing Guide

## Prerequisites

- PHP 7.4 or higher
- MySQL 8.0 or higher
- Apache with mod_rewrite enabled
- Composer
- Gmail account (for email sending)
- Google Cloud Console account (for Google Sign-In)

---

## Step 1: Database Setup

1. Create a new MySQL database:
```sql
CREATE DATABASE sped_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

2. The schema will be automatically applied on first run via SchemaManager

---

## Step 2: Environment Configuration

1. Copy `.env.example` to `.env`:
```bash
cp .env.example .env
```

2. Update `.env` with your actual values:
```env
# Database
DB_HOST=localhost
DB_NAME=sped_lms
DB_USER=root
DB_PASS=your_password

# Email (Gmail)
MAIL_HOST=smtp.gmail.com
MAIL_PORT=587
MAIL_USERNAME=your_email@gmail.com
MAIL_PASSWORD=your_app_password
MAIL_FROM_ADDRESS=your_email@gmail.com
MAIL_FROM_NAME="SPED LMS"

# Google OAuth
GOOGLE_CLIENT_ID=your_client_id
GOOGLE_CLIENT_SECRET=your_client_secret
GOOGLE_REDIRECT_URI=http://localhost/Signedd/public/auth/google/callback

# Application
APP_URL=http://localhost/Signedd/public
```

---

## Step 3: Install Dependencies

Run Composer to install PHPMailer and Google API Client:

```bash
composer install
```

This will install:
- `phpmailer/phpmailer` - For sending emails
- `google/apiclient` - For Google Sign-In

---

## Step 4: Gmail App Password Setup

1. Go to your Google Account settings
2. Enable 2-Factor Authentication
3. Go to Security → App Passwords
4. Generate a new app password for "Mail"
5. Copy the 16-character password
6. Use this in `.env` as `MAIL_PASSWORD`

---

## Step 5: Google OAuth Setup

Follow the detailed guide in `GOOGLE-OAUTH-SETUP.md`:

1. Create Google Cloud project
2. Enable Google+ API
3. Create OAuth 2.0 credentials
4. Add authorized redirect URI
5. Copy Client ID and Secret to `.env`

---

## Step 6: Apache Configuration

Ensure `.htaccess` is working:

1. Check if `mod_rewrite` is enabled:
```bash
# On Ubuntu/Debian
sudo a2enmod rewrite
sudo service apache2 restart
```

2. Verify `.htaccess` exists in `/public/` folder

3. Ensure Apache allows `.htaccess` overrides in your virtual host config

---

## Step 7: File Permissions

Set proper permissions for upload directories:

```bash
chmod 755 public/uploads
chmod 755 public/uploads/enrollment
chmod 755 public/uploads/role_verification
chmod 755 logs
```

---

## Testing Guide

### Test 1: Email/Password Registration with OTP

1. Go to `/register`
2. Fill in all fields:
   - First Name: John
   - Last Name: Doe
   - Email: test@example.com
   - Contact: 09123456789
   - Password: Test@123
3. Click "Register"
4. **Expected:** Redirected to OTP verification page
5. Check email for 6-digit code
6. Enter OTP code
7. **Expected:** Email verified, welcome email sent, redirected to dashboard

### Test 2: OTP Resend

1. On verification page, wait 60 seconds
2. Click "Resend Code"
3. **Expected:** New OTP sent, countdown timer starts

### Test 3: OTP Expiration

1. Wait 10 minutes after receiving OTP
2. Try to verify with old code
3. **Expected:** "Verification code expired" error

### Test 4: OTP Attempt Limit

1. Enter wrong OTP 3 times
2. **Expected:** "Too many attempts" error
3. Must request new OTP

### Test 5: Google Sign-In (New User)

1. Go to `/login`
2. Click "Sign in with Google"
3. Authorize with Google account
4. **Expected:** 
   - Account created automatically
   - Email auto-verified
   - Welcome email sent
   - Redirected to dashboard
   - Must select role

### Test 6: Google Sign-In (Existing Email)

1. Register normally with email: existing@example.com
2. Verify email
3. Logout
4. Click "Sign in with Google" using same email
5. **Expected:** Google account linked to existing account

### Test 7: Email Verification Enforcement

1. Register new account
2. Don't verify email
3. Try to access `/dashboard` directly
4. **Expected:** Redirected to `/auth/verify-email`

### Test 8: Login Without Verification

1. Register account but don't verify
2. Logout
3. Try to login with email/password
4. **Expected:** 
   - Login successful
   - New OTP sent
   - Redirected to verification page

### Test 9: Admin Login Logs

1. Login as admin
2. Go to `/admin/login-logs`
3. **Expected:** 
   - See all login attempts
   - Filter by status (success/failure)
   - Search by email
   - View statistics (24h totals)

### Test 10: Admin Activity Logs

1. Login as admin
2. Go to `/admin/activity-logs`
3. **Expected:**
   - See all user activities
   - Filter by action type
   - Search by user/description
   - View user details

---

## Common Issues and Solutions

### Issue: OTP email not received

**Solutions:**
- Check spam folder
- Verify Gmail app password is correct
- Check `logs/` folder for PHP errors
- Test email sending with a simple script

### Issue: Google Sign-In not working

**Solutions:**
- Run `composer install` to install Google API client
- Verify Client ID and Secret in `.env`
- Check redirect URI matches exactly
- See `GOOGLE-OAUTH-SETUP.md` for detailed troubleshooting

### Issue: 404 errors on routes

**Solutions:**
- Check if `.htaccess` exists in `/public/`
- Verify `mod_rewrite` is enabled
- Check Apache virtual host allows `.htaccess` overrides

### Issue: Database migrations not running

**Solutions:**
- Check database connection in `.env`
- Manually run `config/schema.sql` in phpMyAdmin
- Check `db_version` table for applied migrations

### Issue: Session timeout too fast

**Solutions:**
- Increase `TIMEOUT_DURATION` in `SessionMiddleware.php`
- Default is 900 seconds (15 minutes)

---

## Security Checklist

- [ ] Change default admin password
- [ ] Use strong passwords (8+ chars, uppercase, number, special char)
- [ ] Enable HTTPS in production
- [ ] Set `session.cookie_secure = 1` in production
- [ ] Keep `.env` file secure (never commit to git)
- [ ] Regularly review login logs for suspicious activity
- [ ] Monitor activity logs for unauthorized actions
- [ ] Keep dependencies updated (`composer update`)
- [ ] Backup database regularly

---

## Next Steps

After successful testing:

1. Create admin account
2. Test role approval workflow
3. Proceed to Process 1: Parent Enrollment Submission
4. Continue with remaining DFD processes

---

## Support

For issues or questions:
- Check `NOTIFICATION-TROUBLESHOOTING.md` for notification issues
- Check `GOOGLE-OAUTH-SETUP.md` for OAuth issues
- Review error logs in `logs/` folder
- Check Apache error log for server issues

# Notification System Troubleshooting Guide

## Steps to Debug Notification Issues

### 1. Check if the notifications table exists

Run this SQL query in phpMyAdmin or your MySQL client:

```sql
SHOW TABLES LIKE 'notifications';
```

If the table doesn't exist, run the schema migration:
- Go to your application URL (it should auto-run migrations on first load)
- OR manually run: `config/schema.sql` in phpMyAdmin

### 2. Verify the table structure

```sql
DESCRIBE notifications;
```

Should show columns: id, user_id, type, title, message, data, is_read, created_at

### 3. Check if notifications are being created

After rejecting an application, run:

```sql
SELECT * FROM notifications ORDER BY created_at DESC LIMIT 5;
```

If no notifications appear, the problem is in the creation logic.

### 4. Check browser console for JavaScript errors

1. Open your browser's Developer Tools (F12)
2. Go to the Console tab
3. Look for errors related to notifications
4. You should see logs like:
   - "Loading notifications from: /notifications/get"
   - "Response status: 200"
   - "Notification data: {success: true, ...}"

### 5. Test the notification endpoint directly

Visit in your browser (while logged in):
```
http://your-site.com/notifications/get
```

Should return JSON like:
```json
{
  "success": true,
  "notifications": [...],
  "unreadCount": 0
}
```

### 6. Use the debug script

Visit: `http://your-site.com/debug-notifications.php`

This will show:
- If the notifications table exists
- Table structure
- All notifications in the database
- Test notification creation

### 7. Common Issues and Fixes

#### Issue: Notification bell doesn't appear
**Fix:** Make sure topbar.php is included in your view:
```php
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
```

#### Issue: JavaScript not loading
**Fix:** Check if app.js is included in footer.php:
```html
<script src="/js/app.js"></script>
```

#### Issue: Notifications created but not showing
**Fix:** Check browser console for AJAX errors. Verify the route exists in routes/web.php:
```php
route('GET', '/notifications/get', 'NotificationController', 'getNotifications');
```

#### Issue: 404 error on /notifications/get
**Fix:** Make sure .htaccess is configured correctly and mod_rewrite is enabled in Apache.

### 8. Manual Test

To manually create a test notification, run this SQL:

```sql
INSERT INTO notifications (user_id, type, title, message, data, is_read, created_at)
VALUES (
    1,  -- Replace with your user ID
    'test',
    'Test Notification',
    'This is a test notification',
    '{"test": true}',
    FALSE,
    NOW()
);
```

Then refresh your dashboard and check if the notification bell shows a badge.

### 9. Check Session

Make sure you're logged in and session has user_id:
```php
<?php
session_start();
var_dump($_SESSION);
?>
```

Should show: `user_id`, `user_name`, `role`, etc.

### 10. Enable PHP Error Reporting

Add to the top of `public/index.php`:
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

This will show any PHP errors that might be preventing notifications from working.

---

## Quick Checklist

- [ ] Notifications table exists in database
- [ ] Schema migration v4 has run
- [ ] Topbar is included in all views
- [ ] JavaScript console shows no errors
- [ ] /notifications/get endpoint returns valid JSON
- [ ] User is logged in with valid session
- [ ] NotificationModel.php exists in app/Models/
- [ ] NotificationController.php exists in app/Controllers/
- [ ] Routes are defined in routes/web.php
- [ ] app.js is loaded in the page

---

## Still Not Working?

Check the following files for errors:
1. `app/Models/NotificationModel.php` - Database queries
2. `app/Controllers/NotificationController.php` - AJAX endpoints
3. `app/Controllers/AdminController.php` - Notification creation on reject
4. `app/Controllers/PrincipalController.php` - Notification creation on reject
5. `public/js/app.js` - JavaScript notification logic
6. `routes/web.php` - Route definitions

Look for PHP errors in:
- Apache error log: `C:\xampp\apache\logs\error.log`
- PHP error log: `C:\xampp\php\logs\php_error_log`

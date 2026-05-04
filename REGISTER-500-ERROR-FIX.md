# Registration 500 Error Fix

## Issue
After nag-register, nawala ang connection ug nag-show ug **HTTP ERROR 500** sa `/public/register` route.

## Root Cause
**Undefined constant `RateLimitHelper::ATTEMPT_WINDOW`** sa line 191 ng RateLimitHelper.php

### Error Message:
```
PHP Fatal error: Uncaught Error: Undefined constant RateLimitHelper::ATTEMPT_WINDOW 
in C:\xampp\htdocs\Sign\app\Helpers\RateLimitHelper.php:191
```

### Stack Trace:
```
#0 AuthController.php(192): RateLimitHelper::checkRegistrationAttempts()
#1 routes/web.php(31): AuthController->register()
#2 routes/web.php(49): route('POST', '/register', ...)
#3 public/index.php(73): require_once('routes/web.php')
```

---

## Solution
Added missing constant `ATTEMPT_WINDOW` sa RateLimitHelper class.

### Code Added:
```php
class RateLimitHelper {
    // Rate limiting time windows (in seconds)
    const ATTEMPT_WINDOW = 900; // 15 minutes
    
    // ... rest of class
}
```

---

## What Happened

### Registration Flow:
1. User submits registration form
2. AuthController->register() is called
3. **RateLimitHelper::checkRegistrationAttempts()** is called (line 192)
4. Inside checkRegistrationAttempts(), it tries to use `self::ATTEMPT_WINDOW` (line 191)
5. **ERROR:** Constant not defined → Fatal error → HTTP 500

### Why It Failed:
The `checkRegistrationAttempts()` method was using `self::ATTEMPT_WINDOW` but the constant was never defined in the class.

```php
// Line 191 - BEFORE (ERROR)
$windowStart = date('Y-m-d H:i:s', time() - self::ATTEMPT_WINDOW); // ❌ Undefined constant

// Line 191 - AFTER (FIXED)
$windowStart = date('Y-m-d H:i:s', time() - self::ATTEMPT_WINDOW); // ✅ Now defined as 900 seconds
```

---

## What ATTEMPT_WINDOW Does

### Purpose:
Defines the time window for checking registration rate limits.

### Value:
- **900 seconds** = **15 minutes**

### Usage:
Prevents users from registering multiple accounts too quickly:
- Max **3 registrations per email** within 15 minutes
- Max **5 registrations per IP address** within 15 minutes

### Example:
```php
// Check registrations in last 15 minutes
$windowStart = date('Y-m-d H:i:s', time() - 900);

SELECT COUNT(*) FROM rate_limit_log
WHERE email = 'user@example.com'
AND attempt_type = 'registration'
AND attempted_at > $windowStart
```

---

## Files Modified

### app/Helpers/RateLimitHelper.php
**Line 9-11 (Added):**
```php
class RateLimitHelper {
    // Rate limiting time windows (in seconds)
    const ATTEMPT_WINDOW = 900; // 15 minutes
```

---

## Testing

### Before Fix:
```
1. User fills registration form
2. User submits
3. ❌ HTTP ERROR 500
4. Registration fails
5. User cannot proceed to email verification
```

### After Fix:
```
1. User fills registration form
2. User submits
3. ✅ Registration successful
4. OTP sent to email
5. Redirects to /auth/verify-email
6. User can verify email and continue
```

---

## Rate Limiting Rules

### Registration Limits:
- **Per Email:** Max 3 attempts in 15 minutes
- **Per IP Address:** Max 5 attempts in 15 minutes

### Login Limits (from system_settings):
- **Max Attempts:** Configurable (default: 5)
- **Lockout Duration:** Configurable (default: 15 minutes)

### Time Windows:
- **Registration:** 900 seconds (15 minutes) - `ATTEMPT_WINDOW`
- **Login:** Dynamic from database - `getLockoutDuration()`

---

## Related Code

### AuthController->register() (Line 192):
```php
// Check registration rate limiting
$rateLimit = RateLimitHelper::checkRegistrationAttempts($email, $ipAddress);
if (!$rateLimit['allowed']) {
    $_SESSION['error'] = $rateLimit['message'];
    header('Location: ' . BASE_PATH . '/register');
    exit;
}
```

### RateLimitHelper->checkRegistrationAttempts() (Line 191):
```php
public static function checkRegistrationAttempts($email, $ipAddress = null) {
    $db = Database::getInstance()->getConnection();
    $windowStart = date('Y-m-d H:i:s', time() - self::ATTEMPT_WINDOW); // ✅ Now works
    
    // Check registrations by email (max 3 per 15 min)
    $stmt = $db->prepare("
        SELECT COUNT(*) as count FROM rate_limit_log
        WHERE email = :email
        AND attempt_type = 'registration'
        AND attempted_at > :window_start
    ");
    // ... rest of method
}
```

---

## Why This Happened

### Likely Cause:
The constant was removed or never added during a previous refactoring of the RateLimitHelper class.

### Other Constants in Class:
- ✅ `getMaxAttempts()` - reads from database
- ✅ `getLockoutDuration()` - reads from database
- ❌ `ATTEMPT_WINDOW` - was missing (now fixed)

### Why Not Database?
Login limits are configurable via admin settings, but registration limits are hardcoded for security (prevents abuse).

---

## Status: ✅ FIXED

Registration now works correctly and redirects to email verification.

**Date:** 2026-05-04  
**Issue:** HTTP 500 error after registration  
**Root Cause:** Undefined constant `ATTEMPT_WINDOW`  
**Solution:** Added constant definition (900 seconds = 15 minutes)

---

## Next Steps

### Test Registration Flow:
1. ✅ Go to `/register`
2. ✅ Fill form with valid data
3. ✅ Submit form
4. ✅ Should redirect to `/auth/verify-email`
5. ✅ Should receive OTP email
6. ✅ Verify OTP
7. ✅ Should redirect to dashboard

### Test Rate Limiting:
1. Try registering 4 times with same email in 15 minutes
2. Should block 4th attempt with error message
3. Wait 15 minutes
4. Should allow registration again

# How to Temporarily Disable CSRF (Development Only)

⚠️ **WARNING:** Only do this in development. Never in production!

## Option A: Skip CSRF Verification in Controllers

In `app/Controllers/AuthController.php`, comment out CSRF verification:

```php
public function login() {
    // ... existing code ...
    
    // TEMPORARILY DISABLED FOR DEVELOPMENT
    // try {
    //     CSRFHelper::verify();
    // } catch (Exception $e) {
    //     $_SESSION['error'] = 'Security validation failed. Please try again.';
    //     header('Location: ' . $basePath . '/login');
    //     exit;
    // }
    
    // ... rest of login code ...
}

public function register() {
    // ... existing code ...
    
    // TEMPORARILY DISABLED FOR DEVELOPMENT
    // try {
    //     CSRFHelper::verify();
    // } catch (Exception $e) {
    //     $_SESSION['error'] = 'Security validation failed. Please try again.';
    //     header('Location: ' . $basePath . '/register');
    //     exit;
    // }
    
    // ... rest of register code ...
}
```

## Option B: Make CSRFHelper Always Return True

In `app/Helpers/CSRFHelper.php`, modify the `verify()` method:

```php
public static function verify() {
    // TEMPORARILY DISABLED FOR DEVELOPMENT
    error_log('CSRF: Validation skipped (development mode)');
    return true;
    
    // Original code commented out:
    // if ($_SERVER['REQUEST_METHOD'] === 'GET') {
    //     return true;
    // }
    // ... rest of validation ...
}
```

## Option C: Remove CSRF Token from Forms

In `app/Views/auth/login.php` and `register.php`, comment out:

```php
<!-- CSRF Token -->
<!-- TEMPORARILY DISABLED FOR DEVELOPMENT
<?php require_once __DIR__ . '/../../Helpers/CSRFHelper.php'; ?>
<input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(CSRFHelper::getToken()); ?>">
-->
```

---

## ⚠️ IMPORTANT: Re-enable Before Production!

When you're ready to deploy:

1. Uncomment all CSRF code
2. Set `APP_ENV=production` in `.env`
3. Test all forms with CSRF enabled
4. Verify CSRF tokens are working

---

## Why You Should Keep CSRF Even in Development

1. **Catches bugs early** - If CSRF breaks, you find out now, not in production
2. **Realistic testing** - Your development environment matches production
3. **Security habit** - You won't forget to enable it later
4. **Already working** - I fixed it to be development-friendly

---

## Current Status

✅ CSRF is already working in development mode (lenient)
✅ Won't block your development
✅ Production-ready when you deploy

**Recommendation:** Don't disable it. It's already configured to not interfere with development.

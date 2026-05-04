# 🔧 File Access Fix - 404 Error on Documents

## Problem

When clicking "View Document" or clicking on images, you get:
```
404 - Page Not Found
```

URL example:
```
localhost/Sign/public/uploads/enrollment/psa_birth_cert_1_1777386070_c59f8b4e80cc3.pdf
```

---

## Root Cause

The file path in the view was missing a `/` separator between `$basePath` and `$doc['file_path']`.

**Before:**
```php
<?php echo $basePath; ?><?php echo htmlspecialchars($doc['file_path']); ?>
```

This generated:
```
/Sign/publicuploads/enrollment/file.pdf  ❌ (no slash!)
```

**After:**
```php
<?php echo $basePath; ?>/<?php echo ltrim(htmlspecialchars($doc['file_path']), '/'); ?>
```

This generates:
```
/Sign/public/uploads/enrollment/file.pdf  ✅ (correct!)
```

---

## What Was Fixed

### File: `app/Views/verification/show.php`

**Changed:**
```php
// Image src
<img src="<?php echo $basePath; ?>/<?php echo ltrim(htmlspecialchars($doc['file_path']), '/'); ?>">

// Image onclick
onclick="window.open('<?php echo $basePath; ?>/<?php echo ltrim(...), '/'); ?>', '_blank')"

// View button href
<a href="<?php echo $basePath; ?>/<?php echo ltrim(htmlspecialchars($doc['file_path']), '/'); ?>">
```

**Why `ltrim(..., '/')`?**
- Removes leading slash from file path if it exists
- Prevents double slashes: `/Sign/public//uploads/...`
- Ensures clean URL: `/Sign/public/uploads/...`

---

## Files Modified

1. ✅ `app/Views/verification/show.php` - Fixed file path URLs
2. ✅ `app/Views/enrollment/view.php` - Already had correct format

---

## Testing Tools Created

### 1. `public/check-file-paths.php`
- Shows file paths from database
- Checks if files exist
- Shows correct URL format

### 2. `public/test-file-access.php`
- Lists all files in uploads/enrollment
- Generates correct URLs
- Provides clickable test links

---

## How to Test

### Step 1: Check File Paths
Visit: `http://localhost/Sign/public/check-file-paths.php`

This will show:
- File paths stored in database
- Whether files exist on disk
- Correct URL format

### Step 2: Test File Access
Visit: `http://localhost/Sign/public/test-file-access.php`

This will show:
- All files in uploads/enrollment
- Clickable links to test each file
- File sizes and types

### Step 3: Test in Verification View
1. Go to: `http://localhost/Sign/public/enrollment/verification/1`
2. Scroll to documents section
3. Click on image preview (should show full size)
4. Click "View Full Document" button (should open PDF/image)

---

## Expected Behavior

### Images (JPG, PNG, GIF):
- ✅ Thumbnail shows in card
- ✅ Click thumbnail → Opens full size in new tab
- ✅ Click "View Full Document" → Opens full size in new tab

### PDFs:
- ✅ PDF icon shows in card
- ✅ Click "View Full Document" → Opens PDF in new tab

### URLs Should Look Like:
```
✅ http://localhost/Sign/public/uploads/enrollment/psa_birth_cert_1_1777888078_69f86b4e80cc3.pdf
✅ http://localhost/Sign/public/uploads/enrollment/pwd_id_1_1777888078_69f86b4e839a5.png

❌ http://localhost/Sign/publicuploads/enrollment/file.pdf (missing slash)
❌ http://localhost/Sign/public//uploads/enrollment/file.pdf (double slash)
```

---

## Common Issues & Solutions

### Issue 1: Still getting 404
**Solution:** 
- Check if file actually exists: Run `check-file-paths.php`
- Check file permissions: Files should be readable (644)
- Check .htaccess is not blocking: Should allow direct file access

### Issue 2: File path in database is wrong
**Solution:**
- File paths should be stored as: `uploads/enrollment/filename.ext`
- NOT as: `/uploads/...` or `public/uploads/...`
- Check EnrollmentController where files are uploaded

### Issue 3: Images not showing
**Solution:**
- Check file extension is correct (jpg, jpeg, png, gif)
- Check file is not corrupted
- Check browser console for errors

---

## File Upload Path Format

When files are uploaded, they should be stored in database as:

```
uploads/enrollment/psa_birth_cert_1_1777888078_69f86b4e80cc3.pdf
```

NOT as:
```
❌ /uploads/enrollment/...
❌ public/uploads/enrollment/...
❌ /Sign/public/uploads/enrollment/...
```

The view will add the base path automatically:
```php
$basePath . '/' . ltrim($doc['file_path'], '/')
```

---

## Next Steps

1. **Refresh the verification page**
2. **Click on a document** - should open now
3. **If still 404:**
   - Run `check-file-paths.php` to diagnose
   - Run `test-file-access.php` to test links
   - Check if files exist in `public/uploads/enrollment/`

---

**Karon, refresh ang page ug try pag-click sa documents! Dapat mu-open na sila!** 🎉


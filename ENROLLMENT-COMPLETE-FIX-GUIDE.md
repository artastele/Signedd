# Enrollment Complete Solution — Testing Guide

## What Was Fixed

### Core Problem
Enrollment submissions were failing because the draft auto-save feature was creating incomplete records, and the UPDATE logic couldn't handle the conversion from draft to final submission properly.

### Complete Solution Implemented

#### Part A: Fixed Draft Logic ✅
1. **Safe UPDATE method** - Only updates fields that exist in data array
2. **Proper draft-to-submission conversion** - Merges draft + new data before updating
3. **Draft cleanup** - Automatically deletes drafts older than 7 days
4. **Discard draft feature** - Users can manually delete drafts and start fresh

#### Part B: Session Management ✅
1. **Extended timeout** - 60 minutes for enrollment pages (vs 30 min default)
2. **Session keepalive** - Pings server every 5 minutes to extend session
3. **Session warning** - Shows alert when 5 minutes remaining
4. **Auto-extend** - User can click button to extend session

#### Part C: Draft Resume UI ✅
1. **Draft detection** - Shows prominent card if unfinished enrollment exists
2. **Resume or discard** - Clear options to continue or start fresh
3. **Draft info** - Shows when saved, student name (if entered)
4. **Better UX** - No confusing alerts, clear call-to-action buttons

---

## Files Modified

### Backend
- ✅ `config/schema.sql` - Added `last_activity` column (Migration v7)
- ✅ `app/Models/EnrollmentModel.php` - Fixed update(), added cleanup methods
- ✅ `app/Controllers/EnrollmentController.php` - Improved submit flow, added keepalive
- ✅ `app/Middleware/SessionMiddleware.php` - Extended timeout for enrollment pages
- ✅ `routes/web.php` - Added discard-draft and keepalive routes

### Frontend
- ✅ `app/Views/enrollment/index.php` - Added draft resume card UI
- ✅ `public/js/enrollment.js` - Added session keepalive and warning system

---

## Testing Instructions

### Step 1: Apply Database Migration

Run this SQL to add the new column:

```sql
ALTER TABLE enrollment_submissions 
ADD COLUMN IF NOT EXISTS last_activity TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP 
AFTER draft_saved_at;
```

**Or** re-run the full schema:
```bash
mysql -u root -p sped_lms < config/schema.sql
```

### Step 2: Test Draft Auto-Save

1. **Log in as parent**
2. **Go to Enrollment** → Create New Enrollment
3. **Fill some fields** (name, birth date, etc.)
4. **Wait 30 seconds** - You should see "Draft saved automatically" toast
5. **Close browser** (simulate crash)
6. **Log in again** → Go to Enrollment
7. **Verify:** Draft card appears with "Resume Draft" button

**Expected Result:** ✅ Draft saved, can resume later

### Step 3: Test Draft Resume

1. **Click "Resume Draft"** button
2. **Verify:** Form loads with previously entered data
3. **Continue filling** the form
4. **Submit enrollment**

**Expected Result:** ✅ Enrollment saves successfully to database

### Step 4: Test Draft Discard

1. **Create a draft** (fill partial form, wait for auto-save)
2. **Go back to Enrollment index**
3. **Click "Discard Draft"** button
4. **Confirm** the action

**Expected Result:** ✅ Draft deleted, can start fresh

### Step 5: Test Session Keepalive

1. **Start filling enrollment form**
2. **Wait 5 minutes** (do nothing)
3. **Verify:** No timeout, session automatically extended
4. **Check browser console:** Should see "Session extended at [time]"

**Expected Result:** ✅ Session stays alive while on enrollment page

### Step 6: Test Session Warning

1. **Disable keepalive** temporarily (comment out in enrollment.js)
2. **Fill enrollment form**
3. **Wait 55 minutes** (or modify timeout in SessionMiddleware for testing)
4. **Verify:** Warning appears "Session expiring in 5 minutes"
5. **Click "Extend Session"**

**Expected Result:** ✅ Warning shows, session extends on click

### Step 7: Test Complete Submission Flow

**Scenario A: No Draft Exists**
1. Log in as parent
2. Go to Enrollment → Create New
3. Fill complete form (all 7 steps)
4. Sign signature pad
5. Submit

**Expected:** ✅ New enrollment created in database

**Scenario B: Draft Exists**
1. Create draft (partial form, auto-save)
2. Come back later
3. Resume draft
4. Complete remaining fields
5. Submit

**Expected:** ✅ Draft converted to final submission (same ID, `is_draft=false`)

**Scenario C: Multiple Drafts** (edge case)
1. Create draft
2. Discard it
3. Create new draft
4. Submit

**Expected:** ✅ Only latest draft used, old ones cleaned up

### Step 8: Verify Database

After successful submission, check database:

```sql
SELECT 
    id,
    parent_id,
    first_name,
    last_name,
    is_draft,
    status,
    submitted_at,
    last_activity,
    created_at
FROM enrollment_submissions
ORDER BY id DESC
LIMIT 5;
```

**Expected Results:**
- `is_draft` = 0 (false)
- `status` = 'pending'
- `submitted_at` = current timestamp
- `last_activity` = current timestamp
- All required fields populated

---

## Key Improvements

### Before (Broken):
```php
// Draft exists
if ($draft) {
    update($draft['id'], $newData); // ❌ FAILS - incomplete data
}
```

### After (Fixed):
```php
// Draft exists
if ($draft) {
    $completeData = array_merge($draft, $newData); // ✅ Merge old + new
    update($draft['id'], $completeData); // ✅ Complete data
}
```

### Session Timeout:
- **Before:** 15 minutes (too short for enrollment)
- **After:** 60 minutes for enrollment pages, 30 min for others

### Draft Management:
- **Before:** No way to discard drafts, confusing alerts
- **After:** Clear UI, discard button, auto-cleanup after 7 days

---

## Troubleshooting

### Issue: "Draft saved" but can't resume
**Solution:** Check if `last_activity` column exists:
```sql
DESCRIBE enrollment_submissions;
```
If missing, run migration v7.

### Issue: Session still timing out
**Solution:** Check SessionMiddleware is detecting enrollment pages:
```php
// Should return 3600 (60 min) on enrollment pages
echo SessionMiddleware::getTimeoutDuration();
```

### Issue: Keepalive not working
**Solution:** 
1. Check browser console for errors
2. Verify route exists: `/enrollment/keepalive`
3. Check if user is logged in

### Issue: Draft not loading in form
**Solution:** Check if `resume=1` parameter is in URL and form is loading draft data

---

## Performance Notes

- **Auto-save:** Runs every 30 seconds (only if form changed)
- **Keepalive:** Runs every 5 minutes (lightweight ping)
- **Draft cleanup:** Runs once per enrollment index page load
- **Session check:** Runs on every page load (cached for 10 seconds)

---

## Security Considerations

✅ **RBAC maintained** - Only parents can access their own drafts  
✅ **SQL injection protected** - All queries use PDO prepared statements  
✅ **XSS protected** - All output sanitized with htmlspecialchars  
✅ **Session security** - HttpOnly cookies, proper timeout handling  
✅ **Draft isolation** - Parents can only see/edit their own drafts  

---

## Next Steps After Testing

Once all tests pass:

1. ✅ Mark as approved in CHANGELOG.md
2. Test with real parent users
3. Monitor error logs for any edge cases
4. Consider adding draft email notifications (optional)
5. Add draft statistics to parent dashboard (optional)

---

## Rollback Instructions

If issues occur:

```bash
# Revert code changes
git checkout HEAD~1 app/Controllers/EnrollmentController.php
git checkout HEAD~1 app/Models/EnrollmentModel.php
git checkout HEAD~1 app/Middleware/SessionMiddleware.php
git checkout HEAD~1 app/Views/enrollment/index.php
git checkout HEAD~1 public/js/enrollment.js
git checkout HEAD~1 routes/web.php

# Remove database column (optional)
mysql -u root -p sped_lms -e "ALTER TABLE enrollment_submissions DROP COLUMN last_activity;"
```

---

## Support

For issues or questions:
1. Check `logs/php_error.log` for detailed errors
2. Run `test-enrollment-connection.php` to verify database
3. Check browser console for JavaScript errors
4. Verify session is active: `var_dump($_SESSION);`

# Enrollment Data Persistence Fix — Testing Guide

## What Was Fixed

### Problem
Enrollment form submissions were not saving data to the database.

### Root Causes Identified
1. **Missing required parameters** - `is_draft`, `status`, `verified_by`, `verified_at` were not being set in all cases
2. **Insufficient error logging** - Database errors were logged but not detailed enough
3. **No parameter validation** - The model didn't validate that all required fields were present before INSERT

### Changes Made

#### 1. EnrollmentController.php
- ✅ Added `is_draft` and `status` defaults in `prepareEnrollmentData()`
- ✅ Added `verified_by` and `verified_at` fields to data preparation
- ✅ Enhanced error logging with detailed field presence checks
- ✅ Improved error messages shown to users

#### 2. EnrollmentModel.php
- ✅ Added parameter validation before INSERT
- ✅ Enhanced error logging with PDO error details
- ✅ Added detailed logging of bound parameters
- ✅ Better exception handling with specific error messages

#### 3. New Diagnostic Tool
- ✅ Created `public/test-enrollment-connection.php` for database testing

#### 4. Error Logging
- ✅ Updated `.htaccess` to enable PHP error logging to `logs/php_error.log`

---

## Testing Instructions

### Step 1: Run Database Connection Test
1. Open your browser
2. Navigate to: `http://localhost/Signedd/public/test-enrollment-connection.php`
3. Verify all 4 tests pass:
   - ✓ Database connection successful
   - ✓ Table 'enrollment_submissions' exists
   - ✓ All required columns exist
   - ✓ Test INSERT successful

**Expected Result:** All tests should show green checkmarks

### Step 2: Test Enrollment Submission
1. Log in as a **parent** user
2. Navigate to: Enrollment → Create New Enrollment
3. Fill out the enrollment form with test data:
   - **Learner Info:** First Name, Last Name, Birth Date, Sex
   - **Address:** Current address fields
   - **Parent Info:** At least one parent's information
   - **Grade Level:** Select a grade level
   - **Signature:** Draw a signature on the signature pad
4. Click **Submit Enrollment**
5. Confirm the submission in the dialog

**Expected Result:** 
- Success message: "Enrollment submitted successfully! You will be notified once it is reviewed."
- Redirect to enrollment status page
- New enrollment appears in the list

### Step 3: Verify Data in Database
Run this SQL query in phpMyAdmin or MySQL client:

```sql
SELECT 
    id, 
    parent_id, 
    first_name, 
    last_name, 
    status, 
    is_draft,
    submitted_at,
    created_at
FROM enrollment_submissions
ORDER BY id DESC
LIMIT 5;
```

**Expected Result:** Your test enrollment should appear with:
- `status` = 'pending'
- `is_draft` = 0 (false)
- `submitted_at` = current timestamp
- All name fields populated

### Step 4: Check Error Logs (if submission fails)
If submission fails, check the error log:

**Location:** `logs/php_error.log`

**Look for:**
```
=== ENROLLMENT SUBMISSION ERROR ===
Error: [specific error message]
```

The log will show:
- Missing parameters (if any)
- Database errors (if any)
- Which fields are present/missing

---

## Common Issues & Solutions

### Issue 1: "Missing required parameters" error
**Cause:** Form is not sending all required fields

**Solution:**
1. Check browser console for JavaScript errors
2. Verify signature pad is working
3. Ensure all required fields have values

### Issue 2: Database connection failed
**Cause:** Database credentials incorrect or MySQL not running

**Solution:**
1. Check `.env` file for correct DB credentials
2. Verify MySQL service is running
3. Test connection using `test-enrollment-connection.php`

### Issue 3: "Table does not exist" error
**Cause:** Database schema not applied

**Solution:**
1. Run the schema migration:
   ```bash
   mysql -u root -p sped_lms < config/schema.sql
   ```
2. Or use phpMyAdmin to import `config/schema.sql`

### Issue 4: Signature not saving
**Cause:** Signature pad JavaScript not initialized

**Solution:**
1. Check browser console for errors
2. Verify `signature-pad` library is loaded
3. Ensure canvas element exists on page

---

## Rollback Instructions

If you need to revert these changes:

```bash
git checkout HEAD~1 app/Controllers/EnrollmentController.php
git checkout HEAD~1 app/Models/EnrollmentModel.php
```

---

## Next Steps After Testing

Once enrollment submission is confirmed working:

1. ✅ Mark this fix as approved in CHANGELOG.md
2. Test with SPED teacher role to verify enrollment review workflow
3. Test document upload functionality
4. Test enrollment status tracking for parents

---

## Support

If issues persist after following this guide:

1. Check `logs/php_error.log` for detailed errors
2. Run `test-enrollment-connection.php` to verify database setup
3. Verify all required fields are being submitted from the form
4. Check browser console for JavaScript errors

**Debug Mode:** Set `display_errors = On` in `php.ini` temporarily to see errors on screen (disable in production!)

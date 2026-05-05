# Testing Instructions for Process 6-7

## 🎯 Current Status

### ✅ COMPLETED
1. **File Access Fix** - Encrypted files can be viewed/downloaded
2. **Database Schema** - Migration v23 with 4 new tables
3. **Models** - 7 models created with full CRUD
4. **Controllers** - 2 controllers with all methods
5. **Routes** - All routes added to web.php
6. **Permissions** - Learner role and permissions added

### ⏳ PENDING
1. **Views** - 12 view files (not created yet)
2. **CSS** - Cartoon style for learner UI
3. **JavaScript** - Activity builder and player
4. **End-to-End Testing** - Full workflow testing

---

## 🧪 HOW TO TEST

### Step 1: Access the Test Script

Open your browser and navigate to:
```
http://localhost/Signedd/public/test-process-6-7.php
```

This will run **40+ automated tests** covering:
- Database schema validation
- Model file existence
- Controller file existence
- Model instantiation
- Auto-grading logic
- File encryption
- Routes validation
- Permissions validation

### Step 2: Review Test Results

The test script will show:
- ✅ **PASS** - Test passed (green)
- ❌ **FAIL** - Test failed (red)
- ⚠️ **ERROR** - Test error (yellow)

**Expected Result:** All tests should PASS

### Step 3: Fix Any Failures

If any tests fail:

**Database Issues:**
```sql
-- Run migration manually
SOURCE config/schema.sql;

-- Or run specific migration
-- Copy Migration v23 section from schema.sql and run it
```

**File Issues:**
- Check if all model files exist in `app/Models/`
- Check if all controller files exist in `app/Controllers/`

**Permission Issues:**
- Check `config/permissions.php` has learner role
- Check routes have correct permission strings

---

## 🔍 MANUAL VERIFICATION

After automated tests pass, verify manually:

### 1. Database Schema
```sql
-- Check migration applied
SELECT * FROM db_version WHERE version = 23;

-- Check tables exist
SHOW TABLES LIKE 'activity_%';
SHOW TABLES LIKE 'assignment_%';
SHOW TABLES LIKE 'learner_progress';

-- Check columns added
DESCRIBE learning_materials;
```

### 2. Model Logic

Create a test PHP script:
```php
<?php
require_once 'config/db.php';
require_once 'app/Models/ActivityAttemptModel.php';

$model = new ActivityAttemptModel();

// Test auto-grading
$template = [
    'activity_type' => 'multiple_choice',
    'activity_data' => [
        'questions' => [
            ['correct_answer' => 0, 'points' => 10],
            ['correct_answer' => 1, 'points' => 10]
        ]
    ],
    'total_points' => 20
];

$answers = [0, 1]; // Both correct
$score = $model->calculateScore($template, $answers);

echo "Score: $score (Expected: 20)\n";
```

### 3. File Encryption

Test file serving:
```
1. Upload a document in enrollment
2. Check if file is encrypted (uploads/encrypted/)
3. Try to view document
4. Should decrypt and display correctly
```

### 4. Routes

Test route accessibility:
```
Teacher Routes:
- http://localhost/Signedd/public/iep/implementation
- http://localhost/Signedd/public/iep/implementation/assign

Learner Routes:
- http://localhost/Signedd/public/learning/modules
- http://localhost/Signedd/public/learning/assignments
```

**Note:** These will show errors because views don't exist yet, but routes should be recognized (not 404).

---

## ⚠️ KNOWN ISSUES TO CHECK

### Issue 1: Migration Not Applied
**Symptom:** Tables don't exist
**Fix:** Run migration v23 manually from schema.sql

### Issue 2: Model Class Not Found
**Symptom:** "Class not found" error
**Fix:** Check file paths and class names match

### Issue 3: Permission Denied
**Symptom:** 403 Forbidden on routes
**Fix:** Check permissions.php has correct role permissions

### Issue 4: File Encryption Key Missing
**Symptom:** Encryption fails
**Fix:** Check .env has ENCRYPTION_KEY set

### Issue 5: Student Record Not Found for Learner
**Symptom:** Learner dashboard shows error
**Fix:** Verify learner account was created with LRN email format

---

## 📊 TEST CHECKLIST

Before marking as DONE, verify:

### Database ✓
- [ ] Migration v23 applied
- [ ] activity_templates table exists
- [ ] activity_attempts table exists
- [ ] assignment_submissions table exists
- [ ] learner_progress table exists
- [ ] learning_materials has new columns

### Models ✓
- [ ] All 7 model files exist
- [ ] Models instantiate without errors
- [ ] Auto-grading logic works correctly
- [ ] Progress calculation works
- [ ] Star calculation works

### Controllers ✓
- [ ] IEPImplementationController exists
- [ ] LearningController exists
- [ ] No syntax errors in controllers
- [ ] All methods defined

### Routes ✓
- [ ] All teacher routes added
- [ ] All learner routes added
- [ ] Routes don't return 404
- [ ] Permissions configured correctly

### Security ✓
- [ ] File encryption working
- [ ] Prepared statements used everywhere
- [ ] RBAC middleware applied
- [ ] No SQL injection vulnerabilities

### Integration ✓
- [ ] StudentModel->getByUserId() works
- [ ] File upload & encryption works
- [ ] Activity data stored as JSON
- [ ] Progress tracking updates correctly

---

## 🚀 NEXT STEPS AFTER TESTING

Once all tests pass:

1. **Create Views** (12 files)
   - Teacher: 5 views
   - Learner: 7 views

2. **Add CSS** (1 file)
   - Cartoon style for learner UI
   - Bright colors, rounded buttons, fun fonts

3. **Add JavaScript** (2 files)
   - Activity builder (teacher side)
   - Activity player (learner side)

4. **End-to-End Testing**
   - Teacher creates material → Learner sees it
   - Learner completes activity → Score calculated
   - Progress updates → Stars awarded

5. **Mark as DONE** ✅
   - Only after ALL tests pass
   - Only after full workflow tested
   - Only after no errors in logs

---

## 📞 TROUBLESHOOTING

### Test Script Shows Errors

**Check PHP error log:**
```bash
tail -f /path/to/php_error.log
```

**Check MySQL error log:**
```bash
tail -f /path/to/mysql_error.log
```

**Enable error display:**
```php
error_reporting(E_ALL);
ini_set('display_errors', 1);
```

### Database Connection Fails

**Check .env file:**
```
DB_HOST=localhost
DB_NAME=sped_lms
DB_USER=root
DB_PASS=
```

**Test connection:**
```php
<?php
require_once 'config/db.php';
$db = Database::getInstance()->getConnection();
echo "Connected!";
```

### Models Not Loading

**Check autoload:**
```php
require_once __DIR__ . '/../app/Models/LearnerIEPModel.php';
```

**Check class name matches filename:**
```php
class LearnerIEPModel { } // Must match filename
```

---

## ✅ SIGN-OFF CRITERIA

**DO NOT mark as DONE until:**

1. ✅ All automated tests pass (40+ tests)
2. ✅ Manual verification completed
3. ✅ No PHP errors in logs
4. ✅ No SQL errors in logs
5. ✅ File encryption working
6. ✅ Auto-grading logic verified
7. ✅ Progress tracking verified
8. ✅ Routes accessible (even without views)
9. ✅ Permissions configured correctly
10. ✅ Security checks passed

**Current Status:** ⏳ TESTING REQUIRED

Run the test script and report results before proceeding!

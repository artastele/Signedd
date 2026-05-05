# Testing Guide - Process 6-7 (IEP Implementation & Learning Activities)

## 🎯 QUICK START

When your XAMPP is working, follow this guide to test Process 6-7.

---

## 📋 PRE-TESTING SETUP

### 1. **Create Upload Directories**
```bash
cd public/uploads
mkdir materials
mkdir assignments
chmod 755 materials assignments
```

### 2. **Verify Database**
Check that Migration v23 was applied:
```sql
SELECT * FROM db_version WHERE version = 23;
```

Should show:
- `learner_iep`
- `learning_materials`
- `activity_templates`
- `activity_attempts`
- `learner_progress`
- `assignment_submissions`
- `module_access_logs`

### 3. **Get Test Accounts**

**SPED Teacher Account:**
- Email: (your SPED teacher account)
- Role: SPED Teacher
- Permission: `iep.implement`

**Learner Account:**
- LRN: (generated from enrollment verification)
- Password: (temporary password sent to parent)
- Role: Learner
- Permission: `learning.access`

---

## 🧪 TESTING SEQUENCE

### **PART 1: TEACHER SIDE (Process 6 - IEP Implementation)**

#### Test 1: Teacher Dashboard
1. Login as SPED Teacher
2. Go to: `/iep/implementation`
3. **Check:**
   - [ ] Student cards display
   - [ ] "Assign IEP" button visible
   - [ ] "View Materials" button visible
   - [ ] "View Progress" button visible

#### Test 2: Assign IEP to Learner
1. Click "Assign IEP" on a student card
2. Select IEP document from dropdown
3. Click "Assign IEP"
4. **Check:**
   - [ ] Success message appears
   - [ ] IEP assigned to learner
   - [ ] Database: `learner_iep` table has new row

#### Test 3: Upload Learning Materials (Individual)
1. Click "View Materials" on a student
2. Click "Upload Material" button
3. Fill form:
   - Material Name: "Math Module 1"
   - Description: "Basic addition and subtraction"
   - Type: Module
   - Upload PDF file
4. Click "Upload"
5. **Check:**
   - [ ] Success message
   - [ ] Material appears in list
   - [ ] File uploaded to `/uploads/materials/`
   - [ ] Database: `learning_materials` table has new row

#### Test 4: Upload Learning Materials (Bulk)
1. Click "Bulk Upload" button
2. Select multiple files (3-5 PDFs)
3. Click "Upload All"
4. **Check:**
   - [ ] Progress bar shows
   - [ ] All files uploaded
   - [ ] Success message for each
   - [ ] All materials appear in list

#### Test 5: Create Manual Activity (Multiple Choice)
1. Click "Create Activity" button
2. Select "Multiple Choice"
3. Fill form:
   - Activity Name: "Math Quiz 1"
   - Add 5 questions with 4 options each
   - Mark correct answers
   - Set points per question
4. Click "Save Activity"
5. **Check:**
   - [ ] Success message
   - [ ] Activity appears in materials list
   - [ ] Database: `activity_templates` table has new row
   - [ ] JSON data stored correctly

#### Test 6: Create Manual Activity (True/False)
1. Click "Create Activity" button
2. Select "True/False"
3. Add 10 True/False questions
4. Mark correct answers
5. Click "Save Activity"
6. **Check:**
   - [ ] Activity saved
   - [ ] Appears in list

#### Test 7: Create Manual Activity (Drag & Drop Sorting)
1. Click "Create Activity" button
2. Select "Drag & Drop Sorting"
3. Add 5-7 items to sort
4. Set correct order
5. Click "Save Activity"
6. **Check:**
   - [ ] Activity saved
   - [ ] JSON data correct

#### Test 8: View Student Progress
1. Click "View Progress" on a student
2. **Check:**
   - [ ] Progress timeline displays
   - [ ] Completed materials shown
   - [ ] Stars earned displayed
   - [ ] Time spent tracked
   - [ ] Activity attempts listed

#### Test 9: Delete Material
1. Click "Delete" on a material
2. Confirm deletion
3. **Check:**
   - [ ] Material removed from list
   - [ ] Database: `learning_materials` row deleted
   - [ ] File still exists (soft delete)

---

### **PART 2: LEARNER SIDE (Process 7 - Learning Activities)**

#### Test 10: Learner Dashboard
1. Login as Learner (use LRN)
2. Go to: `/learning/dashboard`
3. **Check:**
   - [ ] Welcome banner with name
   - [ ] Total stars displayed
   - [ ] Progress circle shows percentage
   - [ ] Stats cards (modules, assignments, stars)
   - [ ] Recent modules displayed (max 3)
   - [ ] Recent assignments displayed (max 3)
   - [ ] Quick links work

#### Test 11: View Modules List
1. Click "See All" under Modules
2. Go to: `/learning/modules`
3. **Check:**
   - [ ] All modules display as cards
   - [ ] Module icons (📖, 📕, 📗, etc.)
   - [ ] Status badges (New, In Progress, Completed)
   - [ ] Stars earned shown for completed
   - [ ] "Start Learning" button visible

#### Test 12: View Module Content
1. Click "Start Learning" on a module
2. Go to: `/learning/module/{id}`
3. **Check:**
   - [ ] Timer starts (00:00)
   - [ ] Module title displays
   - [ ] Description shows
   - [ ] File viewer works:
     - [ ] PDF displays in iframe
     - [ ] Image displays
     - [ ] Video plays
     - [ ] Download button for other files
   - [ ] "Mark as Complete" button visible

#### Test 13: Complete Module
1. Click "Mark as Complete"
2. Confirm completion
3. **Check:**
   - [ ] Success message with stars earned
   - [ ] Confetti animation (optional)
   - [ ] Progress updated
   - [ ] Database: `learner_progress` table updated
   - [ ] Status changed to "completed"
   - [ ] Stars earned (1 star)
   - [ ] Time spent recorded

#### Test 14: View Assignments List
1. Click "See All" under Assignments
2. Go to: `/learning/assignments`
3. **Check:**
   - [ ] All assignments display as cards
   - [ ] Filter tabs work (All, Pending, Submitted, Graded)
   - [ ] Due date badges show:
     - [ ] "Due in X days" (normal)
     - [ ] "Due Today" (urgent)
     - [ ] "Overdue by X days" (red, shaking)
   - [ ] Points display (🏆 X points)
   - [ ] Status badges (Submitted, Graded)

#### Test 15: Submit Assignment (Text Answer)
1. Click "Do Assignment" on an assignment
2. Go to: `/learning/assignment/{id}`
3. Type answer in textarea
4. Click "Submit Assignment"
5. Confirm submission
6. **Check:**
   - [ ] Success message
   - [ ] Submission saved
   - [ ] Database: `assignment_submissions` table has new row
   - [ ] Status changed to "Submitted"
   - [ ] "Pending grade" indicator shows

#### Test 16: Submit Assignment (File Upload)
1. Click "Do Assignment" on another assignment
2. Upload a file (PDF, Word, or Image)
3. Click "Submit Assignment"
4. **Check:**
   - [ ] File uploaded to `/uploads/assignments/`
   - [ ] File encrypted (if FileEncryptionHelper enabled)
   - [ ] Submission saved
   - [ ] Status changed to "Submitted"

#### Test 17: Submit Assignment (Both Text + File)
1. Click "Do Assignment"
2. Type answer AND upload file
3. Submit
4. **Check:**
   - [ ] Both text and file saved
   - [ ] Submission type: "both"

#### Test 18: View Submitted Assignment
1. Go back to assignment after submission
2. **Check:**
   - [ ] Submission status displays
   - [ ] Submitted date/time shows
   - [ ] Text answer displays (if any)
   - [ ] Uploaded file link works (if any)
   - [ ] "Waiting for teacher to grade" message

#### Test 19: Play Activity (Multiple Choice)
1. Click on a Multiple Choice activity
2. Go to: `/learning/activity/{id}`
3. **Check:**
   - [ ] Timer starts
   - [ ] Best score banner (if previous attempts)
   - [ ] Questions display with radio buttons
   - [ ] Can select options
   - [ ] Selected option highlights
4. Answer all questions
5. Click "Submit Answers"
6. Confirm submission
7. **Check:**
   - [ ] Auto-grading works
   - [ ] Score displayed (X/Y points)
   - [ ] Percentage calculated
   - [ ] Stars earned (1-3 based on score)
   - [ ] Confetti animation plays 🎉
   - [ ] Success message
   - [ ] Database: `activity_attempts` table has new row

#### Test 20: Play Activity (True/False)
1. Click on a True/False activity
2. Answer all questions
3. Submit
4. **Check:**
   - [ ] Auto-grading works
   - [ ] Score correct
   - [ ] Stars earned

#### Test 21: Play Activity (Fill in the Blanks)
1. Click on a Fill Blanks activity
2. Type answers in text inputs
3. Submit
4. **Check:**
   - [ ] Auto-grading works (case-insensitive)
   - [ ] Score correct

#### Test 22: Play Activity (Matching)
1. Click on a Matching activity
2. Click items from Column A and Column B to match
3. **Check:**
   - [ ] Click-to-match works
   - [ ] Selected items highlight
   - [ ] Matched items fade out
4. Submit
5. **Check:**
   - [ ] Auto-grading works
   - [ ] Score correct

#### Test 23: Play Activity (Drag & Drop Sorting)
1. Click on a Drag & Drop activity
2. **Check:**
   - [ ] Items are shuffled
   - [ ] Can drag items
   - [ ] Items reorder smoothly
   - [ ] SortableJS library loaded
3. Drag items to correct order
4. Submit
5. **Check:**
   - [ ] Auto-grading works
   - [ ] Order checked correctly

#### Test 24: Play Activity (Sequencing)
1. Click on a Sequencing activity
2. Drag steps to correct sequence
3. Submit
4. **Check:**
   - [ ] Auto-grading works
   - [ ] Sequence checked correctly

#### Test 25: Retry Activity to Improve Score
1. Go back to an activity you completed
2. Click "Start Learning" again
3. Answer questions (try to get higher score)
4. Submit
5. **Check:**
   - [ ] New attempt saved
   - [ ] Best score updated (if higher)
   - [ ] Previous attempts list shows both attempts
   - [ ] Can retry unlimited times

#### Test 26: View Progress Page
1. Click "My Progress" from dashboard
2. Go to: `/learning/progress`
3. **Check:**
   - [ ] Overall progress percentage displays
   - [ ] Stats cards show correct numbers:
     - [ ] Total stars
     - [ ] Completed materials
     - [ ] Total materials
     - [ ] Submissions count
   - [ ] Achievement badges display:
     - [ ] Unlocked badges are colored and animated
     - [ ] Locked badges are grayed out
   - [ ] Recent activity timeline shows:
     - [ ] Module completions
     - [ ] Activity attempts
     - [ ] Timestamps
     - [ ] Scores

#### Test 27: Check Achievement Badges
1. On progress page, check badges:
   - [ ] First Star (1 star) - Unlocked?
   - [ ] Star Collector (10 stars) - Locked?
   - [ ] Super Star (50 stars) - Locked?
   - [ ] First Steps (1 module) - Unlocked?
   - [ ] Bookworm (5 modules) - Locked?
   - [ ] Scholar (20 modules) - Locked?
   - [ ] First Submit (1 assignment) - Unlocked?
   - [ ] Hard Worker (10 assignments) - Locked?
   - [ ] Halfway There (50% progress) - Locked?
   - [ ] Champion (100% complete) - Locked?

#### Test 28: Auto-Save Activity Logs
1. Open a module
2. Wait 30 seconds
3. **Check:**
   - [ ] Console shows AJAX request every 30s
   - [ ] Database: `module_access_logs` table updated
   - [ ] Time spent increments

---

## 🐛 COMMON ISSUES & FIXES

### Issue 1: "Access Denied" on files
**Fix:** Check FileController authentication and file paths

### Issue 2: Timer not starting
**Fix:** Check JavaScript console for errors

### Issue 3: Confetti not showing
**Fix:** Check Canvas Confetti CDN is loaded

### Issue 4: Drag-drop not working
**Fix:** Check SortableJS CDN is loaded

### Issue 5: Activities not auto-grading
**Fix:** Check ActivityAttemptModel::calculateScore() method

### Issue 6: Stars not awarded
**Fix:** Check LearnerProgressModel::markComplete() method

### Issue 7: Upload directories not writable
**Fix:** `chmod 755 public/uploads/materials public/uploads/assignments`

---

## ✅ TESTING COMPLETION CHECKLIST

### Teacher Side (9 tests)
- [ ] Test 1: Teacher Dashboard
- [ ] Test 2: Assign IEP
- [ ] Test 3: Upload Material (Individual)
- [ ] Test 4: Upload Material (Bulk)
- [ ] Test 5: Create Activity (Multiple Choice)
- [ ] Test 6: Create Activity (True/False)
- [ ] Test 7: Create Activity (Drag & Drop)
- [ ] Test 8: View Student Progress
- [ ] Test 9: Delete Material

### Learner Side (19 tests)
- [ ] Test 10: Learner Dashboard
- [ ] Test 11: View Modules List
- [ ] Test 12: View Module Content
- [ ] Test 13: Complete Module
- [ ] Test 14: View Assignments List
- [ ] Test 15: Submit Assignment (Text)
- [ ] Test 16: Submit Assignment (File)
- [ ] Test 17: Submit Assignment (Both)
- [ ] Test 18: View Submitted Assignment
- [ ] Test 19: Play Activity (Multiple Choice)
- [ ] Test 20: Play Activity (True/False)
- [ ] Test 21: Play Activity (Fill Blanks)
- [ ] Test 22: Play Activity (Matching)
- [ ] Test 23: Play Activity (Drag & Drop)
- [ ] Test 24: Play Activity (Sequencing)
- [ ] Test 25: Retry Activity
- [ ] Test 26: View Progress Page
- [ ] Test 27: Check Achievement Badges
- [ ] Test 28: Auto-Save Activity Logs

---

## 📊 EXPECTED RESULTS

After completing all tests:

**Database Tables:**
- `learner_iep`: 1+ rows (IEP assignments)
- `learning_materials`: 10+ rows (modules + activities)
- `activity_templates`: 5+ rows (manual activities)
- `activity_attempts`: 5+ rows (activity submissions)
- `learner_progress`: 5+ rows (module completions)
- `assignment_submissions`: 3+ rows (assignment submissions)
- `module_access_logs`: 10+ rows (time tracking)

**File System:**
- `/uploads/materials/`: 5+ files
- `/uploads/assignments/`: 3+ files
- `/uploads/encrypted/`: Files if encryption enabled

**User Experience:**
- Learner can complete modules and earn stars
- Learner can submit assignments
- Learner can play interactive activities
- Learner can track progress and unlock badges
- Teacher can upload materials and create activities
- Teacher can view student progress

---

## 🎉 SUCCESS CRITERIA

Process 6-7 is considered **COMPLETE** when:

1. ✅ All 28 tests pass
2. ✅ No JavaScript errors in console
3. ✅ No PHP errors in logs
4. ✅ All database tables populated correctly
5. ✅ Files upload and display correctly
6. ✅ Activities auto-grade correctly
7. ✅ Stars and badges work correctly
8. ✅ Progress tracking works correctly
9. ✅ Cartoon UI displays correctly
10. ✅ Responsive design works on mobile

---

**Created:** 2026-05-05  
**Status:** Ready for Testing  
**Next:** Run all 28 tests when XAMPP is available

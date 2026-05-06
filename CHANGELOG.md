# CHANGELOG — SPED LMS

> This file is updated after every approved feature. Never skip this step.
> Format: describe what was built, what schema changed, what was tested, and the approval date.

---

## [v1.21] — Show Rejection Reason on Status Page & Dashboard (Process 1 - Enhancement)
- **Built:** Display rejection reason prominently on enrollment status page and parent dashboard
- **Issue:** Parents couldn't see WHY their enrollment was rejected (only saw "rejected" status)
- **Solution:** Show rejection reason (review_note) alongside rejection status
- **Features:**
  - **Enrollment Status Page:**
    - Rejected enrollments show red alert with heading "Enrollment Rejected"
    - Displays full rejection reason from SPED teacher
    - If no reason provided, shows generic message
    - Uses `nl2br()` to preserve line breaks in reason
  - **Parent Dashboard:**
    - Red alert banner at top for each rejected enrollment
    - Shows student name in alert heading
    - Displays rejection reason in highlighted box
    - Quick action buttons: "View Details" and "Resubmit Enrollment"
    - Dismissible alert (can close with X button)
  - **Backend Enhancement:**
    - Modified `EnrollmentModel::updateStatus()` to accept `$reviewNote` parameter
    - Modified `EnrollmentController::rejectEnrollment()` to save rejection reason to enrollment
    - Rejection reason now saved to both enrollment AND documents
- **Implementation:**
  - **EnrollmentModel::updateStatus():**
    - Added 4th parameter: `$reviewNote = null`
    - Updates `review_note` field in `enrollment_submissions` table
    - Backward compatible (optional parameter)
  - **EnrollmentController::rejectEnrollment():**
    - Now passes rejection reason to `updateStatus()` method
    - Saves reason to enrollment record (not just documents)
  - **Views Updated:**
    - `app/Views/enrollment/status.php` - Shows rejection reason in alert
    - `app/Views/dashboard/parent.php` - Shows rejection alert at top
- **UI Design:**
  - Red danger alert with exclamation triangle icon
  - Clear heading: "Enrollment Rejected"
  - Reason displayed in highlighted box for emphasis
  - Action buttons for next steps
  - Professional, empathetic tone
- **Benefits:**
  - ✅ Parents immediately see WHY enrollment was rejected
  - ✅ Clear feedback helps parents fix issues
  - ✅ Reduces confusion and support requests
  - ✅ Better user experience
  - ✅ Transparent communication
- **Workflow:**
  1. SPED Teacher rejects enrollment with reason
  2. System saves reason to `enrollment_submissions.review_note`
  3. Parent logs in → Sees red alert on dashboard
  4. Parent clicks "View Details" → Sees full reason on status page
  5. Parent can resubmit with corrections
- **Files Modified:**
  - `app/Models/EnrollmentModel.php` - Added reviewNote parameter to updateStatus()
  - `app/Controllers/EnrollmentController.php` - Pass rejection reason to updateStatus()
  - `app/Views/enrollment/status.php` - Display rejection reason in alert
  - `app/Views/dashboard/parent.php` - Display rejection alert at top
- **Tables modified:** None (uses existing review_note column)
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.20] — Auto-Copy Documents for Returning Students + Approval Fix (Process 1 - Enhancement)
- **Built:** Automatic document copying for returning students + fixed approval issue
- **Issue 1:** Returning students had to re-upload documents every year
- **Issue 2:** Approval button not working (status not changing from pending to verified)
- **Solution:** Auto-copy approved documents from previous enrollment + made documents optional for approval
- **Features:**
  - **Auto-Copy Documents:**
    - When returning student submits enrollment, system automatically copies documents from previous enrollment
    - Only copies approved documents (not rejected or pending)
    - Links same file to new enrollment (no physical file duplication)
    - Saves storage space and parent's time
  - **Step 7 Enhancement:**
    - Returning students see green success alert: "Documents Auto-Copied"
    - Clear message: "No need to upload documents again!"
    - Only signature required for returning students
  - **Approval Fix:**
    - Changed document check from required to optional
    - Approval now works even if no documents uploaded
    - Prevents "No documents found" exception
- **Implementation:**
  - **EnrollmentController::submit():**
    - Checks if enrollment type is "returning" and has previous_enrollment_id
    - If YES → Calls `copyDocumentsFromPreviousEnrollment()`
    - If NO → Calls `handleDocumentUploads()` (normal upload)
  - **New Method: copyDocumentsFromPreviousEnrollment():**
    - Gets documents from previous enrollment
    - Filters only approved documents
    - Links same file_path to new enrollment
    - Logs copied document count
  - **EnrollmentController::approveEnrollment():**
    - Changed from `if (empty($documents)) throw Exception`
    - To: `if (!empty($documents)) { approve them }`
    - Continues approval even if no documents
- **Benefits:**
  - ✅ Returning students don't re-upload documents
  - ✅ Faster enrollment process
  - ✅ Less storage usage (same file linked multiple times)
  - ✅ Approval works for all enrollments
  - ✅ Better user experience
- **Workflow:**
  1. Parent searches for returning student (LRN or name)
  2. Selects previous enrollment
  3. Form auto-fills with previous data
  4. Parent fills Steps 1-6
  5. **Step 7:** Sees "Documents Auto-Copied" message
  6. Parent signs only (no document upload)
  7. Submits enrollment
  8. **System auto-copies approved documents** from previous enrollment
  9. SPED teacher reviews and approves
  10. **Approval works** even if no new documents uploaded
- **Files Modified:**
  - `app/Controllers/EnrollmentController.php` - Added copyDocumentsFromPreviousEnrollment(), fixed approval logic
  - `app/Views/enrollment/steps/step7_documents_signature.php` - Updated alert message
- **Tables modified:** None (uses existing enrollment_documents table)
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.19] — School Year Field in Step 5 (Process 1 - Enhancement)
- **Built:** School year selection dropdown in Step 5 (Enrollment Details)
- **Issue:** School year was auto-set via hidden field, users couldn't select different year
- **Solution:** Removed hidden field, added dropdown in Step 5, added validation
- **Changes:**
  - **Removed:** Hidden `school_year` field from form.php (line 122)
  - **Added:** School year dropdown in Step 5 with current year + next 2 years
  - **Default:** Current school year pre-selected
  - **Required:** Field marked with red asterisk (*)
  - **Validation:** Added client-side validation to check school_year is selected
  - **Error Message:** "❌ Step 5: School Year is required"
- **Benefits:**
  - ✅ Users can select enrollment for future school years
  - ✅ Useful for early enrollment periods
  - ✅ Clear indication of which school year enrollment is for
  - ✅ Prevents accidental wrong school year
- **Database:**
  - `school_year` column already exists in `enrollment_submissions` table
  - Type: VARCHAR(20), NOT NULL
  - No schema changes needed
- **Files Modified:**
  - `app/Views/enrollment/form.php` - Removed hidden field, added validation
  - `app/Views/enrollment/steps/step5_enrollment_details.php` - Already has dropdown (no changes)
- **Files Created:**
  - `test-school-year-fix.php` - Verification script to check database state
- **Tables modified:** None (uses existing school_year column)
- **Tested:** ✅ Database verified - all enrollments have valid school_year values
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.18.3] — Student Records HTTP 500 Fix (Process 2 - Bug Fix)
- **Fixed:** HTTP 500 error when accessing Student Records
- **Issues Fixed:**
  1. Parse error in StudentModel.php (premature class closing)
  2. SQL error: Unknown column 'sr.parent_id' in student_records table
  3. View displaying wrong field names (first_name, birth_date vs student_name, date_of_birth)
- **Root Causes:**
  - Class closing brace `}` was placed before new methods were added
  - student_records table doesn't have parent_id column (it's in enrollment_submissions)
  - Views were using enrollment field names instead of student_records field names
- **Solution:** Fixed class structure, updated SQL queries, corrected view field names
- **Changes:**
  - **StudentModel.php:**
    - Removed premature class closing brace (line 495)
    - Added proper closing brace at end of file
    - Updated `getAllStudents()` to join with enrollment_submissions for parent_id
    - Updated `findById()` to join with enrollment_submissions for parent_id
    - Both methods now get parent info from enrollment → users join
  - **students/index.php:**
    - Changed from `first_name`, `last_name`, `birth_date`, `sex` 
    - To: `student_name`, `date_of_birth`, `disability_type`
    - Updated table headers to match
  - **students/view.php:**
    - Changed from enrollment field names to student_records field names
    - Shows: LRN, student_name, date_of_birth, disability_type, psa_number, pwd_id_number
- **student_records Table Structure:**
  - id, enrollment_id, lrn, student_name, date_of_birth, disability_type
  - psa_number, pwd_id_number, verified_by, created_at, updated_at
  - Note: parent_id is NOT in this table (get from enrollment_submissions)
- **Files Modified:**
  - `app/Models/StudentModel.php` - Fixed class structure and SQL queries
  - `app/Views/students/index.php` - Updated field names
  - `app/Views/students/view.php` - Updated field names
- **Tables modified:** None
- **Tested:** ✅ Verified working with 11 student records
- **Status:** ✅ Complete - Working
- **Date:** 2026-05-06

---

## [v1.18.2] — Student Records Bug Fix (Process 2 - Bug Fix)
- **Fixed:** Chrome error when accessing Student Records
- **Issue:** Missing `findById()` method in StudentModel causing page load failure
- **Root Cause:** StudentController calls `findById()` but method didn't exist in StudentModel
- **Solution:** Added `findById()` method to StudentModel
- **Implementation:**
  - Added `findById($id)` method to StudentModel
  - Returns student record with parent information
  - Joins with users table to get parent name, email, contact
- **Files Modified:**
  - `app/Models/StudentModel.php` - Added findById() method
- **Tables modified:** None
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.18.1] — Student Records Permissions Fix + School Year Filter (Process 1 & 2 - Bug Fix)
- **Fixed:** 403 error when accessing Student Records
- **Added:** School year dropdown filter for returning student search
- **Issues Fixed:**
  1. Student Records showing 403 Forbidden for all staff roles
  2. No way to filter returning students by school year
  3. Permissions not set correctly for all staff roles
- **Solution:** Updated permissions and added school year filtering
- **Changes:**
  - **Permissions Fixed:**
    - Added `student.records` and `student.view` to Guidance role
    - Added `student.records` and `student.view` to Principal role
    - Added `student.records` and `student.view` to Master Teacher role
    - SPED Teacher already had these permissions
    - Admin has full access (*)
  - **Routes Updated:**
    - Changed from `'*'` (wildcard) to specific permissions
    - `/students` now requires `student.records` permission
    - `/students/view/{id}` now requires `student.view` permission
  - **School Year Filter:**
    - Added dropdown on returning student lookup page
    - Shows current year + past 5 years
    - Default: Current school year selected
    - Optional: Can select "All School Years"
    - Filters search results by school year
  - **Search Enhancement:**
    - `searchByLRN()` now accepts optional `$schoolYear` parameter
    - `searchByName()` now accepts optional `$schoolYear` parameter
    - Controller passes school year from GET parameter
    - JavaScript includes school year in AJAX request
- **Benefits:**
  - ✅ All staff can now access Student Records
  - ✅ Can find specific enrollment by school year
  - ✅ Useful for students with multiple enrollments
  - ✅ Better search accuracy
- **Files Modified:**
  - `config/permissions.php` - Added permissions to all staff roles
  - `routes/web.php` - Changed to specific permissions
  - `app/Views/enrollment/returning_lookup.php` - Added school year dropdown
  - `app/Models/EnrollmentModel.php` - Added school year parameter to search methods
  - `app/Controllers/EnrollmentController.php` - Pass school year to model
- **Tables modified:** None
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.18] — Student Records Management + Document History (Process 2 - Enhancement)
- **Built:** Complete student records management system with enrollment and document history
- **Issues Fixed:**
  1. Enrollment type showing "new" instead of "returning" for old students
  2. Documents stored per enrollment, not accessible for returning students
  3. No way to view student's complete enrollment history
  4. No centralized student records for staff
- **Solution:** New Student Records module with complete history tracking
- **Features:**
  - **Student Records Navigation:**
    - Added to sidebar for all staff roles (SPED Teacher, Guidance, Principal, Master Teacher, Admin)
    - Hidden from Parent and User roles
    - Icon: person-lines-fill
  - **Student Records List Page:**
    - Shows all students with LRN, name, birth date, sex, current grade
    - Statistics cards: Total Students, Active Enrollments, Pending, This School Year
    - Filterable table with status badges
    - Quick "View" button for each student
  - **Student Detail Page:**
    - **Student Information Card:** Complete profile (LRN, name, birth info, parent contact)
    - **Enrollment History Table:** All enrollments across all school years
      - Shows: School Year, Type (New/Transfer/Returning), Grade Level, Status
      - Link to view each enrollment detail
    - **All Documents Table:** Documents from ALL enrollments
      - Shows: Document Type, School Year, Enrollment Type, Status, Upload Date
      - Grouped by student (LRN), not by enrollment
      - Download/view links for each document
  - **Benefits for Returning Students:**
    - Staff can see previous documents without re-upload
    - Complete enrollment history visible
    - Documents preserved across school years
    - Easy verification of returning student status
- **Implementation:**
  - **StudentController:**
    - `index()` - List all students with latest enrollment info
    - `view($id)` - Show student detail with all enrollments and documents
    - RBAC: Staff only (blocks parent and user roles)
  - **StudentModel (new methods):**
    - `getAllStudents()` - Get all students with latest enrollment data
    - `getEnrollmentsByLRN($lrn)` - Get all enrollments for a student
  - **Views:**
    - `students/index.php` - Student list with statistics
    - `students/view.php` - Student detail with history
- **Document Access:**
  - Documents remain linked to enrollment_id (no schema change)
  - Student detail page aggregates documents from all enrollments
  - Shows which school year and enrollment type each document is from
  - Staff can view documents from any previous enrollment
- **UI Design:**
  - Consistent card-based layout
  - Color-coded status badges (success/warning/danger)
  - Responsive tables
  - Bootstrap icons throughout
  - Print-friendly layouts
- **Routes Added:**
  - GET /students - List all students
  - GET /students/view/{id} - View student detail
- **Files Created:**
  - `app/Controllers/StudentController.php` - Student records controller
  - `app/Views/students/index.php` - Student list view
  - `app/Views/students/view.php` - Student detail view
- **Files Modified:**
  - `app/Views/layouts/sidebar.php` - Added Student Records navigation
  - `app/Models/StudentModel.php` - Added getAllStudents(), getEnrollmentsByLRN()
  - `routes/web.php` - Added student routes
- **Tables modified:** None (uses existing tables)
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.17] — Step 4 Conditional + Enhanced Validation (Process 1 - Enhancement)
- **Built:** Improved enrollment form validation and conditional Step 4
- **Issue 1:** Step 4 (Previous School) shown for all students, should only be for transfer students
- **Issue 2:** No clear error messages when submission fails due to missing fields
- **Solution:** Conditional Step 4 + comprehensive client-side validation
- **Changes:**
  - **Step 4 (Previous School):**
    - Now only shows form fields for **transfer students**
    - **New students:** Shows "Not Applicable" message, skip to next step
    - **Returning students:** Shows "Not Applicable" + "Your info is on file" message
    - Previous school name required only for transfer students
    - JavaScript removes required attribute for non-transfer students
  - **Enhanced Form Validation:**
    - Comprehensive client-side validation before submission
    - Checks all required fields across all 7 steps
    - Shows detailed error list with step numbers
    - Auto-navigates to first error step
    - Validates based on enrollment type (transfer vs new/returning)
    - **Validation Rules:**
      - Step 1: Last Name, First Name, Birth Date, Sex, Place of Birth
      - Step 2: Current City, Province, Barangay
      - Step 4: Previous School Name (transfer students only)
      - Step 5: Grade Level to Enroll
      - Step 6: At least one learning modality
      - Step 7: Signature (all students), PSA Birth Certificate (new/transfer only)
  - **Error Messages:**
    - Clear, numbered list of missing fields
    - Shows which step each error is in
    - Example: "❌ Step 1: Last Name is required"
    - Guides user to fix issues before resubmitting
  - **Success Confirmation:**
    - Shows "✅ All required fields are complete!" when validation passes
    - Displays summary of key information
    - Requires explicit confirmation before submission
    - Shows loading spinner during submission
- **UI Improvements:**
  - Submit button shows loading state ("Submitting...")
  - Spinner animation during submission
  - Button disabled to prevent double-submission
- **Files Modified:**
  - `app/Views/enrollment/steps/step4_previous_school.php` - Conditional for transfer only
  - `app/Views/enrollment/form.php` - Enhanced validation logic
- **Tables modified:** None
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.16] — Enrollment Form UX Improvements (Process 1 - Enhancement)
- **Built:** Improved enrollment form for better user experience
- **Issue 1:** Returning students had to re-upload documents (should only need signature)
- **Issue 2:** Step 1 had document-style interface (inconsistent with other steps)
- **Solution:** Conditional document uploads + consistent card-style interface
- **Changes:**
  - **Step 7 (Documents & Signature):**
    - Added conditional logic based on enrollment type
    - **Returning students:** Only signature required, documents hidden
    - **New/Transfer students:** All documents required as before
    - Shows info alert explaining why documents are/aren't needed
    - PSA Birth Certificate made optional for returning students via JavaScript
  - **Step 1 (Learner Information):**
    - Replaced document-style layout with simple card layout
    - Now consistent with Step 2-6 (all use card style)
    - Same fields, cleaner interface
    - Better mobile responsiveness
    - Sections: Name, Birth Info, Indigenous People, 4Ps, Disabilities
- **UI Improvements:**
  - Consistent card-based design across all 7 steps
  - Better visual hierarchy
  - Cleaner spacing and alignment
  - Improved form field grouping
- **Files Created:**
  - `app/Views/enrollment/steps/step1_learner_info.php` - New simple card-style Step 1
- **Files Modified:**
  - `app/Views/enrollment/steps/step7_documents_signature.php` - Conditional document uploads
  - `app/Views/enrollment/form.php` - Updated to use new Step 1
- **Tables modified:** None
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.15.2] — Auto-Fill JavaScript Fix (Process 1 - Bug Fix)
- **Fixed:** Returning student enrollment now properly populates form fields
- **Issue:** Data was loaded from database but not displayed in form fields
- **Root Cause:** PHP `getFormValue()` helper sets default values but browser doesn't always render them
- **Solution:** Added JavaScript to explicitly populate fields after page load
- **Implementation:**
  - Added `DOMContentLoaded` event listener
  - Iterates through all `$formData` fields
  - Finds matching form fields by name or id
  - Handles different input types (text, select, checkbox, radio)
  - Adds green background highlight to auto-filled fields
  - Console logging for debugging (shows populated count)
- **Visual Feedback:**
  - Auto-filled fields have light green background (#e8f5e9)
  - Green left border (3px solid #4caf50)
  - Darker green on focus (#c8e6c9)
  - Smooth transition animation
- **Console Output:**
  - Shows each field being populated
  - Final count: "X fields populated, Y skipped"
  - Success message when complete
- **Files Modified:**
  - `app/Views/enrollment/form.php` - Added JavaScript auto-fill logic and enhanced CSS
- **Tables modified:** None
- **Tested:** ✅ Verified working - fields now populate correctly
- **Status:** ✅ Complete - Auto-Fill Working
- **Date:** 2026-05-06

---

## [v1.15.1] — Auto-Fill Debug Enhancement (Process 1 - Troubleshooting)
- **Built:** Enhanced debug information for returning student auto-fill feature
- **Issue:** User reported auto-fill not working - data visible but fields empty
- **Solution:** Added comprehensive debug logging and visible debug info
- **Changes:**
  - Added detailed error logging in `form.php` to track:
    - Enrollment type (should be "returning")
    - Form data count (should be 70+ fields)
    - Sample field values (last_name, first_name)
  - Added visible debug section in green alert banner showing:
    - Enrollment Type
    - Form Data Count
    - Sample field values (Last Name, First Name, Birth Date)
  - Created `AUTO-FILL-DEBUG-GUIDE.md` with:
    - Testing steps
    - Diagnosis scenarios
    - Expected behavior
    - Quick fix options
- **Purpose:** Help diagnose whether issue is:
  - Data not loading from database (PHP/SQL issue)
  - Data loading but not displaying (JavaScript/HTML issue)
  - Field name mismatch (mapping issue)
  - Draft interference (priority issue)
- **Files Modified:**
  - `app/Views/enrollment/form.php` - Added debug logging and visible debug info
- **Files Created:**
  - `AUTO-FILL-DEBUG-GUIDE.md` - Comprehensive troubleshooting guide
- **Tables modified:** None
- **Tested:** Debug code added, ready for user testing
- **Status:** 🔍 Debug Mode - Awaiting Test Results
- **Date:** 2026-05-06

---

## [v1.15] — Review Page Redesign: BEEF Document Style (Process 1 - UI Enhancement)
- **Built:** Redesigned enrollment review page to match enrollment form's document style
- **Purpose:** Make review page look professional, clean, and printable for SPED teachers
- **Changes:**
  - Created new `review_detail_v2.php` with BEEF document style
  - Uses same CSS (`beef-document.css`) as enrollment form
  - Clean, printable layout matching official DepEd BEEF form
  - Removed card-based sections, replaced with document sections
  - Added print button and print-optimized styles
  - Floating action buttons (Approve/Reject) for easy access
  - Status badge in top-right corner
  - All 8 sections displayed in document format
- **Features:**
  - **Document Header:** Republic of the Philippines, DepEd, BEEF title
  - **8 Sections:** Learner Info, Current Address, Permanent Address, Parent/Guardian, Previous School, Enrollment Details, Learning Modality, Documents & Signature
  - **Print-Friendly:** Hides buttons, sidebar, topbar when printing
  - **Status Indicator:** Color-coded badge (pending/verified/rejected)
  - **Floating Actions:** Approve and Reject buttons fixed at bottom-right
  - **Signature Display:** Shows parent signature image
  - **Document Links:** View uploaded documents (no-print)
  - **Verification Section:** Shows who verified and when
- **UI Style:**
  - White background with subtle borders
  - Professional typography
  - Checkbox symbols (☑/☐) for boolean fields
  - Clean spacing and alignment
  - Max-width: 8.5 inches (standard paper)
  - Print-optimized margins
- **Files Created:**
  - `app/Views/enrollment/review_detail_v2.php` - New BEEF document-style review page
- **Files Modified:**
  - `app/Controllers/EnrollmentController.php` - Updated reviewDetail() to use new view
- **Tables modified:** None
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.14.1] — Returning Student Auto-Fill Debug & Visual Indicator (Process 1 - Bug Fix)
- **Fixed:** Added debug logging and visual indicators for returning student auto-fill
- **Issue:** Auto-fill was working but not obvious to users
- **Solution:** Added visual feedback to confirm auto-fill is active
- **Changes:**
  - Added debug logging in `EnrollmentController::create()` to track auto-fill
  - Added green alert banner at top of form showing auto-fill is active
  - Shows student name and LRN in alert
  - Added green background highlight for auto-filled fields (CSS class)
  - Added error logging to track when previous enrollment is loaded
- **Visual Indicators:**
  - Green success alert: "Auto-Fill Active"
  - Shows student name and LRN
  - Dismissible alert
  - Green left border on auto-filled input fields
- **Files Modified:**
  - `app/Controllers/EnrollmentController.php` - Added debug logging
  - `app/Views/enrollment/form.php` - Added auto-fill indicator alert and CSS
- **Tables modified:** None
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.14] — Returning Student Lookup with Auto-Fill (Process 1 - Enhancement)
- **Built:** Student search feature for returning enrollment with auto-fill capability
- **Issue:** Returning student enrollment didn't fetch previous data - no auto-fill
- **Solution:** Added student lookup page with 2 search methods: LRN or Name
- **Features:**
  - **Lookup Page** (`returning_lookup.php`) with tabbed interface
  - **Search by LRN:** Enter 12-digit LRN, find exact match
  - **Search by Name:** Enter last name, first name, middle name (optional), suffix (optional)
  - **Search Results:** Display matching students with details (name, LRN, birth date, grade, last enrolled date)
  - **Select Button:** Click to use previous enrollment data for auto-fill
  - **AJAX Search:** Real-time search without page reload
  - **Loading Indicator:** Shows "Searching..." while fetching data
  - **No Results Message:** Clear feedback when no match found
- **Implementation:**
  - Added `EnrollmentModel::searchByLRN()` - Search by exact LRN match
  - Added `EnrollmentModel::searchByName()` - Search by name with optional filters
  - Added `EnrollmentController::returningLookup()` - Show lookup page
  - Added `EnrollmentController::searchStudent()` - AJAX search endpoint
  - Modified `EnrollmentController::create()` - Handle `previous_id` parameter
  - Updated enrollment index - "Find Returning Student" button (always enabled)
- **Search Logic:**
  - LRN search: Exact match on 12-digit LRN
  - Name search: Match last name + first name (required), middle name + suffix (optional)
  - Only searches verified or pending enrollments (not drafts)
  - Returns up to 10 matches for name search
  - Most recent enrollment shown first
- **Auto-Fill Workflow:**
  1. Parent clicks "Find Returning Student"
  2. Choose search method (LRN or Name)
  3. Enter search criteria
  4. Click "Search"
  5. System shows matching students
  6. Parent clicks "Select" on correct student
  7. Redirects to enrollment form with `previous_id` parameter
  8. Form auto-fills with previous enrollment data
- **UI Design:**
  - Bootstrap tabs for search methods
  - Green success color scheme (returning student theme)
  - Responsive layout
  - Clear instructions and help text
  - List group for search results
- **Routes Added:**
  - GET /enrollment/returning-lookup
  - GET /enrollment/search-student (AJAX)
- **Files Modified:**
  - `app/Models/EnrollmentModel.php` - Added searchByLRN, searchByName methods
  - `app/Controllers/EnrollmentController.php` - Added returningLookup, searchStudent, modified create
  - `app/Views/enrollment/index.php` - Updated returning student button
  - `routes/web.php` - Added new routes
- **Files Created:**
  - `app/Views/enrollment/returning_lookup.php` - Student lookup page
- **Tables modified:** None (uses existing enrollment_submissions table)
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.13] — SPED Teacher Dashboard: Pending Enrollments Section (UX Enhancement)
- **Built:** Pending enrollments awareness section on SPED Teacher dashboard
- **Purpose:** Help SPED teachers quickly see and act on pending enrollment applications
- **Features:**
  - **Alert banner** at top when pending enrollments exist
  - **Statistics cards** showing counts (Pending, Verified, Assessments, Active IEPs)
  - **Pending enrollments table** showing first 5 recent applications with:
    - Student name
    - Grade level
    - Enrollment type (New/Transfer/Returning)
    - Submission date and time
    - Document count
    - Quick "Review" button
  - **"View All" button** when more than 5 pending enrollments
  - **Dismissible alert** to reduce clutter after viewing
  - **Quick action cards** for main teacher tasks
- **Implementation:**
  - Modified `DashboardController::index()` to fetch pending enrollments for SPED teachers
  - Updated `app/Views/dashboard/teacher.php` with new sections
  - Uses existing `EnrollmentModel::getPending()` method
- **UI Design:**
  - Warning color scheme (amber/yellow) for pending items
  - Responsive layout (works on mobile)
  - Clean table with hover effects
  - Icon indicators for visual clarity
- **Workflow:**
  1. SPED Teacher logs in
  2. Dashboard shows pending count in alert and stats card
  3. Recent pending enrollments listed in table
  4. Click "Review" to go directly to enrollment detail
  5. Click "View All" to see complete pending list
- **Files Modified:**
  - `app/Controllers/DashboardController.php` - Added pending enrollments fetch for SPED teachers
  - `app/Views/dashboard/teacher.php` - Added alert, stats cards, and pending enrollments table
- **Tables modified:** None (uses existing enrollment_submissions table)
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.12] — Use Existing LRN for Transfer/Returning Students (Process 2 Part 2 - Enhancement)
- **Built:** Smart LRN handling for transfer and returning students
- **Issue:** System always generated new LRN, ignoring existing LRN from enrollment form
- **Impact:** Transfer/returning students lost their original LRN
- **Solution:** Check if LRN exists in enrollment form before generating new one
- **Logic:**
  - **New students (no LRN):** Generate new 12-digit LRN + create account
  - **Transfer students (has LRN, no account):** Use existing LRN + create new account
  - **Returning students (has LRN, account exists):** Use existing LRN + reset password + reactivate account
- **Implementation:**
  - Modified `StudentModel::createStudentRecord()` to check for existing LRN
  - Modified `StudentModel::createLearnerAccount()` to handle existing accounts
  - Updated email templates to differentiate new vs password reset
  - Updated notifications to show appropriate message
  - Added validation: LRN must be exactly 12 digits
- **Files Modified:**
  - `app/Models/StudentModel.php` - Enhanced LRN logic, account creation, email/notification methods
- **Workflow:**
  1. Parent submits enrollment with LRN field (optional)
  2. SPED Teacher approves enrollment
  3. SPED Teacher clicks "Create Learner Account"
  4. System checks if LRN exists in enrollment form
  5. **If LRN exists:**
     - Validates format (12 digits)
     - Checks if account exists with that LRN
     - If account exists: Reset password, reactivate, send "Password Reset" email
     - If no account: Create new account with existing LRN, send "Account Created" email
  6. **If NO LRN:**
     - Generate new LRN (YYYYMMDDNNNN format)
     - Create new account
     - Send "Account Created" email
  7. Parent receives appropriate email and notification
- **Tables modified:** None (uses existing fields)
- **Tested:** Ready for user testing
- **Status:** ✅ Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.11] — Learner Dashboard Routing Fix (Process 2 Part 2 - Bug Fix)
- **Fixed:** Learner accounts now route to learner dashboard instead of general dashboard
- **Issue:** After logging in with LRN, learners were redirected to general/user dashboard
- **Root Cause:** DashboardController switch statement missing case for 'learner' role
- **Solution:** Added `case 'learner':` to route learners to `app/Views/dashboard/learner.php`
- **Files Modified:**
  - `app/Controllers/DashboardController.php` - Added learner case in switch statement
- **Workflow:**
  1. Learner logs in with LRN
  2. AuthController sets `$_SESSION['role'] = 'learner'`
  3. DashboardController checks role
  4. Routes to learner dashboard (not general dashboard)
- **Tables modified:** None
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved
- **Date:** 2026-05-06

---

## [v1.10] — LRN Login Support + Parent Dashboard LRN Notification (Process 2 Part 2 - Complete)
- **Built:** Final features to complete Process 2 Part 2 - learner account usability
- **Feature 1: LRN Login Support**
  - Modified `AuthController::login()` to detect 12-digit LRN format
  - Auto-converts LRN to email format: `learner_{LRN}@spedlms.local`
  - Learners can now login using LRN as username (backward compatible with email)
  - Updated login form placeholder: "Email Address or LRN"
  - Added helper text: "Learners can use their 12-digit LRN as username"
  - Error logging for LRN login attempts
- **Feature 2: Parent Dashboard LRN Notification**
  - Added prominent green alert card at top of parent dashboard
  - Shows when `learner_account_created = true` and `lrn` is not empty
  - Displays:
    - Student name
    - LRN in large badge (white card with green text)
    - Login credentials card (username = LRN)
    - Instructions for first login
  - **Persistent notification** - does NOT auto-dismiss (uses `alert-permanent` class)
  - Only dismissible by clicking X button (stored in session)
  - Added `DashboardController::dismissLrnNotification()` AJAX endpoint
  - JavaScript function to dismiss notification without page reload
  - Gradient green background (#3b6d11 to #4a8514) with 3D effect
  - Check-circle icon and professional styling
- **Routes Added:**
  - POST /dashboard/dismiss-lrn-notification (AJAX endpoint)
- **Workflow:**
  1. Parent logs in after learner account is created
  2. Green alert appears at top of dashboard with LRN
  3. Parent can see LRN and login instructions prominently
  4. **Alert stays visible** until parent manually clicks X button
  5. Parent can dismiss notification (won't show again in session)
  6. Learner can login using LRN as username
  7. System auto-converts LRN to email for authentication
- **Files Modified:**
  - `app/Controllers/AuthController.php` - Added LRN detection and conversion
  - `app/Controllers/DashboardController.php` - Added dismissLrnNotification method
  - `app/Views/dashboard/parent.php` - Added LRN notification alert with JavaScript + `alert-permanent` class
  - `app/Views/auth/login.php` - Updated placeholder and added helper text
  - `routes/web.php` - Added dismiss notification route
- **Tables modified:** None (uses existing enrollment_submissions table)
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved
- **Date:** 2026-05-06

---

## [v1.8] — Simplified Enrollment Approval System (Process 1)
- **Built:** Single-action approval/rejection for entire enrollment (replaces per-document approval)
- **Controllers:** Added `EnrollmentController::approveEnrollment()` and `EnrollmentController::rejectEnrollment()`
- **Routes:** Added `POST /enrollment/approve/{id}` and `POST /enrollment/reject/{id}`
- **View:** Updated `review_detail.php` - removed per-document buttons, kept single approve/reject buttons at bottom
- **Logic:** 
  - Approve: Marks ALL documents as approved + enrollment status = 'verified' in one action
  - Reject: Marks enrollment as rejected + applies rejection reason to all documents
  - Notifications sent to parent via in-app notification and email
  - Document status badges now shown in card headers for visibility
- **Tables modified:** None (uses existing enrollment_submissions and enrollment_documents tables)
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved
- **Date:** 2026-05-06

---

## [v1.9] — Process 2 Part 2: Create Learner Account & Generate LRN (Process 2)
- **Built:** Complete learner account creation workflow after enrollment approval
- **View Updates:**
  - Added "Create Learner Account & Generate LRN" button in `review_detail.php` (shows when status = 'verified')
  - Button only appears if `learner_account_created` = false
  - Shows LRN and creation date after account is created
  - AJAX-based account creation with loading state
- **Controller Updates:**
  - Enhanced `VerificationController::verify()` with better validation
  - Added JSON header for proper API response
  - Added check to prevent duplicate account creation
  - Improved error messages with stack trace logging
  - **Fixed:** Added field mapping for enrollment_id to prevent "Parent not found" error
- **Workflow:**
  1. SPED Teacher approves enrollment (Process 2 Part 1)
  2. "Create Learner Account" button appears
  3. SPED Teacher clicks button
  4. System generates LRN (12-digit unique number)
  5. Creates student_record in database
  6. Creates learner user account with temporary password
  7. Sends email + in-app notification to parent with:
     - LRN
     - Learner login credentials
     - Welcome message
  8. Updates enrollment: `learner_account_created` = true, `lrn` = generated LRN
  9. Success message shows LRN and learner ID
  10. Page reloads to show "Account Created" status
- **Security:**
  - RBAC enforced (SPED teachers only)
  - Prevents duplicate account creation
  - Validates enrollment status before creating account
  - Activity logging for audit trail
- **Bug Fixes:**
  - Fixed "Parent not found for enrollment" error by adding enrollment_id field mapping
  - Fixed "Call to undefined method MailHelper::send()" by using correct method name `sendNotification()`
  - Fixed "Unexpected end of JSON input" by wrapping email sending in try-catch with Throwable
  - Email failures now gracefully degrade - account creation succeeds even if email fails
- **Tables modified:** None (uses existing columns: `learner_account_created`, `lrn` in enrollment_submissions)
- **Tested:** Pending user testing
- **Status:** Complete - Ready for Testing
- **Date:** 2026-05-06

---

## [v1.7] — Enrollment Document Approval Error Handling (Process 1)
- **Built:** Improved error handling for document approval/rejection
- **Controllers:** Updated `EnrollmentController::approveDocument()` and `rejectDocument()` with try-catch blocks
- **Validation:** Added checks for missing enrollment_id and empty rejection reasons
- **Tables modified:** None
- **Tested:** Partial - identified need for simplified approval system
- **Status:** Superseded by v1.8
- **Date:** 2026-05-06

---

## [v0.1] — Foundation Setup
- **Built:** 
  - Project structure (MVC folders)
  - Database configuration (PDO singleton)
  - Schema manager with auto-migration
  - Complete database schema (18 tables covering all 7 processes)
  - RBAC middleware and permissions system
  - Session middleware with 15-minute timeout
  - Route definitions for all processes
  - Custom Bootstrap theme (crimson #a01422 + navy #1e4072)
  - Environment configuration template
  - README and documentation
- **Tables added/modified:** 
  - All 18 tables created in schema.sql
  - users, role_requests, role_documents
  - enrollment_submissions, student_records, education_history
  - assessment_records, iep_meetings, iep_documents, iep_signatures
  - learner_iep, learning_materials, activity_records, module_access_logs
  - login_log, activity_log, db_version
- **Tested:** Structure verified, files created
- **Status:** Approved
- **Date:** 2026-05-01

---

## [v0.2] — Authentication System (Security Module 1)
- **Built:**
  - UserModel with authentication methods (findByEmail, create, verifyPassword, logLoginAttempt)
  - AuthController with login, register, logout actions
  - Login view with session timeout alert
  - Registration view with password validation
  - Dashboard controller with role-based routing
  - Navbar component with user dropdown
  - Dashboard views for all roles (general, admin, parent, teacher, guidance, principal, master_teacher)
  - Custom JavaScript for session timeout warning, password strength indicator
  - Layout templates (header, footer, navbar)
- **Tables added/modified:** None (uses existing users, login_log tables)
- **Tested:** ✓ Approved
- **Status:** Approved
- **Date:** 2026-05-01

---

## [v0.3] — UI Redesign + 404 Fix (Security Module 1 Enhancement)
- **Built:**
  - .htaccess for Apache URL rewriting (fixes 404 errors)
  - Dynamic base path detection in router
  - Sidebar navigation component (replaces top navbar)
  - Sidebar gradient: crimson → navy
  - Split-screen auth layout for login/register (desktop-first design)
  - Normalized registration fields (first_name, middle_name, last_name, suffix, contact_number)
  - Updated all dashboard views to use sidebar
  - Logo integration support (placeholder created)
  - Responsive CSS for sidebar and split-screen layouts
  - Updated AuthController and UserModel for new fields
  - **Enhanced General Dashboard:** Welcome banner, school info, quick stats, role selection cards (Apply as Staff vs Enroll Child)
- **Tables added/modified:** 
  - Migration v2: Added first_name, middle_name, last_name, suffix, contact_number columns to users table
- **Tested:** ✓ Approved with dashboard enhancement
- **Status:** Approved
- **Date:** 2026-05-01

---

## [v0.4] — Role Selection & Verification System (Security Module 2)
- **Built:**
  - RoleRequestModel with CRUD operations for role requests
  - RoleController with role selection and staff application
  - AdminController with Principal role request approval/rejection
  - PrincipalController with staff role request approval/rejection
  - **Hierarchical Approval System:** Admin approves Principal, Principal approves Staff (SPED Teacher, Guidance, Master Teacher)
  - Role selection view with two paths: Staff vs Parent
  - Staff application form with file upload (government ID, proof of designation)
  - Parent instant role assignment (no verification needed)
  - Admin role request review panel (Principal requests only)
  - Principal staff request review panel (Staff requests only)
  - File upload validation (PDF, JPG, PNG, max 5MB)
  - Email notifications to correct approver based on role hierarchy
  - Pending request detection and display
- **Tables added/modified:** 
  - Migration v3: Added approver_role column to role_requests table
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved
- **Date:** 2026-05-01

---

## [v0.4.1] — Bug Fixes & UX Improvements
- **Fixed:**
  - 403 Forbidden error on dashboard after login (removed permission check from route, added manual check in controller)
  - Dashboard now accessible for all logged-in users regardless of role
  - Alert messages now stay longer (10 seconds for success, permanent for errors/warnings)
  - Error and warning alerts no longer auto-dismiss
- **Added:**
  - Password visibility toggle (eye icon) on login page
  - Password visibility toggle (eye icon) on register page (both password and confirm password fields)
  - **Pending application alert** on general dashboard showing:
    - Role being applied for
    - Who is reviewing (Admin or Principal)
    - Submission date/time
    - Status message
  - Role selection cards hidden when application is pending
  - Success/error message display on all dashboards
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved
- **Date:** 2026-05-01

---

## [v0.5.1] — Notification System Fixes & Topbar Integration
- **Fixed:**
  - Added topbar to ALL views (admin, parent, teacher, guidance, principal, master_teacher, users, role_requests, staff_requests, role_select, services)
  - Fixed JavaScript base path detection to work with any installation path (e.g., /Signedd/public)
  - Added getBasePath() function for dynamic base path resolution
  - Added console logging to JavaScript for debugging
  - Created debug-notifications.php script for troubleshooting
  - Created NOTIFICATION-TROUBLESHOOTING.md guide
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved
- **Date:** 2026-05-01

---

## [v0.5] — In-App Notification System (Security Module 3)
- **Built:**
  - NotificationModel with CRUD operations (create, getByUserId, getUnreadByUserId, getUnreadCount, markAsRead, markAllAsRead, delete)
  - NotificationController with AJAX endpoints (getNotifications, markAsRead, markAllAsRead, delete)
  - **Top navigation bar** with notification bell and user profile dropdown (upper right corner)
  - Notification bell icon with unread badge count
  - Notification dropdown panel with real-time updates (380px width, positioned below bell)
  - User profile dropdown with avatar, name, role, and quick links
  - Notification items with icons, messages, timestamps, and action buttons
  - Auto-create notifications when applications are approved/rejected
  - Updated AdminController and PrincipalController to create notifications
  - JavaScript polling system (checks every 30 seconds for new notifications)
  - Mark as read functionality (individual and bulk)
  - Reapply button in rejection notifications
  - Time formatting (Just now, X minutes ago, X hours ago, X days ago)
  - XSS protection with HTML escaping
- **Tables added/modified:** 
  - Migration v4: Created notifications table (id, user_id, type, title, message, data, is_read, created_at)
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved
- **Date:** 2026-05-01

---

## [v0.4.3] — Enhanced Rejection Handling (Security Module 2 - Enhancement)
- **Built:**
  - Rejection alert on general dashboard with reason and reapply button
  - Application history modal showing all past applications (pending, approved, rejected)
  - View Application History button on dashboard
  - Popover for viewing review notes in history table
  - RoleRequestModel methods: `getLatestRejectedByUserId()`, `getAllByUserId()`
  - DashboardController now fetches rejected requests and application history
  - Clear pending alert when application is rejected (only shows one alert at a time)
  - Reapply functionality - users can submit new applications after rejection
- **Tables added/modified:** None (uses existing role_requests table)
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved
- **Date:** 2026-05-01

---

## [v0.4.2] — Services Page Placeholder
- **Built:**
  - ServicesController with index method
  - Services view with "Coming Soon" placeholder
  - Future features preview (School Info, SPED Programs, Enrollment Guide, Staff Resources, FAQs, Contact Directory)
  - Route added for /services
  - Sidebar navigation link now functional
- **Tables added/modified:** None
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved (Placeholder for future development)
- **Date:** 2026-05-01

---

## [v0.6] — Email Verification with OTP + Google Sign-In + Admin Logs (Security Module 4)
- **Built:**
  - **Email Verification System:**
    - Migration v5: Added email_verified, verification_token, verification_expires, verification_attempts fields
    - OTP generation (6-digit code, 10-minute expiration)
    - OTP verification with attempt tracking (max 3 attempts)
    - Email verification view with auto-focus 6-digit input
    - Resend OTP with 60-second cooldown
    - OTP email template (styled with SPED LMS branding)
    - Welcome email sent after successful verification
    - Notification created on email verification
  - **Google OAuth Integration:**
    - Migration v5: Added google_id, profile_picture, auth_provider fields
    - Google Sign-In buttons on login and register pages
    - OAuth 2.0 flow with state parameter (CSRF protection)
    - Account creation from Google data (auto-verified)
    - Account linking for existing emails
    - Google users must select role after sign-in
    - **Made optional** - System works without Google API Client
    - Graceful error handling if not configured
    - GOOGLE-OAUTH-SETUP.md guide created
  - **Admin Log Viewers:**
    - Login attempt logs view with filters (status, limit, search)
    - Activity logs view with filters (action type, user, search)
    - Statistics cards (24-hour totals)
    - Export-ready table format
    - Added to admin sidebar navigation
  - **Middleware Enforcement:**
    - SessionMiddleware updated to enforce email verification
    - Exempt routes: /auth/verify-email, /auth/resend-otp, /logout, /auth/google/callback
    - Automatic redirect to verification page if not verified
  - **Manual Autoloader:**
    - Created vendor/autoload.php for non-Composer setup
    - Loads PHPMailer from manual installation
    - Loads environment variables from .env
    - Supports Google API Client if installed
  - **Testing Tools:**
    - test-system.php - System verification script
    - VERIFICATION-RESULTS.md - Setup guide
    - SETUP-AND-TESTING-GUIDE.md - Complete testing guide
- **Tables added/modified:** 
  - Migration v5: users table (email verification and Google OAuth fields)
- **Routes added:**
  - GET /auth/verify-email, POST /auth/verify-email
  - POST /auth/resend-otp
  - GET /auth/google, GET /auth/google/callback
  - GET /admin/login-logs, GET /admin/activity-logs
- **Tested:** ✅ Verified working
- **Status:** ✅ Approved
- **Date:** 2026-05-01

---

## [v0.7] — Process 1 Parts A, B, C: Complete Enrollment Form System (Process 1)
- **Built:**
  - **Part A - Database & Model:**
    - Migration v6: enrollment_submissions table (76 BEEF fields), enrollment_documents table
    - EnrollmentModel with 15 methods (CRUD, draft, submit, document management)
    - Support for 3 enrollment types (New, Transfer, Returning with auto-fill)
  - **Part B - Location Data & JavaScript:**
    - LocationController with API endpoints (provinces, cities, barangays)
    - philippines.json with sample location data
    - enrollment.js with auto-save (30s), signature pad, validation, multi-step navigation
    - Direct PHP endpoints (api-provinces.php, api-cities.php, api-barangays.php)
  - **Part C - Multi-Step Form:**
    - EnrollmentController with 10 methods
    - 8-step enrollment form with all 76 BEEF fields
    - Step 1: Learner Info (20 fields) - name, birth, disabilities, IP, 4Ps
    - Step 2: Current Address (6 fields) - dynamic location dropdowns
    - Step 3: Permanent Address (7 fields) - "Same as Current" checkbox
    - Step 4: Parent/Guardian (12 fields) - father, mother, guardian info
    - Step 5: Previous School (6 fields) - conditional for Transfer/Returning
    - Step 6: Enrollment Details (10 fields) - grade level with "SPED Program" option
    - Step 7: Learning Modality (8 fields) - 7 modality checkboxes
    - Step 8: Documents & Signature (5 fields) - file uploads + signature pad
    - Draft save/load functionality
    - Document upload system (PSA, PWD ID, Medical Record, BEEF Form)
    - Signature pad integration (CDN-based)
- **Tables added/modified:** 
  - Migration v6: enrollment_submissions (76 fields), enrollment_documents
- **Tested:** ✅ Form working, all steps functional, auto-save working, documents uploading
- **Status:** ✅ Approved
- **Date:** 2026-05-01

---

## [v0.8] — Process 1 Part D: SPED Teacher Review Interface (Process 1)
- **Built:**
  - **Review Interface:**
    - review_detail.php - Complete review page with all 76 BEEF fields displayed in 8 sections
    - Document preview/download (images and PDFs)
    - Individual approve/reject buttons per document
    - Modal dialog for rejection with required reason
    - Status badges (pending/approved/rejected)
    - Review information (reviewer name, date, notes)
    - Signature display
  - **Parent Status Tracking:**
    - status.php - Card-based enrollment status page
    - View details link
    - Resubmit button for rejected enrollments
    - Status messages (pending/approved/rejected)
  - **Enrollment Details View:**
    - view.php - Detailed view accessible by both parent and teacher
    - Student information summary
    - Document status table with review history
    - Contact and address information
    - Role-specific actions
  - **Controller Methods:**
    - review() - List all enrollments for SPED teacher
    - reviewDetail($id) - View individual enrollment
    - approveDocument($documentId) - Approve single document
    - rejectDocument($documentId) - Reject with reason
    - status() - Parent enrollment status
    - view($id) - View enrollment details
  - **Workflow:**
    - Document-level approval (each file reviewed individually)
    - Auto-status update when all documents approved
    - Notifications sent to parent on each decision
    - Email notifications via MailHelper
    - Parent can resubmit if rejected
- **Tables added/modified:** None (uses existing Migration v6 tables)
- **Tested:** ✅ SPED teacher can review, approve/reject documents, notifications working, status updates correctly
- **Status:** ✅ Approved
- **Date:** 2026-05-01

---

## [v0.9] — UI Fixes & Session Management Improvements (Security Module Enhancement)
- **Built:**
  - **Email Verification UI Fix:**
    - Fixed verify_email.php messy layout
    - Changed from split-screen to auth-container (consistent with login)
    - Added paste support for OTP (paste all 6 digits at once)
    - Improved mobile responsiveness
    - Better visual feedback (green background when valid)
  - **Dashboard Role Routing Fix:**
    - Fixed DashboardController to handle 'general' role (was only 'user')
    - All roles now properly routed to correct dashboards
  - **Session Timeout Fix:**
    - BASE_PATH now defined before SessionMiddleware::start()
    - Session timeout redirects correctly for all roles
    - No more 404 errors on timeout
  - **Automatic Role Update Detection:**
    - Added checkRoleUpdate() method to SessionMiddleware
    - Checks every 10 seconds if database role matches session role
    - Auto-updates session and redirects to dashboard when role changes
    - No logout/login required after role approval
    - Error logging added for debugging
  - **Test Scripts:**
    - test-session-timeout.php - Session timeout testing
    - test-role-update.php - Role update detection (auto-refresh every 5s)
    - test-all-roles.php - All roles dashboard testing
    - test-enrollment-review.php - Enrollment review system testing
    - TEST-URLS.md - Correct URLs documentation
- **Files Modified:**
  - app/Views/auth/verify_email.php
  - app/Controllers/DashboardController.php
  - app/Middleware/SessionMiddleware.php
  - public/index.php
- **Tables added/modified:** None
- **Tested:** ✅ OTP UI clean, session timeout works for all roles, role updates within 10 seconds, all dashboards accessible
- **Status:** ✅ Approved
- **Date:** 2026-05-01

---

## Unreleased

---

## [v0.21] — Process 6-7 Learner Views Completion (Process 6 & 7)
- **Built:**
  - **Learner View Files (5 files):**
    - `app/Views/learning/assignments.php` - Assignments list with filter tabs (All, Pending, Submitted, Graded)
    - `app/Views/learning/view_module.php` - Module viewer with timer, file viewer (PDF/image/video), complete button
    - `app/Views/learning/view_assignment.php` - Assignment viewer with file upload, text answer, submission tracking
    - `app/Views/learning/progress.php` - Progress page with stats, achievement badges, activity timeline
    - `app/Views/learning/play_activity.php` - Interactive activity player with 8 activity types support
  - **JavaScript Enhancement:**
    - `public/js/activity-player.js` - Standalone activity player class (reusable, modular)
    - Support for 8 activity types: Multiple Choice, True/False, Fill Blanks, Matching, Drag & Drop Sorting, Sequencing, Image Labeling, Flashcards
    - Timer functionality with auto-save every 30 seconds
    - Confetti animation on activity completion (canvas-confetti library)
    - Drag-and-drop support (SortableJS library)
    - XSS protection with HTML escaping
  - **UI Features:**
    - Assignments list with filter tabs, due date badges, points display
    - Module viewer with timer, file viewer (PDF/image/video), complete button
    - Assignment viewer with text/file submission, grade display, teacher feedback
    - Progress page with stats cards, 10 achievement badges, activity timeline
    - Activity player with interactive questions, auto-grading, confetti animation
  - **External Libraries Integrated:**
    - SortableJS v1.15.0 (drag-and-drop functionality)
    - Canvas Confetti v1.6.0 (celebration animations)
  - **Cartoon UI Styling:**
    - Bright colors (yellow, orange, green, blue, purple, pink)
    - Comic Sans MS font family
    - Rounded borders, 3D effects, hover animations
    - Emoji icons, gradient backgrounds, pulse/bounce/shake animations
  - **Security Features:**
    - Authentication check on all routes
    - File encryption support (FileEncryptionHelper integration)
    - XSS protection with htmlspecialchars()
    - Activity logging for all actions
- **Tables added/modified:** None (uses existing tables from Migration v23)
- **Routes used:**
  - GET /learning/modules, /learning/assignments, /learning/module/{id}, /learning/assignment/{id}
  - GET /learning/activity/{id}, /learning/progress
  - POST /learning/complete-module, /learning/submit-activity, /learning/submit-assignment
- **Workflow:**
  - Learners view modules/assignments, complete activities, earn stars (1-3 based on score)
  - Progress tracked with achievement badges and timeline
  - Interactive activities with auto-grading and confetti celebration
- **Testing Status:**
  - ✅ All 5 view files created
  - ✅ JavaScript activity player created
  - ✅ Cartoon CSS styling applied
  - ⏳ Runtime testing pending (user cannot test due to XAMPP issues)
- **Tested:** Pending user testing (XAMPP issues on current PC)
- **Status:** Pending Approval (awaiting user testing)
- **Date:** 2026-05-05

---

## [v0.19] — Login Logs with User Information (Security Module 4 - Enhancement)
- **Built:**
  - **Database Schema Update:**
    - Migration v20: Added `user_id` column to `login_log` table
    - Added foreign key constraint to `users` table
    - Added index on `user_id` for query performance
  - **UserModel Enhancement:**
    - Updated `logLoginAttempt()` to lookup and store `user_id` from email
    - Handles cases where email doesn't exist in system (user_id = NULL)
  - **AdminController Enhancement:**
    - Updated `loginLogs()` to JOIN with `users` table
    - Query now fetches `user_name` and `user_role` for each login attempt
    - Search now works on both email and user name
  - **Login Logs View Update:**
    - Added "User Name" column showing actual user name or "Unknown User"
    - Added "Role" column showing user role badge (color-coded navy)
    - Updated table headers from 5 to 7 columns
    - Updated empty state colspan from 5 to 7
    - User icon indicators for known vs unknown users
    - Proper handling of NULL user_id (non-existent emails)
  - **Purpose:**
    - Admin can now see WHO attempted to login (not just email)
    - Shows user's role at time of login attempt
    - Helps identify legitimate users vs attackers
    - Better security monitoring and audit trail
- **Tables added/modified:** 
  - Migration v20: `login_log` table (added user_id, foreign key, index)
- **Tested:** ✅ Verified working - user_id correctly stored, JOIN query working, view displays user info
- **Status:** ✅ Approved
- **Date:** 2026-05-04

---

## [v0.18] — IEP Approval Queue for Principal (Fixed)
- **Built:**
  - **IEPDocumentController Methods:**
    - `approvalQueue()` - Display queue of P3 documents pending principal approval
    - `approve($documentId)` - Principal final approval of IEP P3 documents
  - **Approval Queue View** (app/Views/iep/approval_queue.php)
    - List of all P3 documents pending principal signature
    - Statistics cards (pending, approved, total)
    - Document table with student name, LRN, created by, date, signature count
    - View and Approve buttons for each document
    - Modal for viewing full document before approval
    - Confirmation dialog before approving
    - Activity logging for all approvals
  - **Sidebar Navigation Update:**
    - Added "IEP Approval Queue" link to Principal navigation (first item)
    - Positioned before other IEP links for quick access
- **Tables added/modified:** None (uses existing iep_p3_documents and iep_p3_signatures tables)
- **Security Features:**
  - RBAC middleware: `iep.approve` permission (Principal only)
  - Activity logging for all approvals
  - Prepared statements for all queries
  - JSON response for AJAX approval
  - Confirmation dialog to prevent accidental approvals
- **Tested:** Pending user testing
- **Status:** Pending Approval
- **Date:** 2026-05-04

## [v0.17] — LRN Notification System + IEP P2 & P3 Form Views
- **Built:**
  - **LRN Notification System (Fixed):**
    - Updated VerificationController to create in-app notifications when enrollment is verified
    - Updated StudentModel to create in-app notifications when learner account is created
    - Parents now receive BOTH email and in-app notifications with LRN credentials
    - Notifications appear in notification bell and can be viewed anytime
    - Added NotificationModel integration to both controllers
  - **IEP P2 Form View** (app/Views/iep/p2_form.php)
    - Online form fill option with 3 sections:
      - Section 1: Developmental Domains (Physical, Cognitive, Social-Emotional, Language)
      - Section 2: Strengths & Challenges
      - Section 3: Recommendations
    - PDF upload option for pre-filled documents
    - Form type selector (Fill Online vs Upload PDF)
    - Save and Print buttons
    - AJAX form submission
  - **IEP P2 Review View** (app/Views/iep/p2_review.php)
    - Display P2 document content (read-only)
    - Feedback textarea for reviewer comments
    - Signature pad for reviewer signature
    - Document status display
    - Print and Back buttons
    - Signature Pad library integration (CDN-based)
  - **IEP P3 Form View** (app/Views/iep/p3_form.php)
    - Online form fill option with 5 sections:
      - Section 1: Student Information
      - Section 2: Present Level of Performance
      - Section 3: Annual Goals & Objectives
      - Section 4: Special Education Services
      - Section 5: Accommodations & Modifications
    - PDF upload option for pre-filled documents
    - Form type selector (Fill Online vs Upload PDF)
    - Save and Print buttons
    - AJAX form submission
  - **IEP P3 Signature View** (app/Views/iep/p3_sign.php)
    - Display P3 document content (read-only)
    - Signature status cards showing who has signed and who's pending
    - Remarks textarea for signer comments
    - Signature pad for signer signature
    - Print and Back buttons
    - Signature Pad library integration (CDN-based)
- **Tables added/modified:** None (uses existing tables from v14-v19)
- **Security Features:**
  - RBAC middleware enforced on all routes
  - Activity logging for all actions
  - File upload validation (PDF only, max 10MB)
  - Prepared statements for all queries
  - XSS protection with HTML escaping
  - CSRF protection via middleware
  - Signature data stored as base64 encoded images
- **UI/UX Features:**
  - Custom Bootstrap theme (crimson #a01422, navy #1e4072)
  - Responsive design for all screen sizes
  - Form validation on client and server side
  - Alert messages for success/error feedback
  - Print-friendly layouts for all documents
  - Signature pad with clear button
  - Dynamic form type switching
- **Tested:** Pending user testing
- **Status:** Pending Approval
- **Date:** 2026-05-04

## [v0.16] — Process 4 & 5: IEP Meeting Scheduling + IEP Document Generation (P2 & P3)
- **Built:**
  - **IEPMeetingModel** (app/Models/IEPMeetingModel.php)
    - `create()` - Create IEP meeting with date/time/location
    - `findById()` - Get meeting details
    - `getByStudentId()` - Get all meetings for student
    - `getScheduled()` - Get all scheduled meetings
    - `getAvailableSlots()` - Get available time slots for user
    - `updateStatus()` - Update meeting status
    - `cancel()` - Cancel meeting with reason
  - **IEPP2DocumentModel** (app/Models/IEPP2DocumentModel.php)
    - `create()` - Create IEP P2 document
    - `findById()` - Get P2 document
    - `getByMeetingId()` - Get P2 by meeting
    - `uploadPDF()` - Upload pre-filled PDF
    - `sendForReview()` - Send to participant for review
    - `addReview()` - Add review and signature
    - `getReviewStatus()` - Get review status
    - Audit logging for all actions
  - **IEPP3DocumentModel** (app/Models/IEPP3DocumentModel.php)
    - `create()` - Create IEP P3 document
    - `findById()` - Get P3 document
    - `getByMeetingId()` - Get P3 by meeting
    - `uploadPDF()` - Upload pre-filled PDF
    - `sendForSignature()` - Send to participant for signature
    - `addSignature()` - Add signature
    - `getSignatureStatus()` - Get signature status
    - Audit logging for all actions
  - **IEPMeetingController** (app/Controllers/IEPMeetingController.php)
    - `schedule()` - Display schedule meeting form with calendars
    - `getAvailability()` - Get available time slots (AJAX)
    - `createMeeting()` - Create meeting and send invitations
    - `show()` - View meeting details
    - Email notifications to Parent, Guidance, Principal
    - Activity logging for all actions
  - **IEPDocumentController** (app/Controllers/IEPDocumentController.php)
    - **P2 Methods:**
      - `createP2()` - Display IEP P2 form
      - `submitP2()` - Save P2 document
      - `uploadP2()` - Upload P2 PDF
      - `sendP2ForReview()` - Send to participant for review
      - `reviewP2()` - Display review page
      - `submitP2Review()` - Submit review and signature
    - **P3 Methods:**
      - `createP3()` - Display IEP P3 form
      - `submitP3()` - Save P3 document
      - `uploadP3()` - Upload P3 PDF
      - `sendP3ForSignature()` - Send to participant for signature
      - `signP3()` - Display signature page
      - `submitP3Signature()` - Submit signature
    - Email notifications to all participants
    - Activity logging for all actions
  - **Security Features:**
    - RBAC middleware: `iep.meeting`, `iep.create`, `iep.sign` permissions
    - Activity logging with user ID, action, details, IP address
    - Audit trail for all IEP document actions
    - File upload validation (PDF only, max 10MB)
    - Prepared statements for all database queries
    - XSS protection with HTML escaping
    - CSRF protection via middleware
    - Rate limiting via RateLimitHelper
    - Encryption of sensitive data via EncryptionHelper
- **Database Changes:**
  - Migration v14: Expanded iep_meetings table with meeting_date, meeting_location, agenda, scheduled_by, timestamps
  - Migration v15: Created iep_p2_documents table (meeting_id, student_id, iep_data JSON, pdf_path, status)
  - Migration v16: Created iep_p2_reviews table (iep_p2_id, reviewer_id, reviewer_role, feedback, signature_data, reviewed_at)
  - Migration v17: Created iep_p3_documents table (meeting_id, student_id, iep_data JSON, pdf_path, status)
  - Migration v18: Created iep_p3_signatures table (iep_p3_id, signer_id, signer_role, signature_data, remarks, signed_at)
  - Migration v19: Created iep_audit_log table (document_type, document_id, user_id, action, details, ip_address, created_at)
- **Routes Added:**
  - GET /iep/meetings/schedule — Schedule meeting form
  - POST /iep/meetings/schedule — Create meeting
  - POST /iep/meetings/availability — Get available slots (AJAX)
  - GET /iep/meetings/{id} — View meeting
  - GET /iep/p2/create/{id} — Create P2 form
  - POST /iep/p2/submit — Save P2
  - POST /iep/p2/upload — Upload P2 PDF
  - POST /iep/p2/send-review — Send for review
  - GET /iep/p2/{id}/review — Review P2
  - POST /iep/p2/review-submit — Submit review
  - GET /iep/p3/create/{id} — Create P3 form
  - POST /iep/p3/submit — Save P3
  - POST /iep/p3/upload — Upload P3 PDF
  - POST /iep/p3/send-signature — Send for signature
  - GET /iep/p3/{id}/sign — Sign P3
  - POST /iep/p3/sign-submit — Submit signature
- **Workflow:**
  - **Process 4 - Meeting & IEP P2:**
    1. SPED teacher schedules meeting (selects date/time available for all)
    2. System shows calendar availability for Guidance and Principal
    3. Invitations sent to Parent, Guidance, Principal
    4. During meeting, SPED teacher fills or uploads IEP P2
    5. If Guidance/Principal absent, P2 disseminated to them
    6. They review, provide feedback, and sign
    7. Status: Draft → Pending Review → Reviewed & Signed
  - **Process 5 - IEP P3:**
    1. After meeting, SPED teacher creates IEP P3 document
    2. Can fill form online or upload pre-filled PDF
    3. Document status: Draft
    4. Send document to Parent, Guidance, Principal, School Head, ILRC Supervisor for signature
    5. Document status: Pending Signatures
    6. Each participant signs
    7. Once all required sign, status becomes: Signed and Approved
    8. Document stored in IEP repository
- **Security Implementation:**
  - All database queries use prepared statements (PDO)
  - Activity logging for all IEP actions (audit trail)
  - File upload validation and storage in secure directory
  - Email notifications with secure links
  - RBAC middleware enforces permissions
  - Encryption of sensitive data
  - Rate limiting on form submissions
  - CSRF protection on all forms
- **Tested:** Pending user testing
- **Status:** Pending Approval
- **Date:** 2026-05-04

## [v0.15] — Process 3: SPED Teacher Reviewing Initial Assessment (Parent-Submitted)
- **Built:**
  - **AssessmentModel** (app/Models/AssessmentModel.php)
    - `create()` - Create assessment from parent submission
    - `findById()` - Get specific assessment
    - `getByStudentId()` - Get all assessments for student
    - `getLatest()` - Get most recent assessment
    - `getByQuarter()` - Get assessment by quarter (Q1/Q2)
    - `getPendingForReview()` - Get all pending assessments for SPED teacher
    - `approve()` - Approve assessment
    - `reject()` - Reject assessment with reason
    - `getHistory()` - Get assessment version history
    - `getStudentsReadyForAssessment()` - Get students ready for assessment
  - **AssessmentController** (app/Controllers/AssessmentController.php)
    - `index()` - Assessment dashboard (SPED teacher review list)
    - `conduct($studentId)` - Display parent assessment form
    - `submit()` - Save parent assessment submission
    - `view($assessmentId)` - Display assessment for SPED teacher review
    - `approve($assessmentId)` - Approve assessment
    - `reject($assessmentId)` - Reject assessment with reason
    - `history($studentId)` - View assessment version history
    - Email notifications to SPED teacher on new submission
    - Email notifications to parent on approval/rejection
  - **Assessment Form View** (app/Views/assessment/conduct.php)
    - Section A: Learner Information (pre-filled from student_records, read-only)
    - Section A.2: Education History (parent fills)
    - Section B: Assessment Information (parent fills with dynamic table)
    - Support services checkboxes
    - Dynamic row addition/removal for assessment services
    - Form validation and submission
  - **Assessment Dashboard** (app/Views/assessment/index.php)
    - List of pending assessments for SPED teacher review
    - Statistics cards (total, Q1, Q2, awaiting action)
    - Search by student name or LRN
    - Filter by quarter (Q1/Q2)
    - Filter by status (pending/approved/rejected)
    - Quick access to assessment details
  - **Assessment Review View** (app/Views/assessment/view.php)
    - Display all assessment data (read-only)
    - Section A.2 and Section B display
    - Approve/Reject buttons for SPED teacher
    - Rejection modal with reason capture
    - Status display and review history
  - **Assessment History View** (app/Views/assessment/history.php)
    - Timeline view of all assessment versions
    - Quarter tracking (Q1, Q2)
    - Status display for each version
    - Rejection reasons displayed
    - Education history and assessment info for each version
  - **Routes Added:**
    - GET /assessment - Assessment dashboard (SPED teacher)
    - GET /assessment/conduct/{id} - Parent assessment form
    - POST /assessment/submit - Save parent assessment
    - GET /assessment/view/{id} - Review assessment (SPED teacher)
    - POST /assessment/{id}/approve - Approve assessment
    - POST /assessment/{id}/reject - Reject assessment
    - GET /assessment/{id}/history - Assessment history
  - **Sidebar Navigation:**
    - Added "Submit Assessment" link for parent role
    - Updated "Conduct Assessment" link for SPED teacher (now links to dashboard)
- **Tables added/modified:**
  - Migration v13: Expanded assessment_records table with:
    - parent_id (who submitted)
    - submitted_data (JSON)
    - education_history (JSON)
    - assessment_info (JSON)
    - status (pending/approved/rejected)
    - reviewed_by (SPED teacher)
    - review_note (rejection reason)
    - quarter (Q1/Q2)
    - submitted_at, reviewed_at timestamps
- **Workflow:**
  - Parent fills assessment form (Section A.2 Education History + Section B Assessment Info)
  - Section A (Learner Information) pre-filled from student_records (read-only)
  - Parent submits assessment
  - SPED teacher receives notification
  - SPED teacher reviews assessment on dashboard
  - SPED teacher approves or rejects with reason
  - Parent receives email notification
  - Assessment versioning by quarter (Q1/Q2)
- **Security:**
  - Parent can only access their own child's assessment form
  - SPED teacher can only review assessments
  - Activity logging for all assessment actions
  - Email notifications to parent on approval/rejection
  - RBAC middleware: assessment.manage, assessment.conduct, assessment.view permissions
- **Tested:** Pending user testing
- **Status:** Pending Approval
- **Date:** 2026-05-04

## [v0.10] — Process 1 UI Improvements (Merged Address Steps & Expanded Locations)
- **Built:** 
  - Merged Step 3 (Permanent Address) into Step 2 (Current Address) with "Same as Current Address" checkbox
  - Reduced enrollment form from 8 steps to 7 steps
  - Updated all step files with correct numbering (step4→step3, step5→step4, etc.)
  - Added conditional notes in Previous School step based on enrollment type (optional for new students, required for transfer/returning)
  - Expanded Philippine location data in philippines.json:
    - Added 4 new provinces (Bohol, Negros Occidental, Iloilo, Pampanga, Laguna)
    - Expanded Cebu cities from 4 to 9 (added Danao, Toledo, Bogo, Carcar, Naga)
    - Expanded Metro Manila cities from 4 to 10 (added Caloocan, Taguig, Parañaque, Las Piñas, Muntinlupa, Valenzuela)
    - Expanded Davao City barangays from 10 to 200+
    - Total: 7 provinces, 40+ cities, 1000+ barangays
  - Updated enrollment.js to handle 7 steps instead of 8
  - Updated form.php progress bar (Step X of 7)
- **Tables added/modified:** None (UI-only changes)
- **Tested:** Pending user testing
- **Status:** Pending Approval
- **Date:** 2026-05-02

---

## [v0.11] — Process 1 Form Field Improvements (Document Requirements & Birth Info)
- **Built:**
  - **Document Requirements Updated:**
    - Made PWD ID optional (was required)
    - Made Medical Record optional (was required)
    - Only PSA Birth Certificate is now required
    - BEEF Form remains optional
    - Updated document cards with proper styling (red border for required, gray for optional)
    - Updated alert message to clarify requirements
  - **Birth Information Improvements:**
    - Removed region field from both current and permanent address
    - Changed birth place from 2 fields (city + province) to single field
    - Added auto-calculate age function when birth date is selected
    - Age field now readonly and auto-populated
    - Added calculateAge() JavaScript function
    - Age calculation runs on page load if birth date exists
  - **Field Changes:**
    - Removed: current_region, permanent_region, place_of_birth_city, place_of_birth_province
    - Added: birth_place (single text field)
    - Modified: age (now auto-calculated and readonly)
- **Tables added/modified:** None (UI-only changes, database schema already supports these fields)
- **Tested:** Pending user testing
- **Status:** Pending Approval
- **Date:** 2026-05-02

---

## [v0.12] — Process 1 Part E: Parent Dashboard Integration & Enrollment Tracking
- **Built:**
  - **Enhanced Parent Dashboard:**
    - Statistics cards showing total, pending, approved, and rejected enrollments
    - Enrollment list with child name, grade level, submission date
    - Status badges (Under Review, Approved, Rejected) with color coding
    - Progress bars showing document approval status (X/Y docs approved)
    - Quick action buttons: View Details, Resubmit (for rejected)
    - Empty state with "Enroll Your Child" call-to-action
    - Quick Actions section with "Enroll Another Child" and "View All Enrollments"
  - **EnrollmentModel Enhancements:**
    - `getEnrollmentsWithStats($parentId)` - Fetch enrollments with document counts
    - `getParentStats($parentId)` - Get enrollment statistics (total, pending, approved, rejected)
  - **DashboardController Update:**
    - Parent dashboard now fetches enrollment data automatically
    - Passes enrollments and stats to view
  - **Sidebar Navigation Update:**
    - Added "My Enrollments" link for parent role (links to `/enrollment/status`)
    - Changed "Submit Enrollment" to "Enroll Child" for clarity
    - Added "Services" link for parents
    - Updated SPED Teacher link from "Verify Enrollment" to "Review Enrollments" (links to `/enrollment/review`)
  - **User Experience:**
    - Parents can now see all their enrollments on dashboard
    - Visual progress tracking with progress bars
    - Clear status indicators with icons
    - One-click access to enrollment details
    - Resubmit option for rejected enrollments
- **Tables added/modified:** None (uses existing enrollment_submissions and enrollment_documents tables)
- **Tested:** Pending user testing
- **Status:** Pending Approval
- **Date:** 2026-05-02

---

## [vN] — Feature Name (Process N or Security Module N)
- **Built:** [what was created — controllers, models, views]
- **Tables added/modified:** [schema.sql changes, or "none"]
- **Tested:** [what was verified and how]
- **Status:** Approved
- **Date:** YYYY-MM-DD

-->

---

## [v0.13] — Security Modules 3-6: Encryption, CSRF, Rate Limiting, DLP (In Progress)
- **Built:**
  - **EncryptionHelper** (app/Helpers/EncryptionHelper.php)
    - AES-256-CBC encryption/decryption
    - Secure token generation
    - Field-level encryption for arrays
    - One-way hashing
  - **CSRFHelper** (app/Helpers/CSRFHelper.php)
    - CSRF token generation and validation
    - One-time use tokens with 1-hour expiry
    - Session-tied tokens
    - Automatic cleanup of expired tokens
    - Failure logging to activity_log
  - **RateLimitHelper** (app/Helpers/RateLimitHelper.php)
    - Login rate limiting (5 attempts per email, 10 per IP, 15-min window)
    - Registration rate limiting (3 per email, 10 per IP, 15-min window)
    - Attempt tracking and clearance
    - Remaining attempts calculation
    - Automatic cleanup of old records
  - **DLPHelper** (app/Helpers/DLPHelper.php)
    - Watermark generation and rendering
    - Screenshot blocking (PrintScreen, Ctrl+PrintScreen, Shift+Ctrl+S)
    - Copy/paste blocking (copy, cut, paste, selection, drag)
    - Print blocking (Ctrl+P, Cmd+P)
    - Export blocking (downloads, Ctrl+S)
    - Configurable DLP settings
    - Event logging for audit trail
  - **AuthController Updates:**
    - Added CSRF token verification to login and register
    - Added rate limiting checks to login and register
    - Integrated RateLimitHelper for attempt tracking
    - Integrated CSRFHelper for token validation
  - **View Updates:**
    - Added CSRF tokens to login.php form
    - Added CSRF tokens to register.php form
    - Hidden input fields with token values
- **Tables added/modified:**
  - Migration v8: `encryption_audit` table (encryption operation tracking)
  - Migration v9: `csrf_tokens` table (CSRF token storage)
  - Migration v10: `rate_limit_log` table (login/registration attempt tracking)
  - Migration v11: `dlp_settings` table (DLP configuration)
- **Tested:** Pending
- **Status:** In Progress
- **Date:** 2026-05-04



## [v0.13] — Security Modules 3-6: Encryption, CSRF, Rate Limiting, DLP
- **Built:**
  - EncryptionHelper (AES-256-CBC encryption/decryption for PII)
  - CSRFHelper (CSRF token generation and validation)
  - RateLimitHelper (Login/registration rate limiting)
  - DLPHelper (Data loss prevention with watermarking)
  - Updated AuthController with rate limiting and CSRF verification
  - Updated login.php and register.php with CSRF token fields
  - Setup script: setup-security-tables.php for database table creation
- **Tables added/modified:**
  - Migration v8: `encryption_audit` table
  - Migration v9: `csrf_tokens` table
  - Migration v10: `rate_limit_log` table
  - Migration v11: `dlp_settings` table
- **Tested:** ✅ All security modules working
  - CSRF tokens generated and validated
  - Rate limiting blocks after 5 failed attempts
  - Encryption/decryption working
  - DLP settings configured
  - Login and registration flows working
- **Status:** ✅ Approved
- **Date:** 2026-05-04

---

## [v0.14] — Process 2: SPED Teacher Enrollment Verification System
- **Built:**
  - **StudentModel** (app/Models/StudentModel.php)
    - `generateLRN()` - Generate unique 12-digit LRN (YYYYMMDDNNNN format)
    - `createStudentRecord($enrollmentId, $verifiedBy)` - Create student record with LRN
    - `createLearnerAccount($studentId, $lrn, $enrollmentData)` - Create learner user account
    - `sendLRNCredentialsEmail()` - Email parent with LRN and temporary password
    - Additional CRUD methods for student management
  - **VerificationController** (app/Controllers/VerificationController.php)
    - `index()` - List all pending enrollments for verification
    - `show($id)` - Display enrollment detail with all 76 BEEF fields
    - `verify($id)` - Verify enrollment and auto-generate LRN + learner account
    - Activity logging for all verification actions
  - **Verification Dashboard** (app/Views/verification/index.php)
    - List pending enrollments with search and filter
    - Document progress bars
    - Status badges (pending/approved/rejected)
    - Quick access to enrollment details
  - **Enrollment Detail View** (app/Views/verification/show.php)
    - All 76 BEEF fields organized in 7 sections
    - Document approval interface (approve/reject per document)
    - Print-friendly layout (A4 optimized)
    - Auto-verify button when all documents approved
    - Rejection modal with reason capture
- **Tables added/modified:**
  - Migration v12: 
    - Added `learner` role to users table ENUM
    - Added `lrn` column to student_records (12-digit unique)
    - Added `learner_account_created` column to enrollment_submissions
- **Routes added:**
  - GET /verification - Verification dashboard
  - GET /verification/{id} - Enrollment detail view
  - POST /verification/{id}/verify - Verify enrollment and create learner account
- **Workflow:**
  - SPED Teacher reviews pending enrollments
  - Approves/rejects individual documents
  - When all documents approved, clicks "Verify Enrollment"
  - System auto-generates 12-digit LRN (YYYYMMDDNNNN)
  - Creates learner user account with LRN as credentials
  - Sends email to parent with LRN and temporary password
  - Enrollment status updated to "verified"
  - Parent notified of verification
- **Security:**
  - RBAC middleware: `enrollment.verify` permission (SPED Teacher only)
  - Activity logging for all approvals/rejections
  - Email notifications to parent
  - Temporary password for learner account (must change on first login)
- **Tested:** Pending user testing
- **Status:** Pending Approval
- **Date:** 2026-05-04

**Navigation Updates Added:**
- **Guidance Role Sidebar:**
  - "Schedule IEP Meeting" - Link to schedule form
  - "IEP Meetings" - Link to meetings list
  - "Review P2 Assessments" - Link to P2 review list
  - "Sign IEP Documents" - Link to P3 signature list
- **Principal Role Sidebar:**
  - "IEP Meetings" - Link to meetings list
  - "Review P2 Assessments" - Link to P2 review list
  - "Sign IEP Documents" - Link to P3 signature list
  - "Staff Requests" - Link to staff requests
  - "Reports" - Link to reports
- **View Files Created:**
  - `app/Views/iep_meeting/index.php` - Meetings list
  - `app/Views/iep/p2_review_list.php` - P2 review list
  - `app/Views/iep/p3_sign_list.php` - P3 signature list
- **Routes Added:**
  - GET /iep/meetings - Meetings list (SPED teacher, Guidance, Principal)
  - GET /iep/p2/review — P2 review list (Guidance/Principal)
  - GET /iep/p3/sign — P3 signature list (All signers)


---

## [v0.20] — File Encryption System (Security Module 3)
- **Built:**
  - **FileEncryptionHelper** (app/Helpers/FileEncryptionHelper.php)
    - `encryptFile()` - Encrypt uploaded files using AES-256-CBC
    - `serveDecryptedFile()` - Decrypt and serve files on-the-fly
    - `getDecryptedContents()` - Get decrypted file contents for processing
    - `migrateFile()` - Migrate existing files to encrypted format
    - `getThumbnail()` - Generate thumbnails for encrypted images
    - `isEncrypted()` - Check if file is encrypted
    - Unique IV (Initialization Vector) per file for maximum security
    - Original files deleted after encryption
    - Encrypted files stored in `/uploads/encrypted/` directory
  - **FileController** (app/Controllers/FileController.php)
    - `serve($filePath)` - Serve encrypted files (decrypt and output inline)
    - `download($filePath)` - Download encrypted files (decrypt and force download)
    - `thumbnail($filePath)` - Generate and serve thumbnails for encrypted images
    - Authentication check (must be logged in)
    - Base64 encoded file paths for security
    - Automatic fallback for unencrypted files
    - Original filename lookup from database
  - **Updated Controllers:**
    - `EnrollmentController::uploadFile()` - Now encrypts enrollment documents on upload
    - `RoleController::uploadFile()` - Now encrypts role verification documents on upload
  - **Updated Views:**
    - `app/Views/verification/show.php` - Uses encrypted file URLs via `/file/serve/{base64_path}`
    - `app/Views/enrollment/view.php` - Uses encrypted file URLs for all document previews and downloads
  - **Migration Script:**
    - Created and executed `public/migrate-encrypt-files.php`
    - Successfully encrypted 6 existing files (2 enrollment + 4 role documents)
    - Updated database paths to point to encrypted files
    - Deleted original unencrypted files
    - Script deleted after successful migration
  - **Security Features:**
    - AES-256-CBC encryption (military-grade)
    - Unique IV per file (prevents pattern analysis)
    - Encryption key stored in `.env` file (ENCRYPTION_KEY)
    - Files unreadable on disk without decryption key
    - Authentication required to access files
    - Base64 encoded paths prevent directory traversal
    - Original files deleted after encryption
    - Transparent encryption/decryption (existing functions continue to work)
- **Tables added/modified:** None (uses existing enrollment_documents and role_documents tables)
- **Routes Added:**
  - GET /file/serve/{path} - Serve encrypted file (decrypt and display inline)
  - GET /file/download/{path} - Download encrypted file (decrypt and force download)
  - GET /file/thumbnail/{path} - Generate thumbnail for encrypted image
- **Workflow:**
  - **New Uploads:**
    1. User uploads file (enrollment document or role verification)
    2. File is encrypted using AES-256-CBC with unique IV
    3. Encrypted file stored in `/uploads/encrypted/` with hashed filename
    4. Original file deleted for security
    5. Database stores encrypted file path
  - **File Access:**
    1. User clicks view/download link
    2. System checks authentication
    3. File path decoded from base64
    4. File decrypted on-the-fly
    5. Served to user (inline or download)
    6. Decrypted content never stored on disk
  - **Migration:**
    1. Migration script loaded all existing file paths from database
    2. Each file encrypted with AES-256-CBC
    3. Database updated with new encrypted path
    4. Original file deleted
    5. 6 files successfully migrated (100% success rate)
- **Files Encrypted:**
  - 2 enrollment documents (PSA Birth Certificate, PWD ID)
  - 4 role verification documents (Government IDs, Proof of Designation)
  - All stored in `/uploads/encrypted/` with `.enc` extension
  - Original directories (`/uploads/enrollment/`, `/uploads/role_verification/`) now empty
- **Testing Results:**
  - ✅ Encryption/decryption verified working (77 bytes → 168 bytes encrypted → 77 bytes decrypted)
  - ✅ File serving working (images display, PDFs open)
  - ✅ Download working (files download with original names)
  - ✅ Migration successful (6/6 files encrypted, 0 failures)
  - ✅ Original files deleted (security confirmed)
  - ✅ Database paths updated correctly
  - ✅ Views updated to use encrypted URLs
- **Tested:** ✅ Verified working - all files encrypted, serving correctly, no data loss
- **Status:** ✅ Approved
- **Date:** 2026-05-04


---

## [v1.0] — XAMPP Environment Setup (System Configuration)
- **Built:** 
  - `.env` - Environment configuration with XAMPP defaults (DB, email, OAuth, security)
  - `SETUP-AND-TESTING-GUIDE.md` - Complete setup instructions with troubleshooting
  - `test-db.php` - Database connection verification script (5-step test)
  - `setup-xampp.bat` - Windows automated setup script
  - `setup-xampp.sh` - Bash automated setup script (Git Bash/WSL)
- **Configuration:**
  - Database: localhost, sped_lms, root (no password)
  - Email: Gmail SMTP with PHPMailer
  - Security: 32-character encryption key
  - Session: 15-minute timeout
  - App URL: http://localhost/Signedd/public
- **Setup Process:**
  1. Create database in phpMyAdmin
  2. Import schema.sql
  3. Install Composer dependencies
  4. Configure .env file
  5. Set file permissions (logs, uploads)
  6. Test database connection
- **Testing Tools:**
  - test-db.php checks: connection, tables, users, migrations, autocommit
  - setup-xampp.bat automates: database creation, schema import, dependency install
  - setup-xampp.sh for Git Bash/WSL users
- **Tables added/modified:** None (uses existing schema.sql)
- **Tested:** Ready for user testing
- **Status:** Approved
- **Date:** 2026-05-05


---

## [v1.1] — Registration & OTP Verification Fix
- **Fixed:** 
  - SessionMiddleware exempt routes updated to include `/register` and `/login`
  - Changed route matching from `strpos === 0` to `strpos !== false` for better matching
  - Removed duplicate redirect check in `checkEmailVerification()`
  - Registration now properly redirects to `/auth/verify-email`
  - OTP verification page now accessible after registration
- **Modified Files:**
  - `app/Middleware/SessionMiddleware.php` — Updated exempt routes and matching logic
- **Testing:**
  - Created `test-registration-flow.php` — Verification script for registration flow
- **Tables added/modified:** None
- **Tested:** Ready for user testing
- **Status:** Fixed
- **Date:** 2026-05-05


---

## [v1.2] — Secure File Access with Decryption
- **Built:**
  - `FileController` — Central file handler with decryption and permission checks
  - Secure file viewing/downloading for all encrypted uploads
  - Permission-based access control for different file types
  - Audit logging for all file access
  - Support for PDF, images, videos, and documents
- **Features:**
  - Decrypt files on-the-fly before serving to browser
  - Check user permissions before allowing access
  - Stream files with proper MIME types
  - Separate view (inline) and download endpoints
  - Log all file access for audit trail
- **File Types Supported:**
  - Enrollment documents (PSA, PWD ID, Medical Record, BEEF)
  - Role request documents (ID, proof of designation)
  - Learning materials (PDF, videos, images)
  - Assignment submissions (student uploads)
  - IEP documents (P2, P3 PDFs)
- **Permission Rules:**
  - Enrollment documents: Parent (owner), SPED Teacher, Admin
  - Role documents: Applicant (owner), Approver, Admin
  - Learning materials: Teacher (uploader), Assigned learner, Admin
  - Assignment submissions: Student (owner), Teacher, Admin
  - IEP documents: Parent, SPED Teacher, Guidance, Principal, Admin
- **Modified Files:**
  - Created: `app/Controllers/FileController.php`
  - Updated: `routes/web.php` — Added file view/download routes
  - Updated: `app/Views/enrollment/review_detail.php` — Use new file routes
  - Updated: `app/Views/learning/view_module.php` — Use new file routes
  - Updated: `app/Views/learning/view_assignment.php` — Use new file routes
- **Routes Added:**
  - `GET /file/view/{type}/{id}` — View file inline with decryption
  - `GET /file/download/{type}/{id}` — Download file with decryption
- **Tables added/modified:** None (uses existing tables)
- **Tested:** Ready for user testing
- **Status:** Complete
- **Date:** 2026-05-05


---

## [v1.3] — File Decryption Test Tools
- **Built:**
  - `test-decrypt-files.php` — Decrypt all files from database for testing
  - `public/test-decrypted/index.php` — Viewer for decrypted test files
  - `public/test-decrypted/.htaccess` — Allow direct access to test files
  - `TEST-DECRYPT-GUIDE.md` — Complete testing guide
- **Features:**
  - Test decryption of enrollment documents
  - Test decryption of learning materials
  - Test decryption of assignment submissions
  - Test decryption of all files in encrypted directory
  - Auto-detect file types from content
  - Save decrypted files with correct extensions
  - Grid viewer with file icons
  - View and download buttons
- **Purpose:**
  - Verify encryption key is correct
  - Verify all files can be decrypted
  - Verify FileController will work correctly
  - Debug decryption issues
- **Security:**
  - Test files gitignored (not committed)
  - Only for testing, not production
  - Delete after testing
- **Files Created:**
  - `test-decrypt-files.php`
  - `public/test-decrypted/index.php`
  - `public/test-decrypted/.htaccess`
  - `public/test-decrypted/.gitignore`
  - `TEST-DECRYPT-GUIDE.md`
- **Tables added/modified:** None
- **Tested:** Ready for user testing
- **Status:** Complete
- **Date:** 2026-05-05


## [v1.3] — File Decryption Fix (Security Module 3)
- **Built:** Fixed missing `$basePath` variable in `EnrollmentController::reviewDetail()` method
- **Issue:** Views couldn't generate FileController URLs because `$basePath` wasn't passed from controller
- **Solution:** Added `$basePath = $this->basePath;` before requiring view file
- **Verified:** All 6 encrypted files decrypt successfully with valid PDF headers
- **Testing:** Created `test-file-controller.php` and `test-decrypt-actual.php` for verification
- **Tables added/modified:** None
- **Tested:** CLI decryption test shows all files decrypt with valid PDF headers
- **Status:** Fixed - Ready for browser testing
- **Date:** 2026-05-05


## [v1.4] — Removed File Encryption (Simplified Upload System)
- **Built:** Simplified file upload system by removing encryption
- **Changes:**
  - Removed FileEncryptionHelper calls from all upload methods
  - Updated EnrollmentController::uploadFile() - direct file storage
  - Updated RoleController::uploadFile() - direct file storage
  - Updated IEPImplementationController::uploadFile() - direct file storage
  - Updated LearningController::submitAssignment() - direct file storage
  - Updated views to use direct file paths instead of FileController routes
  - Files now stored unencrypted in uploads/ directories
  - View/download works with simple direct links
- **Views Updated:**
  - app/Views/enrollment/review_detail.php - Direct file links
  - app/Views/learning/view_module.php - Direct file links
  - app/Views/learning/view_assignment.php - Direct file links
- **Security:** Permission checks still enforced via RBAC middleware
- **Tables added/modified:** None
- **Tested:** Pending user testing
- **Status:** Ready for Testing
- **Date:** 2026-05-06


## [v1.5] — Fixed 404 Error on File Viewing (Missing View Updates)
- **Issue:** Parent and SPED teacher couldn't view uploaded files - 404 error
- **Root Cause:** Two views still using old FileController routes (`/file/serve/`, `/file/view/`)
- **Fixed:**
  - Updated `app/Views/enrollment/view.php` - Changed from `/file/serve/` to direct paths
  - Updated `app/Views/verification/show.php` - Changed from `/file/serve/` to direct paths
  - Removed base64 encoding of file paths (no longer needed)
- **Cleanup:**
  - Removed 8 redundant test files (test-decrypt-*.php, test-file-*.php, check-*-encrypted.php)
  - Kept only `test-file-url.php` for testing
- **Verified:** Database has correct paths, files exist, no encrypted paths in DB
- **Tables added/modified:** None
- **Tested:** Ready for browser testing
- **Status:** Fixed - Ready for Testing
- **Date:** 2026-05-06


## [v1.6] — BEEF Form Redesigned to Document Style (Print-Friendly)
- **Built:** Clean, document-style BEEF enrollment form
- **Design Changes:**
  - Removed red colors and Bootstrap cards
  - Clean black text on white background
  - Official document header (Republic of the Philippines, DepEd)
  - Simple borders and clean spacing
  - Print-friendly layout (hides sidebar, buttons when printing)
  - Times New Roman font for official look
  - Section headers with gray background
  - Proper form field labels in uppercase
- **New Files:**
  - `public/css/beef-document.css` - Document-style CSS
  - `app/Views/enrollment/steps/step1_learner_info_document.php` - Redesigned Step 1
- **Modified:**
  - `app/Views/enrollment/form.php` - Include new CSS and document-style step
- **Features:**
  - Looks like official DepEd form
  - Print-ready (auto page breaks between steps)
  - Responsive (mobile-friendly)
  - All functionality preserved
- **Tables added/modified:** None
- **Tested:** Pending user testing
- **Status:** Ready for Testing
- **Date:** 2026-05-06


## [v1.7] — Simplified Document Approval Logic with Better Error Handling
- **Issue:** Approve button showing JSON parse error
- **Root Cause:** Missing error handling and unclear logic flow
- **Fixed:**
  - Simplified `approveDocument()` method with try-catch
  - Simplified `rejectDocument()` method with try-catch
  - Added validation for missing enrollment_id
  - Added validation for empty rejection reason
  - Better error messages in session
  - Clear success messages showing progress
- **Logic Flow:**
  - Parent uploads X documents (1-4)
  - SPED teacher approves each document individually
  - When ALL uploaded documents approved → Enrollment status = "Verified" ✅
  - If ANY document rejected → Enrollment status = "Rejected" ❌
  - Required: PSA Birth Certificate + Filled enrollment form
- **Messages:**
  - Single document approved: "Document approved: [type]. Waiting for other documents."
  - All documents approved: "Document approved! All documents verified - Enrollment is now complete."
  - Document rejected: "Document rejected: [type]. Parent has been notified."
- **Tables added/modified:** None
- **Tested:** Pending user testing
- **Status:** Ready for Testing
- **Date:** 2026-05-06

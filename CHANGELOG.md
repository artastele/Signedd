# CHANGELOG — SPED LMS

> This file is updated after every approved feature. Never skip this step.
> Format: describe what was built, what schema changed, what was tested, and the approval date.

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

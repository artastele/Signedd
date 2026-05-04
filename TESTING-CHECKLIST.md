# SPED LMS — Comprehensive Testing Checklist

**Last Updated:** May 4, 2026  
**Test Environment:** Development  
**Tester:** [To be filled]  
**Date:** [To be filled]

---

## Pre-Testing Setup

- [ ] Database reset to clean state
- [ ] Test data loaded (sample users, enrollments, assessments)
- [ ] Email service configured and tested
- [ ] File upload directory created and writable
- [ ] Logs directory created and writable
- [ ] Browser cache cleared
- [ ] Session cookies cleared
- [ ] Test accounts created for each role

---

## Phase 1: Authentication & Authorization

### Login Functionality
- [ ] Login with correct email and password
- [ ] Login with incorrect email (error message displayed)
- [ ] Login with incorrect password (error message displayed)
- [ ] Login with non-existent email (error message displayed)
- [ ] Password field masked (not visible as plain text)
- [ ] "Remember me" functionality (if implemented)
- [ ] Login attempt logging (check activity_log table)
- [ ] Rate limiting after 5 failed attempts
- [ ] Rate limiting error message displayed
- [ ] Rate limiting cooldown (15 minutes)

### Registration Functionality
- [ ] Register with valid data
- [ ] Register with existing email (error message)
- [ ] Register with weak password (error message)
- [ ] Register with mismatched passwords (error message)
- [ ] Register with missing required fields (error message)
- [ ] Password strength indicator displayed
- [ ] Terms & conditions acceptance required
- [ ] Registration attempt logging
- [ ] Rate limiting after 3 failed attempts
- [ ] Email verification required after registration

### Email Verification
- [ ] OTP email sent after registration
- [ ] OTP email contains 6-digit code
- [ ] OTP email contains expiration time (10 minutes)
- [ ] OTP verification with correct code
- [ ] OTP verification with incorrect code (error message)
- [ ] OTP verification with expired code (error message)
- [ ] OTP attempt tracking (max 3 attempts)
- [ ] Resend OTP functionality
- [ ] Resend OTP cooldown (60 seconds)
- [ ] Welcome email sent after verification
- [ ] User redirected to role selection after verification
- [ ] Email verification enforced (cannot access dashboard without verification)

### Session Management
- [ ] Session created after login
- [ ] Session timeout after 15 minutes of inactivity
- [ ] Session timeout warning displayed (before timeout)
- [ ] User redirected to login after timeout
- [ ] Logout clears session
- [ ] Logout clears cookies
- [ ] Multiple sessions per user prevented (if required)
- [ ] Session data encrypted (if required)

### Role-Based Access Control
- [ ] Parent can access parent dashboard
- [ ] Parent cannot access SPED teacher dashboard
- [ ] SPED teacher can access teacher dashboard
- [ ] SPED teacher cannot access principal dashboard
- [ ] Guidance can access guidance dashboard
- [ ] Guidance cannot access admin dashboard
- [ ] Principal can access principal dashboard
- [ ] Principal cannot access parent dashboard
- [ ] Admin can access all dashboards
- [ ] Unauthorized access returns 403 error
- [ ] Permission checks enforced on all routes

---

## Phase 2: Role Selection & Verification

### Parent Role Selection
- [ ] Parent role assigned immediately after email verification
- [ ] Parent dashboard accessible after role assignment
- [ ] Parent can proceed to enrollment

### Staff Role Application
- [ ] Staff application form displayed
- [ ] All required fields present (name, email, role, documents)
- [ ] File upload for government ID
- [ ] File upload for proof of designation
- [ ] File upload validation (PDF, JPG, PNG only)
- [ ] File size validation (max 5MB)
- [ ] Application submission successful
- [ ] Confirmation message displayed
- [ ] Application status shows "Pending"
- [ ] Email notification sent to Principal

### Admin Role Request Review
- [ ] Admin can view pending role requests
- [ ] Admin can view applicant details
- [ ] Admin can view uploaded documents
- [ ] Admin can approve role request
- [ ] Admin can reject role request with reason
- [ ] Approval email sent to applicant
- [ ] Rejection email sent to applicant
- [ ] Rejection reason included in email
- [ ] Applicant can reapply after rejection
- [ ] Approved applicant can access staff dashboard

### Principal Staff Request Review
- [ ] Principal can view pending staff requests
- [ ] Principal can view applicant details
- [ ] Principal can view uploaded documents
- [ ] Principal can approve staff request
- [ ] Principal can reject staff request with reason
- [ ] Approval email sent to applicant
- [ ] Rejection email sent to applicant
- [ ] Rejection reason included in email
- [ ] Applicant can reapply after rejection
- [ ] Approved applicant can access staff dashboard

---

## Phase 3: Notification System

### Notification Display
- [ ] Notification bell visible in topbar
- [ ] Unread badge count displayed on bell
- [ ] Notification dropdown opens on bell click
- [ ] Notification list displays all notifications
- [ ] Notification timestamp displayed (Just now, X minutes ago, etc.)
- [ ] Notification icons displayed correctly
- [ ] Notification messages displayed correctly

### Notification Actions
- [ ] Mark as read (individual notification)
- [ ] Mark all as read
- [ ] Delete notification (individual)
- [ ] Delete all notifications
- [ ] Notification count updates after action
- [ ] Unread badge updates after action

### Notification Types
- [ ] Role approval notification created
- [ ] Role rejection notification created
- [ ] Enrollment verification notification created
- [ ] Assessment approval notification created
- [ ] Assessment rejection notification created
- [ ] IEP meeting invitation notification created
- [ ] IEP document review notification created
- [ ] IEP document signature notification created

### Notification Polling
- [ ] Notifications updated every 30 seconds
- [ ] New notifications appear without page refresh
- [ ] Notification count updates in real-time
- [ ] Polling stops when user inactive (if implemented)

---

## Phase 4: Process 1 - Enrollment

### Enrollment Form Navigation
- [ ] Form displays 7 steps
- [ ] Progress bar shows current step
- [ ] Next button advances to next step
- [ ] Previous button goes back to previous step
- [ ] Step numbers displayed correctly
- [ ] All steps accessible via step indicator (if implemented)

### Step 1: Learner Information
- [ ] All 20 fields present
- [ ] LRN field (read-only if pre-filled)
- [ ] Name fields (first, middle, last, extension)
- [ ] Birth date field with date picker
- [ ] Birth place field (text input)
- [ ] Sex field (dropdown)
- [ ] Age field (auto-calculated from birth date)
- [ ] Mother tongue field
- [ ] Indigenous people checkbox
- [ ] Indigenous group field (conditional)
- [ ] 4Ps beneficiary checkbox
- [ ] 4Ps household ID field (conditional)
- [ ] Disability checkboxes (8 types)
- [ ] Disability others field (conditional)
- [ ] Form validation on next button

### Step 2: Current & Permanent Address
- [ ] Current address fields present (6 fields)
- [ ] Province dropdown populated
- [ ] City dropdown populated based on province
- [ ] Barangay dropdown populated based on city
- [ ] Zip code field
- [ ] "Same as current address" checkbox
- [ ] Permanent address fields hidden when checkbox checked
- [ ] Permanent address fields shown when checkbox unchecked
- [ ] Permanent address fields populated when checkbox checked
- [ ] Form validation on next button

### Step 3: Parent/Guardian Information
- [ ] Father information fields (4 fields)
- [ ] Mother information fields (4 fields)
- [ ] Guardian information fields (4 fields)
- [ ] Contact number fields
- [ ] Form validation on next button

### Step 4: Previous School
- [ ] Conditional display based on enrollment type
- [ ] Previous school name field
- [ ] Previous school address field
- [ ] Previous grade level field
- [ ] Previous school year field
- [ ] Previous school type field
- [ ] Form validation on next button

### Step 5: Enrollment Details
- [ ] Grade level dropdown
- [ ] "SPED Program" option in grade level
- [ ] Balik aral checkbox
- [ ] PEPT passer checkbox
- [ ] PEPT rating field (conditional)
- [ ] ALS passer checkbox
- [ ] ALS rating field (conditional)
- [ ] SHS track field (conditional)
- [ ] SHS strand field (conditional)
- [ ] SHS semester field (conditional)
- [ ] Form validation on next button

### Step 6: Learning Modality
- [ ] 7 modality checkboxes present
- [ ] At least one modality required
- [ ] Preferred distance modality field (conditional)
- [ ] Form validation on next button

### Step 7: Documents & Signature
- [ ] PSA birth certificate upload (required)
- [ ] PWD ID upload (optional)
- [ ] Medical record upload (optional)
- [ ] BEEF form upload (optional)
- [ ] File upload validation (PDF, JPG, PNG)
- [ ] File size validation (max 10MB)
- [ ] Signature pad displayed
- [ ] Signature pad clear button works
- [ ] Signature data captured
- [ ] Submit button enabled only when signature present
- [ ] Form validation on submit

### Draft Save Functionality
- [ ] Draft saved every 30 seconds
- [ ] Draft save indicator displayed
- [ ] Draft can be loaded on return
- [ ] All form data preserved in draft
- [ ] Draft can be discarded
- [ ] Confirmation dialog on discard

### Enrollment Submission
- [ ] Enrollment submitted successfully
- [ ] Confirmation message displayed
- [ ] Enrollment status shows "Pending"
- [ ] Email notification sent to parent
- [ ] Email notification sent to SPED teacher
- [ ] Enrollment appears in parent dashboard
- [ ] Enrollment appears in SPED teacher review list

---

## Phase 5: Process 2 - Enrollment Verification

### Verification Dashboard
- [ ] SPED teacher can access verification dashboard
- [ ] Pending enrollments listed
- [ ] Search functionality works
- [ ] Filter by status works
- [ ] Enrollment count displayed
- [ ] Progress bars show document approval status

### Enrollment Review
- [ ] All 76 BEEF fields displayed
- [ ] Fields organized in 7 sections
- [ ] Uploaded documents displayed
- [ ] Document preview/download works
- [ ] Approve button for each document
- [ ] Reject button for each document
- [ ] Rejection modal displays
- [ ] Rejection reason captured

### Document Approval/Rejection
- [ ] Document approved successfully
- [ ] Document rejected successfully
- [ ] Rejection reason saved
- [ ] Email notification sent to parent
- [ ] Document status updated
- [ ] Progress bar updated

### Enrollment Verification
- [ ] Auto-verify button appears when all documents approved
- [ ] LRN generated (12-digit YYYYMMDDNNNN format)
- [ ] Learner account created
- [ ] Learner account has correct role
- [ ] Temporary password generated
- [ ] LRN credentials email sent to parent
- [ ] Email contains LRN and temporary password
- [ ] Enrollment status updated to "Verified"
- [ ] Enrollment removed from pending list

---

## Phase 6: Process 3 - Assessment

### Parent Assessment Form
- [ ] Assessment form accessible from parent dashboard
- [ ] Section A pre-filled from student_records (read-only)
- [ ] Section A.2 education history fields present
- [ ] Section B assessment information fields present
- [ ] Dynamic row addition for assessment services
- [ ] Dynamic row removal for assessment services
- [ ] Form validation on submit
- [ ] Assessment submitted successfully
- [ ] Confirmation message displayed
- [ ] Email notification sent to SPED teacher

### SPED Teacher Assessment Review
- [ ] Assessment dashboard accessible
- [ ] Pending assessments listed
- [ ] Search functionality works
- [ ] Filter by quarter works
- [ ] Filter by status works
- [ ] Assessment count displayed

### Assessment Review & Approval
- [ ] Assessment detail view displays all data
- [ ] Approve button present
- [ ] Reject button present
- [ ] Rejection modal displays
- [ ] Rejection reason captured
- [ ] Assessment approved successfully
- [ ] Assessment rejected successfully
- [ ] Email notification sent to parent
- [ ] Assessment status updated

### Assessment History
- [ ] Assessment history view displays all versions
- [ ] Timeline view shows quarter tracking
- [ ] Status displayed for each version
- [ ] Rejection reasons displayed
- [ ] Education history displayed
- [ ] Assessment info displayed

---

## Phase 7: Process 4 - IEP Meeting

### Meeting Scheduling
- [ ] SPED teacher can access schedule form
- [ ] Approved assessments listed
- [ ] Guidance and Principal users listed
- [ ] Meeting date picker works
- [ ] Meeting time picker works
- [ ] Meeting location field present
- [ ] Agenda field present
- [ ] Participant selection works
- [ ] Meeting created successfully
- [ ] Confirmation message displayed

### Calendar Upload
- [ ] Guidance can upload calendar
- [ ] Principal can upload calendar
- [ ] Calendar file upload works (ICS, PDF, TXT)
- [ ] Valid from date field present
- [ ] Valid until date field present
- [ ] Calendar uploaded successfully
- [ ] Confirmation message displayed

### Meeting Invitations
- [ ] Email sent to parent
- [ ] Email sent to guidance
- [ ] Email sent to principal
- [ ] Email contains meeting details
- [ ] Email contains meeting date/time
- [ ] Email contains meeting location
- [ ] Email contains meeting agenda

### Meetings List
- [ ] Meetings list accessible
- [ ] All meetings displayed
- [ ] Meeting details displayed (student, date, location, status)
- [ ] Status badges displayed correctly
- [ ] View button works
- [ ] Search functionality works (if implemented)
- [ ] Filter by status works (if implemented)

### Meeting Details
- [ ] Meeting details view displays all information
- [ ] Participants listed
- [ ] Meeting status displayed
- [ ] Meeting date/time displayed
- [ ] Meeting location displayed
- [ ] Meeting agenda displayed

---

## Phase 8: Process 5 - IEP Documents

### IEP P2 Form
- [ ] Guidance can create P2 form
- [ ] Principal can create P2 form
- [ ] Form type selector (Fill Online vs Upload PDF)
- [ ] Online form sections present (Developmental Domains, Strengths & Challenges, Recommendations)
- [ ] PDF upload works
- [ ] Form submitted successfully
- [ ] Confirmation message displayed

### IEP P2 Review
- [ ] P2 review list accessible
- [ ] Pending P2 documents listed
- [ ] P2 document details displayed
- [ ] Feedback textarea present
- [ ] Signature pad present
- [ ] Review submitted successfully
- [ ] Confirmation message displayed
- [ ] Email notification sent to creator

### IEP P3 Form
- [ ] Guidance can create P3 form
- [ ] Principal can create P3 form
- [ ] Form type selector (Fill Online vs Upload PDF)
- [ ] Online form sections present (Student Info, Present Level, Goals, Services, Accommodations)
- [ ] PDF upload works
- [ ] Form submitted successfully
- [ ] Confirmation message displayed

### IEP P3 Signature
- [ ] P3 signature list accessible
- [ ] Pending P3 documents listed
- [ ] P3 document details displayed
- [ ] Signature status displayed
- [ ] Remarks textarea present
- [ ] Signature pad present
- [ ] Signature submitted successfully
- [ ] Confirmation message displayed
- [ ] Email notification sent to next signer

### IEP Approval Queue
- [ ] Principal can access approval queue
- [ ] Pending P3 documents listed
- [ ] Document details displayed
- [ ] Approve button present
- [ ] Confirmation dialog displayed
- [ ] Document approved successfully
- [ ] Confirmation message displayed
- [ ] Email notification sent to all participants

---

## Phase 9: Security

### CSRF Protection
- [ ] CSRF token present in all forms
- [ ] CSRF token validated on form submission
- [ ] Invalid CSRF token rejected
- [ ] Error message displayed for invalid token

### Rate Limiting
- [ ] Login rate limiting works (5 attempts)
- [ ] Registration rate limiting works (3 attempts)
- [ ] Rate limiting error message displayed
- [ ] Rate limiting cooldown enforced (15 minutes)
- [ ] Rate limiting cleared after cooldown

### Encryption
- [ ] Sensitive data encrypted in database
- [ ] Encryption/decryption works correctly
- [ ] Encryption audit log created

### DLP (Data Loss Prevention)
- [ ] Watermark displayed on sensitive documents
- [ ] Screenshot blocking works
- [ ] Copy/paste blocking works
- [ ] Print blocking works
- [ ] Export blocking works

---

## Phase 10: Email System

### Email Delivery
- [ ] Registration confirmation email sent
- [ ] OTP email sent
- [ ] Welcome email sent
- [ ] Role approval email sent
- [ ] Role rejection email sent
- [ ] Enrollment verification email sent
- [ ] Assessment approval email sent
- [ ] Assessment rejection email sent
- [ ] IEP meeting invitation email sent
- [ ] IEP document review email sent
- [ ] IEP document signature email sent

### Email Content
- [ ] Email subject line correct
- [ ] Email body formatted correctly
- [ ] Email contains all required information
- [ ] Email contains action links (if applicable)
- [ ] Email contains branding/logo
- [ ] Email footer present

### Email Delivery Issues
- [ ] Email retry logic works (if implemented)
- [ ] Email bounce handling works (if implemented)
- [ ] Email delivery logs created
- [ ] Failed email notifications logged

---

## Phase 11: File Upload System

### File Upload Validation
- [ ] File type validation works
- [ ] File size validation works (max 5MB for documents, 10MB for PDFs)
- [ ] Invalid file type rejected
- [ ] Oversized file rejected
- [ ] Error message displayed

### File Storage
- [ ] Files stored in secure directory
- [ ] Files not accessible via web browser (if required)
- [ ] File permissions set correctly
- [ ] File cleanup on deletion works

### File Download
- [ ] Files can be downloaded
- [ ] File name preserved
- [ ] File content intact
- [ ] File download logged

---

## Phase 12: Database & Performance

### Database Integrity
- [ ] All tables created correctly
- [ ] All columns present
- [ ] All constraints enforced
- [ ] Foreign keys working correctly
- [ ] Unique constraints enforced
- [ ] Default values applied

### Database Performance
- [ ] Query execution time < 100ms
- [ ] Database connection pooling works (if implemented)
- [ ] Database indexes present
- [ ] Query optimization applied

### Data Backup & Recovery
- [ ] Database backup created
- [ ] Backup restoration works
- [ ] Backup schedule configured (if applicable)

---

## Phase 13: Cross-Browser Compatibility

### Chrome
- [ ] All features work in Chrome
- [ ] Layout displays correctly
- [ ] Forms submit correctly
- [ ] File uploads work
- [ ] Signature pad works
- [ ] Notifications work

### Firefox
- [ ] All features work in Firefox
- [ ] Layout displays correctly
- [ ] Forms submit correctly
- [ ] File uploads work
- [ ] Signature pad works
- [ ] Notifications work

### Safari
- [ ] All features work in Safari
- [ ] Layout displays correctly
- [ ] Forms submit correctly
- [ ] File uploads work
- [ ] Signature pad works
- [ ] Notifications work

### Edge
- [ ] All features work in Edge
- [ ] Layout displays correctly
- [ ] Forms submit correctly
- [ ] File uploads work
- [ ] Signature pad works
- [ ] Notifications work

---

## Phase 14: Mobile Compatibility

### iOS (Safari)
- [ ] All features work on iOS
- [ ] Layout responsive on mobile
- [ ] Forms submit correctly
- [ ] File uploads work
- [ ] Signature pad works on touch
- [ ] Notifications work

### Android (Chrome)
- [ ] All features work on Android
- [ ] Layout responsive on mobile
- [ ] Forms submit correctly
- [ ] File uploads work
- [ ] Signature pad works on touch
- [ ] Notifications work

---

## Phase 15: Accessibility

### Keyboard Navigation
- [ ] All buttons accessible via Tab key
- [ ] All form fields accessible via Tab key
- [ ] Tab order logical
- [ ] Enter key submits forms
- [ ] Escape key closes modals

### Screen Reader Compatibility
- [ ] Form labels associated with inputs
- [ ] Button text descriptive
- [ ] Link text descriptive
- [ ] Images have alt text
- [ ] ARIA attributes present (if applicable)

### Color Contrast
- [ ] Text contrast ratio >= 4.5:1
- [ ] Button contrast ratio >= 4.5:1
- [ ] Link contrast ratio >= 4.5:1

---

## Phase 16: Error Handling

### Network Errors
- [ ] Network timeout handled gracefully
- [ ] Error message displayed
- [ ] Retry option provided
- [ ] User data not lost

### Database Errors
- [ ] Database connection failure handled
- [ ] Error message displayed
- [ ] User data not lost
- [ ] Error logged

### File Upload Errors
- [ ] File upload failure handled
- [ ] Error message displayed
- [ ] User can retry
- [ ] User data not lost

### Session Errors
- [ ] Session expiration handled
- [ ] User redirected to login
- [ ] Error message displayed
- [ ] User data not lost

---

## Phase 17: Data Validation

### Email Validation
- [ ] Valid email accepted
- [ ] Invalid email rejected
- [ ] Error message displayed

### Phone Number Validation
- [ ] Valid phone number accepted
- [ ] Invalid phone number rejected
- [ ] Error message displayed

### Date Validation
- [ ] Valid date accepted
- [ ] Invalid date rejected
- [ ] Error message displayed
- [ ] Future dates rejected (if applicable)

### Required Field Validation
- [ ] Required fields enforced
- [ ] Error message displayed
- [ ] Form not submitted without required fields

### Unique Field Validation
- [ ] Duplicate email rejected
- [ ] Duplicate LRN rejected
- [ ] Error message displayed

---

## Phase 18: User Acceptance Testing

### Parent Workflow
- [ ] Parent can register
- [ ] Parent can verify email
- [ ] Parent can submit enrollment
- [ ] Parent can track enrollment status
- [ ] Parent can submit assessment
- [ ] Parent can view IEP meetings
- [ ] Parent can view notifications
- [ ] Parent can logout

### SPED Teacher Workflow
- [ ] SPED teacher can apply for role
- [ ] SPED teacher can verify enrollment
- [ ] SPED teacher can review assessment
- [ ] SPED teacher can schedule IEP meeting
- [ ] SPED teacher can create IEP P2
- [ ] SPED teacher can view IEP meetings
- [ ] SPED teacher can view notifications
- [ ] SPED teacher can logout

### Guidance Workflow
- [ ] Guidance can apply for role
- [ ] Guidance can schedule IEP meeting
- [ ] Guidance can upload calendar
- [ ] Guidance can review IEP P2
- [ ] Guidance can sign IEP P3
- [ ] Guidance can view IEP meetings
- [ ] Guidance can view notifications
- [ ] Guidance can logout

### Principal Workflow
- [ ] Principal can approve staff requests
- [ ] Principal can schedule IEP meeting
- [ ] Principal can upload calendar
- [ ] Principal can review IEP P2
- [ ] Principal can sign IEP P3
- [ ] Principal can approve IEP P3
- [ ] Principal can view IEP meetings
- [ ] Principal can view notifications
- [ ] Principal can logout

### Admin Workflow
- [ ] Admin can approve role requests
- [ ] Admin can view login logs
- [ ] Admin can view activity logs
- [ ] Admin can manage users
- [ ] Admin can view all dashboards
- [ ] Admin can logout

---

## Test Results Summary

| Phase | Total Tests | Passed | Failed | Pending |
|-------|------------|--------|--------|---------|
| 1. Authentication | 20 | [ ] | [ ] | [ ] |
| 2. Role Selection | 15 | [ ] | [ ] | [ ] |
| 3. Notifications | 15 | [ ] | [ ] | [ ] |
| 4. Enrollment | 50 | [ ] | [ ] | [ ] |
| 5. Verification | 15 | [ ] | [ ] | [ ] |
| 6. Assessment | 20 | [ ] | [ ] | [ ] |
| 7. IEP Meeting | 20 | [ ] | [ ] | [ ] |
| 8. IEP Documents | 25 | [ ] | [ ] | [ ] |
| 9. Security | 10 | [ ] | [ ] | [ ] |
| 10. Email | 15 | [ ] | [ ] | [ ] |
| 11. File Upload | 10 | [ ] | [ ] | [ ] |
| 12. Database | 10 | [ ] | [ ] | [ ] |
| 13. Chrome | 10 | [ ] | [ ] | [ ] |
| 14. Firefox | 10 | [ ] | [ ] | [ ] |
| 15. Safari | 10 | [ ] | [ ] | [ ] |
| 16. Edge | 10 | [ ] | [ ] | [ ] |
| 17. iOS | 10 | [ ] | [ ] | [ ] |
| 18. Android | 10 | [ ] | [ ] | [ ] |
| 19. Accessibility | 15 | [ ] | [ ] | [ ] |
| 20. Error Handling | 15 | [ ] | [ ] | [ ] |
| 21. Data Validation | 15 | [ ] | [ ] | [ ] |
| 22. UAT | 30 | [ ] | [ ] | [ ] |
| **TOTAL** | **400+** | [ ] | [ ] | [ ] |

---

## Issues Found

| # | Issue | Severity | Status | Notes |
|---|-------|----------|--------|-------|
| 1 | | | | |
| 2 | | | | |
| 3 | | | | |

---

## Sign-Off

**Tester Name:** ___________________  
**Tester Signature:** ___________________  
**Date:** ___________________  

**QA Lead Name:** ___________________  
**QA Lead Signature:** ___________________  
**Date:** ___________________  

**Project Manager Name:** ___________________  
**Project Manager Signature:** ___________________  
**Date:** ___________________  

---

**Document Version:** 1.0  
**Last Updated:** May 4, 2026

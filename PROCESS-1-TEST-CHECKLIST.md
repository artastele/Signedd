# Process 1 - Complete Testing Checklist

## 🎯 Overview
This checklist covers all functionality of **Process 1: Parent Complying Enrollment Requirements**

**Test as:** Parent (submitter) and SPED Teacher (reviewer)

---

## 📋 Pre-Testing Setup

### ✅ Prerequisites
- [ ] Database Migration v6 applied
- [ ] At least 2 test accounts created:
  - [ ] 1 Parent account (email verified)
  - [ ] 1 SPED Teacher account (role approved)
- [ ] Email system configured (or check notifications only)
- [ ] Browser: Chrome/Edge/Firefox (latest version)

### ✅ Test URLs
```
Main App: http://localhost/Signedd/
Dashboard: http://localhost/Signedd/dashboard
Enrollment: http://localhost/Signedd/enrollment
Review: http://localhost/Signedd/enrollment/review
Test Script: http://localhost/Signedd/test-enrollment-review.php
```

---

## 🧪 PART 1: Parent Enrollment Submission

### A. Enrollment Type Selection
**URL:** `/enrollment`

- [ ] **Page loads correctly**
  - [ ] See 3 enrollment type cards (New Student, Transferee, Old Student)
  - [ ] Each card has icon, title, description
  - [ ] Cards are clickable
  - [ ] Sidebar and topbar visible

- [ ] **New Student card**
  - [ ] Click "Enroll as New Student" button
  - [ ] Redirects to enrollment form
  - [ ] URL: `/enrollment/create?type=new`

- [ ] **Transferee card**
  - [ ] Go back to enrollment page
  - [ ] Click "Enroll as Transferee" button
  - [ ] Redirects to enrollment form
  - [ ] URL: `/enrollment/create?type=transfer`

- [ ] **Old Student (Returning) card**
  - [ ] Go back to enrollment page
  - [ ] Click "Continue as Returning Student" button
  - [ ] Redirects to enrollment form
  - [ ] URL: `/enrollment/create?type=returning`
  - [ ] If previous enrollment exists, form should auto-fill

---

### B. Step 1: Learner Information (20 fields)

- [ ] **Page loads correctly**
  - [ ] Progress bar shows "Step 1 of 8"
  - [ ] All fields visible
  - [ ] Form title matches enrollment type

- [ ] **Basic Information**
  - [ ] LRN field (optional, 12 digits)
  - [ ] Last Name field (required)
  - [ ] First Name field (required)
  - [ ] Middle Name field (optional)
  - [ ] Extension Name dropdown (Jr., Sr., II, III, IV)

- [ ] **Birth Information**
  - [ ] Birth Date field (required, date picker)
  - [ ] Sex dropdown (Male/Female, required)
  - [ ] Age field (optional, number)
  - [ ] Place of Birth - City field
  - [ ] Place of Birth - Province field

- [ ] **Additional Information**
  - [ ] Mother Tongue field
  - [ ] Indigenous People checkbox
    - [ ] When checked, "Specify Ethnic Group" field appears
    - [ ] When unchecked, field hides
  - [ ] 4Ps Beneficiary checkbox
    - [ ] When checked, "4Ps Household ID" field appears
    - [ ] When unchecked, field hides

- [ ] **Disability Information (9 checkboxes)**
  - [ ] Visual Impairment checkbox
  - [ ] Hearing Impairment checkbox
  - [ ] Learning Disability checkbox
  - [ ] Speech/Language Impairment checkbox
  - [ ] Intellectual Disability checkbox
  - [ ] Physical Disability checkbox
  - [ ] Emotional-Behavioral Disorder checkbox
  - [ ] Chronic Illness checkbox
  - [ ] Others checkbox
    - [ ] When checked, "Specify Other Disability" field appears
    - [ ] When unchecked, field hides

- [ ] **Navigation**
  - [ ] "Previous" button is disabled (first step)
  - [ ] "Next" button is enabled
  - [ ] "Save Draft" button visible
  - [ ] Click "Next" → Goes to Step 2

---

### C. Step 2: Current Address (6 fields)

- [ ] **Page loads correctly**
  - [ ] Progress bar shows "Step 2 of 8"
  - [ ] All fields visible

- [ ] **Address Fields**
  - [ ] House No./Street field
  - [ ] Province dropdown
    - [ ] Click dropdown → Shows provinces
    - [ ] Select a province (e.g., "Davao del Sur")
  - [ ] City/Municipality dropdown
    - [ ] After selecting province, cities load
    - [ ] Select a city (e.g., "Davao City")
  - [ ] Barangay dropdown
    - [ ] After selecting city, barangays load
    - [ ] Select a barangay
  - [ ] Region field (auto-filled or manual)
  - [ ] Zip Code field

- [ ] **Navigation**
  - [ ] "Previous" button works → Goes back to Step 1
  - [ ] "Next" button works → Goes to Step 3
  - [ ] Data persists when going back and forth

---

### D. Step 3: Permanent Address (7 fields)

- [ ] **Page loads correctly**
  - [ ] Progress bar shows "Step 3 of 8"
  - [ ] "Same as Current Address" checkbox visible

- [ ] **Same as Current Address**
  - [ ] Check "Same as Current Address"
    - [ ] All address fields become disabled/hidden
    - [ ] Message shows "Using current address"
  - [ ] Uncheck "Same as Current Address"
    - [ ] All address fields become enabled/visible

- [ ] **Address Fields (when not same)**
  - [ ] House No./Street field
  - [ ] Province dropdown (with data)
  - [ ] City/Municipality dropdown (loads after province)
  - [ ] Barangay dropdown (loads after city)
  - [ ] Region field
  - [ ] Zip Code field

- [ ] **Navigation**
  - [ ] "Previous" button works
  - [ ] "Next" button works
  - [ ] Data persists

---

### E. Step 4: Parent/Guardian Information (12 fields)

- [ ] **Page loads correctly**
  - [ ] Progress bar shows "Step 4 of 8"
  - [ ] Three sections: Father, Mother, Guardian

- [ ] **Father Information**
  - [ ] Last Name field
  - [ ] First Name field
  - [ ] Middle Name field
  - [ ] Contact Number field

- [ ] **Mother Information**
  - [ ] Maiden Last Name field
  - [ ] First Name field
  - [ ] Middle Name field
  - [ ] Contact Number field

- [ ] **Guardian Information (if applicable)**
  - [ ] Last Name field
  - [ ] First Name field
  - [ ] Middle Name field
  - [ ] Contact Number field

- [ ] **Navigation**
  - [ ] "Previous" button works
  - [ ] "Next" button works
  - [ ] Data persists

---

### F. Step 5: Previous School Information (6 fields)

- [ ] **Conditional Display**
  - [ ] For "New Student" → Step should be skipped or optional
  - [ ] For "Transferee" → Step should be required
  - [ ] For "Returning Student" → Step should show previous school

- [ ] **School Information**
  - [ ] School ID field
  - [ ] School Name field
  - [ ] School Address field
  - [ ] Grade Level field
  - [ ] School Year field
  - [ ] School Type dropdown (Public/Private)

- [ ] **Navigation**
  - [ ] "Previous" button works
  - [ ] "Next" button works
  - [ ] Data persists

---

### G. Step 6: Enrollment Details (10 fields)

- [ ] **Page loads correctly**
  - [ ] Progress bar shows "Step 6 of 8"

- [ ] **Grade Level**
  - [ ] Grade Level to Enroll dropdown (required)
  - [ ] Options include "SPED Program"
  - [ ] Select a grade level

- [ ] **Additional Programs**
  - [ ] Balik-Aral checkbox
  - [ ] PEPT Passer checkbox
    - [ ] When checked, "PEPT Rating" field appears
  - [ ] ALS Passer checkbox
    - [ ] When checked, "ALS Rating" field appears

- [ ] **Senior High School (if applicable)**
  - [ ] SHS Track field
  - [ ] SHS Strand field
  - [ ] SHS Semester dropdown (1st/2nd)

- [ ] **Navigation**
  - [ ] "Previous" button works
  - [ ] "Next" button works
  - [ ] Data persists

---

### H. Step 7: Learning Modality (8 fields)

- [ ] **Page loads correctly**
  - [ ] Progress bar shows "Step 7 of 8"

- [ ] **Learning Modality Checkboxes (7 options)**
  - [ ] Modular (Print) checkbox
  - [ ] Modular (Digital) checkbox
  - [ ] Online checkbox
  - [ ] Educational TV checkbox
  - [ ] Radio checkbox
  - [ ] Blended checkbox
  - [ ] Face-to-Face checkbox
  - [ ] Can select multiple options

- [ ] **Preferred Distance Modality**
  - [ ] Dropdown or text field
  - [ ] Select preferred option

- [ ] **Navigation**
  - [ ] "Previous" button works
  - [ ] "Next" button works
  - [ ] Data persists

---

### I. Step 8: Documents & Signature (5 fields)

- [ ] **Page loads correctly**
  - [ ] Progress bar shows "Step 8 of 8"
  - [ ] "Next" button hidden
  - [ ] "Submit Enrollment" button visible

- [ ] **Document Uploads (4 files)**
  - [ ] PSA Birth Certificate upload
    - [ ] Click "Choose File"
    - [ ] Select PDF/JPG/PNG file (max 5MB)
    - [ ] File name displays after selection
  - [ ] PWD ID upload
    - [ ] Upload file
    - [ ] File name displays
  - [ ] Medical Record upload
    - [ ] Upload file
    - [ ] File name displays
  - [ ] BEEF Form upload
    - [ ] Upload file
    - [ ] File name displays

- [ ] **Signature Pad**
  - [ ] Signature canvas visible
  - [ ] Can draw signature with mouse/touch
  - [ ] "Clear" button works (clears signature)
  - [ ] Signature is required before submit

- [ ] **Date Signed**
  - [ ] Date field (auto-filled with current date)

- [ ] **Navigation**
  - [ ] "Previous" button works
  - [ ] "Submit Enrollment" button visible
  - [ ] Click submit without signature → Error message
  - [ ] Click submit with signature → Form submits

---

### J. Auto-Save Functionality

- [ ] **Auto-Save (every 30 seconds)**
  - [ ] Fill some fields in Step 1
  - [ ] Wait 30 seconds
  - [ ] Check browser console → Should see "Draft saved" message
  - [ ] Refresh page
  - [ ] Data should still be there (loaded from draft)

- [ ] **Manual Save**
  - [ ] Fill some fields
  - [ ] Click "Save Draft" button
  - [ ] Success message appears
  - [ ] Refresh page
  - [ ] Data should still be there

---

### K. Form Submission

- [ ] **Submit Enrollment**
  - [ ] Complete all required fields (Steps 1-8)
  - [ ] Upload at least 1 document
  - [ ] Draw signature
  - [ ] Click "Submit Enrollment" button
  - [ ] Success message appears: "Enrollment submitted successfully!"
  - [ ] Redirected to `/enrollment/status`

- [ ] **Validation**
  - [ ] Try submitting without required fields → Error messages
  - [ ] Try submitting without signature → Error message
  - [ ] Fix errors and resubmit → Success

---

### L. Enrollment Status Page (Parent View)

**URL:** `/enrollment/status`

- [ ] **Page loads correctly**
  - [ ] See submitted enrollment(s) in card format
  - [ ] Each card shows:
    - [ ] Student name
    - [ ] Enrollment type
    - [ ] Grade level
    - [ ] School year
    - [ ] Submitted date
    - [ ] Status badge (Pending/Verified/Rejected)

- [ ] **Status: Pending**
  - [ ] Yellow/warning badge
  - [ ] Message: "Being reviewed by SPED teacher"
  - [ ] "View Details" button visible

- [ ] **View Details**
  - [ ] Click "View Details" button
  - [ ] Redirects to `/enrollment/view/{id}`
  - [ ] Shows complete enrollment information
  - [ ] Shows document status table
  - [ ] Shows address information

---

## 🧪 PART 2: SPED Teacher Review

### A. Review List Page

**URL:** `/enrollment/review`  
**Login as:** SPED Teacher

- [ ] **Page loads correctly**
  - [ ] See "Review Enrollments" heading
  - [ ] Statistics cards visible:
    - [ ] Pending Review (yellow)
    - [ ] Verified (green)
    - [ ] Rejected (red)
  - [ ] Counts are correct

- [ ] **Enrollments Table**
  - [ ] Table shows all enrollments
  - [ ] Columns: ID, Student Name, Parent, Type, Grade Level, Submitted, Status, Actions
  - [ ] Each row has "Review" button
  - [ ] Status badges are color-coded
  - [ ] Table is sortable/filterable (if implemented)

- [ ] **Click Review Button**
  - [ ] Click "Review" on any pending enrollment
  - [ ] Redirects to `/enrollment/review/{id}`

---

### B. Review Detail Page

**URL:** `/enrollment/review/{id}`

- [ ] **Page loads correctly**
  - [ ] See "Review Enrollment" heading
  - [ ] "Back to List" button visible
  - [ ] Enrollment status banner at top (color-coded)

- [ ] **Section 1: Learner Information**
  - [ ] All 20 fields displayed correctly
  - [ ] LRN, Full Name, Birth Date, Sex, Age
  - [ ] Place of Birth
  - [ ] Mother Tongue
  - [ ] Indigenous People status (with group if applicable)
  - [ ] 4Ps Beneficiary status (with ID if applicable)
  - [ ] Disabilities list (all checked disabilities shown)

- [ ] **Section 2 & 3: Address Information**
  - [ ] Current Address displayed correctly
  - [ ] Permanent Address displayed correctly
  - [ ] If "Same as Current", shows message

- [ ] **Section 4: Parent/Guardian Information**
  - [ ] Father information (name, contact)
  - [ ] Mother information (name, contact)
  - [ ] Guardian information (name, contact)

- [ ] **Section 5: Previous School (if applicable)**
  - [ ] Only shows for Transfer/Returning students
  - [ ] School ID, Name, Address
  - [ ] Grade Level, School Year, School Type

- [ ] **Section 6: Enrollment Details**
  - [ ] Grade Level to Enroll (badge)
  - [ ] School Year
  - [ ] Balik-Aral status
  - [ ] PEPT Passer status (with rating)
  - [ ] ALS Passer status (with rating)
  - [ ] SHS Track/Strand/Semester (if applicable)

- [ ] **Section 7: Learning Modality**
  - [ ] All selected modalities shown (checkmarks)
  - [ ] Preferred distance modality

- [ ] **Section 8: Documents & Signature**
  - [ ] Signature image displayed
  - [ ] Date signed shown
  - [ ] All uploaded documents listed

---

### C. Document Review & Approval

- [ ] **Document Cards**
  - [ ] Each document has its own card
  - [ ] Card shows:
    - [ ] Document type (PSA Birth Cert, PWD ID, etc.)
    - [ ] Status badge (Pending/Approved/Rejected)
    - [ ] File preview (image) or PDF icon
    - [ ] "Download/View" button
    - [ ] Approve/Reject buttons (if pending)

- [ ] **View/Download Document**
  - [ ] Click "Download/View" button
  - [ ] For images: Opens in new tab, shows full image
  - [ ] For PDFs: Opens PDF in new tab or downloads
  - [ ] File path is correct

- [ ] **Approve Document**
  - [ ] Click green "Approve" button
  - [ ] Page reloads
  - [ ] Success message: "Document approved successfully"
  - [ ] Document status changes to "Approved" (green badge)
  - [ ] Approve/Reject buttons disappear
  - [ ] Review information shows:
    - [ ] Reviewed by: [Your name]
    - [ ] Date: [Current date/time]

- [ ] **Reject Document**
  - [ ] Click red "Reject" button
  - [ ] Modal dialog appears: "Reject Document"
  - [ ] Modal shows document type
  - [ ] "Reason for Rejection" textarea (required)
  - [ ] Enter reason: "Document is blurry, please upload clearer copy"
  - [ ] Click "Reject Document" button in modal
  - [ ] Modal closes
  - [ ] Page reloads
  - [ ] Success message: "Document rejected. Parent has been notified."
  - [ ] Document status changes to "Rejected" (red badge)
  - [ ] Review note displays rejection reason
  - [ ] Enrollment status changes to "Rejected"

---

### D. All Documents Approved

- [ ] **Approve All Documents**
  - [ ] Approve PSA Birth Certificate
  - [ ] Approve PWD ID
  - [ ] Approve Medical Record
  - [ ] Approve BEEF Form (last one)
  - [ ] After approving last document:
    - [ ] Enrollment status automatically changes to "Verified" (green)
    - [ ] Success message appears
    - [ ] In review list, enrollment shows "Verified" badge

---

## 🧪 PART 3: Notifications

### A. Parent Notifications

**Login as:** Parent

- [ ] **Notification Bell Icon**
  - [ ] Bell icon visible in top right corner
  - [ ] Unread badge shows count (red circle with number)

- [ ] **Click Bell Icon**
  - [ ] Notification panel opens (dropdown)
  - [ ] Shows list of notifications
  - [ ] Each notification has:
    - [ ] Icon (color-coded)
    - [ ] Title
    - [ ] Message
    - [ ] Time ago (e.g., "2 minutes ago")

- [ ] **Document Approved Notification**
  - [ ] Title: "Document Approved"
  - [ ] Message: "Your [document type] has been approved."
  - [ ] Icon: Green checkmark
  - [ ] Click notification → Marks as read
  - [ ] Unread badge count decreases

- [ ] **Document Rejected Notification**
  - [ ] Title: "Document Rejected"
  - [ ] Message: "Your [document type] has been rejected. Reason: [reason]"
  - [ ] Icon: Red X
  - [ ] Click notification → Marks as read

- [ ] **Enrollment Approved Notification**
  - [ ] Title: "Enrollment Approved!"
  - [ ] Message: "Your enrollment application has been approved. All documents verified."
  - [ ] Icon: Green checkmark
  - [ ] Click notification → Marks as read

---

### B. SPED Teacher Notifications

**Login as:** SPED Teacher

- [ ] **New Enrollment Notification**
  - [ ] When parent submits enrollment
  - [ ] SPED teacher receives notification
  - [ ] Title: "New Enrollment Submission"
  - [ ] Message: "A new enrollment application requires your review."
  - [ ] Bell icon shows unread badge
  - [ ] Click notification → Can go to review page

---

## 🧪 PART 4: Rejection & Resubmit Workflow

### A. Parent Views Rejection

**Login as:** Parent

- [ ] **Go to Enrollment Status**
  - [ ] URL: `/enrollment/status`
  - [ ] Enrollment card shows "Rejected" badge (red)
  - [ ] Alert message: "Some documents were rejected. Please review feedback and resubmit."
  - [ ] "Resubmit" button visible

- [ ] **View Details**
  - [ ] Click "View Details"
  - [ ] Document status table shows:
    - [ ] Which documents were rejected (red badge)
    - [ ] Review notes with rejection reason
    - [ ] Reviewer name and date

---

### B. Parent Resubmits

- [ ] **Click Resubmit Button**
  - [ ] Redirects to enrollment form
  - [ ] Form pre-filled with previous data
  - [ ] Can edit any field
  - [ ] Can upload new documents
  - [ ] Can re-sign

- [ ] **Submit Again**
  - [ ] Upload new/corrected documents
  - [ ] Click "Submit Enrollment"
  - [ ] Success message appears
  - [ ] Status changes back to "Pending"
  - [ ] SPED teacher receives new notification

---

## 🧪 PART 5: Edge Cases & Error Handling

### A. Validation Errors

- [ ] **Required Fields**
  - [ ] Try submitting Step 1 without Last Name → Error message
  - [ ] Try submitting Step 1 without First Name → Error message
  - [ ] Try submitting Step 1 without Birth Date → Error message
  - [ ] Try submitting Step 1 without Sex → Error message
  - [ ] Error messages are clear and helpful

- [ ] **File Upload Validation**
  - [ ] Try uploading file > 5MB → Error message
  - [ ] Try uploading wrong file type (e.g., .exe) → Error message
  - [ ] Try uploading corrupted file → Error message

- [ ] **Signature Validation**
  - [ ] Try submitting without signature → Error message
  - [ ] Draw signature and submit → Success

---

### B. Session & Navigation

- [ ] **Session Timeout**
  - [ ] Fill form halfway
  - [ ] Wait 15 minutes (or use test script to expire)
  - [ ] Try to navigate → Redirected to login
  - [ ] Login again
  - [ ] Draft should still be saved

- [ ] **Browser Back Button**
  - [ ] Fill form, go to Step 5
  - [ ] Click browser back button
  - [ ] Should go to Step 4
  - [ ] Data should persist

- [ ] **Page Refresh**
  - [ ] Fill form, go to Step 3
  - [ ] Refresh page (F5)
  - [ ] Should stay on Step 3
  - [ ] Data should persist (from draft)

---

### C. Multiple Enrollments

- [ ] **Submit Multiple Enrollments**
  - [ ] Submit first enrollment
  - [ ] Go back to `/enrollment`
  - [ ] Submit second enrollment (different student)
  - [ ] Both should appear in status page
  - [ ] Both should be reviewable by SPED teacher

---

## 🧪 PART 6: UI/UX Testing

### A. Responsive Design

- [ ] **Desktop (1920x1080)**
  - [ ] All elements visible
  - [ ] No horizontal scroll
  - [ ] Sidebar visible
  - [ ] Form fields properly aligned

- [ ] **Tablet (768x1024)**
  - [ ] Layout adjusts properly
  - [ ] Sidebar collapses or adapts
  - [ ] Form fields stack correctly
  - [ ] Buttons are accessible

- [ ] **Mobile (375x667)**
  - [ ] Layout is mobile-friendly
  - [ ] Sidebar becomes hamburger menu (if implemented)
  - [ ] Form fields are full-width
  - [ ] Buttons are touch-friendly
  - [ ] OTP inputs are properly sized

---

### B. Visual Design

- [ ] **Color Scheme**
  - [ ] Primary color: Crimson (#a01422)
  - [ ] Secondary color: Navy (#1e4072)
  - [ ] No default Bootstrap blue
  - [ ] Status badges are color-coded correctly

- [ ] **Typography**
  - [ ] Headings are clear and readable
  - [ ] Body text is legible
  - [ ] Font sizes are appropriate

- [ ] **Icons**
  - [ ] Bootstrap Icons load correctly
  - [ ] Icons are meaningful and consistent

---

### C. Accessibility

- [ ] **Keyboard Navigation**
  - [ ] Can tab through form fields
  - [ ] Can submit form with Enter key
  - [ ] Focus indicators are visible

- [ ] **Screen Reader (if available)**
  - [ ] Form labels are read correctly
  - [ ] Error messages are announced
  - [ ] Status changes are announced

---

## 🧪 PART 7: Performance

### A. Page Load Times

- [ ] **Enrollment Form**
  - [ ] Loads in < 2 seconds
  - [ ] No visible lag

- [ ] **Review Detail Page**
  - [ ] Loads in < 2 seconds
  - [ ] All 76 fields display quickly

- [ ] **Location Dropdowns**
  - [ ] Province dropdown loads instantly
  - [ ] City dropdown loads after province selection (< 1 second)
  - [ ] Barangay dropdown loads after city selection (< 1 second)

---

### B. Auto-Save Performance

- [ ] **Auto-Save**
  - [ ] Saves in background without interrupting user
  - [ ] No noticeable lag
  - [ ] Console shows "Draft saved" message

---

## 🧪 PART 8: Database Verification

### A. Check Database Records

**Run these SQL queries:**

```sql
-- Check enrollment submissions
SELECT id, first_name, last_name, status, enrollment_type, submitted_at 
FROM enrollment_submissions 
WHERE is_draft = FALSE 
ORDER BY submitted_at DESC;

-- Check documents
SELECT ed.id, ed.enrollment_id, ed.document_type, ed.status, ed.reviewed_by
FROM enrollment_documents ed
ORDER BY ed.enrollment_id;

-- Check notifications
SELECT n.id, n.user_id, n.type, n.title, n.is_read
FROM notifications n
WHERE n.type IN ('document_approved', 'document_rejected', 'enrollment_approved')
ORDER BY n.created_at DESC;
```

- [ ] **Enrollment Submissions**
  - [ ] Record exists with correct data
  - [ ] Status is correct (pending/verified/rejected)
  - [ ] All 76 fields saved correctly

- [ ] **Documents**
  - [ ] All uploaded documents have records
  - [ ] File paths are correct
  - [ ] Status is correct (pending/approved/rejected)
  - [ ] Review notes saved (if rejected)

- [ ] **Notifications**
  - [ ] Notifications created for each action
  - [ ] User IDs are correct
  - [ ] Types are correct

---

## ✅ Final Checklist Summary

### Critical Features (Must Work)
- [ ] Parent can submit enrollment (all 8 steps)
- [ ] Auto-save works
- [ ] Documents upload successfully
- [ ] Signature pad works
- [ ] SPED teacher can review enrollment
- [ ] SPED teacher can approve/reject documents
- [ ] Notifications sent to parent
- [ ] Status updates correctly
- [ ] Parent can view status
- [ ] Parent can resubmit if rejected

### Important Features (Should Work)
- [ ] Location dropdowns load correctly
- [ ] Draft save/load works
- [ ] Returning student auto-fill works
- [ ] Email notifications sent (if configured)
- [ ] All 76 BEEF fields display correctly
- [ ] Session timeout works
- [ ] Role update works (10 seconds)

### Nice to Have (Can Have Minor Issues)
- [ ] Mobile responsiveness perfect
- [ ] All icons load
- [ ] Animations smooth
- [ ] Console has no errors

---

## 🐛 Bug Reporting Template

If you find any issues, report them like this:

```
**Bug:** [Short description]
**Steps to Reproduce:**
1. [Step 1]
2. [Step 2]
3. [Step 3]

**Expected:** [What should happen]
**Actual:** [What actually happened]
**Screenshot:** [If applicable]
**Browser:** [Chrome/Firefox/Edge]
**Role:** [Parent/SPED Teacher]
```

---

## 📊 Testing Progress

**Total Items:** 200+  
**Completed:** ___  
**Failed:** ___  
**Blocked:** ___  

**Overall Status:** 🟡 In Progress / 🟢 Passed / 🔴 Failed

---

**Good luck with testing! 🚀**

**Tip:** Test one section at a time, take breaks, and document any issues you find!

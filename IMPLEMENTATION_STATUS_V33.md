# Process 3, 4, and PDSP Update - Implementation Status

## ✅ COMPLETED

### 1. Database Migration v33
- ✅ Added `completed_at` column to `pdsp_records`
- ✅ Created `pdsp_signatories` table
- ✅ Migration applied successfully (DB version: 32)
- ✅ Note: `online_link` doesn't exist in current schema (using `meeting_location`)

### 2. New Models Created
- ✅ `PDSPSignatoryModel.php` - Signatory CRUD operations

---

## 🔄 IN PROGRESS / REMAINING

### 3. Model Updates Needed

#### AssessmentModel.php
- [ ] Add `saveServiceDocument()` for multiple files
- [ ] Add `getServiceDocuments($assessmentServiceId)`
- [ ] Add `deleteServiceDocument($documentId)`
- [ ] Update `getAllWithStudentInfo()` to include services and documents

#### IEPMeetingModel.php
- [ ] Remove any `online_link` references (use `meeting_location`)
- [ ] Update `create()` method

#### PDSPModel.php
- [ ] Update `markAsSigned()` to use `pdsp_signatories` table
- [ ] Add `completed_at` timestamp on signing
- [ ] Update `getSignatories()` to read from `pdsp_signatories` table

### 4. Controller Updates Needed

#### AssessmentController.php
- [ ] Update `submit()` to handle multiple file uploads per service
- [ ] Add `deleteServiceDocument()` AJAX endpoint
- [ ] Update `index()` for assessment history fix

#### IEPMeetingController.php
- [ ] Update `show()` for read-only view (Guidance, Principal, Parent)
- [ ] Update `createMeeting()` to remove `online_link`
- [ ] Update `pdspForm()` to pass role-based permissions
- [ ] Update `markAsSigned()` to save to `pdsp_signatories` table
- [ ] Update `aiExtract()` to check signed document exists first

### 5. View Updates Needed

#### assessment/conduct.php
- [ ] Merge screening into support services checklist
- [ ] Add multiple file upload per service
- [ ] Show uploaded files list per service
- [ ] Add remove button per file

#### assessment/index.php
- [ ] Fix to display services and documents
- [ ] Ensure same query as student record history

#### assessment/view.php
- [ ] Display multiple documents per service

#### iep_meeting/schedule.php
- [ ] Remove online link field
- [ ] Show only venue field (required)

#### iep_meeting/show.php
- [ ] Add read-only view for Guidance, Principal, Parent
- [ ] Show PDSP status badge
- [ ] Show "View Signed Document" button if signed
- [ ] SPED Teacher: show "Open PDSP Form" button

#### iep_meeting/pdsp_form.php
- [ ] Complete rewrite with new 7-section layout
- [ ] Section 1: Page header with print button
- [ ] Section 2: AI auto-fill button (conditional)
- [ ] Section 3: Domain form (6 cards)
- [ ] Section 4: Validation summary (conditional)
- [ ] Section 5: Upload signed document
- [ ] Section 6: Signatories (8 rows)
- [ ] Section 7: Mark as Signed button
- [ ] Read-only mode for Guidance, Principal, Parent
- [ ] Print functionality

### 6. CSS Files Needed
- [ ] Create `public/css/print.css` for print view

### 7. UI Text Updates
- [ ] Find and replace "Review IEP P2 Assessments" → "PDSP"
- [ ] Update sidebar navigation
- [ ] Update breadcrumbs
- [ ] Update page titles

---

## CURRENT DATABASE STATE

```
pdsp_records columns:
- id
- meeting_id
- student_id
- filled_by
- status (enum: 'draft', 'signed')
- signed_document_path
- signatories (JSON - for backward compatibility)
- uploaded_image_path
- created_at
- updated_at
- completed_at (NEW)

pdsp_signatories table (NEW):
- id
- pdsp_id
- signatory_role (enum: 8 roles)
- signatory_name
- created_at

iep_meetings columns:
- Uses meeting_location (not venue)
- No online_link column exists
```

---

## NEXT STEPS

1. Update all models with new methods
2. Update all controllers with new logic
3. Rewrite all 6 views
4. Create print.css
5. Test end-to-end flow
6. Update CHANGELOG.md

---

## TESTING CHECKLIST

### Process 3 (Assessment)
- [ ] Merged checklist shows all services including MFAT, ECCD, Psycho-Ed
- [ ] Checking service adds row to Section B
- [ ] Unchecking service removes row from Section B
- [ ] Multiple file uploads per service work
- [ ] Uploaded files show with remove button
- [ ] Assessment history page shows submitted assessments
- [ ] Assessment history matches student record history

### Process 4 (IEP Meeting)
- [ ] Meeting scheduler only shows venue field (no online link)
- [ ] Meeting created successfully
- [ ] Guidance sees read-only meeting view
- [ ] Principal sees read-only meeting view
- [ ] Parent sees read-only meeting view
- [ ] SPED Teacher can open PDSP form

### PDSP Feature
- [ ] Page layout matches 7-section specification
- [ ] Upload signed document works
- [ ] AI button only appears after document uploaded
- [ ] AI button only visible to SPED Teacher
- [ ] Signatory fields work (8 rows)
- [ ] Validation highlights empty fields in crimson
- [ ] Validation summary shows specific errors
- [ ] Confirmation modal shows signatory list
- [ ] Mark as Signed saves to pdsp_signatories table
- [ ] Completed_at timestamp set on signing
- [ ] Notifications sent to Guidance and Principal
- [ ] Print button triggers print view
- [ ] Read-only mode works for Guidance, Principal, Parent

---

**Status:** Database migration complete. Models and views need implementation.
**Next:** Continue with model updates, then controllers, then views.

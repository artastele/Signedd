# Phase 2 Progress — Views Update

**Date:** 2026-05-07  
**Status:** 🔄 IN PROGRESS

---

## Completed Updates

### 1. ✅ `app/Views/iep_meeting/schedule.php`
- **Removed:** Online link field and toggle
- **Updated:** Single venue text input (required)
- **Updated:** Form validation to check venue only
- **Updated:** Help text to indicate face-to-face meetings only

---

## Remaining Updates

### 2. `app/Views/iep_meeting/show.php` - NEEDS COMPLETE REWRITE
**Current:** Basic meeting details only  
**Required:**
- Add read-only view logic for Guidance/Principal/Parent
- Show PDSP status badge (not yet filled / draft / signed)
- If signed: show "View Signed Document" button (Guidance/Principal can download, Parent view only)
- SPED Teacher: show "Open PDSP Form" button
- Display meeting location (not venue/online_link)

### 3. `app/Views/assessment/conduct.php` - NEEDS MAJOR UPDATE
**Current:** Single file upload per service  
**Required:**
- Merge screening into support services checklist (single unified list)
- Change single file input to "Add Document" button per service
- Show uploaded files list per service with filename, badge, remove button
- JavaScript: handle multiple file uploads per service
- Auto-add/remove Section B rows based on checkbox

### 4. `app/Views/assessment/index.php` - MINOR UPDATE
**Current:** Basic assessment list  
**Required:**
- Display services and documents count for each assessment
- Use data passed from controller (already includes services)

### 5. `app/Views/assessment/view.php` - MINOR UPDATE
**Current:** Basic assessment view  
**Required:**
- Display multiple documents per service
- Show document list with download links

### 6. `app/Views/iep_meeting/pdsp_form.php` - NEEDS COMPLETE REWRITE
**Current:** Basic form with upload and signatories  
**Required:** Complete 7-section layout:
1. Page header (title, student info, meeting date badge, status badge, print button)
2. AI auto-fill button (only visible to SPED Teacher, only after document uploaded)
3. Domain form (6 cards, navy border, crimson header, read-only for Guidance/Principal/Parent)
4. Validation summary (conditional, crimson border, specific errors with domain/row)
5. Upload signed document (dashed crimson border, drag-drop, visible to all, upload SPED Teacher only)
6. Signatories (8 rows: role + name input, read-only after signing)
7. Mark as Signed button (full-width crimson, replaced with green badge after signing)

**Read-only mode:** For Guidance/Principal/Parent (plain text, no inputs)  
**Print functionality:** CSS print stylesheet

### 7. `public/css/print.css` - NEEDS CREATION
**Required:**
- Clean table layout for PDSP form
- Signature lines
- No UI chrome (buttons, navigation)
- Page breaks between domains

---

## UI Text Updates (All Views)
- Find/replace "Review IEP P2 Assessments" → "PDSP"
- Update `app/Views/layouts/sidebar.php` navigation labels
- Update breadcrumbs and page titles

---

## Next Steps

Due to context length, I recommend:
1. Complete the remaining 5 view files one at a time
2. Test each view after update
3. Create print.css stylesheet
4. Update sidebar navigation labels
5. Final testing of all views

**Estimated Remaining Work:** 5 files + 1 CSS file + sidebar updates

---

## Notes

- All backend (Phase 1) is complete and tested
- Views need to match the new backend API
- Color scheme: #a01422 (crimson), #1e4072 (navy)
- Bootstrap 5 customized components
- SweetAlert2 for all confirmations


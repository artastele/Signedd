# Remaining Views Implementation Guide

**Date:** 2026-05-07  
**Status:** 2 files remaining

---

## Completed So Far (Phase 2)

✅ `app/Views/iep_meeting/schedule.php` - Venue only, no online link  
✅ `app/Views/iep_meeting/show.php` - Read-only view + PDSP status  
✅ `app/Views/assessment/index.php` - Display services/documents count  
✅ `app/Views/assessment/view.php` - Display multiple documents per service  
✅ `public/css/print.css` - Print stylesheet for PDSP form  

---

## Remaining Files

### 1. `app/Views/assessment/conduct.php` - MAJOR UPDATE NEEDED

**Current Issues:**
- Single file upload per service
- Separate screening checklist
- File upload uses single `<input type="file">`

**Required Changes:**

#### A. Merge Screening into Services Checklist
Replace the separate "Screening and Assessment Types" section with a unified checklist:

```php
<!-- Services/Screening Unified Checklist -->
<h6 class="text-secondary mb-3 mt-4">Services / Screening Checklist</h6>
<p class="text-muted small">Check all services and screening types that apply</p>

<div id="services-checklist-container">
    <div class="row">
        <div class="col-md-6">
            <!-- Existing services -->
            <div class="form-check mb-2">
                <input class="form-check-input service-checkbox" type="checkbox" 
                       name="services[]" value="Occupational Therapy" id="service_ot">
                <label class="form-check-label" for="service_ot">Occupational Therapy</label>
            </div>
            <!-- ... other services ... -->
        </div>
        <div class="col-md-6">
            <!-- Add screening types here -->
            <div class="form-check mb-2">
                <input class="form-check-input service-checkbox" type="checkbox" 
                       name="services[]" value="MFAT" id="service_mfat">
                <label class="form-check-label" for="service_mfat">MFAT</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input service-checkbox" type="checkbox" 
                       name="services[]" value="ECCD Checklist" id="service_eccd">
                <label class="form-check-label" for="service_eccd">ECCD Checklist</label>
            </div>
            <div class="form-check mb-2">
                <input class="form-check-input service-checkbox" type="checkbox" 
                       name="services[]" value="Psycho-Educational" id="service_psycho">
                <label class="form-check-label" for="service_psycho">Psycho-Educational</label>
            </div>
        </div>
    </div>
</div>
```

**Remove the separate screening section entirely.**

#### B. Multiple File Upload Per Service

Change the MDT table file upload column from:

```html
<!-- OLD: Single file input -->
<input type="file" class="d-none" id="file-${sanitizeId(serviceName)}" 
       name="mdt_file_${sanitizeId(serviceName)}" 
       accept=".jpg,.jpeg,.png,.pdf"
       onchange="handleFileUpload('${serviceName}', this)">
```

To:

```html
<!-- NEW: Multiple file input -->
<input type="file" class="d-none" id="file-${sanitizeId(serviceName)}" 
       name="mdt_file_${sanitizeId(serviceName)}[]" 
       accept=".jpg,.jpeg,.png,.pdf"
       multiple
       onchange="handleMultipleFileUpload('${serviceName}', this)">

<button type="button" class="btn btn-sm" style="background-color: #1e4072; color: white;" 
        onclick="document.getElementById('file-${sanitizeId(serviceName)}').click()">
    <i class="bi bi-plus-circle"></i> Add Document
</button>

<!-- File list container -->
<div id="file-list-${sanitizeId(serviceName)}" class="mt-2">
    <!-- Files will be listed here -->
</div>
```

#### C. JavaScript Updates

Replace `handleFileUpload()` with:

```javascript
// Track uploaded files per service
const uploadedFiles = {};

function handleMultipleFileUpload(serviceName, input) {
    const files = Array.from(input.files);
    const serviceId = sanitizeId(serviceName);
    
    if (!uploadedFiles[serviceName]) {
        uploadedFiles[serviceName] = [];
    }
    
    files.forEach(file => {
        // Validate file type
        const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
        if (!allowedTypes.includes(file.type)) {
            alert(`Invalid file type: ${file.name}. Only JPG, PNG, PDF allowed.`);
            return;
        }
        
        // Validate file size (10MB)
        const maxSize = 10 * 1024 * 1024;
        if (file.size > maxSize) {
            alert(`File too large: ${file.name}. Maximum size is 10MB.`);
            return;
        }
        
        // Add to uploaded files
        uploadedFiles[serviceName].push(file);
    });
    
    // Update file list display
    updateFileList(serviceName);
}

function updateFileList(serviceName) {
    const serviceId = sanitizeId(serviceName);
    const container = document.getElementById(`file-list-${serviceId}`);
    const files = uploadedFiles[serviceName] || [];
    
    if (files.length === 0) {
        container.innerHTML = '<small class="text-muted">No documents uploaded</small>';
        return;
    }
    
    container.innerHTML = files.map((file, index) => `
        <div class="file-item d-flex align-items-center gap-2 p-2 mb-2" 
             style="background-color: #e8f5e9; border-radius: 4px;">
            <i class="bi bi-file-earmark-check-fill text-success"></i>
            <span class="flex-grow-1 small">${file.name}</span>
            <span class="badge" style="background-color: #3b6d11;">${formatFileSize(file.size)}</span>
            <button type="button" class="btn btn-sm btn-danger" 
                    onclick="removeFile('${serviceName}', ${index})">
                <i class="bi bi-x"></i>
            </button>
        </div>
    `).join('');
}

function removeFile(serviceName, index) {
    uploadedFiles[serviceName].splice(index, 1);
    updateFileList(serviceName);
}

function formatFileSize(bytes) {
    if (bytes < 1024) return bytes + ' B';
    if (bytes < 1024 * 1024) return (bytes / 1024).toFixed(1) + ' KB';
    return (bytes / (1024 * 1024)).toFixed(1) + ' MB';
}
```

#### D. Form Submission Update

The form already uses `enctype="multipart/form-data"` and the backend controller now handles multiple files with the `[]` array notation in the name attribute.

---

### 2. `app/Views/iep_meeting/pdsp_form.php` - COMPLETE REWRITE NEEDED

**This is the most complex file. It needs a complete 7-section rewrite.**

#### Required Structure:

```php
<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4 Part II
// Last modified: 2026-05-07
// Part of: SPED LMS — PDSP Form

$pageTitle = 'PDSP Form (Part II) - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Include print stylesheet
echo '<link rel="stylesheet" href="' . $basePath . '/css/print.css" media="print">';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        
        <!-- SECTION 1: Page Header -->
        <div class="row mb-4 no-print">
            <div class="col-md-8">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="bi bi-file-earmark-medical"></i> PDSP Form (Part II)
                </h1>
                <p class="text-muted mt-2">
                    Present Levels of Development and Performance for 
                    <strong><?php echo htmlspecialchars($meeting['student_name']); ?></strong>
                </p>
                <div class="mt-2">
                    <span class="badge" style="background-color: #1e4072; font-size: 0.9rem;">
                        <i class="bi bi-calendar"></i> 
                        Meeting: <?php echo date('M d, Y', strtotime($meeting['meeting_date'])); ?>
                    </span>
                    <?php
                    $statusBadge = match($pdsp['status']) {
                        'signed' => '<span class="badge" style="background-color: #3b6d11; font-size: 0.9rem;"><i class="bi bi-check-circle"></i> Signed</span>',
                        'draft' => '<span class="badge" style="background-color: #ffc107; font-size: 0.9rem;"><i class="bi bi-pencil"></i> Draft</span>',
                        default => '<span class="badge" style="background-color: #6c757d; font-size: 0.9rem;">Not Started</span>'
                    };
                    echo $statusBadge;
                    ?>
                </div>
            </div>
            <div class="col-md-4 text-end">
                <?php if (!$isReadOnly): ?>
                <button type="button" class="btn btn-outline-secondary" onclick="window.print()">
                    <i class="bi bi-printer"></i> Print
                </button>
                <?php endif; ?>
            </div>
        </div>

        <!-- SECTION 2: AI Auto-Fill Button (Only for SPED Teacher, Only After Document Uploaded) -->
        <?php if ($canEdit && $hasSignedDocument): ?>
        <div class="card mb-4 no-print" style="border-left: 4px solid #1e4072;">
            <div class="card-body">
                <div class="row align-items-center">
                    <div class="col-md-8">
                        <h6 class="mb-1">
                            <i class="bi bi-magic"></i> Optional: AI Auto-Fill
                        </h6>
                        <p class="text-muted small mb-0">
                            Use AI to extract data from your uploaded signed document and pre-fill the form fields below.
                        </p>
                    </div>
                    <div class="col-md-4 text-end">
                        <button type="button" class="btn btn-secondary" 
                                style="background-color: #1e4072; border-color: #1e4072;" 
                                onclick="triggerAIExtraction()">
                            <i class="bi bi-cloud-upload"></i> AI Auto-Fill
                        </button>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- SECTION 3: Domain Form (6 Cards) -->
        <form id="pdspForm" method="POST" action="<?php echo $basePath; ?>/iep/meetings/pdsp/save">
            <input type="hidden" name="pdsp_id" value="<?php echo $pdsp['id']; ?>">
            <input type="hidden" name="meeting_id" value="<?php echo $meeting['id']; ?>">

            <?php foreach ($domainNames as $domainName): ?>
            <div class="card mb-4 domain-card" style="border-left: 4px solid #1e4072;">
                <div class="card-header" style="background-color: #a01422; color: white;">
                    <h5 class="mb-0"><?php echo htmlspecialchars($domainName); ?></h5>
                </div>
                <div class="card-body">
                    <div class="domain-rows" data-domain="<?php echo htmlspecialchars($domainName); ?>">
                        <?php
                        $domainRows = array_filter($domains, fn($d) => $d['domain_name'] === $domainName);
                        $rowIndex = 0;
                        
                        if (empty($domainRows)):
                            // Default empty row
                            include __DIR__ . '/pdsp_domain_row.php';
                        else:
                            foreach ($domainRows as $row):
                                include __DIR__ . '/pdsp_domain_row.php';
                                $rowIndex++;
                            endforeach;
                        endif;
                        ?>
                    </div>
                    
                    <?php if ($canEdit): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary add-subdomain-btn no-print" 
                            data-domain="<?php echo htmlspecialchars($domainName); ?>">
                        <i class="bi bi-plus-circle"></i> Add Sub-Domain
                    </button>
                    <?php endif; ?>
                </div>
            </div>
            <?php endforeach; ?>

            <!-- SECTION 4: Validation Summary (Conditional) -->
            <div id="validationSummary" class="alert alert-danger no-print" style="display: none; border-left: 4px solid #a01422;">
                <h6><i class="bi bi-exclamation-triangle"></i> Please complete the following before marking as signed:</h6>
                <ul id="validationErrors" class="mb-0"></ul>
            </div>

            <!-- SECTION 5: Upload Signed Document -->
            <div class="card mb-4 no-print" style="border-left: 4px solid #a01422;">
                <div class="card-header" style="background-color: #a01422; color: white;">
                    <h5 class="mb-0">
                        <i class="bi bi-file-earmark-arrow-up"></i> Upload Signed Handwritten Document
                    </h5>
                </div>
                <div class="card-body">
                    <?php if (!empty($pdsp['signed_document_path'])): ?>
                    <div class="alert alert-success">
                        <i class="bi bi-check-circle"></i> Signed document uploaded successfully!
                        <a href="<?php echo $basePath . '/' . $pdsp['signed_document_path']; ?>" 
                           target="_blank" 
                           class="btn btn-sm btn-outline-success ms-2">
                            <i class="bi bi-eye"></i> View Document
                        </a>
                    </div>
                    <?php elseif ($canUploadDocument): ?>
                    <div id="uploadZone" class="text-center p-5" 
                         style="border: 3px dashed #a01422; border-radius: 10px; background-color: #f9f9f9; cursor: pointer;">
                        <i class="bi bi-cloud-upload" style="font-size: 4rem; color: #a01422;"></i>
                        <h5 class="mt-3">Drag and drop your signed document here</h5>
                        <p class="text-muted">or click to browse</p>
                        <p class="small text-muted">Accepts: JPG, PNG, PDF (Max 10MB)</p>
                        <input type="file" id="signedDocInput" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                    </div>
                    <div id="uploadProgress" class="mt-3" style="display: none;">
                        <div class="text-center">
                            <div class="spinner-border" style="color: #a01422;" role="status">
                                <span class="visually-hidden">Uploading...</span>
                            </div>
                            <p class="mt-2">Uploading document...</p>
                        </div>
                    </div>
                    <?php else: ?>
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i> Document upload is managed by SPED Teacher
                    </p>
                    <?php endif; ?>
                </div>
            </div>

            <!-- SECTION 6: Signatories -->
            <?php if ($canMarkAsSigned): ?>
            <div class="card mb-4 no-print" style="border-left: 4px solid #1e4072;">
                <div class="card-header" style="background-color: #1e4072; color: white;">
                    <h5 class="mb-0">
                        <i class="bi bi-people"></i> Who Signed This Document?
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Check all signatories who signed the handwritten document and enter their names.</p>
                    
                    <div class="row g-3">
                        <?php
                        $signatoryRoles = [
                            'sped_teacher' => 'SPED Teacher',
                            'gen_ed_teacher' => 'General Ed Teacher',
                            'school_head' => 'School Head',
                            'ilrc_supervisor' => 'ILRC Supervisor',
                            'parent_guardian' => 'Parents/Guardian',
                            'medical_allied_1' => 'Medical/Allied Health Professional 1',
                            'medical_allied_2' => 'Medical/Allied Health Professional 2',
                            'medical_allied_3' => 'Medical/Allied Health Professional 3'
                        ];
                        
                        foreach ($signatoryRoles as $role => $label):
                            $existingSig = array_filter($signatories, fn($s) => $s['role'] === $role);
                            $isChecked = !empty($existingSig);
                            $name = $isChecked ? reset($existingSig)['name'] : '';
                        ?>
                        <div class="col-md-6">
                            <div class="card h-100">
                                <div class="card-body">
                                    <div class="form-check mb-2">
                                        <input class="form-check-input signatory-checkbox" 
                                               type="checkbox" 
                                               value="<?php echo $role; ?>" 
                                               id="sig_<?php echo $role; ?>" 
                                               <?php echo $isChecked ? 'checked' : ''; ?>>
                                        <label class="form-check-label fw-bold" for="sig_<?php echo $role; ?>">
                                            <?php echo $label; ?>
                                        </label>
                                    </div>
                                    <input type="text" 
                                           class="form-control signatory-name" 
                                           data-role="<?php echo $role; ?>" 
                                           placeholder="Enter full name" 
                                           value="<?php echo htmlspecialchars($name); ?>" 
                                           <?php echo !$isChecked ? 'disabled' : ''; ?>>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
            <?php endif; ?>

            <!-- SECTION 7: Mark as Signed Button / Signed Badge -->
            <div class="card no-print">
                <div class="card-body text-center">
                    <?php if ($pdsp['status'] === 'signed'): ?>
                    <div class="alert alert-success mb-0" style="background-color: #3b6d11; color: white; border: none;">
                        <h5 class="mb-0">
                            <i class="bi bi-check-circle-fill"></i> This PDSP has been marked as signed
                        </h5>
                        <?php if (!empty($pdsp['completed_at'])): ?>
                        <p class="mb-0 mt-2">
                            <small>Completed on <?php echo date('F d, Y g:i A', strtotime($pdsp['completed_at'])); ?></small>
                        </p>
                        <?php endif; ?>
                    </div>
                    <?php elseif ($canEdit): ?>
                    <button type="submit" class="btn btn-secondary btn-lg me-2">
                        <i class="bi bi-save"></i> Save Draft
                    </button>
                    <?php if ($canMarkAsSigned): ?>
                    <button type="button" id="markAsSignedBtn" class="btn btn-lg" 
                            style="background-color: #a01422; border-color: #a01422; color: white;">
                        <i class="bi bi-check-circle"></i> Mark as Signed
                    </button>
                    <?php endif; ?>
                    <?php else: ?>
                    <p class="text-muted mb-0">
                        <i class="bi bi-info-circle"></i> This form is read-only
                    </p>
                    <?php endif; ?>
                    
                    <a href="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>" 
                       class="btn btn-outline-secondary btn-lg ms-2">
                        <i class="bi bi-arrow-left"></i> Back to Meeting
                    </a>
                </div>
            </div>
        </form>

        <!-- Signed Signatories Display (After Signing) -->
        <?php if ($pdsp['status'] === 'signed' && !empty($signatories)): ?>
        <div class="card mt-4 signature-section" style="border-left: 4px solid #3b6d11;">
            <div class="card-header" style="background-color: #3b6d11; color: white;">
                <h5 class="mb-0">
                    <i class="bi bi-check-circle"></i> Signatories
                </h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php foreach ($signatories as $sig): 
                        $roleLabel = $signatoryRoles[$sig['role']] ?? $sig['role'];
                    ?>
                    <div class="col-md-6 mb-3">
                        <div class="signature-box">
                            <div class="signature-line"></div>
                            <div class="signature-label">
                                <strong><?php echo htmlspecialchars($roleLabel); ?>:</strong><br>
                                <?php echo htmlspecialchars($sig['name']); ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<!-- Confirmation Modal -->
<div class="modal fade" id="confirmationModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header" style="background-color: #a01422; color: white;">
                <h5 class="modal-title">
                    <i class="bi bi-check-circle"></i> Confirm Signing
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p>All fields are complete. Mark Part II as signed and notify Guidance and Principal?</p>
                <div id="signatoryList" class="mt-3">
                    <h6>Signatories:</h6>
                    <ul id="signatoryListItems"></ul>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" id="confirmSignBtn" class="btn btn-primary" 
                        style="background-color: #a01422; border-color: #a01422;">
                    <i class="bi bi-check-circle"></i> Confirm
                </button>
            </div>
        </div>
    </div>
</div>

<script>
// [Include all JavaScript for validation, upload, AI extraction, etc.]
// This is too long to include here - see existing pdsp_form.php for reference
// Key functions needed:
// - validateForm()
// - handleSignedDocUpload()
// - triggerAIExtraction()
// - markAsSigned()
// - signatory checkbox toggles
// - add subdomain functionality
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>
```

#### Create Separate Domain Row Template

Create `app/Views/iep_meeting/pdsp_domain_row.php`:

```php
<?php
// Domain row template - included in loop
$isReadOnly = $isReadOnly ?? false;
$row = $row ?? [];
$rowIndex = $rowIndex ?? 0;
?>

<div class="domain-row mb-3 p-3 page-break-avoid" style="background-color: #f9f9f9; border-radius: 6px;">
    <div class="row g-3">
        <div class="col-md-6">
            <label class="form-label">Sub-Domain <span class="text-danger">*</span></label>
            <?php if ($isReadOnly): ?>
                <p class="mb-0"><?php echo htmlspecialchars($row['sub_domain'] ?? 'N/A'); ?></p>
            <?php else: ?>
                <input type="text" class="form-control" 
                       name="domains[<?php echo htmlspecialchars($domainName); ?>][<?php echo $rowIndex; ?>][sub_domain]" 
                       value="<?php echo htmlspecialchars($row['sub_domain'] ?? ''); ?>" 
                       placeholder="Enter sub-domain" required>
            <?php endif; ?>
        </div>
        
        <!-- [Continue with all other fields: skills_description, mastered, educational_recommendation, q1_level, q2_level] -->
        <!-- Use same pattern: if read-only show <p>, else show <input> or <select> -->
    </div>
</div>
```

---

## Summary

**Completed:** 5 files + print CSS  
**Remaining:** 2 files (conduct.php and pdsp_form.php)

Both remaining files are complex and require significant JavaScript updates. The implementation guide above provides the complete structure needed.

**Recommendation:** Implement these two files carefully, testing each section as you go.


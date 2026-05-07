<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4 Part II (Simplified)
// Last modified: 2026-05-07
// Part of: SPED LMS — PDSP Form (Upload Only)

$pageTitle = 'PDSP Form (Part II) - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-md-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="bi bi-file-earmark-medical"></i> PDSP Form (Part II)
                </h1>
                <p class="text-muted mt-2">
                    Upload completed PDSP document for <strong><?php echo htmlspecialchars($meeting['student_name']); ?></strong>
                </p>
                <div class="mt-2">
                    <span class="badge" style="background-color: #1e4072; font-size: 0.9rem;">
                        <i class="bi bi-calendar"></i> 
                        Meeting: <?php echo date('M d, Y', strtotime($meeting['meeting_date'])); ?>
                    </span>
                    <?php
                    $statusBadge = match($pdsp['status']) {
                        'signed' => '<span class="badge" style="background-color: #3b6d11; font-size: 0.9rem;"><i class="bi bi-check-circle"></i> Completed</span>',
                        'draft' => '<span class="badge" style="background-color: #ffc107; color: #000; font-size: 0.9rem;"><i class="bi bi-pencil"></i> Pending</span>',
                        default => '<span class="badge" style="background-color: #6c757d; font-size: 0.9rem;">Not Started</span>'
                    };
                    echo ' ' . $statusBadge;
                    ?>
                </div>
            </div>
        </div>

        <?php if ($pdsp['status'] === 'signed'): ?>
        <!-- Completed Status -->
        <div class="card mb-4" style="border-left: 4px solid #3b6d11;">
            <div class="card-header" style="background-color: #3b6d11; color: white;">
                <h5 class="mb-0"><i class="bi bi-check-circle-fill"></i> PDSP Completed</h5>
            </div>
            <div class="card-body">
                <p class="mb-3">This PDSP has been completed and the meeting status has been updated.</p>
                
                <?php if (!empty($pdsp['signed_document_path'])): ?>
                <div class="mb-3">
                    <strong>Uploaded Document:</strong><br>
                    <a href="<?php echo $basePath . '/' . $pdsp['signed_document_path']; ?>" target="_blank" class="btn btn-outline-success mt-2">
                        <i class="bi bi-file-earmark-pdf"></i> View PDSP Document
                    </a>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($pdsp['signatories'])): ?>
                <div class="mt-4">
                    <strong>Signatories:</strong>
                    <div class="row mt-2">
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
                        
                        $signatories = json_decode($pdsp['signatories'], true);
                        foreach ($signatories as $sig):
                            $roleLabel = $signatoryRoles[$sig['role']] ?? $sig['role'];
                        ?>
                        <div class="col-md-6 mb-2">
                            <div class="card">
                                <div class="card-body py-2">
                                    <strong><?php echo htmlspecialchars($roleLabel); ?>:</strong><br>
                                    <span class="text-muted"><?php echo htmlspecialchars($sig['name']); ?></span>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($pdsp['completed_at'])): ?>
                <div class="mt-3">
                    <small class="text-muted">
                        <i class="bi bi-clock"></i> Completed on <?php echo date('F d, Y g:i A', strtotime($pdsp['completed_at'])); ?>
                    </small>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <?php else: ?>
        <!-- Upload Section -->
        <div class="card mb-4" style="border-left: 4px solid #a01422;">
            <div class="card-header" style="background-color: #a01422; color: white;">
                <h5 class="mb-0"><i class="bi bi-file-earmark-arrow-up"></i> Step 1: Upload Completed PDSP Document</h5>
            </div>
            <div class="card-body">
                <div class="alert alert-info">
                    <i class="bi bi-info-circle"></i> Upload the completed and signed PDSP document from the IEP meeting. Accepted formats: JPG, PNG, PDF (Max 10MB)
                </div>
                
                <?php if (!empty($pdsp['signed_document_path'])): ?>
                <div class="alert alert-success">
                    <i class="bi bi-check-circle"></i> Document uploaded successfully!
                    <a href="<?php echo $basePath . '/' . $pdsp['signed_document_path']; ?>" target="_blank" class="btn btn-sm btn-outline-success ms-2">
                        <i class="bi bi-eye"></i> View Document
                    </a>
                    <button type="button" class="btn btn-sm btn-outline-danger ms-2" onclick="deleteDocument()">
                        <i class="bi bi-trash"></i> Replace
                    </button>
                </div>
                <?php else: ?>
                <div id="uploadZone" class="text-center p-5" style="border: 3px dashed #a01422; border-radius: 10px; background-color: #f9f9f9; cursor: pointer;">
                    <i class="bi bi-cloud-upload" style="font-size: 4rem; color: #a01422;"></i>
                    <h5 class="mt-3">Drag and drop your PDSP document here</h5>
                    <p class="text-muted">or click to browse</p>
                    <p class="small text-muted">Accepts: JPG, PNG, PDF (Max 10MB)</p>
                    <input type="file" id="signedDocInput" accept=".jpg,.jpeg,.png,.pdf" style="display: none;">
                </div>
                
                <div id="uploadProgress" class="mt-3" style="display: none;">
                    <div class="text-center">
                        <div class="spinner-border text-primary" role="status" style="color: #a01422 !important;">
                            <span class="visually-hidden">Uploading...</span>
                        </div>
                        <p class="mt-2">Uploading document...</p>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- Who Signed Section -->
        <div class="card mb-4" style="border-left: 4px solid #1e4072;">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0"><i class="bi bi-people"></i> Step 2: Who Signed This Document?</h5>
            </div>
            <div class="card-body">
                <p class="text-muted mb-3">Check all signatories who signed the PDSP document and enter their names.</p>
                
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
                    
                    $existingSignatories = !empty($pdsp['signatories']) ? json_decode($pdsp['signatories'], true) : [];
                    $signatoryMap = [];
                    foreach ($existingSignatories as $sig) {
                        $signatoryMap[$sig['role']] = $sig['name'];
                    }
                    
                    foreach ($signatoryRoles as $role => $label):
                        $isChecked = isset($signatoryMap[$role]);
                        $name = $signatoryMap[$role] ?? '';
                    ?>
                    <div class="col-md-6">
                        <div class="card h-100">
                            <div class="card-body">
                                <div class="form-check mb-2">
                                    <input class="form-check-input signatory-checkbox" type="checkbox" value="<?php echo $role; ?>" id="sig_<?php echo $role; ?>" <?php echo $isChecked ? 'checked' : ''; ?>>
                                    <label class="form-check-label fw-bold" for="sig_<?php echo $role; ?>">
                                        <?php echo $label; ?>
                                    </label>
                                </div>
                                <input type="text" class="form-control signatory-name" data-role="<?php echo $role; ?>" placeholder="Enter full name" value="<?php echo htmlspecialchars($name); ?>" <?php echo !$isChecked ? 'disabled' : ''; ?>>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- Submit Button -->
        <div class="card">
            <div class="card-body text-center">
                <button type="button" id="submitBtn" class="btn btn-lg" style="background-color: #a01422; border-color: #a01422; color: white;">
                    <i class="bi bi-check-circle"></i> Submit PDSP & Complete Meeting
                </button>
                <a href="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>" class="btn btn-outline-secondary btn-lg ms-2">
                    <i class="bi bi-arrow-left"></i> Back to Meeting
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>
</div>

<script>
const basePath = '<?php echo $basePath; ?>';
const pdspId = '<?php echo $pdsp['id']; ?>';
const meetingId = '<?php echo $meeting['id']; ?>';
const pdspStatus = '<?php echo $pdsp['status']; ?>';

document.addEventListener('DOMContentLoaded', function() {
    if (pdspStatus === 'draft') {
        // Upload functionality
        const uploadZone = document.getElementById('uploadZone');
        const signedDocInput = document.getElementById('signedDocInput');
        const uploadProgress = document.getElementById('uploadProgress');
        
        if (uploadZone) {
            uploadZone.addEventListener('click', () => signedDocInput.click());
            
            uploadZone.addEventListener('dragover', (e) => {
                e.preventDefault();
                uploadZone.style.borderColor = '#3b6d11';
            });
            
            uploadZone.addEventListener('dragleave', () => {
                uploadZone.style.borderColor = '#a01422';
            });
            
            uploadZone.addEventListener('drop', (e) => {
                e.preventDefault();
                uploadZone.style.borderColor = '#a01422';
                const files = e.dataTransfer.files;
                if (files.length > 0) {
                    handleDocumentUpload(files[0]);
                }
            });
            
            signedDocInput.addEventListener('change', (e) => {
                if (e.target.files.length > 0) {
                    handleDocumentUpload(e.target.files[0]);
                }
            });
        }
        
        // Signatory checkbox toggle
        document.querySelectorAll('.signatory-checkbox').forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                const role = this.value;
                const nameInput = document.querySelector(`.signatory-name[data-role="${role}"]`);
                nameInput.disabled = !this.checked;
                if (!this.checked) {
                    nameInput.value = '';
                }
            });
        });
        
        // Submit button
        document.getElementById('submitBtn').addEventListener('click', submitPDSP);
    }
});

function handleDocumentUpload(file) {
    // Validate file type
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'application/pdf'];
    if (!allowedTypes.includes(file.type)) {
        Swal.fire({
            icon: 'error',
            title: 'Invalid File Type',
            text: 'Only JPG, PNG, and PDF files are accepted.',
            confirmButtonColor: '#a01422'
        });
        return;
    }
    
    // Validate file size (10MB)
    if (file.size > 10 * 1024 * 1024) {
        Swal.fire({
            icon: 'error',
            title: 'File Too Large',
            text: 'Maximum file size is 10MB.',
            confirmButtonColor: '#a01422'
        });
        return;
    }
    
    const uploadProgress = document.getElementById('uploadProgress');
    const uploadZone = document.getElementById('uploadZone');
    
    uploadProgress.style.display = 'block';
    uploadZone.style.display = 'none';
    
    // Create FormData
    const formData = new FormData();
    formData.append('signed_document', file);
    formData.append('pdsp_id', pdspId);
    
    // Send to server
    fetch(basePath + '/iep/meetings/pdsp/upload-signed-document', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Document Uploaded!',
                text: data.message,
                confirmButtonColor: '#3b6d11'
            }).then(() => {
                location.reload();
            });
        } else {
            throw new Error(data.message || 'Upload failed');
        }
    })
    .catch(error => {
        Swal.fire({
            icon: 'error',
            title: 'Upload Failed',
            text: error.message,
            confirmButtonColor: '#a01422'
        });
        uploadProgress.style.display = 'none';
        uploadZone.style.display = 'block';
    });
}

function submitPDSP() {
    // Validate: document must be uploaded
    <?php if (empty($pdsp['signed_document_path'])): ?>
    Swal.fire({
        icon: 'warning',
        title: 'Document Required',
        text: 'Please upload the PDSP document first.',
        confirmButtonColor: '#a01422'
    });
    return;
    <?php endif; ?>
    
    // Collect signatories
    const signatories = [];
    document.querySelectorAll('.signatory-checkbox:checked').forEach(checkbox => {
        const role = checkbox.value;
        const nameInput = document.querySelector(`.signatory-name[data-role="${role}"]`);
        const name = nameInput.value.trim();
        
        if (name) {
            signatories.push({ role, name });
        }
    });
    
    // Validate: at least one signatory
    if (signatories.length === 0) {
        Swal.fire({
            icon: 'warning',
            title: 'Signatories Required',
            text: 'Please select at least one signatory and enter their name.',
            confirmButtonColor: '#a01422'
        });
        return;
    }
    
    // Confirm submission
    Swal.fire({
        title: 'Submit PDSP?',
        html: `This will mark the PDSP as completed and change the meeting status to "Completed".<br><br><strong>Signatories:</strong> ${signatories.length}`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Submit',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Submitting...',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Send to server
            fetch(basePath + '/iep/meetings/pdsp/submit', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({
                    pdsp_id: pdspId,
                    meeting_id: meetingId,
                    signatories: signatories
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: 'PDSP Submitted!',
                        text: 'Meeting status has been updated to Completed.',
                        confirmButtonColor: '#3b6d11'
                    }).then(() => {
                        location.reload();
                    });
                } else {
                    throw new Error(data.message || 'Submission failed');
                }
            })
            .catch(error => {
                Swal.fire({
                    icon: 'error',
                    title: 'Submission Failed',
                    text: error.message,
                    confirmButtonColor: '#a01422'
                });
            });
        }
    });
}

function deleteDocument() {
    Swal.fire({
        title: 'Replace Document?',
        text: 'This will remove the current document so you can upload a new one.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Replace',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            location.reload();
        }
    });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

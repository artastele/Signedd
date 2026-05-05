<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — Enrollment Review Detail (SPED Teacher)

$pageTitle = 'Review Enrollment - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Document type labels
$docLabels = [
    'psa_birth_cert' => 'PSA Birth Certificate',
    'pwd_id' => 'PWD ID',
    'medical_record' => 'Medical Record',
    'beef_form' => 'BEEF Form'
];

// Status colors
$statusColors = [
    'pending' => 'warning',
    'approved' => 'success',
    'rejected' => 'danger'
];
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-clipboard-check text-primary"></i> Review Enrollment
        </h1>
        <a href="<?php echo $basePath; ?>/enrollment/review" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>

    <!-- Enrollment Status Card -->
    <div class="card mb-4 border-<?php echo $statusColors[$enrollment['status']]; ?>">
        <div class="card-header bg-<?php echo $statusColors[$enrollment['status']]; ?> text-white">
            <h5 class="mb-0">
                <i class="bi bi-info-circle"></i> Enrollment Status: 
                <strong><?php echo strtoupper($enrollment['status']); ?></strong>
            </h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-3">
                    <strong>Enrollment ID:</strong><br>
                    #<?php echo $enrollment['id']; ?>
                </div>
                <div class="col-md-3">
                    <strong>Type:</strong><br>
                    <?php echo ucfirst($enrollment['enrollment_type']); ?>
                </div>
                <div class="col-md-3">
                    <strong>Submitted:</strong><br>
                    <?php echo date('M j, Y g:i A', strtotime($enrollment['submitted_at'])); ?>
                </div>
                <div class="col-md-3">
                    <strong>Parent:</strong><br>
                    <?php echo htmlspecialchars($enrollment['parent_name']); ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 1: Learner Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person-fill"></i> Section 1: Learner Information</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>LRN:</strong> <?php echo htmlspecialchars($enrollment['lrn'] ?? 'Not provided'); ?>
                </div>
                <div class="col-md-6">
                    <strong>Full Name:</strong> 
                    <?php 
                    echo htmlspecialchars($enrollment['first_name'] . ' ' . 
                         ($enrollment['middle_name'] ? $enrollment['middle_name'] . ' ' : '') . 
                         $enrollment['last_name'] . 
                         ($enrollment['extension_name'] ? ' ' . $enrollment['extension_name'] : ''));
                    ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-4">
                    <strong>Birth Date:</strong> <?php echo date('F j, Y', strtotime($enrollment['birth_date'])); ?>
                </div>
                <div class="col-md-4">
                    <strong>Sex:</strong> <?php echo htmlspecialchars($enrollment['sex']); ?>
                </div>
                <div class="col-md-4">
                    <strong>Age:</strong> <?php echo htmlspecialchars($enrollment['age'] ?? 'N/A'); ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Place of Birth:</strong> 
                    <?php echo htmlspecialchars(($enrollment['place_of_birth_city'] ?? '') . ', ' . ($enrollment['place_of_birth_province'] ?? '')); ?>
                </div>
                <div class="col-md-6">
                    <strong>Mother Tongue:</strong> <?php echo htmlspecialchars($enrollment['mother_tongue'] ?? 'N/A'); ?>
                </div>
            </div>
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Indigenous People:</strong> 
                    <?php if ($enrollment['is_indigenous_people']): ?>
                        <span class="badge bg-info">Yes - <?php echo htmlspecialchars($enrollment['indigenous_group']); ?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary">No</span>
                    <?php endif; ?>
                </div>
                <div class="col-md-6">
                    <strong>4Ps Beneficiary:</strong> 
                    <?php if ($enrollment['is_4ps_beneficiary']): ?>
                        <span class="badge bg-info">Yes - <?php echo htmlspecialchars($enrollment['fourps_household_id']); ?></span>
                    <?php else: ?>
                        <span class="badge bg-secondary">No</span>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Disabilities -->
            <div class="alert alert-info">
                <strong><i class="bi bi-heart-pulse"></i> Disabilities:</strong><br>
                <?php
                $disabilities = [];
                if ($enrollment['disability_visual']) $disabilities[] = 'Visual Impairment';
                if ($enrollment['disability_hearing']) $disabilities[] = 'Hearing Impairment';
                if ($enrollment['disability_learning']) $disabilities[] = 'Learning Disability';
                if ($enrollment['disability_speech']) $disabilities[] = 'Speech/Language Impairment';
                if ($enrollment['disability_intellectual']) $disabilities[] = 'Intellectual Disability';
                if ($enrollment['disability_physical']) $disabilities[] = 'Physical Disability';
                if ($enrollment['disability_emotional']) $disabilities[] = 'Emotional-Behavioral Disorder';
                if ($enrollment['disability_chronic_illness']) $disabilities[] = 'Chronic Illness';
                if ($enrollment['disability_others']) $disabilities[] = 'Others: ' . htmlspecialchars($enrollment['disability_others_specify']);
                
                echo !empty($disabilities) ? implode(', ', $disabilities) : 'None specified';
                ?>
            </div>
        </div>
    </div>

    <!-- Section 2 & 3: Address Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-geo-alt-fill"></i> Section 2 & 3: Address Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Current Address</h6>
                    <p>
                        <?php echo htmlspecialchars($enrollment['current_house_no'] ?? ''); ?><br>
                        Barangay <?php echo htmlspecialchars($enrollment['current_barangay'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($enrollment['current_city'] ?? ''); ?>, 
                        <?php echo htmlspecialchars($enrollment['current_province'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($enrollment['current_region'] ?? ''); ?> 
                        <?php echo htmlspecialchars($enrollment['current_zip_code'] ?? ''); ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Permanent Address</h6>
                    <?php if ($enrollment['same_as_current_address']): ?>
                        <p class="text-muted"><em>Same as current address</em></p>
                    <?php else: ?>
                        <p>
                            <?php echo htmlspecialchars($enrollment['permanent_house_no'] ?? ''); ?><br>
                            Barangay <?php echo htmlspecialchars($enrollment['permanent_barangay'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($enrollment['permanent_city'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($enrollment['permanent_province'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($enrollment['permanent_region'] ?? ''); ?> 
                            <?php echo htmlspecialchars($enrollment['permanent_zip_code'] ?? ''); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 4: Parent/Guardian Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-people-fill"></i> Section 4: Parent/Guardian Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-primary">Father</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['father_first_name'] ?? '') . ' ' . 
                             ($enrollment['father_middle_name'] ?? '') . ' ' . 
                             ($enrollment['father_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['father_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary">Mother</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['mother_first_name'] ?? '') . ' ' . 
                             ($enrollment['mother_middle_name'] ?? '') . ' ' . 
                             ($enrollment['mother_maiden_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['mother_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary">Guardian</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['guardian_first_name'] ?? '') . ' ' . 
                             ($enrollment['guardian_middle_name'] ?? '') . ' ' . 
                             ($enrollment['guardian_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['guardian_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Section 5: Previous School (if applicable) -->
    <?php if ($enrollment['enrollment_type'] !== 'new'): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-building"></i> Section 5: Previous School Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <strong>School ID:</strong> <?php echo htmlspecialchars($enrollment['previous_school_id'] ?? 'N/A'); ?><br>
                    <strong>School Name:</strong> <?php echo htmlspecialchars($enrollment['previous_school_name'] ?? 'N/A'); ?><br>
                    <strong>Address:</strong> <?php echo htmlspecialchars($enrollment['previous_school_address'] ?? 'N/A'); ?>
                </div>
                <div class="col-md-6">
                    <strong>Grade Level:</strong> <?php echo htmlspecialchars($enrollment['previous_grade_level'] ?? 'N/A'); ?><br>
                    <strong>School Year:</strong> <?php echo htmlspecialchars($enrollment['previous_school_year'] ?? 'N/A'); ?><br>
                    <strong>School Type:</strong> <?php echo htmlspecialchars($enrollment['previous_school_type'] ?? 'N/A'); ?>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Section 6: Enrollment Details -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-journal-text"></i> Section 6: Enrollment Details</h5>
        </div>
        <div class="card-body">
            <div class="row mb-3">
                <div class="col-md-6">
                    <strong>Grade Level to Enroll:</strong> 
                    <span class="badge bg-primary"><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></span>
                </div>
                <div class="col-md-6">
                    <strong>School Year:</strong> <?php echo htmlspecialchars($enrollment['school_year']); ?>
                </div>
            </div>
            <div class="row">
                <div class="col-md-4">
                    <strong>Balik-Aral:</strong> 
                    <span class="badge bg-<?php echo $enrollment['is_balik_aral'] ? 'success' : 'secondary'; ?>">
                        <?php echo $enrollment['is_balik_aral'] ? 'Yes' : 'No'; ?>
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>PEPT Passer:</strong> 
                    <span class="badge bg-<?php echo $enrollment['is_pept_passer'] ? 'success' : 'secondary'; ?>">
                        <?php echo $enrollment['is_pept_passer'] ? 'Yes - ' . htmlspecialchars($enrollment['pept_rating']) : 'No'; ?>
                    </span>
                </div>
                <div class="col-md-4">
                    <strong>ALS Passer:</strong> 
                    <span class="badge bg-<?php echo $enrollment['is_als_passer'] ? 'success' : 'secondary'; ?>">
                        <?php echo $enrollment['is_als_passer'] ? 'Yes - ' . htmlspecialchars($enrollment['als_rating']) : 'No'; ?>
                    </span>
                </div>
            </div>
            <?php if ($enrollment['shs_track']): ?>
            <div class="row mt-3">
                <div class="col-md-12">
                    <strong>SHS Track:</strong> <?php echo htmlspecialchars($enrollment['shs_track']); ?> | 
                    <strong>Strand:</strong> <?php echo htmlspecialchars($enrollment['shs_strand']); ?> | 
                    <strong>Semester:</strong> <?php echo htmlspecialchars($enrollment['shs_semester']); ?>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 7: Learning Modality -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-laptop"></i> Section 7: Learning Modality</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <?php
                $modalities = [
                    'modality_modular_print' => 'Modular (Print)',
                    'modality_modular_digital' => 'Modular (Digital)',
                    'modality_online' => 'Online',
                    'modality_educational_tv' => 'Educational TV',
                    'modality_radio' => 'Radio',
                    'modality_blended' => 'Blended',
                    'modality_face_to_face' => 'Face-to-Face'
                ];
                
                foreach ($modalities as $key => $label):
                    if ($enrollment[$key]):
                ?>
                <div class="col-md-4 mb-2">
                    <span class="badge bg-success"><i class="bi bi-check-circle"></i> <?php echo $label; ?></span>
                </div>
                <?php 
                    endif;
                endforeach; 
                ?>
            </div>
            <?php if ($enrollment['preferred_distance_modality']): ?>
            <div class="mt-3">
                <strong>Preferred Distance Modality:</strong> 
                <span class="badge bg-primary"><?php echo htmlspecialchars($enrollment['preferred_distance_modality']); ?></span>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Section 8: Documents & Signature -->
    <div class="card mb-4 border-warning">
        <div class="card-header bg-warning text-dark">
            <h5 class="mb-0"><i class="bi bi-file-earmark-check"></i> Section 8: Documents & Signature</h5>
        </div>
        <div class="card-body">
            <!-- Signature -->
            <div class="mb-4">
                <h6 class="text-primary">Parent/Guardian Signature</h6>
                <?php if ($enrollment['signature_data']): ?>
                    <div class="border p-2" style="max-width: 400px; background: #f9f9f9;">
                        <img src="<?php echo htmlspecialchars($enrollment['signature_data']); ?>" 
                             alt="Signature" class="img-fluid">
                    </div>
                    <small class="text-muted">Signed on: <?php echo date('F j, Y', strtotime($enrollment['date_signed'])); ?></small>
                <?php else: ?>
                    <p class="text-danger">No signature provided</p>
                <?php endif; ?>
            </div>

            <!-- Uploaded Documents -->
            <h6 class="text-primary mb-3">Uploaded Documents</h6>
            <?php if (empty($documents)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> No documents uploaded yet.
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($documents as $doc): ?>
                    <div class="col-md-6 mb-3">
                        <div class="card border-<?php echo $statusColors[$doc['status']]; ?>">
                            <div class="card-header bg-light">
                                <div class="d-flex justify-content-between align-items-center">
                                    <strong><?php echo $docLabels[$doc['document_type']]; ?></strong>
                                    <span class="badge bg-<?php echo $statusColors[$doc['status']]; ?>">
                                        <?php echo ucfirst($doc['status']); ?>
                                    </span>
                                </div>
                            </div>
                            <div class="card-body">
                                <!-- File Preview/Download -->
                                <div class="mb-3">
                                    <?php 
                                    $fileExt = pathinfo($doc['file_path'], PATHINFO_EXTENSION);
                                    $isImage = in_array(strtolower($fileExt), ['jpg', 'jpeg', 'png', 'gif']);
                                    $encodedPath = base64_encode($doc['file_path']);
                                    $fileUrl = $basePath . '/file/serve/' . $encodedPath;
                                    $downloadUrl = $basePath . '/file/download/' . $encodedPath;
                                    ?>
                                    
                                    <?php if ($isImage): ?>
                                        <img src="<?php echo $fileUrl; ?>" 
                                             alt="Document" class="img-fluid mb-2" style="max-height: 200px;">
                                    <?php else: ?>
                                        <div class="text-center p-3 bg-light">
                                            <i class="bi bi-file-earmark-pdf" style="font-size: 3rem;"></i>
                                            <p class="mb-0">PDF Document</p>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <a href="<?php echo $fileUrl; ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-primary w-100">
                                        <i class="bi bi-download"></i> Download/View
                                    </a>
                                </div>

                                <!-- Review Information -->
                                <?php if ($doc['status'] !== 'pending'): ?>
                                    <div class="alert alert-<?php echo $statusColors[$doc['status']]; ?> mb-2">
                                        <small>
                                            <strong>Reviewed by:</strong> <?php echo htmlspecialchars($doc['reviewer_name'] ?? 'Unknown'); ?><br>
                                            <strong>Date:</strong> <?php echo date('M j, Y g:i A', strtotime($doc['reviewed_at'])); ?>
                                            <?php if ($doc['review_note']): ?>
                                                <br><strong>Note:</strong> <?php echo htmlspecialchars($doc['review_note']); ?>
                                            <?php endif; ?>
                                        </small>
                                    </div>
                                <?php endif; ?>

                                <!-- Action Buttons (only for pending documents) -->
                                <?php if ($doc['status'] === 'pending'): ?>
                                    <form method="POST" action="<?php echo $basePath; ?>/enrollment/document/approve/<?php echo $doc['id']; ?>" 
                                          class="d-inline">
                                        <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                        <input type="hidden" name="document_type" value="<?php echo $doc['document_type']; ?>">
                                        <button type="submit" class="btn btn-success btn-sm w-100 mb-2">
                                            <i class="bi bi-check-circle"></i> Approve
                                        </button>
                                    </form>
                                    
                                    <button type="button" class="btn btn-danger btn-sm w-100" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#rejectModal<?php echo $doc['id']; ?>">
                                        <i class="bi bi-x-circle"></i> Reject
                                    </button>

                                    <!-- Reject Modal -->
                                    <div class="modal fade" id="rejectModal<?php echo $doc['id']; ?>" tabindex="-1">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header bg-danger text-white">
                                                    <h5 class="modal-title">Reject Document</h5>
                                                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                                                </div>
                                                <form method="POST" action="<?php echo $basePath; ?>/enrollment/document/reject/<?php echo $doc['id']; ?>">
                                                    <div class="modal-body">
                                                        <input type="hidden" name="enrollment_id" value="<?php echo $enrollment['id']; ?>">
                                                        <input type="hidden" name="document_type" value="<?php echo $doc['document_type']; ?>">
                                                        <p>You are about to reject: <strong><?php echo $docLabels[$doc['document_type']]; ?></strong></p>
                                                        <div class="mb-3">
                                                            <label for="review_note<?php echo $doc['id']; ?>" class="form-label">
                                                                Reason for Rejection *
                                                            </label>
                                                            <textarea class="form-control" id="review_note<?php echo $doc['id']; ?>" 
                                                                      name="review_note" rows="3" required
                                                                      placeholder="Explain why this document is being rejected..."></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                                        <button type="submit" class="btn btn-danger">
                                                            <i class="bi bi-x-circle"></i> Reject Document
                                                        </button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Back Button -->
    <div class="text-center mb-4">
        <a href="<?php echo $basePath; ?>/enrollment/review" class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Back to Enrollment List
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

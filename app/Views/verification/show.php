<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 2
// Last modified: 2026-05-04
// Part of: SPED LMS — Enrollment Detail View

$basePath = isset($basePath) ? $basePath : '/';
$pageTitle = 'Enrollment Detail - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Print Button -->
    <div class="mb-3">
        <button class="btn btn-outline-secondary" onclick="window.print()">
            <i class="bi bi-printer"></i> Print
        </button>
    </div>
    
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3" style="color: #1e4072;">Enrollment Detail</h1>
            <p class="text-muted">
                Student: <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
            </p>
        </div>
        <div>
            <span class="badge bg-info">Enrollment ID: <?php echo $enrollment['id']; ?></span>
        </div>
    </div>
    
    <!-- Verification Alert -->
    <?php if ($allApproved): ?>
        <div class="alert alert-success" style="background-color: #d4edda; border-color: #3b6d11; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 1rem;">
            <strong>✓ All documents approved!</strong> Click "Verify Enrollment" below to create learner account and generate LRN.
        </div>
    <?php endif; ?>
                
                <!-- SECTION 1: LEARNER INFORMATION -->
                <div class="section-header">Section 1: Learner Information</div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Last Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['last_name']); ?></div>
                    </div>
                    <div>
                        <div class="field-label">First Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['first_name']); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Middle Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['middle_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Extension Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['extension_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Date of Birth</div>
                        <div class="field-value"><?php echo date('M d, Y', strtotime($enrollment['birth_date'])); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Age</div>
                        <div class="field-value"><?php echo $enrollment['age'] ?? 'N/A'; ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Sex</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['sex']); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Place of Birth</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['birth_place'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Mother Tongue</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_tongue'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Indigenous People</div>
                        <div class="field-value"><?php echo $enrollment['is_indigenous_people'] ? 'Yes - ' . htmlspecialchars($enrollment['indigenous_group']) : 'No'; ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">4Ps Beneficiary</div>
                        <div class="field-value"><?php echo $enrollment['is_4ps_beneficiary'] ? 'Yes - ' . htmlspecialchars($enrollment['fourps_household_id']) : 'No'; ?></div>
                    </div>
                </div>
                
                <!-- Disabilities -->
                <div class="field-row full">
                    <div>
                        <div class="field-label">Disabilities</div>
                        <div class="field-value">
                            <?php
                            $disabilities = [];
                            if ($enrollment['disability_visual']) $disabilities[] = 'Visual';
                            if ($enrollment['disability_hearing']) $disabilities[] = 'Hearing';
                            if ($enrollment['disability_learning']) $disabilities[] = 'Learning';
                            if ($enrollment['disability_speech']) $disabilities[] = 'Speech';
                            if ($enrollment['disability_intellectual']) $disabilities[] = 'Intellectual';
                            if ($enrollment['disability_physical']) $disabilities[] = 'Physical';
                            if ($enrollment['disability_emotional']) $disabilities[] = 'Emotional';
                            if ($enrollment['disability_chronic_illness']) $disabilities[] = 'Chronic Illness';
                            if ($enrollment['disability_others']) $disabilities[] = 'Others: ' . htmlspecialchars($enrollment['disability_others_specify']);
                            echo !empty($disabilities) ? implode(', ', $disabilities) : 'None';
                            ?>
                        </div>
                    </div>
                </div>
                
                <!-- SECTION 2: CURRENT ADDRESS -->
                <div class="section-header">Section 2: Current Address</div>
                <div class="field-row">
                    <div>
                        <div class="field-label">House No./Street</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_house_no'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Barangay</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_barangay'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">City/Municipality</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_city'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Province</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_province'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Zip Code</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['current_zip_code'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <!-- SECTION 3: PERMANENT ADDRESS -->
                <div class="section-header">Section 3: Permanent Address</div>
                <?php if ($enrollment['same_as_current_address']): ?>
                    <div class="alert alert-info">Same as Current Address</div>
                <?php else: ?>
                    <div class="field-row">
                        <div>
                            <div class="field-label">House No./Street</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_house_no'] ?? 'N/A'); ?></div>
                        </div>
                        <div>
                            <div class="field-label">Barangay</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_barangay'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="field-row">
                        <div>
                            <div class="field-label">City/Municipality</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_city'] ?? 'N/A'); ?></div>
                        </div>
                        <div>
                            <div class="field-label">Province</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_province'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                    <div class="field-row">
                        <div>
                            <div class="field-label">Zip Code</div>
                            <div class="field-value"><?php echo htmlspecialchars($enrollment['permanent_zip_code'] ?? 'N/A'); ?></div>
                        </div>
                    </div>
                <?php endif; ?>
                
                <!-- SECTION 4: PARENT/GUARDIAN INFORMATION -->
                <div class="section-header">Section 4: Parent/Guardian Information</div>
                <div class="field-row full">
                    <div>
                        <div class="field-label"><strong>Father's Information</strong></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Last Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['father_last_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">First Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['father_first_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Middle Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['father_middle_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Contact Number</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['father_contact_number'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <div class="field-row full">
                    <div>
                        <div class="field-label"><strong>Mother's Information</strong></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Maiden Last Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_maiden_last_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">First Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_first_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Middle Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_middle_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Contact Number</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['mother_contact_number'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <div class="field-row full">
                    <div>
                        <div class="field-label"><strong>Guardian's Information (if applicable)</strong></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Last Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['guardian_last_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">First Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['guardian_first_name'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Middle Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['guardian_middle_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Contact Number</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['guardian_contact_number'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <!-- SECTION 5: PREVIOUS SCHOOL INFORMATION -->
                <div class="section-header">Section 5: Previous School Information</div>
                <div class="field-row">
                    <div>
                        <div class="field-label">School Name</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_school_name'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">School Type</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_school_type'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Grade Level</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_grade_level'] ?? 'N/A'); ?></div>
                    </div>
                    <div>
                        <div class="field-label">School Year</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_school_year'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                <div class="field-row full">
                    <div>
                        <div class="field-label">School Address</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['previous_school_address'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <!-- SECTION 6: ENROLLMENT DETAILS -->
                <div class="section-header">Section 6: Enrollment Details</div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Grade Level to Enroll</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></div>
                    </div>
                    <div>
                        <div class="field-label">Balik Aral</div>
                        <div class="field-value"><?php echo $enrollment['is_balik_aral'] ? 'Yes' : 'No'; ?></div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">PEPT Passer</div>
                        <div class="field-value"><?php echo $enrollment['is_pept_passer'] ? 'Yes - Rating: ' . htmlspecialchars($enrollment['pept_rating']) : 'No'; ?></div>
                    </div>
                    <div>
                        <div class="field-label">ALS Passer</div>
                        <div class="field-value"><?php echo $enrollment['is_als_passer'] ? 'Yes - Rating: ' . htmlspecialchars($enrollment['als_rating']) : 'No'; ?></div>
                    </div>
                </div>
                
                <!-- SECTION 7: LEARNING MODALITY -->
                <div class="section-header">Section 7: Learning Modality</div>
                <div class="field-row full">
                    <div>
                        <div class="field-label">Selected Modalities</div>
                        <div class="field-value">
                            <?php
                            $modalities = [];
                            if ($enrollment['modality_modular_print']) $modalities[] = 'Modular (Print)';
                            if ($enrollment['modality_modular_digital']) $modalities[] = 'Modular (Digital)';
                            if ($enrollment['modality_online']) $modalities[] = 'Online';
                            if ($enrollment['modality_educational_tv']) $modalities[] = 'Educational TV';
                            if ($enrollment['modality_radio']) $modalities[] = 'Radio';
                            if ($enrollment['modality_blended']) $modalities[] = 'Blended';
                            if ($enrollment['modality_face_to_face']) $modalities[] = 'Face-to-Face';
                            echo !empty($modalities) ? implode(', ', $modalities) : 'None';
                            ?>
                        </div>
                    </div>
                </div>
                <div class="field-row">
                    <div>
                        <div class="field-label">Preferred Distance Learning Modality</div>
                        <div class="field-value"><?php echo htmlspecialchars($enrollment['preferred_distance_modality'] ?? 'N/A'); ?></div>
                    </div>
                </div>
                
                <!-- DOCUMENTS SECTION -->
                <div class="section-header">Documents for Approval</div>
                <div class="row">
                    <?php foreach ($documents as $doc): ?>
                        <div class="col-md-6">
                            <div class="doc-card">
                                <div class="d-flex justify-content-between align-items-start mb-2">
                                    <h6 class="mb-0">
                                        <?php
                                        $docLabels = [
                                            'psa_birth_cert' => 'PSA Birth Certificate',
                                            'pwd_id' => 'PWD ID',
                                            'medical_record' => 'Medical Record',
                                            'beef_form' => 'BEEF Form'
                                        ];
                                        echo $docLabels[$doc['document_type']] ?? $doc['document_type'];
                                        ?>
                                    </h6>
                                    <span class="doc-status <?php echo $doc['status']; ?>">
                                        <?php echo ucfirst($doc['status']); ?>
                                    </span>
                                </div>
                                
                                <!-- Document Preview -->
                                <div class="mb-2">
                                    <a href="<?php echo $basePath; ?><?php echo htmlspecialchars($doc['file_path']); ?>" target="_blank" class="btn btn-sm btn-outline-secondary">
                                        View Document
                                    </a>
                                </div>
                                
                                <!-- Review Notes -->
                                <?php if ($doc['review_note']): ?>
                                    <div class="alert alert-warning small mb-2">
                                        <strong>Review Note:</strong> <?php echo htmlspecialchars($doc['review_note']); ?>
                                    </div>
                                <?php endif; ?>
                                
                                <!-- Action Buttons (only if pending) -->
                                <?php if ($doc['status'] === 'pending'): ?>
                                    <div class="doc-actions">
                                        <button class="btn btn-sm btn-approve" onclick="approveDocument(<?php echo $doc['id']; ?>)">
                                            ✓ Approve
                                        </button>
                                        <button class="btn btn-sm btn-reject" onclick="rejectDocument(<?php echo $doc['id']; ?>)">
                                            ✗ Reject
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
                
                <!-- Verify Enrollment Button -->
                <?php if ($allApproved): ?>
                    <div class="mt-4">
                        <button class="btn btn-lg btn-success btn-verify" onclick="verifyEnrollment(<?php echo $enrollment['id']; ?>)" style="background-color: #3b6d11; border-color: #3b6d11;">
                            ✓ Verify Enrollment & Create Learner Account
                        </button>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<!-- Rejection Modal -->
<div class="modal fade" id="rejectModal" tabindex="-1">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title">Reject Document</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <label class="form-label">Reason for Rejection</label>
                <textarea class="form-control" id="rejectionReason" rows="4" placeholder="Explain why this document is being rejected..."></textarea>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-danger" onclick="submitRejection()">Reject Document</button>
            </div>
        </div>
    </div>
</div>

<script>
    let currentDocumentId = null;
    const basePath = '<?php echo $basePath; ?>';
    
    function approveDocument(docId) {
        if (confirm('Approve this document?')) {
            fetch(basePath + 'enrollment/document/approve/' + docId, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Document approved!');
                    location.reload();
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(e => alert('Error: ' + e.message));
        }
    }
    
    function rejectDocument(docId) {
        currentDocumentId = docId;
        new bootstrap.Modal(document.getElementById('rejectModal')).show();
    }
    
    function submitRejection() {
        const reason = document.getElementById('rejectionReason').value;
        if (!reason.trim()) {
            alert('Please provide a reason for rejection');
            return;
        }
        
        fetch(basePath + 'enrollment/document/reject/' + currentDocumentId, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json'
            },
            body: JSON.stringify({ reason: reason })
        })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                alert('Document rejected!');
                location.reload();
            } else {
                alert('Error: ' + data.message);
            }
        })
        .catch(e => alert('Error: ' + e.message));
    }
    
    function verifyEnrollment(enrollmentId) {
        if (confirm('Verify this enrollment? This will create a learner account and generate an LRN.')) {
            fetch(basePath + 'verification/' + enrollmentId + '/verify', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    alert('Enrollment verified!\nLRN: ' + data.lrn + '\nLearner account created successfully.');
                    location.href = basePath + 'verification';
                } else {
                    alert('Error: ' + data.message);
                }
            })
            .catch(e => alert('Error: ' + e.message));
        }
    }
</script>

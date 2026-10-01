<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-06
// Part of: SignED — Enrollment Review Detail (BEEF Document Style)

$pageTitle = 'Review Enrollment - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Include BEEF Document Style -->
<link rel="stylesheet" href="<?php echo $basePath; ?>/css/beef-document.css">

<style>
/* Print-specific styles */
@media print {
    .no-print {
        display: none !important;
    }
    .beef-document {
        box-shadow: none;
        margin: 0;
        padding: 20px;
    }
    body {
        background: white;
    }
}

/* Review-specific styles */
.review-badge {
    display: inline-block;
    padding: 4px 12px;
    border-radius: 4px;
    font-size: 12px;
    font-weight: bold;
    margin-left: 10px;
}

.badge-pending {
    background: #fff3cd;
    color: #856404;
}

.badge-verified {
    background: #d4edda;
    color: #155724;
}

.badge-rejected {
    background: #f8d7da;
    color: #721c24;
}

.action-buttons {
    position: fixed;
    bottom: 20px;
    right: 20px;
    z-index: 1000;
    display: flex;
    gap: 10px;
}

@media print {
    .action-buttons {
        display: none;
    }
}
</style>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Action Bar (No Print) -->
    <div class="d-flex justify-content-between align-items-center mb-4 no-print">
        <div>
            <a href="<?php echo $basePath; ?>/enrollment/review" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to List
            </a>
            <button onclick="window.print()" class="btn btn-outline-primary">
                <i class="bi bi-printer"></i> Print
            </button>
        </div>
        <div>
            <span class="review-badge badge-<?php echo $enrollment['status']; ?>">
                <?php echo strtoupper($enrollment['status']); ?>
            </span>
        </div>
    </div>

    <!-- BEEF Document -->
    <div class="beef-document">
        <!-- Document Header -->
        <div class="document-header">
            <p>Republic of the Philippines</p>
            <p>Department of Education</p>
            <h1>Basic Education Enrollment Form (BEEF)</h1>
            <h2>Special Education (SPED) Program</h2>
            <p>School Year <?php echo htmlspecialchars($enrollment['school_year']); ?></p>
            <p style="margin-top: 10px; font-size: 11px;">
                <strong>Enrollment ID:</strong> #<?php echo $enrollment['id']; ?> | 
                <strong>Type:</strong> <?php echo ucfirst($enrollment['enrollment_type']); ?> | 
                <strong>Submitted:</strong> <?php echo date('M j, Y g:i A', strtotime($enrollment['submitted_at'])); ?>
            </p>
        </div>

        <!-- Section 1: Learner Information -->
        <div class="form-section">
            <div class="section-title">SECTION 1: LEARNER INFORMATION</div>
            
            <div class="form-row">
                <div class="form-field full-width">
                    <label class="form-label">DepEd LRN (optional — assigned by DepEd LIS)</label>
                    <div class="form-value"><?php echo htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($enrollment['lrn'] ?? null)); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Last Name</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['last_name']); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">First Name</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['first_name']); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Middle Name</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['middle_name'] ?: 'N/A'); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">Extension Name</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['extension_name'] ?: 'N/A'); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Birth Date</label>
                    <div class="form-value"><?php echo date('F j, Y', strtotime($enrollment['birth_date'])); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">Sex</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['sex']); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">Age</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['age'] ?: 'N/A'); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Place of Birth</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['place_of_birth_city'] . ', ' . $enrollment['place_of_birth_province']); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">Mother Tongue</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['mother_tongue'] ?: 'N/A'); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Indigenous People</label>
                    <div class="form-value">
                        <?php if ($enrollment['is_indigenous_people']): ?>
                            ☑ Yes - <?php echo htmlspecialchars($enrollment['indigenous_group']); ?>
                        <?php else: ?>
                            ☐ No
                        <?php endif; ?>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label">4Ps Beneficiary</label>
                    <div class="form-value">
                        <?php if ($enrollment['is_4ps_beneficiary']): ?>
                            ☑ Yes - <?php echo htmlspecialchars($enrollment['fourps_household_id']); ?>
                        <?php else: ?>
                            ☐ No
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field full-width">
                    <label class="form-label">Disabilities</label>
                    <div class="form-value">
                        <?php
                        $disabilities = [];
                        if ($enrollment['disability_visual']) $disabilities[] = '☑ Visual Impairment';
                        if ($enrollment['disability_hearing']) $disabilities[] = '☑ Hearing Impairment';
                        if ($enrollment['disability_learning']) $disabilities[] = '☑ Learning Disability';
                        if ($enrollment['disability_speech']) $disabilities[] = '☑ Speech/Language Impairment';
                        if ($enrollment['disability_intellectual']) $disabilities[] = '☑ Intellectual Disability';
                        if ($enrollment['disability_physical']) $disabilities[] = '☑ Physical Disability';
                        if ($enrollment['disability_emotional']) $disabilities[] = '☑ Emotional-Behavioral Disorder';
                        if ($enrollment['disability_chronic_illness']) $disabilities[] = '☑ Chronic Illness';
                        if ($enrollment['disability_others']) $disabilities[] = '☑ Others: ' . htmlspecialchars($enrollment['disability_others_specify']);
                        
                        echo !empty($disabilities) ? implode(', ', $disabilities) : 'None specified';
                        ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 2: Current Address -->
        <div class="form-section">
            <div class="section-title">SECTION 2: CURRENT ADDRESS</div>
            
            <div class="form-row">
                <div class="form-field full-width">
                    <label class="form-label">Complete Address</label>
                    <div class="form-value">
                        <?php echo htmlspecialchars($enrollment['current_house_no']); ?>,
                        Barangay <?php echo htmlspecialchars($enrollment['current_barangay']); ?>,
                        <?php echo htmlspecialchars($enrollment['current_city']); ?>,
                        <?php echo htmlspecialchars($enrollment['current_province']); ?>,
                        <?php echo htmlspecialchars($enrollment['current_region']); ?>
                        <?php echo htmlspecialchars($enrollment['current_zip_code']); ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Section 3: Permanent Address -->
        <div class="form-section">
            <div class="section-title">SECTION 3: PERMANENT ADDRESS</div>
            
            <?php if ($enrollment['same_as_current_address']): ?>
                <div class="form-row">
                    <div class="form-field full-width">
                        <div class="form-value">☑ Same as current address</div>
                    </div>
                </div>
            <?php else: ?>
                <div class="form-row">
                    <div class="form-field full-width">
                        <label class="form-label">Complete Address</label>
                        <div class="form-value">
                            <?php echo htmlspecialchars($enrollment['permanent_house_no']); ?>,
                            Barangay <?php echo htmlspecialchars($enrollment['permanent_barangay']); ?>,
                            <?php echo htmlspecialchars($enrollment['permanent_city']); ?>,
                            <?php echo htmlspecialchars($enrollment['permanent_province']); ?>,
                            <?php echo htmlspecialchars($enrollment['permanent_region']); ?>
                            <?php echo htmlspecialchars($enrollment['permanent_zip_code']); ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <!-- Section 4: Parent/Guardian Information -->
        <div class="form-section">
            <div class="section-title">SECTION 4: PARENT/GUARDIAN INFORMATION</div>
            
            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Father's Name</label>
                    <div class="form-value">
                        <?php echo htmlspecialchars(($enrollment['father_first_name'] ?: '') . ' ' . 
                             ($enrollment['father_middle_name'] ?: '') . ' ' . 
                             ($enrollment['father_last_name'] ?: '') ?: 'N/A'); ?>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label">Contact Number</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['father_contact_number'] ?: 'N/A'); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Mother's Maiden Name</label>
                    <div class="form-value">
                        <?php echo htmlspecialchars(($enrollment['mother_first_name'] ?: '') . ' ' . 
                             ($enrollment['mother_middle_name'] ?: '') . ' ' . 
                             ($enrollment['mother_maiden_last_name'] ?: '') ?: 'N/A'); ?>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label">Contact Number</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['mother_contact_number'] ?: 'N/A'); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Guardian's Name</label>
                    <div class="form-value">
                        <?php echo htmlspecialchars(($enrollment['guardian_first_name'] ?: '') . ' ' . 
                             ($enrollment['guardian_middle_name'] ?: '') . ' ' . 
                             ($enrollment['guardian_last_name'] ?: '') ?: 'N/A'); ?>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label">Contact Number</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['guardian_contact_number'] ?: 'N/A'); ?></div>
                </div>
            </div>
        </div>

        <!-- Section 5: Previous School (if applicable) -->
        <?php if ($enrollment['enrollment_type'] !== 'new'): ?>
        <div class="form-section">
            <div class="section-title">SECTION 5: PREVIOUS SCHOOL INFORMATION</div>
            
            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">School Name</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['previous_school_name'] ?: 'N/A'); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">School ID</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['previous_school_id'] ?: 'N/A'); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field full-width">
                    <label class="form-label">School Address</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['previous_school_address'] ?: 'N/A'); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Grade Level</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['previous_grade_level'] ?: 'N/A'); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">School Year</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['previous_school_year'] ?: 'N/A'); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">School Type</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['previous_school_type'] ?: 'N/A'); ?></div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Section 6: Enrollment Details -->
        <div class="form-section">
            <div class="section-title">SECTION 6: ENROLLMENT DETAILS</div>
            
            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Grade Level to Enroll</label>
                    <div class="form-value"><strong><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></strong></div>
                </div>
                <div class="form-field">
                    <label class="form-label">School Year</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['school_year']); ?></div>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Balik-Aral</label>
                    <div class="form-value"><?php echo $enrollment['is_balik_aral'] ? '☑ Yes' : '☐ No'; ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">PEPT Passer</label>
                    <div class="form-value">
                        <?php echo $enrollment['is_pept_passer'] ? '☑ Yes - ' . htmlspecialchars($enrollment['pept_rating']) : '☐ No'; ?>
                    </div>
                </div>
                <div class="form-field">
                    <label class="form-label">ALS Passer</label>
                    <div class="form-value">
                        <?php echo $enrollment['is_als_passer'] ? '☑ Yes - ' . htmlspecialchars($enrollment['als_rating']) : '☐ No'; ?>
                    </div>
                </div>
            </div>

            <?php if ($enrollment['shs_track']): ?>
            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">SHS Track</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['shs_track']); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">Strand</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['shs_strand']); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">Semester</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['shs_semester']); ?></div>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Section 7: Learning Modality -->
        <div class="form-section">
            <div class="section-title">SECTION 7: LEARNING MODALITY</div>
            
            <div class="form-row">
                <div class="form-field full-width">
                    <label class="form-label">Selected Modalities</label>
                    <div class="form-value">
                        <?php
                        $modalities = [];
                        if ($enrollment['modality_modular_print']) $modalities[] = '☑ Modular (Print)';
                        if ($enrollment['modality_modular_digital']) $modalities[] = '☑ Modular (Digital)';
                        if ($enrollment['modality_online']) $modalities[] = '☑ Online';
                        if ($enrollment['modality_educational_tv']) $modalities[] = '☑ Educational TV';
                        if ($enrollment['modality_radio']) $modalities[] = '☑ Radio';
                        if ($enrollment['modality_blended']) $modalities[] = '☑ Blended';
                        if ($enrollment['modality_face_to_face']) $modalities[] = '☑ Face-to-Face';
                        
                        echo !empty($modalities) ? implode(', ', $modalities) : 'None selected';
                        ?>
                    </div>
                </div>
            </div>

            <?php if ($enrollment['preferred_distance_modality']): ?>
            <div class="form-row">
                <div class="form-field full-width">
                    <label class="form-label">Preferred Distance Modality</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['preferred_distance_modality']); ?></div>
                </div>
            </div>
            <?php endif; ?>

            <!-- Digital Readiness & SignED Candidate Survey Result -->
            <?php 
                $hasInternet = !empty($enrollment['survey_has_internet']);
                $devices = !empty($enrollment['survey_devices']) ? $enrollment['survey_devices'] : (!empty($enrollment['has_device']) ? 'Smartphone/Device Available' : 'None');
                $isWilling = !empty($enrollment['survey_willing_online']) || !empty($enrollment['willing_digital']);
                $isSignEDCandidate = $hasInternet && $isWilling;
            ?>
            <div style="margin-top: 15px; padding: 12px; background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 6px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 8px;">
                    <strong style="font-size: 0.85rem; color: #1e293b;">Digital Readiness & SignED Preparedness Survey:</strong>
                    <?php if ($isSignEDCandidate): ?>
                        <span style="background-color: #e0f2fe; color: #0369a1; border: 1px solid #bae6fd; font-size: 0.75rem; font-weight: 600; padding: 3px 8px; border-radius: 4px;">
                            <i class="bi bi-laptop me-1"></i> SignED Candidate
                        </span>
                    <?php else: ?>
                        <span style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.75rem; font-weight: 600; padding: 3px 8px; border-radius: 4px;">
                            <i class="bi bi-person-workspace me-1"></i> Non-SignED Candidate (Traditional F2F)
                        </span>
                    <?php endif; ?>
                </div>
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(180px, 1fr)); gap: 8px; font-size: 0.8rem;">
                    <div><span style="color: #64748b;">Internet Access:</span> <strong><?= $hasInternet ? '<span style="color: #16a34a;">Yes (Available)</span>' : '<span style="color: #64748b;">No</span>' ?></strong></div>
                    <div><span style="color: #64748b;">Available Devices:</span> <strong><?= htmlspecialchars($devices) ?></strong></div>
                    <div><span style="color: #64748b;">Online Learning Willingness:</span> <strong><?= $isWilling ? '<span style="color: #16a34a;">Yes (Willing)</span>' : '<span style="color: #64748b;">Physical Only</span>' ?></strong></div>
                </div>
            </div>
        </div>

        <!-- Section 8: Documents & Signature -->
        <div class="form-section">
            <div class="section-title">SECTION 8: DOCUMENTS & SIGNATURE</div>
            
            <div class="form-row">
                <div class="form-field full-width">
                    <label class="form-label">Parent/Guardian Signature</label>
                    <?php if ($enrollment['signature_data']): ?>
                        <div style="border: 1px solid #dee2e6; padding: 10px; background: #f9f9f9; max-width: 400px;">
                            <img src="<?php echo htmlspecialchars($enrollment['signature_data']); ?>" 
                                 alt="Signature" style="max-width: 100%; height: auto;">
                        </div>
                        <div class="form-value" style="margin-top: 5px;">
                            <small>Signed on: <?php echo date('F j, Y', strtotime($enrollment['date_signed'])); ?></small>
                        </div>
                    <?php else: ?>
                        <div class="form-value">No signature provided</div>
                    <?php endif; ?>
                </div>
            </div>

            <div class="form-row">
                <div class="form-field full-width">
                    <label class="form-label">Uploaded Documents</label>
                    <div class="form-value">
                        <?php if (empty($documents)): ?>
                            No documents uploaded
                        <?php else: ?>
                            <ul style="margin: 0; padding-left: 20px;">
                                <?php 
                                $docLabels = [
                                    'psa_birth_cert' => 'PSA Birth Certificate',
                                    'pwd_id' => 'PWD ID',
                                    'medical_record' => 'Medical Record',
                                    'beef_form' => 'BEEF Form'
                                ];
                                foreach ($documents as $doc): 
                                ?>
                                    <li>
                                        <?php echo $docLabels[$doc['document_type']]; ?> 
                                        <span class="no-print">
                                            - <a href="<?php echo $basePath . '/' . $doc['file_path']; ?>" target="_blank">View</a>
                                        </span>
                                    </li>
                                <?php endforeach; ?>
                            </ul>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>

        <!-- Verification Section (for SPED Teacher) -->
        <div class="form-section no-print" style="border-top: 3px double #000; margin-top: 30px; padding-top: 20px;">
            <div class="section-title">VERIFICATION BY SPED TEACHER</div>
            
            <div class="form-row">
                <div class="form-field">
                    <label class="form-label">Status</label>
                    <div class="form-value">
                        <span class="review-badge badge-<?php echo $enrollment['status']; ?>">
                            <?php echo strtoupper($enrollment['status']); ?>
                        </span>
                    </div>
                </div>
                <?php if ($enrollment['verified_by']): ?>
                <div class="form-field">
                    <label class="form-label">Verified By</label>
                    <div class="form-value"><?php echo htmlspecialchars($enrollment['verifier_name'] ?: 'N/A'); ?></div>
                </div>
                <div class="form-field">
                    <label class="form-label">Verified On</label>
                    <div class="form-value"><?php echo $enrollment['verified_at'] ? date('M j, Y g:i A', strtotime($enrollment['verified_at'])) : 'N/A'; ?></div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Floating Action Buttons (No Print) -->
    <?php if ($enrollment['status'] === 'pending'): ?>
    <?php 
        $isSignEDCand = (!empty($enrollment['survey_has_internet']) && !empty($enrollment['survey_willing_online']))
                     || (!empty($enrollment['has_device']) && !empty($enrollment['willing_digital']));
    ?>
    <div class="action-buttons no-print">
        <button type="button" class="btn btn-success btn-lg" data-bs-toggle="modal" data-bs-target="#approveModal">
            <i class="bi bi-check-circle-fill me-1"></i> Approve & Claim Learner
        </button>
        
        <button type="button" class="btn btn-danger btn-lg" 
                data-bs-toggle="modal" data-bs-target="#rejectModal">
            <i class="bi bi-x-circle-fill me-1"></i> Reject
        </button>
    </div>

    <!-- Approve Modal -->
    <div class="modal fade" id="approveModal" tabindex="-1" aria-labelledby="approveModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-success text-white py-3">
                    <h5 class="modal-title fs-6 fw-bold" id="approveModalLabel">
                        <i class="bi bi-check-circle-fill me-2"></i> Approve & Verify Enrollment
                    </h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="POST" action="<?php echo $basePath; ?>/enrollment/approve/<?php echo $enrollment['id']; ?>">
                    <div class="modal-body p-4">
                        <div class="d-flex align-items-center mb-3 p-3 bg-light rounded-3 border">
                            <div class="rounded-circle bg-success bg-opacity-10 text-success d-flex align-items-center justify-content-center me-3" style="width: 44px; height: 44px; flex-shrink: 0;">
                                <i class="bi bi-person-check fs-4"></i>
                            </div>
                            <div>
                                <div class="fw-bold text-dark fs-6"><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></div>
                                <div class="small text-muted">Grade Level: <strong><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></strong> • SY: <strong><?php echo htmlspecialchars($enrollment['school_year']); ?></strong></div>
                            </div>
                        </div>

                        <!-- Candidate Status Alert -->
                        <?php if ($isSignEDCand): ?>
                            <div class="p-3 mb-3 rounded-3 border" style="background-color: #f0fdf4; border-color: #bbf7d0 !important;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong class="text-success small"><i class="bi bi-laptop me-1"></i> SignED Candidate (Digital LMS Ready)</strong>
                                    <span class="badge" style="background-color: #dcfce7; color: #15803d; border: 1px solid #86efac; font-size: 0.7rem;">Recommended</span>
                                </div>
                                <p class="small text-muted mb-0" style="font-size: 0.8rem;">
                                    This learner has internet & device access at home and is prepared for interactive SignED LMS learning modules.
                                </p>
                            </div>
                        <?php else: ?>
                            <div class="p-3 mb-3 rounded-3 border" style="background-color: #f8fafc; border-color: #e2e8f0 !important;">
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <strong class="text-secondary small"><i class="bi bi-person-workspace me-1"></i> Non-SignED Candidate (Traditional F2F)</strong>
                                    <span class="badge" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.7rem;">Physical Class</span>
                                </div>
                                <p class="small text-muted mb-0" style="font-size: 0.8rem;">
                                    This learner is recommended for classroom face-to-face instruction and printed SPED modules.
                                </p>
                            </div>
                        <?php endif; ?>

                        <!-- Account Generation Toggle Option -->
                        <div class="p-3 rounded-3 border bg-white shadow-sm">
                            <div class="form-check form-switch mb-0">
                                <input class="form-check-input" type="checkbox" role="switch" id="create_learner_account" name="create_learner_account" value="1" <?= $isSignEDCand ? 'checked' : '' ?> style="cursor: pointer; width: 2.5em; height: 1.25em;">
                                <label class="form-check-label fw-bold text-dark ms-2 small" for="create_learner_account" style="cursor: pointer;">
                                    Generate SignED Learner Account & Credentials
                                </label>
                            </div>
                            <div class="form-text text-muted mt-2 ps-4" style="font-size: 0.75rem;">
                                <?php if ($isSignEDCand): ?>
                                    Generates an internal Student ID & temporary password and emails credentials to the parent for LMS login.
                                <?php else: ?>
                                    Leave unchecked if the student will only attend physical classes without online LMS portal access.
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                        <button type="submit" class="btn btn-success btn-sm px-3 fw-semibold" style="border-radius: 6px;">
                            <i class="bi bi-check-lg me-1"></i> Confirm & Approve
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Reject Modal -->
    <div class="modal fade" id="rejectModal" tabindex="-1">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-danger text-white py-3">
                    <h5 class="modal-title fs-6 fw-bold">Reject Enrollment</h5>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
                <form method="POST" action="<?php echo $basePath; ?>/enrollment/reject/<?php echo $enrollment['id']; ?>">
                    <div class="modal-body p-4">
                        <div class="mb-3">
                            <label for="rejection_reason" class="form-label small fw-bold">
                                Reason for Rejection <span class="text-danger">*</span>
                            </label>
                            <textarea class="form-control" id="rejection_reason" 
                                      name="rejection_reason" rows="4" required
                                      placeholder="Explain what needs to be corrected..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light py-2 px-3">
                        <button type="button" class="btn btn-outline-secondary btn-sm px-3" data-bs-dismiss="modal" style="border-radius: 6px;">Cancel</button>
                        <button type="submit" class="btn btn-danger btn-sm px-3 fw-semibold" style="border-radius: 6px;">Reject Enrollment</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

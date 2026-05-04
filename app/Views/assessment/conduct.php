<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Assessment Form (Parent Submission)

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-clipboard-list"></i> Learner's Educational Assessment
                </h1>
                <p class="text-muted mt-2">IEP Part 1 - Assessment Form</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Assessment Form -->
        <form id="assessmentForm" method="POST" action="<?php echo BASE_PATH; ?>/assessment/submit">
            <input type="hidden" name="student_id" value="<?php echo htmlspecialchars($student['id']); ?>">

            <!-- Card: Section A - Learner Information (Read-Only) -->
            <div class="card mb-4 border-left-crimson">
                <div class="card-header bg-light" style="border-left: 4px solid #a01422;">
                    <h5 class="mb-0" style="color: #1e4072;">
                        <i class="fas fa-user"></i> Section A: Learner's Information Background (Pre-filled)
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Last Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['last_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">First Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['first_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Middle Name</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['middle_name'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Name Extension</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['extension_name'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Date of Birth</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['birth_date']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Age</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['age'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Religion</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['mother_tongue'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-2">
                            <div class="form-group">
                                <label class="form-label">Sex</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['sex']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Home Address</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['current_house_no'] . ' ' . $student['current_barangay'] . ', ' . $student['current_city']); ?>" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">LRN</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['lrn']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">School</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['current_city'] . ' SPED School'); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">School Year</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars(date('Y') . ' - ' . (date('Y') + 1)); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-3">
                            <div class="form-group">
                                <label class="form-label">Name of Adviser</label>
                                <input type="text" class="form-control" value="SPED Teacher" readonly>
                            </div>
                        </div>
                    </div>

                    <!-- Sources of Information -->
                    <hr class="my-4">
                    <h6 style="color: #1e4072; font-weight: bold;">Sources of Information</h6>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Name of Father</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['father_first_name'] . ' ' . $student['father_last_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['father_contact_number'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Occupation</label>
                                <input type="text" class="form-control" value="" readonly>
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Name of Mother</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['mother_first_name'] . ' ' . $student['mother_maiden_last_name']); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Contact Number</label>
                                <input type="text" class="form-control" value="<?php echo htmlspecialchars($student['mother_contact_number'] ?? ''); ?>" readonly>
                            </div>
                        </div>
                        <div class="col-md-4">
                            <div class="form-group">
                                <label class="form-label">Occupation</label>
                                <input type="text" class="form-control" value="" readonly>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Section A.2 - Education History (Parent Fills) -->
            <div class="card mb-4 border-left-navy">
                <div class="card-header bg-light" style="border-left: 4px solid #1e4072;">
                    <h5 class="mb-0" style="color: #1e4072;">
                        <i class="fas fa-book"></i> Section A.2: Education History
                    </h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Previous School Attended <span class="text-danger">*</span></label>
                                <input type="text" name="previous_school" class="form-control" 
                                       value="<?php echo htmlspecialchars($existingAssessment['education_history']['previous_school'] ?? ''); ?>" required>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">Grade Level</label>
                                <input type="text" name="grade_level" class="form-control"
                                       value="<?php echo htmlspecialchars($existingAssessment['education_history']['grade_level'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">With IEP?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="with_iep" id="with_iep_yes" value="yes"
                                               <?php echo ($existingAssessment['education_history']['with_iep'] ?? 'no') === 'yes' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="with_iep_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="with_iep" id="with_iep_no" value="no"
                                               <?php echo ($existingAssessment['education_history']['with_iep'] ?? 'no') === 'no' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="with_iep_no">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-md-6">
                            <div class="form-group">
                                <label class="form-label">With Support Services?</label>
                                <div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="with_support_services" id="with_support_yes" value="yes"
                                               <?php echo ($existingAssessment['education_history']['with_support_services'] ?? 'no') === 'yes' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="with_support_yes">Yes</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="with_support_services" id="with_support_no" value="no"
                                               <?php echo ($existingAssessment['education_history']['with_support_services'] ?? 'no') === 'no' ? 'checked' : ''; ?>>
                                        <label class="form-check-label" for="with_support_no">No</label>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">If Yes, Specify the Support Service/s Availed</label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="support_services[]" value="screening_assessment" id="support_screening">
                                    <label class="form-check-label" for="support_screening">Screening and Assessment (e.g. MFAT, ECCD Checklist, Psycho-Educational)</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="support_services[]" value="occupational_therapy" id="support_ot">
                                    <label class="form-check-label" for="support_ot">Occupational Therapy</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="support_services[]" value="physical_therapy" id="support_pt">
                                    <label class="form-check-label" for="support_pt">Physical Therapy</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="support_services[]" value="behavioral_therapy" id="support_behavioral">
                                    <label class="form-check-label" for="support_behavioral">Behavioral Therapy</label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="support_services[]" value="psychosocial_intervention" id="support_psychosocial">
                                    <label class="form-check-label" for="support_psychosocial">Psychosocial Intervention</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="support_services[]" value="speech_language" id="support_speech">
                                    <label class="form-check-label" for="support_speech">Speech and Language Therapy</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="support_services[]" value="daily_living_skills" id="support_daily">
                                    <label class="form-check-label" for="support_daily">Daily Living Skills</label>
                                </div>
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="support_services[]" value="skills_development" id="support_skills">
                                    <label class="form-check-label" for="support_skills">Skills Development</label>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Card: Section B - Assessment Information (Parent Fills) -->
            <div class="card mb-4 border-left-crimson">
                <div class="card-header bg-light" style="border-left: 4px solid #a01422;">
                    <h5 class="mb-0" style="color: #1e4072;">
                        <i class="fas fa-table"></i> Section B: Assessment Information
                    </h5>
                </div>
                <div class="card-body">
                    <p class="text-muted mb-3">Add assessment services, MDT members, dates, and supporting documents</p>

                    <div class="table-responsive">
                        <table class="table table-bordered" id="assessmentTable">
                            <thead style="background-color: #1e4072; color: white;">
                                <tr>
                                    <th>Assessment Service/s Availed</th>
                                    <th>Members of MDT</th>
                                    <th>Date/s of Assessment/s</th>
                                    <th>Supporting Documents</th>
                                    <th style="width: 80px;">Action</th>
                                </tr>
                            </thead>
                            <tbody id="assessmentRows">
                                <?php if ($existingAssessment && !empty($existingAssessment['assessment_info'])): ?>
                                    <?php foreach ($existingAssessment['assessment_info'] as $index => $item): ?>
                                        <tr class="assessment-row">
                                            <td>
                                                <input type="text" name="assessment_service[]" class="form-control form-control-sm" 
                                                       value="<?php echo htmlspecialchars($item['service']); ?>" required>
                                            </td>
                                            <td>
                                                <input type="text" name="mdt_members[]" class="form-control form-control-sm"
                                                       value="<?php echo htmlspecialchars($item['mdt_members']); ?>">
                                            </td>
                                            <td>
                                                <input type="date" name="assessment_dates[]" class="form-control form-control-sm"
                                                       value="<?php echo htmlspecialchars($item['assessment_date']); ?>">
                                            </td>
                                            <td>
                                                <input type="text" name="supporting_docs[]" class="form-control form-control-sm"
                                                       value="<?php echo htmlspecialchars($item['supporting_documents']); ?>">
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-danger remove-row">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr class="assessment-row">
                                        <td><input type="text" name="assessment_service[]" class="form-control form-control-sm" required></td>
                                        <td><input type="text" name="mdt_members[]" class="form-control form-control-sm"></td>
                                        <td><input type="date" name="assessment_dates[]" class="form-control form-control-sm"></td>
                                        <td><input type="text" name="supporting_docs[]" class="form-control form-control-sm"></td>
                                        <td>
                                            <button type="button" class="btn btn-sm btn-danger remove-row">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>

                    <button type="button" class="btn btn-sm" style="background-color: #1e4072; color: white;" id="addRowBtn">
                        <i class="fas fa-plus"></i> Add Assessment Service
                    </button>
                </div>
            </div>

            <!-- Form Actions -->
            <div class="row mb-4">
                <div class="col-12">
                    <button type="submit" class="btn" style="background-color: #a01422; color: white;">
                        <i class="fas fa-check"></i> Submit Assessment
                    </button>
                    <button type="reset" class="btn btn-secondary">
                        <i class="fas fa-redo"></i> Clear Form
                    </button>
                    <a href="<?php echo BASE_PATH; ?>/dashboard" class="btn btn-outline-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Remove signature pad script since not needed -->


<style>
.border-left-crimson {
    border-left: 4px solid #a01422 !important;
}

.border-left-navy {
    border-left: 4px solid #1e4072 !important;
}

.form-check-input:checked {
    background-color: #a01422;
    border-color: #a01422;
}

.form-control:focus {
    border-color: #a01422;
    box-shadow: 0 0 0 0.2rem rgba(160, 20, 34, 0.25);
}
</style>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Add row button
    document.getElementById('addRowBtn').addEventListener('click', function() {
        const tbody = document.getElementById('assessmentRows');
        const newRow = document.createElement('tr');
        newRow.className = 'assessment-row';
        newRow.innerHTML = `
            <td><input type="text" name="assessment_service[]" class="form-control form-control-sm" required></td>
            <td><input type="text" name="mdt_members[]" class="form-control form-control-sm"></td>
            <td><input type="date" name="assessment_dates[]" class="form-control form-control-sm"></td>
            <td><input type="text" name="supporting_docs[]" class="form-control form-control-sm"></td>
            <td>
                <button type="button" class="btn btn-sm btn-danger remove-row">
                    <i class="fas fa-trash"></i>
                </button>
            </td>
        `;
        tbody.appendChild(newRow);
        attachRemoveListener(newRow.querySelector('.remove-row'));
    });

    // Remove row listeners
    document.querySelectorAll('.remove-row').forEach(btn => {
        attachRemoveListener(btn);
    });

    function attachRemoveListener(btn) {
        btn.addEventListener('click', function(e) {
            e.preventDefault();
            const rows = document.querySelectorAll('.assessment-row');
            if (rows.length > 1) {
                this.closest('tr').remove();
            } else {
                alert('At least one assessment service is required');
            }
        });
    }

    // Form submission
    document.getElementById('assessmentForm').addEventListener('submit', function(e) {
        e.preventDefault();

        const formData = new FormData(this);

        fetch('<?php echo BASE_PATH; ?>/assessment/submit', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                showAlert('Assessment submitted successfully!', 'success');
                setTimeout(() => {
                    window.location.href = '<?php echo BASE_PATH; ?>/dashboard';
                }, 2000);
            } else {
                showAlert(data.message || 'Error submitting assessment', 'danger');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            showAlert('Error submitting assessment', 'danger');
        });
    });

    function showAlert(message, type) {
        const alertContainer = document.getElementById('alertContainer');
        const alertDiv = document.createElement('div');
        alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
        alertDiv.innerHTML = `
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        alertContainer.appendChild(alertDiv);
    }
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

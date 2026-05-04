<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-02
// Part of: SPED LMS — Enrollment Form (7 Steps)

$pageTitle = 'Enrollment Form - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Prepare data for form (draft, previous enrollment, or empty)
$formData = [];
if (isset($draft) && $draft) {
    $formData = $draft;
} elseif (isset($previousEnrollment) && $previousEnrollment && $enrollmentType === 'returning') {
    $formData = $previousEnrollment;
}

// Helper function to get form value
function getFormValue($field, $default = '') {
    global $formData;
    return $formData[$field] ?? $_SESSION['old_data'][$field] ?? $default;
}

// Helper function for checkboxes
function isChecked($field) {
    global $formData;
    return !empty($formData[$field]) || !empty($_SESSION['old_data'][$field]);
}
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-clipboard-heart text-primary"></i> 
        <?php 
        $typeLabels = ['new' => 'New Student', 'transfer' => 'Transfer Student', 'returning' => 'Returning Student'];
        echo $typeLabels[$enrollmentType] ?? 'Enrollment';
        ?> Enrollment Form
    </h1>

    <!-- Progress Bar -->
    <div class="progress mb-4" style="height: 30px;">
        <div class="progress-bar bg-primary" role="progressbar" style="width: 14.3%">
            Step 1 of 7
        </div>
    </div>

    <!-- Error Messages -->
    <?php if (isset($_SESSION['errors'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <h5><i class="bi bi-exclamation-triangle"></i> Please fix the following errors:</h5>
            <ul class="mb-0">
                <?php foreach ($_SESSION['errors'] as $error): ?>
                    <li><?php echo htmlspecialchars($error); ?></li>
                <?php endforeach; ?>
            </ul>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['errors']); ?>
    <?php endif; ?>

    <form id="enrollmentForm" method="POST" action="<?php echo $basePath; ?>/enrollment/submit" enctype="multipart/form-data">
        <input type="hidden" name="enrollment_type" value="<?php echo htmlspecialchars($enrollmentType); ?>">
        <input type="hidden" name="school_year" value="<?php echo date('Y') . '-' . (date('Y') + 1); ?>">
        <?php if (isset($previousEnrollment) && $enrollmentType === 'returning'): ?>
            <input type="hidden" name="previous_enrollment_id" value="<?php echo $previousEnrollment['id']; ?>">
        <?php endif; ?>

        <?php 
        // Include all 7 form steps
        require_once __DIR__ . '/steps/step1_learner_info.php';
        require_once __DIR__ . '/steps/step2_current_address.php';
        require_once __DIR__ . '/steps/step3_parent_guardian.php';
        require_once __DIR__ . '/steps/step4_previous_school.php';
        require_once __DIR__ . '/steps/step5_enrollment_details.php';
        require_once __DIR__ . '/steps/step6_learning_modality.php';
        require_once __DIR__ . '/steps/step7_documents_signature.php';
        ?>

        <!-- Navigation Buttons -->
        <div class="d-flex justify-content-between mt-4">
            <button type="button" id="prevBtn" class="btn btn-secondary" onclick="prevStep()">
                <i class="bi bi-arrow-left"></i> Previous
            </button>
            <button type="button" class="btn btn-outline-primary" onclick="manualSave()">
                <i class="bi bi-save"></i> Save Draft
            </button>
            <button type="button" id="nextBtn" class="btn btn-primary" onclick="nextStep()">
                Next <i class="bi bi-arrow-right"></i>
            </button>
            <button type="submit" id="submitBtn" class="btn btn-success" style="display: none;">
                <i class="bi bi-check-circle"></i> Submit Enrollment
            </button>
        </div>
    </form>
</div>

<!-- Signature Pad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<!-- Enrollment Utilities -->
<script src="<?php echo $basePath; ?>/js/enrollment.js"></script>

<script>
// Override getBasePath for this form
function getBasePath() {
    return '<?php echo $basePath; ?>';
}

// Add signature data to form before submit
document.getElementById('enrollmentForm').addEventListener('submit', function(e) {
    const signatureData = getSignatureData();
    if (!signatureData) {
        e.preventDefault();
        alert('Please provide your signature before submitting.');
        showStep(7); // Go to signature step
        return false;
    }
    
    // Show confirmation with debug info
    e.preventDefault();
    
    const form = this;
    const formData = new FormData(form);
    const data = Object.fromEntries(formData);
    
    // Build confirmation message
    let confirmMsg = '📋 Please review the following information before submitting:\n\n';
    confirmMsg += `👤 Name: ${data.first_name || ''} ${data.middle_name || ''} ${data.last_name || ''}\n`;
    confirmMsg += `📅 Birth Date: ${data.birth_date || 'Not set'}\n`;
    confirmMsg += `🏠 Birth Place: ${data.birth_place || 'Not set'}\n`;
    confirmMsg += `👨‍👩‍👧 Grade Level: ${data.grade_level_to_enroll || 'Not set'}\n`;
    confirmMsg += `🌍 City: ${data.current_city || 'Not set'}\n`;
    confirmMsg += `📍 Province: ${data.current_province || 'Not set'}\n\n`;
    confirmMsg += '⚠️  Once submitted, your enrollment will be sent for review.\n\n';
    confirmMsg += 'Do you want to continue with the submission?\n\n';
    confirmMsg += '(If something is wrong, click "Cancel" and review the form)';
    
    if (confirm(confirmMsg)) {
        // User confirmed - add signature and submit
        document.getElementById('signature_data').value = signatureData;
        form.submit();
    } else {
        // User cancelled - show debug page option
        const showDebug = confirm('Would you like to view submission debug information?\n\nThis will help troubleshoot any issues.');
        if (showDebug) {
            window.location.href = getBasePath() + '/debug-enrollment-submit.php';
        }
    }
});
</script>

<?php 
unset($_SESSION['old_data']);
require_once __DIR__ . '/../layouts/footer.php'; 
?>

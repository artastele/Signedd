<?php
// Focused mini-form for collaborative edit of itp_team_members row
// Process 11: Feature 2

$pageTitle = 'Fill Transition Team Details - SignED';
require_once __DIR__ . '/../layouts/header.php';
$basePath = BASE_PATH;
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container py-5" style="max-width: 700px;">
        <!-- Header -->
        <div class="mb-4">
            <h1 class="mb-1 h3" style="color:#1e4072; font-weight:700;">
                <i class="bi bi-pencil-square me-2"></i>Update Team Member Details
            </h1>
            <p class="text-muted">Fill in your information for the learner's Transition Team.</p>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <!-- Form Card -->
        <div class="card shadow-sm border-0 overflow-hidden mb-4" style="border-radius: 12px;">
            <div class="card-header text-white py-3" style="background: linear-gradient(135deg, #1e4072 0%, #2a528f 100%);">
                <h5 class="mb-0 font-weight-bold">
                    <i class="bi bi-person-badge-fill me-2"></i>
                    Role: <?= htmlspecialchars(ucwords(str_replace('_', ' ', $member['role']))) ?>
                </h5>
            </div>
            <div class="card-body p-4 bg-white">
                <div class="mb-4 p-3 border rounded bg-light" style="border-color: #cbd5e1; border-left: 4px solid #a01422;">
                    <span class="font-weight-bold text-dark d-block">Learner Information</span>
                    <span class="text-muted small">
                        <strong>Student:</strong> <?= htmlspecialchars($iep['student_name'] ?? 'N/A') ?><br>
                        <strong>LRN:</strong> <?= htmlspecialchars($iep['lrn'] ?? 'N/A') ?>
                    </span>
                </div>

                <form method="post" action="<?= $basePath ?>/itp-team/save/<?= intval($member['id']) ?>">
                    <div class="mb-3">
                        <label for="name" class="form-label small font-weight-bold text-muted text-uppercase">Full Name / Representative</label>
                        <input type="text" id="name" name="name" class="form-control form-control-lg border-2" style="border-radius: 8px;" 
                               value="<?= htmlspecialchars($member['name'] ?? '') ?>" required>
                    </div>

                    <div class="mb-3">
                        <label for="contact_details" class="form-label small font-weight-bold text-muted text-uppercase">Contact Details (Email / Phone)</label>
                        <input type="text" id="contact_details" name="contact_details" class="form-control form-control-lg border-2" style="border-radius: 8px;" 
                               value="<?= htmlspecialchars($member['contact_details'] ?? '') ?>" required>
                    </div>

                    <div class="mb-4">
                        <label for="date_started" class="form-label small font-weight-bold text-muted text-uppercase">Date Started Working with Learner</label>
                        <input type="date" id="date_started" name="date_started" class="form-control form-control-lg border-2" style="border-radius: 8px;" 
                               value="<?= htmlspecialchars($member['date_started'] ?? '') ?>">
                    </div>

                    <div class="d-flex justify-content-between align-items-center bg-light p-3 border rounded gap-2">
                        <a href="<?= $basePath ?>/iep/<?= intval($iepId) ?>/individual-transition-plan" class="btn btn-outline-secondary px-4">
                            Cancel
                        </a>
                        <button type="submit" class="btn text-white px-5" style="background-color: #a01422; font-weight: 600; border-radius: 8px;">
                            <i class="bi bi-save me-2"></i>Save Details
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

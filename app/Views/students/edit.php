<?php
$pageTitle = 'Edit Student - SignED';
require_once __DIR__ . '/../../Helpers/CSRFHelper.php';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <h1 class="mb-0">
            <i class="bi bi-pencil-square text-primary"></i> Edit Student Record
        </h1>
        <a href="<?php echo $basePath; ?>/students/view/<?php echo (int)$student['id']; ?>" class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back to Student
        </a>
    </div>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <div class="card shadow">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><?php echo htmlspecialchars($student['student_name']); ?></h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo $basePath; ?>/students/edit/<?php echo (int)$student['id']; ?>">
                <input type="hidden" name="_csrf_token" value="<?php echo htmlspecialchars(CSRFHelper::getToken()); ?>">

                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="student_id_display" class="form-label">Student ID</label>
                        <input type="text" class="form-control" id="student_id_display"
                               value="<?php echo htmlspecialchars(StudentDisplayHelper::formatStudentId($student['student_id'] ?? null)); ?>"
                               readonly>
                    </div>
                    <div class="col-md-6">
                        <label for="lrn" class="form-label">DepEd LRN (optional — assigned by DepEd LIS)</label>
                        <input type="text" class="form-control" id="lrn" name="lrn" maxlength="12"
                               value="<?php echo htmlspecialchars(StudentDisplayHelper::lrnFieldValue($student['lrn'] ?? null)); ?>"
                               placeholder="12-digit LRN">
                        <div class="form-text">Leave blank if not yet assigned by DepEd.</div>
                    </div>
                </div>

                <div class="mt-4 d-flex gap-2">
                    <button type="submit" class="btn btn-primary">
                        <i class="bi bi-check-lg"></i> Save Changes
                    </button>
                    <a href="<?php echo $basePath; ?>/students/view/<?php echo (int)$student['id']; ?>" class="btn btn-outline-secondary">Cancel</a>
                </div>
            </form>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
$pageTitle = 'Attendance Log - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <h2 class="mb-1 fw-bold" style="color:#1e4072;">
                    <i class="bi bi-calendar-check me-2"></i>Attendance Log
                </h2>
                <div class="text-muted">SF2 attendance sheets by learner</div>
            </div>
            <a href="<?php echo $basePath; ?>/progress-reports" class="btn btn-outline-secondary">
                <i class="bi bi-bar-chart-line me-1"></i> Grades
            </a>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success alert-dismissible fade show py-2" role="alert">
                <?php echo htmlspecialchars($success); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-dismissible fade show py-2" role="alert">
                <?php echo htmlspecialchars($error); ?>
                <button type="button" class="btn-close py-2" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Learner</th>
                                <th>LRN</th>
                                <th>Grade Level</th>
                                <th>School Year</th>
                                <th class="text-center">Entries</th>
                                <th class="text-center">Present</th>
                                <th>Last Update</th>
                                <th class="text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($learners)): ?>
                                <tr>
                                    <td colspan="8" class="text-center text-muted py-5">
                                        No learners found.
                                    </td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($learners as $learner): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($learner['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars($learner['lrn']); ?></td>
                                        <td><?php echo htmlspecialchars($learner['grade_level_to_enroll'] ?? 'N/A'); ?></td>
                                        <td><?php echo htmlspecialchars($learner['school_year'] ?? 'N/A'); ?></td>
                                        <td class="text-center"><?php echo (int)($learner['attendance_entries'] ?? 0); ?></td>
                                        <td class="text-center"><?php echo (int)($learner['present_entries'] ?? 0); ?></td>
                                        <td>
                                            <?php echo !empty($learner['last_attendance_at']) ? date('M d, Y', strtotime($learner['last_attendance_at'])) : 'N/A'; ?>
                                        </td>
                                        <td class="text-end">
                                            <a href="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$learner['student_id']; ?>/attendance" class="btn btn-sm btn-primary" style="background-color:#1e4072; border-color:#1e4072;">
                                                <i class="bi bi-box-arrow-up-right me-1"></i> Open SF2
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
$pageTitle = 'Grades - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>
<div class="main-content">
    <div class="container-fluid py-3">
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h4 class="mb-1" style="color:#1e4072;"><?php echo ($role ?? '') === 'parent' ? 'Progress Reports' : 'Grades'; ?></h4>
                <p class="text-muted mb-0"><?php echo ($role ?? '') === 'parent' ? "View your child's quarterly progress reports and sign off on remarks." : 'Manage student grades in a dedicated process page.'; ?></p>
            </div>
        </div>

        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <div class="card">
            <div class="card-body">
                <?php if (empty($reports)): ?>
                    <div class="alert alert-info">No students with IEP records found.</div>
                <?php else: ?>
                    <?php
                    require_once __DIR__ . '/../../Models/StudentModel.php';
                    $gradesListStudentModel = new StudentModel();
                    $gradesListCodeCache = [];
                    ?>
                    <div class="table-responsive">
                        <table class="table table-hover align-middle">
                            <thead>
                                <tr>
                                    <th>Student</th>
                                    <th>Student ID</th>
                                    <th>DepEd LRN</th>
                                    <th>IEP ID</th>
                                    <th>School Year</th>
                                    <th>Quarter</th>
                                    <th>Status</th>
                                    <th>Last Updated</th>
                                    <th class="text-end">Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($reports as $report): ?>
                                    <?php
                                    $reportFk = (int)($report['student_id'] ?? 0);
                                    if ($reportFk && !isset($gradesListCodeCache[$reportFk])) {
                                        $reportRec = $gradesListStudentModel->findById($reportFk);
                                        $gradesListCodeCache[$reportFk] = $reportRec['student_id'] ?? null;
                                    }
                                    ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo htmlspecialchars($report['student_name']); ?></td>
                                        <td><?php echo htmlspecialchars(StudentDisplayHelper::formatStudentId($gradesListCodeCache[$reportFk] ?? null)); ?></td>
                                        <td><?php echo htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($report['lrn'] ?? null)); ?></td>
                                        <td><span class="badge bg-secondary bg-opacity-10 text-secondary">IEP #<?php echo htmlspecialchars($report['iep_id']); ?></span></td>
                                        <td><?php echo htmlspecialchars($report['school_year'] ?? '—'); ?></td>
                                        <td><?php echo htmlspecialchars($report['quarter'] ?? '—'); ?></td>
                                        <td>
                                            <?php if (empty($report['status'])): ?>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">Not Started</span>
                                            <?php else: ?>
                                                <?php
                                                $badgeClass = 'bg-primary';
                                                if ($report['status'] === 'finalized') {
                                                    $badgeClass = 'bg-success bg-opacity-10 text-success border border-success';
                                                } else {
                                                    $badgeClass = 'bg-info bg-opacity-10 text-info border border-info';
                                                }
                                                ?>
                                                <span class="badge <?php echo $badgeClass; ?>">
                                                    <?php echo htmlspecialchars(ucwords(str_replace('_', ' ', $report['status']))); ?>
                                                </span>
                                            <?php endif; ?>
                                        </td>
                                        <td class="small text-muted">
                                            <?php echo !empty($report['updated_at']) ? htmlspecialchars(date('M d, Y g:i A', strtotime($report['updated_at']))) : '—'; ?>
                                        </td>
                                        <td class="text-end">
                                            <?php if (($role ?? '') === 'parent'): ?>
                                                <a href="<?php echo $basePath; ?>/progress-reports/<?php echo (int) $report['student_id']; ?>" class="btn btn-sm btn-success">
                                                    <i class="bi bi-file-earmark-check me-1"></i> View SF9 &amp; Sign
                                                </a>
                                            <?php else: ?>
                                                <a href="<?php echo $basePath; ?>/progress-reports/<?php echo (int) $report['student_id']; ?>" class="btn btn-sm btn-primary">
                                                    <i class="bi bi-pencil-square me-1"></i> Manage Grades &amp; Attendance
                                                </a>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

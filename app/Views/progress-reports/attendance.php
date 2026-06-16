<?php
$pageTitle = 'Attendance Sheet - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-3">
        <!-- Student Info Header -->
        <div class="d-flex align-items-center justify-content-between mb-3 flex-wrap gap-2">
            <div>
                <h4 class="mb-1" style="color:#1e4072;">Attendance Sheet for <?php echo htmlspecialchars($student['student_name']); ?></h4>
                <div class="text-muted">LRN: <?php echo htmlspecialchars($student['lrn']); ?> | IEP ID: <?php echo htmlspecialchars($iep['id']); ?></div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo $basePath; ?>/progress-reports" class="btn btn-sm btn-outline-secondary">Back to List</a>
            </div>
        </div>

        <!-- Alerts Block -->
        <?php if (!empty($success)): ?>
            <div class="alert alert-success py-2"><?php echo htmlspecialchars($success); ?></div>
        <?php endif; ?>
        <?php if (!empty($error)): ?>
            <div class="alert alert-danger py-2"><?php echo htmlspecialchars($error); ?></div>
        <?php endif; ?>

        <!-- Tabs for Grades and Attendance -->
        <ul class="nav nav-tabs mb-4" id="gradesTab" role="tablist">
            <li class="nav-item" role="presentation">
                <a href="<?php echo $basePath; ?>/progress-reports/<?php echo (int) $student['id']; ?>" class="nav-link" id="report-tab" type="button" role="tab">
                    <i class="bi bi-file-earmark-bar-graph"></i> Grades & Progress Report
                </a>
            </li>
            <li class="nav-item" role="presentation">
                <a href="<?php echo $basePath; ?>/progress-reports/<?php echo (int) $student['id']; ?>/attendance" class="nav-link active" id="attendance-tab" type="button" role="tab">
                    <i class="bi bi-calendar-check"></i> Attendance Sheet
                </a>
            </li>
        </ul>

        <!-- Attendance Stats row -->
        <div class="row g-3 mb-4">
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #0d6efd !important;">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase mb-1">Total Days</h6>
                                <h3 class="mb-0 fw-bold"><?php echo $stats['total']; ?></h3>
                            </div>
                            <div class="bg-primary bg-opacity-10 text-primary rounded-circle p-2">
                                <i class="bi bi-calendar-range" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #198754 !important;">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase mb-1">Present</h6>
                                <h3 class="mb-0 fw-bold text-success"><?php echo $stats['present']; ?></h3>
                            </div>
                            <div class="bg-success bg-opacity-10 text-success rounded-circle p-2">
                                <i class="bi bi-check-circle" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #fd7e14 !important;">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase mb-1">Absent / Tardy</h6>
                                <h3 class="mb-0 fw-bold text-warning"><?php echo $stats['absent'] + $stats['tardy']; ?></h3>
                            </div>
                            <div class="bg-warning bg-opacity-10 text-warning rounded-circle p-2">
                                <i class="bi bi-exclamation-triangle" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="col-6 col-md-3">
                <div class="card h-100 border-0 shadow-sm" style="border-left: 4px solid #0dcaf0 !important;">
                    <div class="card-body py-3">
                        <div class="d-flex align-items-center justify-content-between">
                            <div>
                                <h6 class="text-muted small text-uppercase mb-1">Attendance Rate</h6>
                                <h3 class="mb-0 fw-bold text-info"><?php echo $stats['rate']; ?>%</h3>
                            </div>
                            <div class="bg-info bg-opacity-10 text-info rounded-circle p-2">
                                <i class="bi bi-percent" style="font-size: 1.5rem;"></i>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <div class="row g-4">
            <!-- Logging Form -->
            <div class="col-lg-4">
                <div class="card shadow-sm border-0">
                    <div class="card-header bg-white py-3 border-0">
                        <h5 class="card-title mb-0 fw-bold text-primary">Log Attendance</h5>
                    </div>
                    <div class="card-body pt-0">
                        <?php if ($canEdit): ?>
                            <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int) $student['id']; ?>/attendance">
                                <div class="mb-3">
                                    <label for="attendance_date" class="form-label small fw-bold">Date</label>
                                    <input type="date" id="attendance_date" name="attendance_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label small fw-bold d-block">Status</label>
                                    <div class="d-flex flex-column gap-2">
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="status_present" value="present" checked required>
                                            <label class="form-check-label text-success fw-semibold" for="status_present">
                                                <i class="bi bi-check-circle-fill me-1"></i> Present
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="status_absent" value="absent" required>
                                            <label class="form-check-label text-danger fw-semibold" for="status_absent">
                                                <i class="bi bi-x-circle-fill me-1"></i> Absent
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="status_tardy" value="tardy" required>
                                            <label class="form-check-label text-warning fw-semibold" for="status_tardy">
                                                <i class="bi bi-exclamation-circle-fill me-1"></i> Tardy
                                            </label>
                                        </div>
                                        <div class="form-check form-check-inline">
                                            <input class="form-check-input" type="radio" name="status" id="status_excused" value="excused" required>
                                            <label class="form-check-label text-info fw-semibold" for="status_excused">
                                                <i class="bi bi-question-circle-fill me-1"></i> Excused
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="mb-3">
                                    <label for="remarks" class="form-label small fw-bold">Remarks (Optional)</label>
                                    <textarea id="remarks" name="remarks" class="form-control" rows="3" placeholder="e.g. Late due to traffic, medical excuse, etc."></textarea>
                                </div>
                                <button type="submit" class="btn btn-primary w-100">
                                    <i class="bi bi-save me-1"></i> Save Entry
                                </button>
                            </form>
                        <?php else: ?>
                            <div class="alert alert-secondary py-3 small mb-0">
                                <i class="bi bi-lock-fill me-1"></i> You have read-only access to this attendance sheet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- History Table -->
            <div class="col-lg-8">
                <div class="card shadow-sm border-0 h-100">
                    <div class="card-header bg-white py-3 border-0 d-flex justify-content-between align-items-center">
                        <h5 class="card-title mb-0 fw-bold text-primary">Attendance History</h5>
                    </div>
                    <div class="card-body pt-0">
                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead class="table-light">
                                    <tr>
                                        <th>Date</th>
                                        <th>Status</th>
                                        <th>Remarks</th>
                                        <th>Logged By</th>
                                        <?php if ($canEdit): ?>
                                            <th class="text-end">Actions</th>
                                        <?php endif; ?>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php if (empty($attendanceRecords)): ?>
                                        <tr>
                                            <td colspan="<?php echo $canEdit ? 5 : 4; ?>" class="text-center text-muted py-4">
                                                <i class="bi bi-calendar-x d-block mb-2 text-secondary" style="font-size: 2rem;"></i>
                                                No attendance logs found for this student.
                                            </td>
                                        </tr>
                                    <?php else: ?>
                                        <?php foreach ($attendanceRecords as $record): ?>
                                            <tr>
                                                <td class="fw-semibold">
                                                    <?php echo date('M d, Y', strtotime($record['attendance_date'])); ?>
                                                </td>
                                                <td>
                                                    <?php
                                                    $badgeClass = 'bg-secondary';
                                                    $statusText = ucfirst($record['status']);
                                                    switch ($record['status']) {
                                                        case 'present':
                                                            $badgeClass = 'bg-success bg-opacity-10 text-success border border-success';
                                                            break;
                                                        case 'absent':
                                                            $badgeClass = 'bg-danger bg-opacity-10 text-danger border border-danger';
                                                            break;
                                                        case 'tardy':
                                                            $badgeClass = 'bg-warning bg-opacity-10 text-warning border border-warning';
                                                            break;
                                                        case 'excused':
                                                            $badgeClass = 'bg-info bg-opacity-10 text-info border border-info';
                                                            break;
                                                    }
                                                    ?>
                                                    <span class="badge <?php echo $badgeClass; ?> px-2 py-1.5 rounded">
                                                        <?php echo $statusText; ?>
                                                    </span>
                                                </td>
                                                <td class="text-muted small">
                                                    <?php echo htmlspecialchars($record['remarks'] ?? '—'); ?>
                                                </td>
                                                <td class="small">
                                                    <?php echo htmlspecialchars($record['logger_name'] ?? 'System'); ?>
                                                </td>
                                                <?php if ($canEdit): ?>
                                                    <td class="text-end">
                                                        <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int) $student['id']; ?>/attendance/delete/<?php echo (int) $record['id']; ?>" onsubmit="return confirm('Are you sure you want to delete this attendance entry?');" class="d-inline m-0">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger border-0" title="Delete entry">
                                                                <i class="bi bi-trash"></i>
                                                            </button>
                                                        </form>
                                                    </td>
                                                <?php endif; ?>
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
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
$pageTitle = 'SF2 Attendance Sheet - SignED';
require_once __DIR__ . '/../layouts/header.php';

$daysInMonth = (int)date('t', strtotime($yearMonth . '-01'));
$monthLabel = date('F Y', strtotime($yearMonth . '-01'));
$recordsByDay = [];
foreach ($attendanceRecords as $record) {
    $day = (int)date('j', strtotime($record['date']));
    $recordsByDay[$day] = $record;
}

$statusMarks = [
    'present' => 'P',
    'absent' => 'A',
    'tardy' => 'T',
    'excused' => 'E'
];
$statusClasses = [
    'present' => 'text-success',
    'absent' => 'text-danger',
    'tardy' => 'text-warning',
    'excused' => 'text-info'
];
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid py-4">
        <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
            <div>
                <nav aria-label="breadcrumb">
                    <ol class="breadcrumb mb-1">
                        <li class="breadcrumb-item"><a href="<?php echo $basePath; ?>/attendance-log" style="color:#1e4072;">Attendance Log</a></li>
                        <li class="breadcrumb-item active" aria-current="page"><?php echo htmlspecialchars($student['student_name']); ?></li>
                    </ol>
                </nav>
                <h2 class="mb-0 fw-bold" style="color:#1e4072;">
                    <i class="bi bi-table me-2"></i>SF2 Attendance Sheet
                </h2>
                <div class="text-muted mt-1">
                    <?php echo htmlspecialchars($student['student_name']); ?> | LRN: <?php echo htmlspecialchars($student['lrn']); ?> | IEP ID: <?php echo (int)$iep['id']; ?>
                </div>
            </div>
            <div class="d-flex gap-2 flex-wrap">
                <a href="<?php echo $basePath; ?>/attendance-log" class="btn btn-outline-secondary">
                    <i class="bi bi-arrow-left me-1"></i> Back to Attendance Log
                </a>
                <a href="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>" class="btn btn-outline-primary">
                    <i class="bi bi-file-earmark-bar-graph me-1"></i> SF9
                </a>
            </div>
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

        <div class="card border-0 shadow-sm mb-4">
            <div class="card-body p-3 d-flex align-items-center justify-content-between flex-wrap gap-3">
                <form method="GET" class="d-flex align-items-end gap-2 flex-wrap">
                    <div>
                        <label class="form-label small fw-bold mb-1" for="month">Month</label>
                        <input type="month" id="month" name="month" class="form-control form-control-sm" value="<?php echo htmlspecialchars($yearMonth); ?>">
                    </div>
                    <button type="submit" class="btn btn-sm btn-primary" style="background-color:#1e4072; border-color:#1e4072;">
                        <i class="bi bi-funnel me-1"></i> View
                    </button>
                </form>

                <div class="d-flex gap-3 flex-wrap small">
                    <span><strong>Total:</strong> <?php echo (int)$stats['total']; ?></span>
                    <span class="text-success"><strong>Present:</strong> <?php echo (int)$stats['present']; ?></span>
                    <span class="text-danger"><strong>Absent:</strong> <?php echo (int)$stats['absent']; ?></span>
                    <span class="text-warning"><strong>Tardy:</strong> <?php echo (int)$stats['tardy']; ?></span>
                    <span class="text-info"><strong>Excused:</strong> <?php echo (int)$stats['excused']; ?></span>
                    <span><strong>Rate:</strong> <?php echo htmlspecialchars((string)$stats['rate']); ?>%</span>
                </div>
            </div>
        </div>

        <?php if ($canEdit): ?>
            <div class="row g-4 mb-4">
                <div class="col-lg-5">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold" style="color:#1e4072;">Manual Entry</h5>
                        </div>
                        <div class="card-body pt-0">
                            <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>/attendance">
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold" for="attendance_date">Date</label>
                                        <input type="date" id="attendance_date" name="attendance_date" class="form-control" value="<?php echo date('Y-m-d'); ?>" required>
                                    </div>
                                    <div class="col-md-6">
                                        <label class="form-label small fw-bold" for="status">Status</label>
                                        <select id="status" name="status" class="form-select">
                                            <option value="present">Present</option>
                                            <option value="absent">Absent</option>
                                            <option value="tardy">Tardy</option>
                                            <option value="excused">Excused</option>
                                        </select>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn btn-primary w-100" style="background-color:#1e4072; border-color:#1e4072;">
                                            <i class="bi bi-save me-1"></i> Save Attendance
                                        </button>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-lg-7">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-header bg-white border-0 py-3">
                            <h5 class="mb-0 fw-bold" style="color:#1e4072;">Import LIS SF2</h5>
                        </div>
                        <div class="card-body pt-0">
                            <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>/attendance/import" enctype="multipart/form-data">
                                <div class="row g-3 align-items-end">
                                    <div class="col-md-4">
                                        <label class="form-label small fw-bold" for="sf2_month">SF2 Month</label>
                                        <input type="month" id="sf2_month" name="sf2_month" class="form-control" value="<?php echo htmlspecialchars($yearMonth); ?>" required>
                                    </div>
                                    <div class="col-md-5">
                                        <label class="form-label small fw-bold" for="sf2_file">SF2 CSV File</label>
                                        <input type="file" id="sf2_file" name="sf2_file" class="form-control" accept=".csv,text/csv" required>
                                    </div>
                                    <div class="col-md-3">
                                        <button type="submit" class="btn btn-success w-100">
                                            <i class="bi bi-upload me-1"></i> Import
                                        </button>
                                    </div>
                                    <div class="col-12">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" value="1" id="blank_as_present" name="blank_as_present">
                                            <label class="form-check-label small" for="blank_as_present">
                                                Treat blank day cells as Present
                                            </label>
                                        </div>
                                        <div class="text-muted small mt-2">
                                            Accepted day marks: P/Present, A/Absent/X, T/Tardy, E/Excused. CSV row matching uses LRN first, then learner name.
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 py-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold" style="color:#1e4072;">Daily Attendance for <?php echo htmlspecialchars($monthLabel); ?></h5>
                    <div class="text-muted small">SF2 learner row view</div>
                </div>
                <div class="d-flex gap-3 small">
                    <span class="text-success fw-semibold">P Present</span>
                    <span class="text-danger fw-semibold">A Absent</span>
                    <span class="text-warning fw-semibold">T Tardy</span>
                    <span class="text-info fw-semibold">E Excused</span>
                </div>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-bordered align-middle text-center mb-0 sf2-table">
                        <thead class="table-light">
                            <tr>
                                <th class="text-start learner-col">Learner</th>
                                <?php for ($day = 1; $day <= 31; $day++): ?>
                                    <th class="day-col <?php echo $day > $daysInMonth ? 'text-muted bg-light' : ''; ?>"><?php echo $day; ?></th>
                                <?php endfor; ?>
                                <th>P</th>
                                <th>A</th>
                                <th>T</th>
                                <th>E</th>
                            </tr>
                        </thead>
                        <tbody>
                            <tr>
                                <td class="text-start">
                                    <div class="fw-semibold"><?php echo htmlspecialchars($student['student_name']); ?></div>
                                    <div class="text-muted small"><?php echo htmlspecialchars($student['lrn']); ?></div>
                                </td>
                                <?php for ($day = 1; $day <= 31; $day++): ?>
                                    <?php $record = $recordsByDay[$day] ?? null; ?>
                                    <td class="<?php echo $day > $daysInMonth ? 'bg-light' : ''; ?>">
                                        <?php if ($day <= $daysInMonth && $record): ?>
                                            <span class="fw-bold <?php echo $statusClasses[$record['status']] ?? ''; ?>">
                                                <?php echo $statusMarks[$record['status']] ?? ''; ?>
                                            </span>
                                        <?php endif; ?>
                                    </td>
                                <?php endfor; ?>
                                <td class="fw-bold text-success"><?php echo (int)$stats['present']; ?></td>
                                <td class="fw-bold text-danger"><?php echo (int)$stats['absent']; ?></td>
                                <td class="fw-bold text-warning"><?php echo (int)$stats['tardy']; ?></td>
                                <td class="fw-bold text-info"><?php echo (int)$stats['excused']; ?></td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="card border-0 shadow-sm mt-4">
            <div class="card-header bg-white border-0 py-3">
                <h5 class="mb-0 fw-bold" style="color:#1e4072;">Attendance History</h5>
            </div>
            <div class="card-body pt-0">
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead class="table-light">
                            <tr>
                                <th>Date</th>
                                <th>Status</th>
                                <th>Source</th>
                                <th>Logged By</th>
                                <?php if ($canEdit): ?><th class="text-end">Action</th><?php endif; ?>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (empty($allAttendanceRecords)): ?>
                                <tr>
                                    <td colspan="<?php echo $canEdit ? 5 : 4; ?>" class="text-center text-muted py-4">No attendance records yet.</td>
                                </tr>
                            <?php else: ?>
                                <?php foreach ($allAttendanceRecords as $record): ?>
                                    <tr>
                                        <td class="fw-semibold"><?php echo date('M d, Y', strtotime($record['date'])); ?></td>
                                        <td>
                                            <span class="badge bg-light border <?php echo $statusClasses[$record['status']] ?? 'text-secondary'; ?>">
                                                <?php echo ucfirst($record['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($record['source']))); ?></td>
                                        <td><?php echo htmlspecialchars($record['logger_name'] ?? 'System'); ?></td>
                                        <?php if ($canEdit): ?>
                                            <td class="text-end">
                                                <form method="POST" action="<?php echo $basePath; ?>/progress-reports/<?php echo (int)$student['id']; ?>/attendance/delete/<?php echo (int)$record['id']; ?>?month=<?php echo urlencode($yearMonth); ?>" onsubmit="return confirm('Are you sure you want to delete this attendance entry?');" class="d-inline m-0">
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

<style>
.sf2-table {
    min-width: 1180px;
}
.sf2-table .learner-col {
    min-width: 220px;
}
.sf2-table .day-col,
.sf2-table td:not(:first-child) {
    width: 34px;
    min-width: 34px;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-04
// Part of: SignED — Assessment Dashboard (SPED Teacher Review)

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12 d-flex justify-content-between align-items-center">
                <div>
                    <h1 class="h3 mb-0" style="color: #a01422;">
                        <i class="fas fa-clipboard-list"></i> Assessment History
                    </h1>
                    <p class="text-muted mt-2">View all submitted and draft assessments</p>
                </div>
                <div>
                    <a href="<?php echo BASE_PATH; ?>/assessment/conduct" class="btn btn-primary" style="background-color: #a01422; border-color: #a01422;">
                        <i class="fas fa-plus"></i> Conduct New Assessment
                    </a>
                </div>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #3b6d11;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Finalized</h6>
                        <h3 style="color: #3b6d11;"><?php echo count($finalized); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Drafts</h6>
                        <h3 style="color: #ffc107;"><?php echo count($drafts); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #1e4072;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Assessments</h6>
                        <h3 style="color: #1e4072;"><?php echo count($allAssessments); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #a01422;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Students Assessed</h6>
                        <h3 style="color: #a01422;">
                            <?php echo count(array_unique(array_column($allAssessments, 'student_id'))); ?>
                        </h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Search and Filter -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by student name, Student ID, or DepEd LRN...">
                    </div>
                    <div class="col-md-3">
                        <select id="filterStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="finalized">Finalized</option>
                            <option value="draft">Draft</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="clearFilters()">
                            <i class="fas fa-redo"></i> Clear Filters
                        </button>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessments Table — Grouped by Student -->
        <div class="card shadow-sm border-0 mb-4">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-journal-text text-primary me-2"></i> Assessments by Student
                </h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0" id="assessmentsTable">
                    <thead class="table-light text-secondary" style="font-size: 0.82rem; text-transform: uppercase; letter-spacing: 0.5px;">
                        <tr>
                            <th class="ps-3 py-3">Student Name</th>
                            <th class="py-3">Student ID</th>
                            <th class="py-3">DepEd LRN</th>
                            <th class="py-3">Versions</th>
                            <th class="py-3">Latest Status</th>
                            <th class="py-3">Last Updated</th>
                            <th class="text-end pe-3 py-3">Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allAssessments)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-5 text-muted">
                                    <i class="bi bi-inbox fs-1 text-secondary mb-2 d-block"></i>
                                    <p class="mb-3">No assessments recorded yet</p>
                                    <a href="<?php echo BASE_PATH; ?>/assessment/conduct" class="btn btn-sm btn-primary px-3 py-2" style="border-radius: 6px; font-weight: 500;">
                                        <i class="bi bi-plus-lg me-1"></i> Conduct First Assessment
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php
                            require_once __DIR__ . '/../../Models/StudentModel.php';
                            $studentModelForCodes = new StudentModel();
                            $studentCodeCache = [];
                            // Group assessments by student
                            $byStudent = [];
                            foreach ($allAssessments as $a) {
                                $sid = $a['student_id'];
                                if (!isset($studentCodeCache[$sid])) {
                                    $rec = $studentModelForCodes->findById($sid);
                                    $studentCodeCache[$sid] = $rec['student_id'] ?? null;
                                }
                                if (!isset($byStudent[$sid])) {
                                    $byStudent[$sid] = [
                                        'student_name' => $a['student_name'],
                                        'lrn'          => $a['lrn'],
                                        'student_code' => $studentCodeCache[$sid],
                                        'record_id'    => $sid,
                                        'versions'     => []
                                    ];
                                }
                                $byStudent[$sid]['versions'][] = $a;
                            }
                            foreach ($byStudent as $sid => $student):
                                $latest = $student['versions'][0]; // already ordered DESC
                                $vCount = count($student['versions']);
                                $hasDraft = !empty(array_filter($student['versions'], fn($v) => $v['status'] === 'draft'));
                            ?>
                            <tr class="assessment-row"
                                data-status="<?php echo htmlspecialchars($latest['status']); ?>"
                                data-search="<?php echo strtolower($student['student_name'] . ' ' . ($student['student_code'] ?? '') . ' ' . ($student['lrn'] ?? '')); ?>">
                                <td class="ps-3">
                                    <span class="fw-semibold text-dark"><?php echo htmlspecialchars($student['student_name']); ?></span>
                                </td>
                                <td>
                                    <span class="badge bg-light text-dark border font-monospace px-2 py-1" style="font-size: 0.8rem; font-weight: 600;">
                                        <?php echo htmlspecialchars(StudentDisplayHelper::formatStudentId($student['student_code'] ?? null)); ?>
                                    </span>
                                </td>
                                <td>
                                    <span class="text-muted small"><?php echo htmlspecialchars(StudentDisplayHelper::formatDepEdLrn($student['lrn'] ?? null)); ?></span>
                                </td>
                                <td>
                                    <span class="badge rounded-pill bg-light text-secondary border px-2 py-1" style="font-size: 0.75rem; font-weight: 500;">
                                        <?php echo $vCount; ?> version<?php echo $vCount > 1 ? 's' : ''; ?>
                                    </span>
                                </td>
                                <td>
                                    <?php if ($latest['status'] === 'finalized'): ?>
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-check-circle me-1"></i>Finalized
                                        </span>
                                    <?php elseif ($latest['status'] === 'approved'): ?>
                                        <span class="badge rounded-pill bg-success-subtle text-success border border-success-subtle px-2 py-1" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-check-circle me-1"></i>Approved
                                        </span>
                                    <?php elseif ($latest['status'] === 'draft'): ?>
                                        <span class="badge rounded-pill bg-warning-subtle text-warning-emphasis border border-warning-subtle px-2 py-1" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-clock me-1"></i>Draft
                                        </span>
                                    <?php elseif ($latest['status'] === 'rejected'): ?>
                                        <span class="badge rounded-pill bg-danger-subtle text-danger border border-danger-subtle px-2 py-1" style="font-size: 0.75rem; font-weight: 600;">
                                            <i class="bi bi-x-circle me-1"></i>Rejected
                                        </span>
                                    <?php else: ?>
                                        <span class="badge rounded-pill bg-light text-secondary border px-2 py-1" style="font-size: 0.75rem; font-weight: 500;">
                                            <?php echo ucfirst($latest['status']); ?>
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="text-muted small"><?php echo date('M d, Y', strtotime($latest['created_at'])); ?></span>
                                </td>
                                <td class="text-end pe-3">
                                    <div class="d-inline-flex align-items-center gap-1">
                                        <?php if ($hasDraft): ?>
                                            <a href="<?php echo BASE_PATH; ?>/assessment/conduct/<?php echo $sid; ?>"
                                               class="btn btn-sm btn-outline-warning py-1 px-2" style="border-radius: 6px; font-size: 0.78rem; font-weight: 500;" title="Edit Draft Assessment">
                                                <i class="bi bi-pencil-square me-1"></i> Edit
                                            </a>
                                        <?php endif; ?>
                                        <a href="<?php echo BASE_PATH; ?>/assessment/view/<?php echo $latest['id']; ?>"
                                           class="btn btn-sm btn-outline-secondary py-1 px-2" style="border-radius: 6px; font-size: 0.78rem; font-weight: 500;" title="View Assessment">
                                            <i class="bi bi-eye me-1"></i> View
                                        </a>
                                        <a href="<?php echo BASE_PATH; ?>/assessment/history/<?php echo $sid; ?>"
                                           class="btn btn-sm btn-outline-secondary py-1 px-2" style="border-radius: 6px; font-size: 0.78rem; font-weight: 500;" title="Version History">
                                            <i class="bi bi-clock-history me-1"></i> History
                                        </a>
                                        <?php if (in_array($latest['status'], ['finalized', 'approved'])): ?>
                                            <a href="<?php echo BASE_PATH; ?>/iep/meetings/schedule?student_id=<?php echo $sid; ?>"
                                               class="btn btn-sm btn-success py-1 px-2" style="border-radius: 6px; font-size: 0.78rem; font-weight: 500;"
                                               title="Schedule IEP Meeting & Sign PDSP">
                                                <i class="bi bi-calendar-plus me-1"></i> Schedule IEP
                                            </a>
                                        <?php endif; ?>
                                    </div>
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterStatus = document.getElementById('filterStatus');
    const rows = document.querySelectorAll('.assessment-row');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const statusFilter = filterStatus.value;

        rows.forEach(row => {
            let show = true;

            // Search filter
            if (searchTerm && !row.dataset.search.includes(searchTerm)) {
                show = false;
            }

            // Status filter
            if (statusFilter && row.dataset.status !== statusFilter) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
        });
    }

    window.clearFilters = function() {
        searchInput.value = '';
        filterStatus.value = '';
        filterTable();
    };

    searchInput.addEventListener('keyup', filterTable);
    filterStatus.addEventListener('change', filterTable);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

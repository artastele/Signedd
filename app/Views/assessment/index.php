<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 3
// Last modified: 2026-05-04
// Part of: SPED LMS — Assessment Dashboard (SPED Teacher Review)

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
                    <i class="fas fa-tasks"></i> Review Assessments
                </h1>
                <p class="text-muted mt-2">Pending assessments for review and approval</p>
            </div>
        </div>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #a01422;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total Pending</h6>
                        <h3 style="color: #a01422;"><?php echo count($pendingAssessments); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #1e4072;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Q1 Assessments</h6>
                        <h3 style="color: #1e4072;">
                            <?php echo count(array_filter($pendingAssessments, fn($a) => $a['quarter'] === 'Q1')); ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #3b6d11;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Q2 Assessments</h6>
                        <h3 style="color: #3b6d11;">
                            <?php echo count(array_filter($pendingAssessments, fn($a) => $a['quarter'] === 'Q2')); ?>
                        </h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Awaiting Action</h6>
                        <h3 style="color: #ffc107;">
                            <?php echo count(array_filter($pendingAssessments, fn($a) => $a['status'] === 'pending')); ?>
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
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by student name or LRN...">
                    </div>
                    <div class="col-md-3">
                        <select id="filterQuarter" class="form-control">
                            <option value="">All Quarters</option>
                            <option value="Q1">Q1</option>
                            <option value="Q2">Q2</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select id="filterStatus" class="form-control">
                            <option value="">All Status</option>
                            <option value="pending">Pending</option>
                            <option value="approved">Approved</option>
                            <option value="rejected">Rejected</option>
                        </select>
                    </div>
                </div>
            </div>
        </div>

        <!-- Assessments Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="assessmentsTable">
                    <thead style="background-color: #1e4072; color: white;">
                        <tr>
                            <th>Student Name</th>
                            <th>LRN</th>
                            <th>Quarter</th>
                            <th>Submitted</th>
                            <th>Status</th>
                            <th>Parent</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($pendingAssessments)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> No pending assessments
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($pendingAssessments as $assessment): ?>
                                <tr class="assessment-row" data-quarter="<?php echo htmlspecialchars($assessment['quarter']); ?>" 
                                    data-status="<?php echo htmlspecialchars($assessment['status']); ?>"
                                    data-search="<?php echo strtolower($assessment['student_name'] . ' ' . $assessment['lrn']); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($assessment['student_name']); ?></strong>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($assessment['lrn']); ?></code>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #1e4072;">
                                            <?php echo htmlspecialchars($assessment['quarter']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($assessment['submitted_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusColor = match($assessment['status']) {
                                            'pending' => '#ffc107',
                                            'approved' => '#3b6d11',
                                            'rejected' => '#dc3545',
                                            default => '#6c757d'
                                        };
                                        $statusText = ucfirst($assessment['status']);
                                        ?>
                                        <span class="badge" style="background-color: <?php echo $statusColor; ?>;">
                                            <?php echo $statusText; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($assessment['parent_name']); ?></small>
                                    </td>
                                    <td>
                                        <a href="<?php echo BASE_PATH; ?>/assessment/view/<?php echo $assessment['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="fas fa-eye"></i> Review
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

<script>
document.addEventListener('DOMContentLoaded', function() {
    const searchInput = document.getElementById('searchInput');
    const filterQuarter = document.getElementById('filterQuarter');
    const filterStatus = document.getElementById('filterStatus');
    const rows = document.querySelectorAll('.assessment-row');

    function filterTable() {
        const searchTerm = searchInput.value.toLowerCase();
        const quarterFilter = filterQuarter.value;
        const statusFilter = filterStatus.value;

        rows.forEach(row => {
            let show = true;

            // Search filter
            if (searchTerm && !row.dataset.search.includes(searchTerm)) {
                show = false;
            }

            // Quarter filter
            if (quarterFilter && row.dataset.quarter !== quarterFilter) {
                show = false;
            }

            // Status filter
            if (statusFilter && row.dataset.status !== statusFilter) {
                show = false;
            }

            row.style.display = show ? '' : 'none';
        });
    }

    searchInput.addEventListener('keyup', filterTable);
    filterQuarter.addEventListener('change', filterTable);
    filterStatus.addEventListener('change', filterTable);
});
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

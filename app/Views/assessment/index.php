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
                        <input type="text" id="searchInput" class="form-control" placeholder="Search by student name or LRN...">
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

        <!-- Assessments Table -->
        <div class="card">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0"><i class="fas fa-list"></i> All Assessments</h5>
            </div>
            <div class="table-responsive">
                <table class="table table-hover mb-0" id="assessmentsTable">
                    <thead style="background-color: #f5f5f5;">
                        <tr>
                            <th>Student Name</th>
                            <th>LRN</th>
                            <th>Version</th>
                            <th>Status</th>
                            <th>Conducted By</th>
                            <th>Date</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($allAssessments)): ?>
                            <tr>
                                <td colspan="7" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox fa-3x mb-3"></i>
                                    <p>No assessments found</p>
                                    <a href="<?php echo BASE_PATH; ?>/assessment/conduct" class="btn btn-primary" style="background-color: #a01422; border-color: #a01422;">
                                        <i class="fas fa-plus"></i> Conduct First Assessment
                                    </a>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($allAssessments as $assessment): ?>
                                <tr class="assessment-row" 
                                    data-status="<?php echo htmlspecialchars($assessment['status']); ?>"
                                    data-search="<?php echo strtolower($assessment['student_name'] . ' ' . $assessment['lrn']); ?>">
                                    <td>
                                        <strong><?php echo htmlspecialchars($assessment['student_name']); ?></strong>
                                        <?php if (!empty($assessment['services'])): ?>
                                            <br>
                                            <small class="text-muted">
                                                <i class="bi bi-folder"></i> <?php echo count($assessment['services']); ?> service(s)
                                                <?php 
                                                $totalDocs = array_sum(array_column($assessment['services'], 'document_count'));
                                                if ($totalDocs > 0): 
                                                ?>
                                                | <i class="bi bi-file-earmark"></i> <?php echo $totalDocs; ?> document(s)
                                                <?php endif; ?>
                                            </small>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($assessment['lrn']); ?></code>
                                    </td>
                                    <td>
                                        <span class="badge bg-secondary">v<?php echo $assessment['version']; ?></span>
                                    </td>
                                    <td>
                                        <?php 
                                        $statusColor = match($assessment['status']) {
                                            'finalized' => '#3b6d11',
                                            'draft' => '#ffc107',
                                            'pending' => '#17a2b8',
                                            'approved' => '#28a745',
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
                                        <small><?php echo htmlspecialchars($assessment['conducted_by_name'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <small class="text-muted">
                                            <?php echo date('M d, Y', strtotime($assessment['created_at'])); ?>
                                        </small>
                                    </td>
                                    <td>
                                        <?php if ($assessment['status'] === 'draft'): ?>
                                            <a href="<?php echo BASE_PATH; ?>/assessment/conduct/<?php echo $assessment['student_id']; ?>" 
                                               class="btn btn-sm btn-warning">
                                                <i class="fas fa-edit"></i> Continue
                                            </a>
                                        <?php else: ?>
                                            <a href="<?php echo BASE_PATH; ?>/assessment/view/<?php echo $assessment['id']; ?>" 
                                               class="btn btn-sm" style="background-color: #1e4072; color: white;">
                                                <i class="fas fa-eye"></i> View
                                            </a>
                                        <?php endif; ?>
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

<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-05
// Part of: SPED LMS — IEP Implementation Dashboard

$pageTitle = 'IEP Implementation - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-clipboard-check text-primary"></i> IEP Implementation
        </h1>
        <a href="<?php echo $basePath; ?>/iep/implementation/assign" class="btn btn-primary">
            <i class="bi bi-plus-circle"></i> Assign New IEP
        </a>
    </div>

    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Total Students</h6>
                            <h3 class="mb-0"><?php echo count($students); ?></h3>
                        </div>
                        <div class="bg-primary bg-opacity-10 p-3 rounded">
                            <i class="bi bi-people text-primary" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">In Progress</h6>
                            <h3 class="mb-0">
                                <?php 
                                $inProgress = array_filter($students, fn($s) => $s['implementation_status'] === 'in_progress');
                                echo count($inProgress);
                                ?>
                            </h3>
                        </div>
                        <div class="bg-warning bg-opacity-10 p-3 rounded">
                            <i class="bi bi-hourglass-split text-warning" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Completed</h6>
                            <h3 class="mb-0">
                                <?php 
                                $completed = array_filter($students, fn($s) => $s['implementation_status'] === 'completed');
                                echo count($completed);
                                ?>
                            </h3>
                        </div>
                        <div class="bg-success bg-opacity-10 p-3 rounded">
                            <i class="bi bi-check-circle text-success" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-center">
                        <div>
                            <h6 class="text-muted mb-1">Avg Progress</h6>
                            <h3 class="mb-0">
                                <?php 
                                $avgProgress = count($students) > 0 
                                    ? round(array_sum(array_column($students, 'progress_percentage')) / count($students)) 
                                    : 0;
                                echo $avgProgress;
                                ?>%
                            </h3>
                        </div>
                        <div class="bg-info bg-opacity-10 p-3 rounded">
                            <i class="bi bi-graph-up text-info" style="font-size: 2rem;"></i>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Students List -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
            <h5 class="mb-0"><i class="bi bi-list-ul"></i> Students with Assigned IEPs</h5>
        </div>
        <div class="card-body">
            <?php if (empty($students)): ?>
                <div class="text-center py-5">
                    <i class="bi bi-inbox" style="font-size: 4rem; color: #ccc;"></i>
                    <p class="text-muted mt-3">No students with assigned IEPs yet.</p>
                    <a href="<?php echo $basePath; ?>/iep/implementation/assign" class="btn btn-primary">
                        <i class="bi bi-plus-circle"></i> Assign First IEP
                    </a>
                </div>
            <?php else: ?>
                <div class="row">
                    <?php foreach ($students as $student): ?>
                        <div class="col-md-4 mb-4">
                            <div class="card h-100 border-0 shadow-sm hover-shadow">
                                <div class="card-body">
                                    <!-- Student Info -->
                                    <div class="d-flex align-items-center mb-3">
                                        <div class="bg-primary bg-opacity-10 rounded-circle p-3 me-3">
                                            <i class="bi bi-person-fill text-primary" style="font-size: 1.5rem;"></i>
                                        </div>
                                        <div>
                                            <h6 class="mb-0"><?php echo htmlspecialchars($student['student_name']); ?></h6>
                                            <small class="text-muted">LRN: <?php echo htmlspecialchars($student['lrn']); ?></small>
                                        </div>
                                    </div>

                                    <!-- Status Badge -->
                                    <?php
                                    $statusColors = [
                                        'not_started' => 'secondary',
                                        'in_progress' => 'warning',
                                        'completed' => 'success'
                                    ];
                                    $statusLabels = [
                                        'not_started' => 'Not Started',
                                        'in_progress' => 'In Progress',
                                        'completed' => 'Completed'
                                    ];
                                    $statusColor = $statusColors[$student['implementation_status']] ?? 'secondary';
                                    $statusLabel = $statusLabels[$student['implementation_status']] ?? 'Unknown';
                                    ?>
                                    <span class="badge bg-<?php echo $statusColor; ?> mb-3">
                                        <?php echo $statusLabel; ?>
                                    </span>

                                    <!-- Progress Bar -->
                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between mb-1">
                                            <small class="text-muted">Progress</small>
                                            <small class="text-muted"><?php echo $student['progress_percentage']; ?>%</small>
                                        </div>
                                        <div class="progress" style="height: 8px;">
                                            <div class="progress-bar bg-primary" 
                                                 style="width: <?php echo $student['progress_percentage']; ?>%">
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Materials Count -->
                                    <div class="d-flex justify-content-between mb-3">
                                        <small class="text-muted">
                                            <i class="bi bi-file-earmark"></i> 
                                            <?php echo $student['materials_count']; ?> Materials
                                        </small>
                                        <small class="text-muted">
                                            <i class="bi bi-check-circle"></i> 
                                            <?php echo $student['completed_count']; ?> Completed
                                        </small>
                                    </div>

                                    <!-- Action Buttons -->
                                    <div class="d-grid gap-2">
                                        <a href="<?php echo $basePath; ?>/iep/implementation/materials/<?php echo $student['id']; ?>" 
                                           class="btn btn-primary btn-sm">
                                            <i class="bi bi-folder"></i> Manage Materials
                                        </a>
                                        <a href="<?php echo $basePath; ?>/iep/implementation/progress/<?php echo $student['id']; ?>" 
                                           class="btn btn-outline-secondary btn-sm">
                                            <i class="bi bi-graph-up"></i> View Progress
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.hover-shadow {
    transition: box-shadow 0.3s ease;
}
.hover-shadow:hover {
    box-shadow: 0 0.5rem 1rem rgba(0, 0, 0, 0.15) !important;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

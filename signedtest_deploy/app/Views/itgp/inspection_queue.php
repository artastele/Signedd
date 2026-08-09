<?php
$pageTitle = 'ITGP Inspection Queue - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1>ITGP Inspection Queue</h1>
            <p class="text-muted mb-0">Inclusive IEP & ITGP records that are ready for Master Teacher inspection.</p>
        </div>
        <a href="<?= $basePath ?>/dashboard" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Dashboard
        </a>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success"><?php echo htmlspecialchars($success); ?></div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger"><?php echo htmlspecialchars($error); ?></div>
    <?php endif; ?>

    <div class="card">
        <div class="card-body p-0">
            <?php if (empty($itgps)): ?>
                <div class="text-center py-5 text-muted">
                    <i class="bi bi-inbox" style="font-size: 2.5rem;"></i>
                    <p class="mt-3">No ITGP records are currently ready for inspection.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th>Student</th>
                                <th>School Year</th>
                                <th>General Teacher</th>
                                <th>Status</th>
                                <th>Updated</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($itgps as $row): ?>
                                <tr>
                                    <td>
                                        <strong><?php echo htmlspecialchars($row['student_name']); ?></strong><br>
                                        <small class="text-muted">LRN: <?php echo htmlspecialchars($row['lrn'] ?? '—'); ?></small>
                                    </td>
                                    <td><?php echo htmlspecialchars($row['school_year'] ?? '—'); ?></td>
                                    <td><?php echo htmlspecialchars($row['general_teacher_name'] ?? 'Unassigned'); ?></td>
                                    <td>
                                        <span class="badge bg-primary"><?php echo htmlspecialchars(str_replace('_', ' ', ucfirst($row['status']))); ?></span>
                                    </td>
                                    <td><?php echo date('M d, Y g:i A', strtotime($row['updated_at'])); ?></td>
                                    <td>
                                        <a href="<?= $basePath ?>/iep/<?php echo (int)$row['iep_id']; ?>/inclusive-iep-itgp" class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-box-arrow-in-right"></i> Open
                                        </a>
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

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

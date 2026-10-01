<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SignED — Assignments List

$pageTitle = 'My Assignments - SignED';
require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . (defined('BASE_PATH') ? BASE_PATH : '') . '/css/learner.css">';
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-content">
    <div class="section-header">
        <h1><i class="ph-bold ph-pencil-simple me-2 text-primary" aria-hidden="true"></i>My Assignments</h1>
        <a href="<?php echo $basePath; ?>/learning/dashboard" class="btn btn-outline-secondary rounded-2 px-3 fw-semibold">
            <i class="ph-bold ph-arrow-left me-1" aria-hidden="true"></i> Back
        </a>
    </div>

    <?php if (empty($assignments)): ?>
        <div class="empty-state text-center py-5">
            <i class="ph-bold ph-tray text-muted" style="font-size: 3rem; display: block; margin-bottom: 12px;" aria-hidden="true"></i>
            <p class="text-muted">No assignments yet. Enjoy your free time!</p>
        </div>
    <?php else: ?>
        <!-- Filter Tabs -->
        <div class="filter-tabs mb-4">
            <button class="filter-tab active" data-filter="all">
                All (<?php echo count($assignments); ?>)
            </button>
            <button class="filter-tab" data-filter="pending">
                Pending
            </button>
            <button class="filter-tab" data-filter="submitted">
                Submitted
            </button>
            <button class="filter-tab" data-filter="graded">
                Graded
            </button>
        </div>

        <div class="row">
            <?php foreach ($assignments as $assignment): ?>
                <?php
                $status = 'pending';
                if ($assignment['submission_id']) {
                    $status = $assignment['grade'] !== null ? 'graded' : 'submitted';
                }
                ?>
                <div class="col-md-4 mb-4 assignment-item" data-status="<?php echo $status; ?>">
                    <div class="assignment-card card border rounded-3 p-3 shadow-sm bg-white text-center">
                        <div class="assignment-icon mb-3">
                            <i class="ph-bold ph-file-text text-primary" style="font-size: 2.5rem;" aria-hidden="true"></i>
                        </div>
                        <h5 class="assignment-title fw-bold text-dark mb-2"><?php echo htmlspecialchars($assignment['material_name']); ?></h5>
                        
                        <!-- Due Date -->
                        <?php if ($assignment['due_date']): ?>
                            <?php
                            $daysLeft = ceil((strtotime($assignment['due_date']) - time()) / 86400);
                            $urgentClass = $daysLeft <= 3 && $daysLeft > 0 ? 'urgent' : '';
                            $overdueClass = $daysLeft < 0 ? 'overdue' : '';
                            ?>
                            <div class="assignment-due mb-2 <?php echo $urgentClass . ' ' . $overdueClass; ?>">
                                <?php if ($daysLeft < 0): ?>
                                    <i class="ph-bold ph-warning text-danger me-1" aria-hidden="true"></i> Overdue by <?php echo abs($daysLeft); ?> day<?php echo abs($daysLeft) != 1 ? 's' : ''; ?>
                                <?php elseif ($daysLeft == 0): ?>
                                    <i class="ph-bold ph-clock-countdown text-danger me-1" aria-hidden="true"></i> Due Today!
                                <?php else: ?>
                                    <i class="ph-bold ph-calendar me-1" aria-hidden="true"></i> Due in <?php echo $daysLeft; ?> day<?php echo $daysLeft != 1 ? 's' : ''; ?>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Points -->
                        <?php if ($assignment['points']): ?>
                            <div class="assignment-points text-muted small mb-3">
                                <i class="ph-bold ph-trophy text-warning me-1" aria-hidden="true"></i> <?php echo $assignment['points']; ?> points
                            </div>
                        <?php endif; ?>

                        <!-- Status Badge -->
                        <?php if ($assignment['submission_id']): ?>
                            <?php if ($assignment['grade'] !== null): ?>
                                <div class="badge bg-success mb-2">
                                    <i class="ph-bold ph-check-circle me-1" aria-hidden="true"></i> Graded: <?php echo $assignment['grade']; ?>/<?php echo $assignment['points']; ?>
                                </div>
                            <?php else: ?>
                                <div class="badge bg-primary mb-2"><i class="ph-bold ph-check me-1" aria-hidden="true"></i> Submitted</div>
                            <?php endif; ?>
                            <a href="<?php echo $basePath; ?>/learning/assignment/<?php echo $assignment['id']; ?>" 
                               class="btn btn-outline-primary w-100 fw-bold rounded-2 py-2 mt-2 d-flex align-items-center justify-content-center gap-1">
                                <i class="ph-bold ph-magnifying-glass me-1" aria-hidden="true"></i> View Details
                            </a>
                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>/learning/assignment/<?php echo $assignment['id']; ?>" 
                               class="btn btn-primary w-100 fw-bold rounded-2 py-2 mt-2 d-flex align-items-center justify-content-center gap-1">
                                <i class="ph-bold ph-pencil-simple me-1" aria-hidden="true"></i> Do Assignment!
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<style>
.filter-tabs {
    display: flex;
    gap: 10px;
    flex-wrap: wrap;
}

.filter-tab {
    padding: 10px 20px;
    border-radius: 20px;
    border: 3px solid #333;
    background: #fff;
    font-weight: bold;
    cursor: pointer;
    transition: all 0.3s ease;
}

.filter-tab:hover {
    transform: translateY(-2px);
}

.filter-tab.active {
    background: var(--kid-orange);
    color: #fff;
}

.assignment-points {
    padding: 8px 15px;
    border-radius: 15px;
    background: var(--kid-yellow);
    color: #333;
    font-size: 0.9rem;
    font-weight: bold;
    margin-bottom: 10px;
}

.assignment-due.overdue {
    background: var(--kid-red);
    animation: shake 0.5s infinite;
}

.badge-graded {
    background: var(--kid-blue);
    color: #fff;
}

.btn-cartoon.btn-view {
    background: var(--kid-purple);
    color: #fff;
    width: 100%;
    margin-top: 15px;
}
</style>

<script>
// Filter functionality
document.querySelectorAll('.filter-tab').forEach(tab => {
    tab.addEventListener('click', function() {
        // Update active tab
        document.querySelectorAll('.filter-tab').forEach(t => t.classList.remove('active'));
        this.classList.add('active');
        
        const filter = this.dataset.filter;
        
        // Filter assignments
        document.querySelectorAll('.assignment-item').forEach(item => {
            if (filter === 'all' || item.dataset.status === filter) {
                item.style.display = 'block';
            } else {
                item.style.display = 'none';
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

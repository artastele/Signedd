<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SignED — Modules List

$pageTitle = 'My Modules - SignED';
require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . (defined('BASE_PATH') ? BASE_PATH : '') . '/css/learner.css">';
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-content">
    <div class="section-header">
        <h1><i class="ph-bold ph-books me-2 text-primary" aria-hidden="true"></i>My Modules</h1>
        <a href="<?php echo $basePath; ?>/learning/dashboard" class="btn btn-outline-secondary rounded-2 px-3 fw-semibold">
            <i class="ph-bold ph-arrow-left me-1" aria-hidden="true"></i> Back
        </a>
    </div>

    <?php if (empty($modules)): ?>
        <div class="empty-state text-center py-5">
            <i class="ph-bold ph-tray text-muted" style="font-size: 3rem; display: block; margin-bottom: 12px;" aria-hidden="true"></i>
            <p class="text-muted">No modules available yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="row">
            <?php foreach ($modules as $module): ?>
                <div class="col-md-4 mb-4">
                    <div class="module-card card border rounded-3 p-3 shadow-sm bg-white text-center">
                        <div class="module-icon mb-3">
                            <i class="ph-bold ph-book-open text-primary" style="font-size: 2.5rem;" aria-hidden="true"></i>
                        </div>
                        <h5 class="module-title fw-bold text-dark mb-3"><?php echo htmlspecialchars($module['material_name']); ?></h5>
                        
                        <?php if ($module['progress_status'] === 'completed'): ?>
                            <div class="badge bg-success mb-2"><i class="ph-bold ph-check-circle me-1" aria-hidden="true"></i> Completed</div>
                            <?php if ($module['stars_earned'] > 0): ?>
                                <div class="mb-2">
                                    <?php for ($i = 0; $i < $module['stars_earned']; $i++): ?>
                                        <i class="ph-bold ph-star text-warning" style="font-size: 1.25rem;" aria-hidden="true"></i>
                                    <?php endfor; ?>
                                </div>
                            <?php endif; ?>
                        <?php elseif ($module['progress_status'] === 'in_progress'): ?>
                            <div class="badge bg-warning text-dark mb-2"><i class="ph-bold ph-hourglass me-1" aria-hidden="true"></i> In Progress</div>
                        <?php else: ?>
                            <div class="badge bg-primary mb-2"><i class="ph-bold ph-sparkle me-1" aria-hidden="true"></i> New!</div>
                        <?php endif; ?>
                        
                        <a href="<?php echo $basePath; ?>/learning/module/<?php echo $module['id']; ?>" 
                           class="btn btn-primary w-100 fw-bold rounded-2 py-2 mt-2 d-flex align-items-center justify-content-center gap-1">
                            <span><?php echo $module['progress_status'] === 'completed' ? 'Review Again' : 'Start Learning!'; ?></span>
                            <i class="ph-bold <?php echo $module['progress_status'] === 'completed' ? 'ph-arrow-counter-clockwise' : 'ph-arrow-right'; ?>" aria-hidden="true"></i>
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

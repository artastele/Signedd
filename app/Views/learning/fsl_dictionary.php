<?php
// Part of: SignED — FSL Digital Dictionary View
// Last modified: 2026-08-27

$pageTitle = 'FSL Digital Dictionary — SignED';
require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . (defined('BASE_PATH') ? BASE_PATH : '') . '/css/learner.css">';
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid p-0">
            <!-- Header Card -->
            <div class="card text-white border-0 shadow-sm rounded-4 p-4 mb-4" style="background: var(--gradient-banner-fsl) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="ph-bold ph-hand-waving me-2" aria-hidden="true"></i>Aklat ng mga Senyas (FSL Digital Dictionary)</h3>
                        <p class="text-white opacity-90 mb-0">Tuklasin ang mga tunay na video at senyas sa Filipino Sign Language para sa mga mag-aaral at guro.</p>
                    </div>
                    <span class="badge bg-white text-dark fs-6 px-3 py-2 rounded-pill shadow-sm">
                        <i class="ph-bold ph-book-open me-1 text-warning" aria-hidden="true"></i> <?= count($words) ?> Senyas na Magagamit
                    </span>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
                <form method="GET" action="<?= $basePath ?>/fsl-dictionary" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light border-end-0"><i class="ph-bold ph-magnifying-glass text-muted" aria-hidden="true"></i></span>
                            <input type="text" name="search" class="form-control border-start-0" placeholder="Maghanap ng salita o kahulugan..." value="<?= htmlspecialchars($search ?? '') ?>" aria-label="Maghanap ng salita">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select" onchange="this.form.submit()" aria-label="Pumili ng Kategorya">
                            <option value="all">Lahat ng Kategorya (All Categories)</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= ($category === $cat) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100 d-flex align-items-center justify-content-center gap-1 fw-semibold" style="height: 38px; border-radius: 8px;">
                            <i class="ph-bold ph-magnifying-glass" aria-hidden="true"></i>
                            <span>Hanapin</span>
                        </button>
                    </div>
                </form>
            </div>

            <!-- Dictionary Grid -->
            <?php if (empty($words)): ?>
                <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-3">
                    <i class="ph-bold ph-magnifying-glass text-muted" style="font-size: 3rem; display: block; margin-bottom: 12px;" aria-hidden="true"></i>
                    <h5 class="text-muted">Walang nahanap na senyas para sa iyong hinahanap.</h5>
                    <p class="text-muted small">Subukang palitan ang salita o piliin ang "Lahat ng Kategorya".</p>
                </div>
            <?php else: ?>
                <div class="row g-3">
                    <?php foreach ($words as $w): 
                        $catKey = strtolower(trim($w['category'] ?? 'default'));
                        $catClass = match(true) {
                            str_contains($catKey, 'family') => 'fsl-cat-badge-family',
                            str_contains($catKey, 'survival') => 'fsl-cat-badge-survival',
                            str_contains($catKey, 'number') => 'fsl-cat-badge-number',
                            str_contains($catKey, 'calendar') || str_contains($catKey, 'time') => 'fsl-cat-badge-calendar',
                            str_contains($catKey, 'color') => 'fsl-cat-badge-color',
                            default => 'fsl-cat-badge-default'
                        };
                    ?>
                        <div class="col-sm-6 col-md-4 col-lg-3">
                            <div class="card h-100 fsl-dict-card">
                                <div class="card-body text-center p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <span class="badge <?= $catClass ?> px-2.5 py-1 rounded-pill mb-2" style="font-size: 0.75rem; font-weight: 600;">
                                            <?= htmlspecialchars($w['category']) ?>
                                        </span>
                                        <h5 class="fw-bold text-dark text-capitalize mb-2" style="font-size: 1.1rem;"><?= htmlspecialchars($w['word']) ?></h5>
                                        <p class="text-muted small mb-3" style="font-size: 0.82rem; min-height: 38px;">
                                            <?= htmlspecialchars($w['description'] ?? 'Senyas sa FSL') ?>
                                        </p>
                                    </div>

                                    <div>
                                        <button type="button" class="btn btn-outline-primary btn-sm w-100 fsl-trigger-btn fw-semibold d-flex align-items-center justify-content-center gap-1.5" 
                                                data-word="<?= htmlspecialchars($w['word']) ?>"
                                                data-category="<?= htmlspecialchars($w['category']) ?>"
                                                data-desc="<?= htmlspecialchars($w['description'] ?? '') ?>"
                                                data-gif="<?= !empty($w['gif_path']) ? $basePath . '/' . ltrim($w['gif_path'], '/') : '' ?>"
                                                data-video="<?= !empty($w['video_path']) ? $basePath . '/' . ltrim($w['video_path'], '/') : '' ?>"
                                                style="border-radius: 8px; font-size: 0.85rem; padding: 7px 12px;">
                                            <i class="ph-bold ph-play-circle fs-6" aria-hidden="true"></i>
                                            <span>Panoorin ang Senyas</span>
                                        </button>
                                    </div>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

    </div> <!-- End container-fluid -->
</div> <!-- End .main-content -->

<!-- FSL Modal Component Included Globally -->
<script src="<?= $basePath ?>/js/fsl-modal.js"></script>

<style>
.hover-shadow:hover {
    transform: translateY(-2px);
    box-shadow: 0 6px 16px rgba(0,0,0,0.08) !important;
}
.transition {
    transition: all 0.2s ease-in-out;
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
// Part of: SignED — Teacher FSL Training Portal View
// Last modified: 2026-08-27

$pageTitle = 'Teacher FSL Training & Guides — SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid p-0">

            <!-- Hero Header -->
            <div class="card bg-primary text-white border-0 shadow-sm rounded-3 p-4 mb-4" style="background: linear-gradient(135deg, #1e4072 0%, #0f172a 100%) !important;">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h3 class="fw-bold mb-1"><i class="bi bi-mortarboard-fill me-2"></i>SPED Teacher FSL Instructional Training Portal</h3>
                        <p class="text-white-50 mb-0">Professional Filipino Sign Language guides, classroom command clips, and inclusive communication tips.</p>
                    </div>
                    <div class="d-flex gap-2">
                        <a href="<?= $basePath ?>/teacher-fsl-training/print" target="_blank" class="btn btn-warning btn-sm fw-semibold">
                            <i class="bi bi-printer-fill me-1"></i> Printable Reference Cards
                        </a>
                        <a href="<?= $basePath ?>/fsl-dictionary" class="btn btn-outline-light btn-sm">
                            <i class="bi bi-book me-1"></i> FSL Dictionary
                        </a>
                    </div>
                </div>
            </div>

            <!-- Quick Tips Alert -->
            <div class="alert alert-info border-info shadow-sm p-3 mb-4 rounded-3">
                <div class="d-flex align-items-start gap-2">
                    <i class="bi bi-lightbulb-fill fs-4 text-warning"></i>
                    <div>
                        <strong class="d-block mb-1">Essential Signing Etiquette for Inclusive Classrooms:</strong>
                        <ul class="small mb-0 ps-3">
                            <li>Always maintain direct eye contact before signing to ensure learner attention.</li>
                            <li>Facial expressions form an active part of FSL grammar (conveying questions, emphasis, and tone).</li>
                            <li>Sign clearly at a steady rhythm at chest/shoulder level.</li>
                        </ul>
                    </div>
                </div>
            </div>

            <!-- Filter & Search Bar -->
            <div class="card border-0 shadow-sm rounded-3 p-3 mb-4 bg-white">
                <form method="GET" action="<?= $basePath ?>/teacher-fsl-training" class="row g-2 align-items-center">
                    <div class="col-md-6">
                        <div class="input-group">
                            <span class="input-group-text bg-light"><i class="bi bi-search"></i></span>
                            <input type="text" name="search" class="form-control" placeholder="Search training guides, commands, or tips..." value="<?= htmlspecialchars($search ?? '') ?>">
                        </div>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select" onchange="this.form.submit()">
                            <option value="all">All Modules &amp; Categories</option>
                            <?php foreach ($categories as $cat): ?>
                                <option value="<?= htmlspecialchars($cat) ?>" <?= ($category === $cat) ? 'selected' : '' ?>>
                                    <?= htmlspecialchars($cat) ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <button type="submit" class="btn btn-primary w-100" style="height: 38px;">
                            Filter
                        </button>
                    </div>
                </form>
            </div>

            <!-- Modules Grid -->
            <?php if (empty($modules)): ?>
                <div class="card border-0 shadow-sm p-5 text-center bg-white rounded-3">
                    <i class="bi bi-journal-x text-muted mb-3" style="font-size: 3rem;"></i>
                    <h5 class="text-muted">No training modules found matching your search.</h5>
                </div>
            <?php else: ?>
                <div class="row g-4">
                    <?php foreach ($modules as $m): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card h-100 border rounded-3 shadow-sm bg-white hover-shadow transition">
                                <div class="card-header bg-light border-bottom d-flex align-items-center justify-content-between py-2">
                                    <span class="badge bg-primary-subtle text-primary border" style="font-size: 0.75rem;">
                                        <?= htmlspecialchars($m['category']) ?>
                                    </span>
                                    <span class="text-muted small">Module #<?= (int)$m['display_order'] ?></span>
                                </div>
                                <div class="card-body p-3 d-flex flex-column justify-content-between">
                                    <div>
                                        <h5 class="fw-bold text-dark mb-2" style="font-size: 1rem;"><?= htmlspecialchars($m['title']) ?></h5>
                                        <p class="text-muted small mb-3"><?= htmlspecialchars($m['description']) ?></p>

                                        <?php if (!empty($m['tips'])): ?>
                                            <div class="p-2 bg-warning-subtle rounded border border-warning-subtle mb-3">
                                                <strong class="text-dark small d-block mb-1"><i class="bi bi-hand-thumbs-up-fill me-1 text-warning"></i>Signing Tip:</strong>
                                                <p class="small text-dark mb-0" style="font-size: 0.78rem;"><?= htmlspecialchars($m['tips']) ?></p>
                                            </div>
                                        <?php endif; ?>
                                    </div>

                                    <div>
                                        <button type="button" class="btn btn-primary btn-sm w-100 fsl-trigger-btn"
                                                data-word="<?= htmlspecialchars($m['title']) ?>"
                                                data-category="<?= htmlspecialchars($m['category']) ?>"
                                                data-desc="<?= htmlspecialchars($m['description']) ?>"
                                                data-gif="<?= !empty($m['gif_path']) ? $basePath . '/' . ltrim($m['gif_path'], '/') : '' ?>"
                                                data-video="<?= !empty($m['video_path']) ? $basePath . '/' . ltrim($m['video_path'], '/') : '' ?>">
                                            <i class="bi bi-play-circle-fill me-1"></i> Watch Sign Demonstration
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


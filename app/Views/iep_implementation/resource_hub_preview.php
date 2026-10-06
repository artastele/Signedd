<?php
// SignED — Accessibility Pack Resource Hub Preview (MVP)
// Read-only inspection of shared pack slides, approved FSL elements, and activities.

$pageTitle = 'Preview Pack: ' . htmlspecialchars($pack['title'] ?? 'Accessibility Pack');
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<style>
.preview-slide-canvas {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 12px;
    padding: 2.25rem 2.5rem;
    min-height: 480px;
    box-shadow: 0 2px 8px rgba(0,0,0,0.04);
}
.preview-slide-canvas h1, .preview-slide-canvas h2, .preview-slide-canvas h3 {
    color: #1e293b;
    font-weight: 700;
}
.slide-nav-btn {
    border-radius: 8px;
    padding: 0.5rem 0.85rem;
    font-size: 0.8125rem;
    font-weight: 600;
    text-align: left;
    transition: all 0.15s ease;
    border: 1px solid #e2e8f0;
    background: #ffffff;
    color: #334155;
    display: flex;
    align-items: center;
    justify-content: space-between;
    width: 100%;
}
.slide-nav-btn:hover {
    background: #f8fafc;
    border-color: #cbd5e1;
    color: #0f172a;
}
.slide-nav-btn.active {
    background: #1e4072;
    border-color: #1e4072;
    color: #ffffff;
    box-shadow: 0 2px 6px rgba(30, 64, 114, 0.25);
}
.slide-nav-btn.active .slide-num-pill {
    background: rgba(255, 255, 255, 0.2);
    color: #ffffff;
}
.slide-num-pill {
    background: #f1f5f9;
    color: #64748b;
    border-radius: 6px;
    font-size: 0.72rem;
    padding: 0.15rem 0.45rem;
    font-weight: 700;
}
/* Interactive .fsl-word styling in preview */
.fsl-word {
    background: linear-gradient(180deg, #fef3c7 0%, #fde68a 100%) !important;
    border-bottom: 2px solid #d97706 !important;
    color: #92400e !important;
    font-weight: 600 !important;
    padding: 0.1rem 0.35rem !important;
    border-radius: 4px !important;
    cursor: pointer !important;
    display: inline-block;
    transition: all 0.15s ease;
}
.fsl-word:hover {
    background: #fbbf24 !important;
    color: #78350f !important;
    transform: translateY(-1px);
}
.fsl-word::after {
    content: " 🤟";
    font-size: 0.75rem;
}
.btn-hub-action {
    font-size: 0.8125rem;
    font-weight: 500;
    border-radius: 6px;
    padding: 0.4rem 0.85rem;
}
</style>

<div class="main-content">
    <div class="container-fluid py-4">

        <!-- Header Bar -->
        <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex align-items-center gap-2">
                <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/resource-hub" class="btn btn-outline-secondary btn-sm" style="border-radius: 6px;">
                    <i class="ph-bold ph-arrow-left me-1"></i> Back to Hub
                </a>
                <div>
                    <h4 class="fw-bold text-dark mb-0 d-flex align-items-center gap-2">
                        <span><?php echo htmlspecialchars($pack['title']); ?></span>
                        <span class="badge bg-secondary-subtle text-secondary fw-semibold border" style="font-size:0.75rem; border-radius:6px;">Preview Mode</span>
                    </h4>
                </div>
            </div>

            <div class="d-flex align-items-center gap-2">
                <?php if (!empty($pack['mainstream_mastery_url'])): ?>
                    <a href="<?php echo htmlspecialchars($pack['mainstream_mastery_url']); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-outline-primary btn-sm btn-hub-action">
                        <i class="ph-bold ph-arrow-square-out me-1"></i> <?php echo htmlspecialchars($pack['mainstream_mastery_title'] ?: 'External Mastery Activity'); ?>
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-primary btn-sm btn-hub-action fw-semibold" onclick="openUseAsTemplateModal(<?php echo (int)$pack['id']; ?>, <?php echo htmlspecialchars(json_encode($pack['title'])); ?>)">
                    <i class="ph-bold ph-copy me-1"></i> Use as Template
                </button>
            </div>
        </div>

        <!-- Metadata Ribbon -->
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 10px; background:#f8fafc;">
            <div class="card-body py-2.5 px-3.5">
                <div class="d-flex flex-wrap gap-3 align-items-center justify-content-between text-muted small" style="font-size: 0.8rem;">
                    <div class="d-flex flex-wrap gap-2 align-items-center">
                        <span class="badge bg-primary-subtle text-primary fw-semibold px-2 py-1" style="border-radius: 6px;">
                            <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $pack['pdsp_domain']))); ?>
                        </span>
                        <span class="badge bg-light text-dark border fw-medium px-2 py-1" style="border-radius: 6px;">
                            <?php echo htmlspecialchars($pack['learning_level']); ?>
                        </span>
                        <?php if (!empty($pack['fsl_supported'])): ?>
                            <span class="badge bg-warning-subtle text-warning-emphasis fw-bold px-2 py-1" style="border-radius: 6px;">
                                <i class="ph-bold ph-hands-clapping me-1"></i> <?php echo (int)$pack['fsl_sign_count']; ?> FSL Signs
                            </span>
                        <?php endif; ?>
                        <span>Author: <strong><?php echo htmlspecialchars($pack['author_name']); ?></strong></span>
                        <span>&middot;</span>
                        <span>Slides: <strong><?php echo count($pages); ?></strong></span>
                        <?php if (!empty($activities)): ?>
                            <span>&middot;</span>
                            <span>Activities: <strong><?php echo count($activities); ?></strong></span>
                        <?php endif; ?>
                    </div>
                    <?php if (!empty($pack['learning_competency'])): ?>
                        <div class="text-truncate" style="max-width: 450px;" title="<?php echo htmlspecialchars($pack['learning_competency']); ?>">
                            <i class="ph-bold ph-target me-1"></i> <em><?php echo htmlspecialchars($pack['learning_competency']); ?></em>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Interactive Slide Inspector -->
        <div class="row g-3">
            <!-- Left: Slide List Navigator -->
            <div class="col-12 col-lg-3">
                <div class="card border-0 shadow-sm" style="border-radius: 10px;">
                    <div class="card-header bg-white border-bottom py-2.5 px-3">
                        <span class="fw-bold small text-dark">Lesson Slides (<?php echo count($pages); ?>)</span>
                    </div>
                    <div class="card-body p-2 d-flex flex-column gap-1.5" id="slideNavList">
                        <?php if (empty($pages)): ?>
                            <p class="text-muted small p-2 mb-0">Walay slides.</p>
                        <?php else: ?>
                            <?php foreach ($pages as $idx => $p): ?>
                                <button type="button" class="slide-nav-btn <?php echo $idx === 0 ? 'active' : ''; ?>" onclick="showPreviewSlide(<?php echo $idx; ?>)" id="btnSlideNav_<?php echo $idx; ?>">
                                    <span class="text-truncate me-2" style="max-width: 170px;">
                                        <?php echo htmlspecialchars($p['title'] ?: 'Slide ' . ($idx + 1)); ?>
                                    </span>
                                    <span class="slide-num-pill">#<?php echo $idx + 1; ?></span>
                                </button>
                            <?php endforeach; ?>
                        <?php endif; ?>

                        <?php if (!empty($activities)): ?>
                            <hr class="my-1.5 text-muted">
                            <button type="button" class="slide-nav-btn" onclick="showActivitiesView()" id="btnActivitiesNav">
                                <span class="d-flex align-items-center gap-1.5 text-dark">
                                    <i class="ph-bold ph-game-controller text-primary"></i>
                                    <span>Activities (<?php echo count($activities); ?>)</span>
                                </span>
                                <span class="slide-num-pill bg-primary-subtle text-primary">Practice</span>
                            </button>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Info Callout -->
                <div class="card border-0 shadow-sm mt-3 bg-light" style="border-radius: 10px;">
                    <div class="card-body p-3 small text-muted" style="font-size: 0.76rem; line-height: 1.45;">
                        <div class="d-flex gap-2">
                            <i class="ph-bold ph-info text-primary mt-0.5" style="font-size: 1rem;"></i>
                            <div>
                                <strong>Interactive Preview:</strong>
                                I-klik ang bisan unsang <span class="badge bg-warning text-dark px-1 py-0.5" style="font-size:0.7rem;">FSL Word</span> aron ma-test ang pop-up video player ug speed controls.
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Right: Active Slide Canvas -->
            <div class="col-12 col-lg-9">
                <div id="previewSlideContainer" class="preview-slide-canvas">
                    <!-- Dynamic slide content populated by JavaScript -->
                </div>

                <!-- Activities Container (Hidden by default) -->
                <div id="previewActivitiesContainer" class="preview-slide-canvas" style="display: none;">
                    <h4 class="fw-bold text-dark mb-3">
                        <i class="ph-bold ph-game-controller text-primary me-1.5"></i> Reusable Practice Activities
                    </h4>
                    <p class="text-muted small mb-4">
                        Ang mga mosunod nga educational activities ma-kopya sa imong bag-ong lesson plan. Walay learner scores o logs ang maapil.
                    </p>

                    <div class="d-flex flex-column gap-3">
                        <?php foreach ($activities as $actIdx => $act): ?>
                            <div class="p-3 rounded border bg-light">
                                <div class="d-flex justify-content-between align-items-center mb-1.5">
                                    <h6 class="fw-bold mb-0 text-dark">
                                        <?php echo ($actIdx + 1); ?>. <?php echo htmlspecialchars($act['title']); ?>
                                    </h6>
                                    <span class="badge bg-white text-muted border small">
                                        <?php echo htmlspecialchars(ucfirst(str_replace('_', ' ', $act['activity_type']))); ?>
                                    </span>
                                </div>
                                <?php if (!empty($act['instructions'])): ?>
                                    <p class="small text-muted mb-2"><?php echo htmlspecialchars($act['instructions']); ?></p>
                                <?php endif; ?>
                                <div class="small text-muted fst-italic">
                                    <i class="ph-bold ph-check-circle text-success me-1"></i> Structured activity template ready for learner practice.
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>
        </div>

    </div><!-- /container-fluid -->
</div><!-- /main-content -->

<!-- Clone Modal (Reuse same modal) -->
<div class="modal fade" id="useAsTemplateModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow" style="border-radius: 12px;">
            <div class="modal-header border-bottom py-3 px-3.5" style="background:#f8fafc; border-radius: 12px 12px 0 0;">
                <div class="d-flex align-items-center gap-2">
                    <div class="rounded-circle d-flex align-items-center justify-content-center bg-primary text-white" style="width:34px; height:34px; font-size:1rem;">
                        <i class="ph-bold ph-copy"></i>
                    </div>
                    <div>
                        <h6 class="modal-title fw-bold mb-0 text-dark">Use as Template</h6>
                        <span class="text-muted" style="font-size:0.75rem;">Create an independent draft for your learner</span>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
            </div>
            <form id="useAsTemplateForm" onsubmit="submitCloneForm(event)">
                <div class="modal-body p-3.5">
                    <input type="hidden" id="clonePackId" name="pack_id" value="<?php echo (int)$pack['id']; ?>">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold mb-1">Selected Accessibility Pack:</label>
                        <div class="fw-bold text-dark p-2 rounded bg-light border" style="font-size: 0.9rem;">
                            <?php echo htmlspecialchars($pack['title']); ?>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="targetIepSelect" class="form-label small fw-bold text-dark mb-1">
                            Pilia ang Imong Estudyante / IEP Context: <span class="text-danger">*</span>
                        </label>
                        <?php if (empty($myIeps)): ?>
                            <div class="alert alert-warning py-2 px-3 small mb-0" style="border-radius: 6px;">
                                <i class="ph-bold ph-warning me-1"></i>
                                Wala kay nakita nga pirmadong IEP nga imong gidumala. Aron maka-clone og lesson, kinahanglan aduna kay bisan usa ka <strong>Signed IEP</strong> ubos sa imong pagtudlo.
                            </div>
                        <?php else: ?>
                            <select id="targetIepSelect" name="target_iep_id" class="form-select form-select-sm" required style="border-radius: 6px;">
                                <option value="">-- Pilia ang Estudiyante / IEP --</option>
                                <?php foreach ($myIeps as $iep): ?>
                                    <option value="<?php echo (int)$iep['iep_id']; ?>" data-student-id="<?php echo (int)($iep['student_id'] ?? 0); ?>">
                                        <?php echo htmlspecialchars($iep['student_name'] ?? 'Learner'); ?>
                                        (LRN: <?php echo htmlspecialchars($iep['lrn'] ?? 'N/A'); ?> &middot; SY <?php echo htmlspecialchars($iep['school_year'] ?? ''); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text text-muted" style="font-size:0.74rem;">
                                Ang bag-ong lesson plan ipahinungod sa napili nga estudyante ug ablihan dayon sa Lesson Builder.
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Privacy & Safety Notice -->
                    <div class="p-2.5 rounded border border-info-subtle bg-info-subtle text-dark small" style="font-size: 0.76rem; line-height: 1.45;">
                        <div class="d-flex align-items-start gap-2">
                            <i class="ph-bold ph-shield-check text-primary mt-0.5" style="font-size: 1.1rem;"></i>
                            <div>
                                <strong>Gipanalipdan ang Pribasiya:</strong>
                                Ang mga slides, mga litrato, ug ang gi-aprobahan nga FSL signs lamang ang kopyahon.
                                Walay personal nga impormasyon o grado sa orihinal nga estudyante ang maapil o mabutyag.
                            </div>
                        </div>
                    </div>
                </div>
                <div class="modal-footer border-top py-2.5 px-3.5 bg-light" style="border-radius: 0 0 12px 12px;">
                    <button type="button" class="btn btn-outline-secondary btn-sm fw-medium px-3" data-bs-dismiss="modal" style="border-radius: 6px;">
                        Kanselahon
                    </button>
                    <button type="submit" id="btnSubmitClone" class="btn btn-primary btn-sm fw-semibold px-3" style="border-radius: 6px;" <?php echo empty($myIeps) ? 'disabled' : ''; ?>>
                        <i class="ph-bold ph-copy me-1"></i> Clone &amp; Open in Builder
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
const BASE_URL = '<?php echo htmlspecialchars($basePath); ?>';
const PACK_PAGES = <?php echo json_encode($pages ?: []); ?>;

function showPreviewSlide(idx) {
    document.getElementById('previewSlideContainer').style.display = 'block';
    const actContainer = document.getElementById('previewActivitiesContainer');
    if (actContainer) actContainer.style.display = 'none';

    document.querySelectorAll('#slideNavList .slide-nav-btn').forEach(btn => btn.classList.remove('active'));
    const activeBtn = document.getElementById('btnSlideNav_' + idx);
    if (activeBtn) activeBtn.classList.add('active');

    const page = PACK_PAGES[idx];
    if (!page) return;

    let mediaHtml = '';
    if (page.media_path && page.media_type === 'image') {
        const fullSrc = page.media_path.startsWith('http') || page.media_path.startsWith('/') 
            ? page.media_path 
            : BASE_URL + '/' + page.media_path;
        mediaHtml = `
            <div class="text-center mb-4">
                <img src="${fullSrc}" alt="Slide Visual" class="img-fluid rounded border shadow-sm" style="max-height: 320px; object-fit: contain;">
            </div>
        `;
    }

    let questionsHtml = '';
    if (page.guide_questions && page.guide_questions.trim()) {
        questionsHtml = `
            <div class="p-3 mt-4 rounded border bg-light">
                <strong class="d-block text-dark small fw-bold mb-1">
                    <i class="ph-bold ph-question text-primary me-1"></i> Guide Questions / Panudlo:
                </strong>
                <div class="small text-muted" style="white-space: pre-line;">${escapeHtml(page.guide_questions)}</div>
            </div>
        `;
    }

    const container = document.getElementById('previewSlideContainer');
    container.innerHTML = `
        <div class="d-flex justify-content-between align-items-center pb-2 mb-3 border-bottom">
            <h4 class="fw-bold text-dark mb-0">${escapeHtml(page.title || 'Slide ' + (idx + 1))}</h4>
            <span class="badge bg-light text-muted border small">Slide ${idx + 1} of ${PACK_PAGES.length}</span>
        </div>
        ${mediaHtml}
        <div class="lesson-slide-content mb-3" style="font-size: 1.05rem; line-height: 1.7; color: #1e293b;">
            ${page.content || '<em class="text-muted">Walay sulod kini nga slide.</em>'}
        </div>
        ${questionsHtml}
    `;

    bindFslPreviewTriggers(container);
}

function showActivitiesView() {
    document.getElementById('previewSlideContainer').style.display = 'none';
    const actContainer = document.getElementById('previewActivitiesContainer');
    if (actContainer) actContainer.style.display = 'block';

    document.querySelectorAll('#slideNavList .slide-nav-btn').forEach(btn => btn.classList.remove('active'));
    const btnAct = document.getElementById('btnActivitiesNav');
    if (btnAct) btnAct.classList.add('active');
}

function bindFslPreviewTriggers(container) {
    const fslWords = container.querySelectorAll('.fsl-word');
    fslWords.forEach(span => {
        span.onclick = function(e) {
            e.preventDefault();
            e.stopPropagation();
            const word = this.dataset.word || this.textContent.trim();
            const video = this.dataset.video || '';
            const desc = this.dataset.desc || '';
            const category = this.dataset.category || 'General';

            if (typeof window.openFSLModal === 'function') {
                window.openFSLModal(word, {
                    video: video,
                    desc: desc,
                    category: category,
                    displayText: this.textContent.trim()
                });
            } else {
                alert('FSL Sign: ' + word + (video ? '\nVideo: ' + video : ''));
            }
        };
    });
}

function escapeHtml(str) {
    if (!str) return '';
    return str.replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
}

function openUseAsTemplateModal(packId, title) {
    const modal = new bootstrap.Modal(document.getElementById('useAsTemplateModal'));
    modal.show();
}

async function submitCloneForm(e) {
    e.preventDefault();
    const packId = document.getElementById('clonePackId').value;
    const select = document.getElementById('targetIepSelect');
    const targetIepId = select ? select.value : '';

    if (!targetIepId) {
        alert('Palihug pilia ang usa sa imong signed learner IEPs.');
        return;
    }

    const selectedOption = select.options[select.selectedIndex];
    const studentId = selectedOption ? selectedOption.dataset.studentId : null;

    const btn = document.getElementById('btnSubmitClone');
    const origHtml = btn.innerHTML;
    btn.disabled = true;
    btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Cloning...';

    try {
        const res = await fetch(`${BASE_URL}/iep/implementation/resource-hub/clone/${packId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                target_iep_id: targetIepId,
                target_student_id: studentId
            })
        });

        const data = await res.json();
        if (data.success && data.redirect) {
            window.location.href = data.redirect;
        } else {
            alert(data.message || 'Nabigo ang pag-clone sa template.');
            btn.disabled = false;
            btn.innerHTML = origHtml;
        }
    } catch (err) {
        console.error(err);
        alert('Adunay sayop sa koneksyon sa server.');
        btn.disabled = false;
        btn.innerHTML = origHtml;
    }
}

// Initialize on page load with Slide 0
document.addEventListener('DOMContentLoaded', () => {
    if (PACK_PAGES.length > 0) {
        showPreviewSlide(0);
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

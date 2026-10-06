<?php
// SignED — Accessibility Pack Resource Hub (MVP)
// Teacher-Facing Reusable Deaf-Accessible Lesson Library

$pageTitle = 'SignED Accessibility Pack Resource Hub';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<style>
/* Modern Resource Hub Styling */
.hub-header-card {
    background: linear-gradient(135deg, #1e4072 0%, #0f274a 100%);
    border-radius: 12px;
    color: #ffffff;
    padding: 1.75rem 2rem;
    box-shadow: 0 4px 16px rgba(15, 39, 74, 0.12);
    margin-bottom: 1.75rem;
}
.hub-filter-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    padding: 1.25rem 1.5rem;
    margin-bottom: 1.75rem;
    box-shadow: 0 1px 3px rgba(0,0,0,0.04);
}
.hub-pack-card {
    background: #ffffff;
    border: 1px solid #e2e8f0;
    border-radius: 10px;
    transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    display: flex;
    flex-direction: column;
    height: 100%;
    box-shadow: 0 1px 3px rgba(0,0,0,0.03);
}
.hub-pack-card:hover {
    border-color: #cbd5e1;
    transform: translateY(-2px);
    box-shadow: 0 8px 20px rgba(0,0,0,0.08);
}
.hub-pack-body {
    padding: 1.25rem 1.25rem 1rem;
    flex: 1 1 auto;
}
.hub-pack-footer {
    padding: 0.875rem 1.25rem;
    background: #f8fafc;
    border-top: 1px solid #f1f5f9;
    border-radius: 0 0 10px 10px;
}
.badge-fsl {
    background: #fef3c7;
    color: #92400e;
    font-weight: 600;
    font-size: 0.75rem;
    border-radius: 6px;
    padding: 0.25rem 0.55rem;
    display: inline-flex;
    align-items: center;
    gap: 0.3rem;
}
.badge-domain {
    background: #e0f2fe;
    color: #0369a1;
    font-weight: 600;
    font-size: 0.72rem;
    border-radius: 6px;
    padding: 0.25rem 0.5rem;
    text-transform: capitalize;
}
.badge-level {
    background: #f1f5f9;
    color: #475569;
    font-weight: 500;
    font-size: 0.72rem;
    border-radius: 6px;
    padding: 0.25rem 0.5rem;
}
.badge-pages {
    background: #f3e8ff;
    color: #6b21a8;
    font-weight: 600;
    font-size: 0.72rem;
    border-radius: 6px;
    padding: 0.25rem 0.5rem;
}
.btn-hub-action {
    font-size: 0.8125rem;
    font-weight: 500;
    border-radius: 6px;
    padding: 0.375rem 0.75rem;
    transition: all 0.15s ease;
}
</style>

<div class="main-content">
    <div class="container-fluid py-4">

        <!-- Top Notification Banner -->
        <?php if (!empty($_SESSION['success'])): ?>
            <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius:8px;">
                <i class="ph-bold ph-check-circle me-2"></i><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>
        <?php if (!empty($_SESSION['error'])): ?>
            <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert" style="border-radius:8px;">
                <i class="ph-bold ph-warning-circle me-2"></i><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?>
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            </div>
        <?php endif; ?>

        <!-- Hub Hero Header -->
        <div class="hub-header-card">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <div class="d-flex align-items-center gap-2 mb-2">
                        <span class="badge bg-warning text-dark fw-bold px-2.5 py-1" style="border-radius:6px; font-size:0.75rem;">
                            <i class="ph-bold ph-sparkle me-1"></i> Teacher Resource Hub
                        </span>
                        <span class="badge bg-white text-dark fw-semibold px-2 py-1" style="border-radius:6px; font-size:0.75rem;">
                            Deaf-Accessible &middot; FSL Packs
                        </span>
                    </div>
                    <h3 class="fw-bold mb-1 text-white">SignED Accessibility Pack Resource Hub</h3>
                    <p class="mb-0 text-white-50" style="font-size: 0.9rem; max-width: 720px;">
                        I-browse, sil-iba, ug i-clone ang mga gi-aprobahan nga accessible learning packs nga adunay Filipino Sign Language (FSL) nga suporta.
                        I-adaptar kini para sa IEP sa imong estudyante samtang nagpabiling protektado ang pribasiya.
                    </p>
                </div>
                <div class="d-flex gap-2">
                    <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation" class="btn btn-outline-light btn-sm fw-medium px-3" style="border-radius: 6px;">
                        <i class="ph-bold ph-arrow-left me-1"></i> IEP Workspace
                    </a>
                </div>
            </div>
        </div>

        <!-- Filter & Search Bar -->
        <div class="hub-filter-card">
            <form method="GET" action="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/resource-hub" class="row g-2 align-items-center">
                <div class="col-12 col-md-4">
                    <div class="input-group input-group-sm">
                        <span class="input-group-text bg-white text-muted border-end-0">
                            <i class="ph-bold ph-magnifying-glass"></i>
                        </span>
                        <input type="text" name="search" class="form-control border-start-0" placeholder="Pangitaa pinaagi sa titulo, competency, keywords..." value="<?php echo htmlspecialchars($search ?? ''); ?>" style="border-radius: 0 6px 6px 0;">
                    </div>
                </div>
                <div class="col-6 col-md-3">
                    <select name="domain" class="form-select form-select-sm" style="border-radius: 6px;">
                        <option value="">Tanan nga PDSP Domain</option>
                        <option value="perceptuo_cognitive" <?php echo ($selectedDomain ?? '') === 'perceptuo_cognitive' ? 'selected' : ''; ?>>Perceptuo-Cognitive</option>
                        <option value="communication_language" <?php echo ($selectedDomain ?? '') === 'communication_language' ? 'selected' : ''; ?>>Communication / Language</option>
                        <option value="psychomotor" <?php echo ($selectedDomain ?? '') === 'psychomotor' ? 'selected' : ''; ?>>Psychomotor</option>
                        <option value="psychosocial" <?php echo ($selectedDomain ?? '') === 'psychosocial' ? 'selected' : ''; ?>>Psychosocial</option>
                        <option value="socio_emotional" <?php echo ($selectedDomain ?? '') === 'socio_emotional' ? 'selected' : ''; ?>>Socio-Emotional</option>
                        <option value="daily_living_skills" <?php echo ($selectedDomain ?? '') === 'daily_living_skills' ? 'selected' : ''; ?>>Daily Living Skills</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <select name="level" class="form-select form-select-sm" style="border-radius: 6px;">
                        <option value="">Tanan nga Level</option>
                        <option value="Primary SPED / Deaf Ed" <?php echo ($selectedLevel ?? '') === 'Primary SPED / Deaf Ed' ? 'selected' : ''; ?>>Primary SPED</option>
                        <option value="Intermediate SPED" <?php echo ($selectedLevel ?? '') === 'Intermediate SPED' ? 'selected' : ''; ?>>Intermediate</option>
                        <option value="Transition / Life Skills" <?php echo ($selectedLevel ?? '') === 'Transition / Life Skills' ? 'selected' : ''; ?>>Transition</option>
                    </select>
                </div>
                <div class="col-6 col-md-2">
                    <div class="form-check form-check-inline mt-1 mb-0">
                        <input class="form-check-input" type="checkbox" name="fsl_only" id="filterFslOnly" value="1" <?php echo !empty($fslOnly) ? 'checked' : ''; ?>>
                        <label class="form-check-label small fw-semibold" for="filterFslOnly">
                            <i class="ph-bold ph-hands-clapping text-warning me-1"></i> FSL-Only
                        </label>
                    </div>
                </div>
                <div class="col-6 col-md-1 d-flex gap-1 justify-content-end">
                    <button type="submit" class="btn btn-primary btn-sm px-3 fw-medium w-100" style="border-radius: 6px;">
                        Filter
                    </button>
                    <?php if (!empty($search) || !empty($selectedDomain) || !empty($selectedLevel) || !empty($fslOnly)): ?>
                        <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/resource-hub" class="btn btn-light btn-sm" title="Clear Filters" style="border-radius: 6px;">
                            <i class="ph-bold ph-x"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </form>
        </div>

        <!-- Packs Grid -->
        <?php if (empty($packs)): ?>
            <div class="card border-0 shadow-sm text-center py-5 px-3" style="border-radius: 12px;">
                <div class="mb-3 text-muted" style="font-size: 3rem; color:#cbd5e1;">
                    <i class="ph-bold ph-folder-open"></i>
                </div>
                <h5 class="fw-bold text-dark mb-1">Walay Accessibility Pack nga Nakit-an</h5>
                <p class="text-muted small mb-3" style="max-width: 480px; margin: 0 auto;">
                    Wala pay shared accessibility pack nga motakdo sa imong search criteria. Pwede kang mo-share sa imong kaugalingong lesson gikan sa Lesson Builder o IEP Workspace!
                </p>
                <div>
                    <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation" class="btn btn-primary btn-sm px-3 fw-medium" style="border-radius: 6px;">
                        <i class="ph-bold ph-arrow-left me-1"></i> Balik sa Workspace
                    </a>
                </div>
            </div>
        <?php else: ?>
            <div class="d-flex justify-content-between align-items-center mb-3">
                <span class="text-muted small fw-medium">
                    Showing <strong><?php echo count($packs); ?></strong> accessible template pack<?php echo count($packs) > 1 ? 's' : ''; ?>
                </span>
            </div>

            <div class="row g-3">
                <?php foreach ($packs as $pack): ?>
                    <?php
                    $isAuthor = ((int)($pack['created_by'] ?? 0) === (int)$_SESSION['user_id']) || ($userRole === 'admin');
                    $domainLabels = [
                        'perceptuo_cognitive'    => 'Perceptuo-Cognitive',
                        'communication_language' => 'Communication & Language',
                        'psychomotor'            => 'Psychomotor',
                        'psychosocial'           => 'Psychosocial',
                        'socio_emotional'        => 'Socio-Emotional',
                        'daily_living_skills'    => 'Daily Living Skills'
                    ];
                    $domainPretty = $domainLabels[$pack['pdsp_domain']] ?? ucfirst(str_replace('_', ' ', $pack['pdsp_domain']));
                    ?>
                    <div class="col-12 col-md-6 col-xl-4">
                        <div class="hub-pack-card">
                            <div class="hub-pack-body">
                                <!-- Badges Row -->
                                <div class="d-flex flex-wrap gap-1.5 align-items-center mb-2.5">
                                    <span class="badge-domain"><?php echo htmlspecialchars($domainPretty); ?></span>
                                    <span class="badge-level"><?php echo htmlspecialchars($pack['learning_level'] ?? 'SPED'); ?></span>
                                    <?php if (!empty($pack['fsl_supported'])): ?>
                                        <span class="badge-fsl" title="Teacher-reviewed FSL signs included">
                                            <i class="ph-bold ph-hands-clapping"></i>
                                            <?php echo (int)($pack['fsl_sign_count'] ?? 0); ?> FSL Sign<?php echo ((int)$pack['fsl_sign_count'] === 1 ? '' : 's'); ?>
                                        </span>
                                    <?php endif; ?>
                                    <span class="badge-pages">
                                        <i class="ph-bold ph-cards me-0.5"></i> <?php echo (int)($pack['page_count'] ?? 1); ?> Slide<?php echo ((int)$pack['page_count'] === 1 ? '' : 's'); ?>
                                    </span>
                                </div>

                                <!-- Title -->
                                <h5 class="fw-bold text-dark mb-1.5" style="font-size: 1.05rem; line-height: 1.35;">
                                    <?php echo htmlspecialchars($pack['title']); ?>
                                </h5>

                                <!-- Author & Date -->
                                <div class="text-muted small mb-2.5" style="font-size: 0.78rem;">
                                    <i class="ph-bold ph-user me-1"></i>
                                    Gikan ni: <strong><?php echo htmlspecialchars($pack['author_name'] ?? 'SPED Teacher'); ?></strong>
                                    &middot; <?php echo date('M j, Y', strtotime($pack['created_at'])); ?>
                                </div>

                                <!-- Learning Objective / Competency -->
                                <?php if (!empty($pack['learning_competency'])): ?>
                                    <div class="p-2 mb-2.5 rounded bg-light border" style="font-size: 0.78rem; color:#334155; line-height:1.4;">
                                        <strong class="d-block text-muted text-uppercase mb-0.5" style="font-size:0.68rem; letter-spacing:0.5px;">
                                            <i class="ph-bold ph-target me-1"></i> Learning Competency:
                                        </strong>
                                        <?php echo htmlspecialchars(mb_strimwidth($pack['learning_competency'], 0, 140, '...')); ?>
                                    </div>
                                <?php endif; ?>

                                <!-- Description Snippet -->
                                <?php if (!empty($pack['description'])): ?>
                                    <p class="text-muted mb-2.5" style="font-size: 0.8125rem; line-height: 1.45;">
                                        <?php echo htmlspecialchars(mb_strimwidth($pack['description'], 0, 120, '...')); ?>
                                    </p>
                                <?php endif; ?>

                                <!-- Optional Mainstream Mastery Link -->
                                <?php if (!empty($pack['mainstream_mastery_url'])): ?>
                                    <div class="mt-2 pt-2 border-top">
                                        <a href="<?php echo htmlspecialchars($pack['mainstream_mastery_url']); ?>" target="_blank" rel="noopener noreferrer" class="d-inline-flex align-items-center gap-1 text-decoration-none small text-primary fw-semibold" style="font-size: 0.76rem;">
                                            <i class="ph-bold ph-arrow-square-out"></i>
                                            External Mastery: <?php echo htmlspecialchars($pack['mainstream_mastery_title'] ?: 'Online Resource'); ?>
                                        </a>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <!-- Footer Actions -->
                            <div class="hub-pack-footer d-flex justify-content-between align-items-center gap-2">
                                <div class="small text-muted" style="font-size: 0.75rem;">
                                    <i class="ph-bold ph-copy-simple me-0.5"></i>
                                    <strong><?php echo (int)($pack['clone_count'] ?? 0); ?></strong> adapted
                                </div>

                                <div class="d-flex align-items-center gap-1.5">
                                    <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/resource-hub/preview/<?php echo (int)$pack['id']; ?>" class="btn btn-outline-secondary btn-hub-action" title="Preview Lesson Content & Signs">
                                        <i class="ph-bold ph-eye me-1"></i> Preview
                                    </a>
                                    <button type="button" class="btn btn-primary btn-hub-action" onclick="openUseAsTemplateModal(<?php echo (int)$pack['id']; ?>, <?php echo htmlspecialchars(json_encode($pack['title'])); ?>)">
                                        <i class="ph-bold ph-copy me-1"></i> Use as Template
                                    </button>
                                    <?php if ($isAuthor): ?>
                                        <button type="button" class="btn btn-outline-danger btn-hub-action px-2" title="Archive / Unshare from Hub" onclick="unsharePack(<?php echo (int)$pack['id']; ?>)">
                                            <i class="ph-bold ph-trash"></i>
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        <?php endif; ?>

    </div><!-- /container-fluid -->
</div><!-- /main-content -->

<!-- Use as Template / Clone Modal -->
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
                    <input type="hidden" id="clonePackId" name="pack_id" value="">

                    <div class="mb-3">
                        <label class="form-label text-muted small fw-semibold mb-1">Selected Accessibility Pack:</label>
                        <div id="clonePackTitleDisplay" class="fw-bold text-dark p-2 rounded bg-light border" style="font-size: 0.9rem;">
                            <!-- Populated dynamically -->
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

function openUseAsTemplateModal(packId, title) {
    document.getElementById('clonePackId').value = packId;
    document.getElementById('clonePackTitleDisplay').textContent = title;
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

async function unsharePack(packId) {
    if (!confirm('Sigurado ka ba nga gusto nimong i-archive/i-unshare kini nga Accessibility Pack gikan sa Resource Hub?')) {
        return;
    }

    try {
        const res = await fetch(`${BASE_URL}/iep/implementation/resource-hub/unshare/${packId}`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' }
        });
        const data = await res.json();
        if (data.success) {
            alert(data.message);
            window.location.reload();
        } else {
            alert(data.message || 'Nabigo ang pag-archive.');
        }
    } catch (err) {
        console.error(err);
        alert('Server error while unsharing.');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

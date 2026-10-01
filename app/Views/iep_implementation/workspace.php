<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 6
// Last modified: 2026-05-13
// Part of: SignED — IEP Implementation Workspace

$pageTitle = 'IEP Workspace — ' . htmlspecialchars($iep['student_name'] ?? 'Student') . ' — SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<!-- Summernote Lite (Rich Text / WYSIWYG Editor for Lesson Pages) -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>
<style>
.note-editor.note-frame {
    border-radius: 8px !important;
    border-color: #cbd5e1 !important;
    box-shadow: none !important;
}
.note-toolbar {
    background-color: #f8fafc !important;
    border-bottom: 1px solid #e2e8f0 !important;
    border-top-left-radius: 8px !important;
    border-top-right-radius: 8px !important;
    padding: 6px !important;
}
.note-btn {
    border-radius: 4px !important;
    font-size: 0.8rem !important;
}
</style>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
<div class="container-fluid py-3">

    <?php
    $iepLinkedLessonPlanIds = $iepLinkedLessonPlanIds ?? [];
    $iepId = (int) ($iep['id'] ?? 0);
    $navActive = 'workspace';
    $showWorkspaceLink = true;
    require __DIR__ . '/../iep/partials/iep_p5_p6_nav_bar.php';
    ?>

    <!-- ============================================================
         PAGE HEADER
         ============================================================ -->
    <div class="d-flex align-items-center gap-2 mb-3 flex-wrap">
        <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation"
           class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;flex-shrink:0;">
            <i class="ti ti-arrow-left me-1"></i>Back
        </a>
        <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/progress-tracker"
           class="btn btn-sm btn-outline-secondary flex-shrink-0">
            <i class="ti ti-bar-chart-line me-1"></i>Progress tracker
        </a>
        <div class="flex-grow-1 min-width-0">
            <h4 class="mb-0 fw-bold" style="color:#1e4072;">
                IEP Implementation &mdash; <?php echo htmlspecialchars($iep['student_name']); ?>
            </h4>
        </div>
        <span class="badge" style="background:#1e4072;font-size:0.78rem;padding:6px 12px;flex-shrink:0;">
            <i class="ti ti-calendar me-1"></i><?php echo htmlspecialchars($iep['school_year']); ?>
        </span>
    </div>


    <!-- ============================================================
         STAGE 2 DUAL-TRACK STATUS & TRADITIONAL IEP RECORD-KEEPING
         ============================================================ -->
    <?php 
        $currentTrack = $studentTrackInfo['learning_track'] ?? 'unassigned';
        $isTraditional = ($currentTrack === 'traditional');
    ?>
    <div class="card mb-4 border-<?= $isTraditional ? 'secondary' : 'primary' ?> shadow-sm">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background: <?= $isTraditional ? '#475569' : '#1e4072' ?>; color:#fff; border-radius:6px 6px 0 0;">
            <span class="fw-semibold">
                <i class="bi bi-<?= $isTraditional ? 'folder2-open' : 'laptop-fill' ?> me-2"></i>
                Dual-Track Mode: <?= $isTraditional ? '🏫 SEN Traditional F2F Track (Record-Keeping Active)' : '💻 SignED Interactive LMS Track' ?>
            </span>
            <button type="button" class="btn btn-sm btn-light py-1 px-2 fw-semibold" data-bs-toggle="modal" data-bs-target="#uploadTraditionalDocModal">
                <i class="bi bi-cloud-arrow-up-fill me-1"></i> Upload Traditional Document (DLL / Physical IEP)
            </button>
        </div>
        <div class="card-body p-3 bg-light">
            <?php if (empty($traditionalDocs)): ?>
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 text-muted small">
                    <span><i class="bi bi-info-circle me-1"></i>No uploaded traditional Daily Lesson Logs (DLL) or physical IEP forms yet for this learner.</span>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-bs-toggle="modal" data-bs-target="#uploadTraditionalDocModal">
                        <i class="bi bi-plus-circle me-1"></i>Add DLL / Scanned Form
                    </button>
                </div>
            <?php else: ?>
                <h6 class="fw-bold text-dark mb-2" style="font-size:0.875rem;"><i class="bi bi-file-earmark-medical me-1 text-primary"></i>Uploaded Traditional Documents &amp; Lesson Logs (DLL):</h6>
                <div class="row g-2">
                    <?php foreach ($traditionalDocs as $td): ?>
                        <div class="col-md-6 col-lg-4">
                            <div class="card bg-white p-2 border rounded shadow-sm h-100">
                                <div class="d-flex align-items-start justify-content-between gap-1">
                                    <div>
                                        <span class="badge bg-<?= $td['document_type'] === 'dll' ? 'primary' : 'secondary' ?> text-uppercase" style="font-size:0.7rem;">
                                            <?= htmlspecialchars($td['document_type']) ?> &bull; <?= htmlspecialchars($td['quarter'] ?? 'Q1') ?>
                                        </span>
                                        <h6 class="fw-bold mb-1 mt-1 text-dark" style="font-size:0.85rem;"><?= htmlspecialchars($td['title']) ?></h6>
                                        <div class="small text-muted" style="font-size:0.75rem;">
                                            <i class="bi bi-person me-1"></i><?= htmlspecialchars($td['uploaded_by_name'] ?? 'Teacher') ?> &bull; <?= date('M j, Y', strtotime($td['created_at'])) ?>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-1">
                                        <a href="<?= $basePath . '/' . ltrim($td['file_path'], '/') ?>" target="_blank" class="btn btn-sm btn-outline-primary py-0 px-1" title="View Document">
                                            <i class="bi bi-eye"></i>
                                        </a>
                                        <form method="POST" action="<?= $basePath ?>/iep/implementation/traditional-doc/delete/<?= $td['id'] ?>" onsubmit="return confirm('Remove this document?');">
                                            <input type="hidden" name="iep_id" value="<?= $iepId ?>">
                                            <button type="submit" class="btn btn-sm btn-outline-danger py-0 px-1" title="Delete">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </form>
                                    </div>
                                </div>
                                <?php if (!empty($td['notes'])): ?>
                                    <div class="mt-1 pt-1 border-top text-muted small" style="font-size:0.75rem;">
                                        <em><?= htmlspecialchars($td['notes']) ?></em>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upload Traditional Document Modal -->
    <div class="modal fade" id="uploadTraditionalDocModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content text-dark">
                <form method="POST" action="<?= $basePath ?>/iep/implementation/traditional-doc/upload" enctype="multipart/form-data">
                    <input type="hidden" name="iep_id" value="<?= $iepId ?>">
                    <input type="hidden" name="student_id" value="<?= (int)($iep['student_id'] ?? 0) ?>">
                    <div class="modal-header bg-light">
                        <h6 class="modal-title fw-bold"><i class="bi bi-cloud-arrow-up-fill me-2 text-primary"></i>Upload Traditional IEP / DLL Document</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Document Type <span class="text-danger">*</span></label>
                            <select name="document_type" class="form-select" required>
                                <option value="dll">Daily Lesson Log (DLL)</option>
                                <option value="physical_iep">Physical / Scanned Signed IEP Form</option>
                                <option value="assessment_report">Assessment / Progress Report</option>
                                <option value="progress_note">Progress Note / Offline Activity Sheet</option>
                                <option value="other">Other Supporting Document</option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Title / Description <span class="text-danger">*</span></label>
                            <input type="text" name="title" class="form-control" placeholder="e.g. DLL Week 3 - Functional Academics" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Quarter</label>
                                <select name="quarter" class="form-select">
                                    <option value="Q1">Quarter 1</option>
                                    <option value="Q2">Quarter 2</option>
                                    <option value="Q3">Quarter 3</option>
                                    <option value="Q4">Quarter 4</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">File (PDF, Docx, Image) <span class="text-danger">*</span></label>
                                <input type="file" name="doc_file" class="form-control" accept=".pdf,.doc,.docx,.jpg,.jpeg,.png" required>
                            </div>
                        </div>
                        <div class="mb-2">
                            <label class="form-label fw-semibold">Notes / Objectives</label>
                            <textarea name="notes" class="form-control" rows="2" placeholder="Optional notes..."></textarea>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Upload Document</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- ====================================================
         SECTION 1 — LESSON PLANS
         ==================================================== -->
    <div class="card mb-4" id="sectionLessonPlans">
        <div class="card-header d-flex justify-content-between align-items-center flex-wrap gap-2"
             style="background:#1e4072;color:#fff;border-radius:6px 6px 0 0;">
            <span class="fw-semibold">
                <i class="ti ti-book me-2"></i>Lesson Plans
            </span>
        </div>
        <div class="card-body p-3">

            <?php if (empty($lessonPlans)): ?>
                <p class="text-muted small mb-0">
                    No lesson plans yet. Add them from the <a href="<?php echo htmlspecialchars($basePath); ?>/iep/form/<?php echo (int)$iepId; ?>">IEP form</a> (IEP steps) or publish plans you create elsewhere for this learner.
                </p>
            <?php else: ?>
                <!-- Lesson plan cards -->
                <div class="row g-3" id="lessonPlansList">
                <?php foreach ($lessonPlans as $lp): ?>
                    <?php
                    $lpId     = (int)$lp['id'];
                    $lpStatus = $lp['status'] ?? 'draft';
                    $lpDomain = $lp['pdsp_domain'] ?? '';
                    $lpDoc    = $lp['document_path'] ?? '';

                    $domainLabels = [
                        'perceptuo_cognitive'    => 'Perceptuo-Cognitive',
                        'psychosocial'           => 'Psychosocial',
                        'socio_emotional'        => 'Socio-Emotional',
                        'psychomotor'            => 'Psychomotor',
                        'daily_living_skills'    => 'Daily Living Skills',
                        'communication_language' => 'Communication & Language',
                    ];
                    $domainLabel = $domainLabels[$lpDomain] ?? ucwords(str_replace('_', ' ', $lpDomain));

                    $lpMaterialCount = 0;
                    $lpActivityCount = 0;
                    foreach ($materials as $m) {
                        if ((int)$m['lesson_plan_id'] === $lpId) $lpMaterialCount++;
                    }
                    foreach ($activities as $a) {
                        if ((int)$a['lesson_plan_id'] === $lpId) $lpActivityCount++;
                    }
                    $iepLpLocked = !empty($iepLinkedLessonPlanIds) && in_array($lpId, $iepLinkedLessonPlanIds, true);
                    ?>
                    <div class="col-lg-6" id="lp-<?php echo $lpId; ?>">
                        <div class="card h-100" style="border-left:4px solid <?php echo $lpStatus === 'published' ? '#3b6d11' : '#5a6670'; ?>;">
                            <div class="card-body pb-2">
                                <div class="d-flex justify-content-between align-items-start gap-2 mb-2 flex-wrap">
                                    <h6 class="fw-bold mb-0 flex-grow-1" style="color:#1e4072;font-size:0.95rem;">
                                        <?php echo htmlspecialchars($lp['title']); ?>
                                    </h6>
                                    <div class="d-flex flex-wrap gap-1 align-items-center justify-content-end">
                                        <?php if ($iepLpLocked): ?>
                                            <span class="badge" style="background:#1e4072;color:#fff;font-size:0.65rem;">From IEP</span>
                                        <?php endif; ?>
                                        <?php if ($lpStatus === 'published'): ?>
                                            <span class="badge" style="background:#3b6d11;font-size:0.7rem;">
                                                <i class="ti ti-circle-check me-1"></i>Published
                                            </span>
                                        <?php else: ?>
                                            <span class="badge" style="background:#5a6670;color:#fff;font-size:0.7rem;">Draft</span>
                                        <?php endif; ?>
                                    </div>
                                </div>

                                <div class="d-flex flex-wrap gap-1 mb-2">
                                    <span class="badge" style="background:#1e4072;font-size:0.7rem;">
                                        <?php echo htmlspecialchars($domainLabel); ?>
                                    </span>
                                    <?php if ($lpDoc): ?>
                                        <span class="badge" style="background:#3b6d11;font-size:0.7rem;">
                                            <i class="ti ti-circle-check me-1"></i>Doc uploaded
                                        </span>
                                    <?php endif; ?>
                                </div>

                                <div class="d-flex gap-3 mb-3 small text-muted">
                                    <span><i class="ti ti-files me-1"></i><?php echo $lpMaterialCount; ?> material<?php echo $lpMaterialCount !== 1 ? 's' : ''; ?></span>
                                    <span><i class="ti ti-activity me-1"></i><?php echo $lpActivityCount; ?> activit<?php echo $lpActivityCount !== 1 ? 'ies' : 'y'; ?></span>
                                </div>

                                <div class="d-flex flex-wrap gap-1">
                                    <?php if ($iepLpLocked): ?>
                                        <p class="small text-muted w-100 mb-1" style="border-left:3px solid #1e4072;padding-left:8px;">
                                            Linked from the IEP step table. Deleting removes the plan here; unlink on the IEP form if the step should stay without this plan.
                                        </p>
                                    <?php endif; ?>
                                    <?php if (!$lpDoc): ?>
                                        <button class="btn btn-sm"
                                                style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="openUploadDocModal(<?php echo $lpId; ?>, '<?php echo htmlspecialchars(addslashes($lp['title'])); ?>')">
                                            <i class="ti ti-upload me-1"></i>Upload Doc
                                        </button>
                                    <?php endif; ?>
                                    <?php if ($lpStatus === 'draft'): ?>
                                        <button class="btn btn-sm"
                                                style="background:#3b6d11;color:#fff;border:none;font-size:0.75rem;"
                                                onclick="confirmPublish(<?php echo $lpId; ?>, '<?php echo htmlspecialchars(addslashes($lp['title'])); ?>')">
                                            <i class="ti ti-send me-1"></i>Publish
                                        </button>
                                    <?php endif; ?>
                                    <button class="btn btn-sm"
                                            style="background:#a01422;color:#fff;border:none;font-size:0.75rem;"
                                            onclick="confirmDeleteLessonPlan(<?php echo $lpId; ?>, '<?php echo htmlspecialchars(addslashes($lp['title'])); ?>')">
                                        <i class="ti ti-trash me-1"></i>Delete
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
                </div>
            <?php endif; ?>

        </div>
    </div><!-- /Section 1 -->


    <!-- ====================================================
         SECTION 2 — LEARNING MATERIALS
         ==================================================== -->
    <div class="card mb-4" id="sectionMaterials">
        <div class="card-header" style="background:#1e4072;color:#fff;border-radius:6px 6px 0 0;">
            <span class="fw-semibold"><i class="ti ti-files me-2"></i>Learning Materials</span>
        </div>
        <div class="card-body p-3">

            <!-- Material type selector grid -->
            <div class="row g-3 mb-4">
                <div class="col-md-6 col-lg-3">
                    <div class="material-type-card" id="matTypeFile" onclick="openMaterialModal('file')"
                         tabindex="0" role="button" aria-label="Upload File">
                        <div class="material-type-icon">📁</div>
                        <div class="material-type-title">Upload File</div>
                        <div class="material-type-desc">Upload a document, image, or video</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="material-type-card" id="matTypeLink" onclick="openMaterialModal('link')"
                         tabindex="0" role="button" aria-label="External Link">
                        <div class="material-type-icon">🔗</div>
                        <div class="material-type-title">External Link</div>
                        <div class="material-type-desc">Add a URL to an external resource</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="material-type-card" id="matTypeEmbed" onclick="openMaterialModal('embed')"
                         tabindex="0" role="button" aria-label="Embed Content">
                        <div class="material-type-icon">▶</div>
                        <div class="material-type-title">Embed</div>
                        <div class="material-type-desc">Embed YouTube or Google Drive content</div>
                    </div>
                </div>
                <div class="col-md-6 col-lg-3">
                    <div class="material-type-card" id="matTypeInteractive" onclick="openMaterialModal('interactive')"
                         tabindex="0" role="button" aria-label="Interactive Lesson Slides" style="border-color:#0284c7;">
                        <div class="material-type-icon">📑</div>
                        <div class="material-type-title" style="color:#0284c7;">Interactive Lesson</div>
                        <div class="material-type-desc">Custom multi-page slides, text, &amp; media</div>
                    </div>
                </div>
            </div>

            <!-- Materials list -->
            <?php if (empty($materials)): ?>
                <div class="text-center py-4" id="materialsEmptyState">
                    <i class="ti ti-files-off" style="font-size:2.5rem;color:#ccc;"></i>
                    <p class="text-muted small mt-2 mb-0">No materials added yet.</p>
                </div>
            <?php endif; ?>

            <div id="materialsList" <?php echo empty($materials) ? 'style="display:none;"' : ''; ?>>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.85rem;">
                        <thead style="background:#1e4072;color:#fff;">
                            <tr>
                                <th style="width:36px;"></th>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Lesson Plan</th>
                                <th style="min-width:200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="materialsTableBody">
                        <?php foreach ($materials as $mat): ?>
                            <?php
                            $matType = $mat['material_type'] ?? 'file';
                            $isInteractive = ($matType === 'interactive' || !empty($mat['is_interactive']));
                            $matIcon = $isInteractive ? '📑' : ($matType === 'file' ? '📁' : ($matType === 'link' ? '🔗' : '▶'));
                            $matBg   = $isInteractive ? '#0284c7' : ($matType === 'file' ? '#1e4072' : ($matType === 'link' ? '#6c757d' : '#a01422'));
                            $rowId   = htmlspecialchars((string)$mat['id']);
                            ?>
                            <tr id="matRow_<?php echo $rowId; ?>">
                                <td class="text-center"><?php echo $matIcon; ?></td>
                                <td class="fw-semibold">
                                    <?php if ($isInteractive): ?>
                                        <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/lesson/<?php echo (int)$mat['lesson_plan_id']; ?>/builder" class="text-decoration-none text-dark d-flex align-items-center gap-1">
                                            <span><?php echo htmlspecialchars($mat['title']); ?></span>
                                            <span class="badge bg-light text-primary border" style="font-size:0.65rem;">Open Studio</span>
                                        </a>
                                    <?php else: ?>
                                        <a href="javascript:void(0)" class="text-decoration-none text-dark" onclick="viewMaterial(<?php echo htmlspecialchars(json_encode($mat), ENT_QUOTES); ?>)">
                                            <?php echo htmlspecialchars($mat['title']); ?>
                                        </a>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <span class="badge" style="background:<?php echo $matBg; ?>;font-size:0.7rem;">
                                        <?php echo ucfirst(htmlspecialchars($matType)); ?>
                                    </span>
                                </td>
                                <td class="text-muted"><?php echo htmlspecialchars($mat['lesson_plan_title'] ?? '—'); ?></td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <?php if ($isInteractive): ?>
                                            <a href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo (int)$mat['lesson_plan_id']; ?>" target="_blank" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem; border-radius:6px;">
                                                <i class="ti ti-eye me-1"></i>View
                                            </a>
                                            <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/lesson/<?php echo (int)$mat['lesson_plan_id']; ?>/builder" class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;font-size:0.75rem; border-radius:6px;">
                                                <i class="ti ti-pencil me-1"></i>Edit Slides
                                            </a>
                                            <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.75rem; border-radius:6px;"
                                                    onclick="confirmDeleteMaterial('<?php echo $rowId; ?>', '<?php echo htmlspecialchars(addslashes($mat['title'])); ?>')">
                                                <i class="ti ti-trash me-1"></i>Delete
                                            </button>
                                        <?php else: ?>
                                            <button class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem; border-radius:6px;"
                                                    onclick="viewMaterial(<?php echo htmlspecialchars(json_encode($mat), ENT_QUOTES); ?>)">
                                                <i class="ti ti-eye me-1"></i>View
                                            </button>
                                            <button class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;font-size:0.75rem; border-radius:6px;"
                                                    onclick="openEditMaterial(<?php echo htmlspecialchars(json_encode($mat), ENT_QUOTES); ?>)">
                                                <i class="ti ti-pencil me-1"></i>Edit
                                            </button>
                                            <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.75rem; border-radius:6px;"
                                                    onclick="confirmDeleteMaterial('<?php echo $rowId; ?>', '<?php echo htmlspecialchars(addslashes($mat['title'])); ?>')">
                                                <i class="ti ti-trash me-1"></i>Delete
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div><!-- /Section 2 -->


    <!-- ====================================================
         SECTION 3 — ACTIVITIES
         ==================================================== -->
    <div class="card mb-4" id="sectionActivities">
        <div class="card-header" style="background:#1e4072;color:#fff;border-radius:6px 6px 0 0;">
            <span class="fw-semibold"><i class="ti ti-activity me-2"></i>Activities</span>
        </div>
        <div class="card-body p-3">

            <div class="d-flex justify-content-between align-items-center mb-3">
                <h6 class="fw-semibold mb-0" style="color:#1e4072;">Select an activity type to build:</h6>
                <button class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;" onclick="openImportActivityModal()">
                    <i class="ti ti-file-import me-1"></i>Import from CSV
                </button>
            </div>

            <!-- Activity type grid -->
            <div class="row g-2 mb-3" id="activityTypeGrid">
                <?php
                $activityTypes = [
                    ['key' => 'multiple_choice', 'icon' => 'ti-list-check',      'label' => 'Multiple Choice',   'desc' => 'Pick the correct answer'],
                    ['key' => 'true_false',      'icon' => 'ti-check',            'label' => 'True / False',      'desc' => 'True or false statement'],
                    ['key' => 'fill_in_blanks',  'icon' => 'ti-forms',            'label' => 'Fill in the Blanks','desc' => 'Complete the sentence'],
                    ['key' => 'matching',        'icon' => 'ti-arrows-exchange',  'label' => 'Matching',          'desc' => 'Match items together'],
                    ['key' => 'drag_drop_sort',  'icon' => 'ti-drag-drop',        'label' => 'Drag & Drop Sort',  'desc' => 'Arrange in correct order'],
                    ['key' => 'image_label',     'icon' => 'ti-photo',            'label' => 'Image Label',       'desc' => 'Label parts of an image'],
                    ['key' => 'flashcards',      'icon' => 'ti-cards',            'label' => 'Flashcards',        'desc' => 'Flip and learn'],
                    ['key' => 'sequencing',      'icon' => 'ti-sort-ascending',   'label' => 'Sequencing',        'desc' => 'Put events in order'],
                ];
                foreach ($activityTypes as $at):
                ?>
                <div class="col-6 col-md-3">
                    <div class="activity-type-card" id="actCard_<?php echo $at['key']; ?>"
                         data-type="<?php echo $at['key']; ?>"
                         onclick="selectActivityType('<?php echo $at['key']; ?>')"
                         tabindex="0" role="button">
                        <i class="ti <?php echo $at['icon']; ?> activity-type-icon"></i>
                        <div class="activity-type-label"><?php echo $at['label']; ?></div>
                        <div class="activity-type-desc"><?php echo $at['desc']; ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Activity Builder (slides in below grid) -->
            <div id="activityBuilder" style="display:none;overflow:hidden;">
                <div class="activity-builder-inner p-3 border rounded mb-3"
                     style="border-color:#1e4072 !important;background:#f8f9fa;">

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <h6 class="fw-bold mb-0" style="color:#1e4072;">
                            <i class="ti ti-pencil me-1"></i>
                            <span id="builderTypeLabel">Activity Builder</span>
                        </h6>
                        <div class="d-flex align-items-center gap-2">
                            <a href="#" id="builderLearnerPreviewBtn" target="_blank" class="btn btn-sm d-none align-items-center gap-1" style="background:#6c5ce7;color:#fff;border:none;font-size:0.75rem;border-radius:6px;font-weight:600;text-decoration:none;">
                                <i class="ti ti-device-gamepad"></i><span>Preview as Learner</span>
                            </a>
                            <button class="btn btn-sm btn-outline-secondary" onclick="closeActivityBuilder()" style="border-radius:6px;font-size:0.75rem;">
                                <i class="ti ti-x me-1"></i>Cancel
                            </button>
                        </div>
                    </div>

                    <!-- Common fields -->
                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Lesson Plan <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="builderLessonPlan" required>
                                <option value="">— Select lesson plan —</option>
                                <?php foreach ($lessonPlans as $lp): ?>
                                    <option value="<?php echo (int)$lp['id']; ?>">
                                        <?php echo htmlspecialchars($lp['title']); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label small fw-semibold">Activity Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="builderTitle" placeholder="Enter title" required>
                        </div>
                        <div class="col-12">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <label class="form-label small fw-semibold mb-0">Instructions / Mga Panuto</label>
                                <span class="badge bg-light text-primary border" style="font-size:0.7rem;"><i class="ti ti-typography me-1"></i>WYSIWYG: Tables &amp; Images Supported</span>
                            </div>
                            <textarea class="form-control form-control-sm" id="builderInstructions" rows="3"
                                      placeholder="Instructions for the learner (tables, formatted text, and images supported)..."></textarea>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label small fw-semibold">Due Date <span class="text-muted">(optional)</span></label>
                            <input type="date" class="form-control form-control-sm" id="builderDueDate">
                        </div>
                        <div class="col-md-4" id="builderMaxScoreWrap">
                            <label class="form-label small fw-semibold">Max Score</label>
                            <input type="number" class="form-control form-control-sm" id="builderMaxScore"
                                   min="0" value="10" placeholder="10">
                        </div>
                        <div class="col-md-4 d-flex align-items-end mb-1">
                            <div class="form-check form-switch mb-1">
                                <input class="form-check-input" type="checkbox" id="builderIsF2F" onchange="toggleF2FFields()">
                                <label class="form-check-label small fw-semibold" for="builderIsF2F">Face-to-Face / Direct Observation</label>
                            </div>
                        </div>
                    </div>

                    <!-- Type-specific builder area -->
                    <div id="builderTypeArea"></div>

                    <div class="d-flex gap-2 mt-3">
                        <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;"
                                onclick="saveActivity()">
                            <i class="ti ti-device-floppy me-1"></i>Save Activity
                        </button>
                        <button class="btn btn-sm btn-outline-secondary" onclick="closeActivityBuilder()">
                            Cancel
                        </button>
                    </div>
                </div>
            </div>

            <!-- Activities list -->
            <?php if (empty($activities)): ?>
                <div class="text-center py-4" id="activitiesEmptyState">
                    <i class="ti ti-mood-empty" style="font-size:2.5rem;color:#ccc;"></i>
                    <p class="text-muted small mt-2 mb-0">No activities added yet.</p>
                </div>
            <?php endif; ?>

            <div id="activitiesList" <?php echo empty($activities) ? 'style="display:none;"' : ''; ?>>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.85rem;">
                        <thead style="background:#1e4072;color:#fff;">
                            <tr>
                                <th>Title</th>
                                <th>Type</th>
                                <th>Lesson Plan</th>
                                <th>Due Date</th>
                                <th>Max Score</th>
                                <th style="min-width:200px;">Actions</th>
                            </tr>
                        </thead>
                        <tbody id="activitiesTableBody">
                        <?php foreach ($activities as $act): ?>
                            <?php
                            $actType = $act['activity_type'] ?? '';
                            $actTypeColors = [
                                'multiple_choice' => '#1e4072',
                                'true_false'      => '#3b6d11',
                                'fill_in_blanks'  => '#a01422',
                                'matching'        => '#6c757d',
                                'drag_drop_sort'  => '#e67e22',
                                'image_label'     => '#8e44ad',
                                'flashcards'      => '#2980b9',
                                'sequencing'      => '#16a085',
                            ];
                            $actColor = $actTypeColors[$actType] ?? '#6c757d';
                            $actTypeLabel = ucwords(str_replace('_', ' ', $actType));
                            ?>
                            <tr id="actRow_<?php echo (int)$act['id']; ?>">
                                <td class="fw-semibold"><?php echo htmlspecialchars($act['title']); ?></td>
                                <td>
                                    <span class="badge" style="background:<?php echo $actColor; ?>;font-size:0.7rem;">
                                        <?php echo htmlspecialchars($actTypeLabel); ?>
                                    </span>
                                    <?php if (!empty($act['is_f2f'])): ?>
                                        <span class="badge bg-success ms-1" style="font-size:0.7rem;">F2F</span>
                                    <?php endif; ?>
                                </td>
                                <td class="text-muted"><?php echo htmlspecialchars($act['lesson_plan_title'] ?? '—'); ?></td>
                                <td class="text-muted"><?php echo $act['due_date'] ? htmlspecialchars($act['due_date']) : '—'; ?></td>
                                <td><?php echo !empty($act['is_f2f']) ? '—' : (int)($act['max_score'] ?? 0); ?></td>
                                <td>
                                    <div class="d-flex gap-1 flex-wrap">
                                        <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/learning/activity/<?php echo (int)$act['id']; ?>"
                                           target="_blank"
                                           class="btn btn-sm"
                                           style="background:#6c5ce7;color:#fff;border:none;font-size:0.75rem; border-radius:6px; text-decoration:none; display:inline-flex; align-items:center;"
                                           title="Open Learner's Interactive Preview">
                                            <i class="ti ti-device-gamepad me-1"></i>Preview
                                        </a>
                                        <button class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem; border-radius:6px;"
                                                onclick="viewActivity(<?php echo htmlspecialchars(json_encode($act), ENT_QUOTES); ?>)">
                                            <i class="ti ti-eye me-1"></i>View
                                        </button>
                                        <button class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;font-size:0.75rem; border-radius:6px;"
                                                onclick="openEditActivity(<?php echo htmlspecialchars(json_encode($act), ENT_QUOTES); ?>)">
                                            <i class="ti ti-pencil me-1"></i>Edit
                                        </button>
                                        <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.75rem; border-radius:6px;"
                                                onclick="confirmDeleteActivity(<?php echo (int)$act['id']; ?>, '<?php echo htmlspecialchars(addslashes($act['title'])); ?>')">
                                            <i class="ti ti-trash me-1"></i>Delete
                                        </button>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div><!-- /Section 3 -->


    <!-- ====================================================
         SECTION 4 — PUBLISH & SUBMISSIONS
         ==================================================== -->
    <div class="card mb-4" id="sectionPublish">
        <div class="card-header" style="background:#1e4072;color:#fff;border-radius:6px 6px 0 0;">
            <span class="fw-semibold"><i class="ti ti-send me-2"></i>Publish &amp; Submissions</span>
        </div>
        <div class="card-body p-3">

            <?php
            $draftPlans = array_filter($lessonPlans, fn($lp) => ($lp['status'] ?? '') === 'draft');
            ?>
            <?php if (!empty($draftPlans)): ?>
                <h6 class="fw-semibold mb-3" style="color:#1e4072;">Draft Lesson Plans</h6>
                <div class="d-flex flex-wrap gap-2 mb-4">
                    <?php foreach ($draftPlans as $dp): ?>
                        <button class="btn btn-sm"
                                style="background:#3b6d11;color:#fff;border:none;"
                                onclick="confirmPublish(<?php echo (int)$dp['id']; ?>, '<?php echo htmlspecialchars(addslashes($dp['title'])); ?>')">
                            <i class="ti ti-send me-1"></i>Publish: <?php echo htmlspecialchars($dp['title']); ?>
                        </button>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>

            <h6 class="fw-semibold mb-3" style="color:#1e4072;">Submissions</h6>
            <?php 
            $hasSubmissions = false;
            foreach ($submissionsByLp as $lpId => $subs) {
                if (!empty($subs)) $hasSubmissions = true;
            }
            ?>
            <?php if (!$hasSubmissions): ?>
                <div class="text-center py-4">
                    <i class="ti ti-inbox" style="font-size:2.5rem;color:#ccc;"></i>
                    <p class="text-muted small mt-2 mb-0">No submissions yet.</p>
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-sm table-hover mb-0" style="font-size:0.85rem;">
                        <thead style="background:#1e4072;color:#fff;">
                            <tr>
                                <th>Learner</th>
                                <th>Activity</th>
                                <th>Submitted At</th>
                                <th>Score</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($submissionsByLp as $lpId => $subs): ?>
                                <?php foreach ($subs as $sub): ?>
                                    <tr>
                                        <td><?php echo htmlspecialchars($sub['student_name'] ?? 'Learner'); ?></td>
                                        <td><?php echo htmlspecialchars($sub['activity_title']); ?></td>
                                        <td><?php echo date('M d, Y h:i A', strtotime($sub['submitted_at'])); ?></td>
                                        <td>
                                            <?php
                                            $dm = (int)($sub['display_max_score'] ?? $sub['activity_max_score'] ?? 0);
                                            $gmx = (int)($sub['graded_max_score'] ?? 0);
                                            ?>
                                            <?php if ($sub['graded_score'] !== null && $sub['graded_score'] !== ''): ?>
                                                <span class="badge bg-success"><?php echo (int)$sub['graded_score']; ?> / <?php echo $gmx > 0 ? $gmx : $dm; ?></span>
                                            <?php elseif ($sub['auto_score'] !== null && $sub['auto_score'] !== ''): ?>
                                                <span class="badge" style="background:#1e4072;"><?php echo (int)$sub['auto_score']; ?> / <?php echo $dm > 0 ? $dm : (int)($sub['activity_max_score'] ?? 0); ?> <span class="opacity-75">(auto)</span></span>
                                            <?php else: ?>
                                                <span class="badge bg-secondary">— / <?php echo $dm > 0 ? $dm : (int)($sub['activity_max_score'] ?? 0); ?></span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/iep/implementation/submission/<?php echo $sub['activity_id']; ?>?student_id=<?php echo $sub['student_id']; ?>" target="_blank" class="btn btn-sm btn-outline-primary" style="font-size:0.75rem;">
                                                <i class="ti ti-eye"></i> View
                                            </a>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>

        </div>
    </div><!-- /Section 4 -->

</div><!-- /container-fluid -->
</div><!-- /main-content -->

<?php require __DIR__ . '/../iep/partials/iep_pdsp_reference_drawer.php'; ?>


<!-- ================================================================
     MODAL: Upload Lesson Document
     ================================================================ -->
<div class="modal fade" id="uploadDocModal" tabindex="-1" aria-labelledby="uploadDocModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="uploadDocModalLabel">
                    <i class="ti ti-upload me-2"></i>Upload Lesson Document
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <p class="text-muted small mb-3">Uploading for: <strong id="uploadDocLpTitle"></strong></p>
                <input type="hidden" id="uploadDocLpId">
                <?php
                $fieldName     = 'lesson_doc_upload';
                $acceptedTypes = '.jpg,.jpeg,.png,.pdf';
                $maxSize       = 10;
                $showCamera    = true;
                require __DIR__ . '/../components/upload-zone.php';
                ?>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#a01422;color:#fff;border:none;"
                        onclick="submitUploadDoc()">
                    <i class="ti ti-upload me-1"></i>Upload
                </button>
            </div>
        </div>
    </div>
</div>


<!-- ================================================================
     MODAL: Add Material — File Upload
     ================================================================ -->
<div class="modal fade" id="matFileModal" tabindex="-1" aria-labelledby="matFileModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="matFileModalLabel">
                    <i class="ti ti-upload me-2"></i>Upload File Material
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lesson Plan <span class="text-danger">*</span></label>
                    <select class="form-select" id="matFileLessonPlan" required>
                        <option value="">— Select lesson plan —</option>
                        <?php foreach ($lessonPlans as $lp): ?>
                            <option value="<?php echo (int)$lp['id']; ?>"><?php echo htmlspecialchars($lp['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="matFileTitle" placeholder="Material title" required>
                </div>
                <!-- Simple file input — no upload-zone component to avoid auto-reload side effects -->
                <div class="mb-3">
                    <label class="form-label fw-semibold small">File <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="matFileInput"
                           accept=".jpg,.jpeg,.png,.pdf,.mp4" required>
                    <div class="form-text">Accepted: JPG, PNG, PDF, MP4 · Max 50MB for video, 10MB for others</div>
                    <div id="matFileError" class="text-danger small mt-1" style="display:none;"></div>
                </div>
                <!-- Camera option (mobile only) -->
                <div class="mb-2 d-none" id="matCameraWrap">
                    <label class="form-label fw-semibold small">Or take a photo</label>
                    <input type="file" class="form-control" id="matCameraInput"
                           accept="image/*" capture="environment">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#a01422;color:#fff;border:none;"
                        onclick="submitMaterialFile()">
                    <i class="ti ti-device-floppy me-1"></i>Save Material
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Add Material — External Link
     ================================================================ -->
<div class="modal fade" id="matLinkModal" tabindex="-1" aria-labelledby="matLinkModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="matLinkModalLabel">
                    <i class="ti ti-link me-2"></i>Add External Link
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lesson Plan <span class="text-danger">*</span></label>
                    <select class="form-select" id="matLinkLessonPlan" required>
                        <option value="">— Select lesson plan —</option>
                        <?php foreach ($lessonPlans as $lp): ?>
                            <option value="<?php echo (int)$lp['id']; ?>"><?php echo htmlspecialchars($lp['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="matLinkTitle" placeholder="Material title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">URL <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="matLinkUrl" placeholder="https://..." required>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#a01422;color:#fff;border:none;"
                        onclick="submitMaterialLink()">
                    <i class="ti ti-device-floppy me-1"></i>Save Material
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Add Material — Embed
     ================================================================ -->
<div class="modal fade" id="matEmbedModal" tabindex="-1" aria-labelledby="matEmbedModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="matEmbedModalLabel">
                    <i class="ti ti-player-play me-2"></i>Embed Content
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lesson Plan <span class="text-danger">*</span></label>
                    <select class="form-select" id="matEmbedLessonPlan" required>
                        <option value="">— Select lesson plan —</option>
                        <?php foreach ($lessonPlans as $lp): ?>
                            <option value="<?php echo (int)$lp['id']; ?>"><?php echo htmlspecialchars($lp['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="matEmbedTitle" placeholder="Material title" required>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">YouTube or Google Drive URL <span class="text-danger">*</span></label>
                    <input type="url" class="form-control" id="matEmbedUrl"
                           placeholder="https://youtube.com/... or https://drive.google.com/..."
                           oninput="detectEmbedType(this.value)" required>
                    <div class="form-text" id="matEmbedTypeHint"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#a01422;color:#fff;border:none;"
                        onclick="submitMaterialEmbed()">
                    <i class="ti ti-device-floppy me-1"></i>Save Material
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Import Activity CSV
     ================================================================ -->
<div class="modal fade" id="importActivityModal" tabindex="-1" aria-labelledby="importActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#3b6d11;color:#fff;">
                <h5 class="modal-title" id="importActivityModalLabel">
                    <i class="ti ti-file-import me-2"></i>Import Activity CSV
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Lesson Plan <span class="text-danger">*</span></label>
                    <select class="form-select" id="importActivityLessonPlan" required>
                        <option value="">— Select lesson plan —</option>
                        <?php foreach ($lessonPlans as $lp): ?>
                            <option value="<?php echo (int)$lp['id']; ?>"><?php echo htmlspecialchars($lp['title']); ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="mb-3">
                    <label class="form-label fw-semibold small">CSV File <span class="text-danger">*</span></label>
                    <input type="file" class="form-control" id="importActivityFile" accept=".csv" required>
                    <div class="form-text">Upload a simple CSV to automatically generate activities. <br>
                    Format: <strong>title, instructions, type, max_score, question1, q1_option1, q1_option2, q1_correct...</strong>
                    </div>
                </div>
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="#" class="btn btn-sm btn-outline-secondary" onclick="downloadActivityCsvTemplate(event)">
                    <i class="ti ti-download me-1"></i>Sample Template
                </a>
                <div>
                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                    <button type="button" class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;" onclick="submitImportActivity()">
                        <i class="ti ti-upload me-1"></i>Import
                    </button>
                </div>
            </div>
        </div>
    </div>
</div>



<!-- ================================================================
     MODAL: View Material
     ================================================================ -->
<div class="modal fade" id="viewMaterialModal" tabindex="-1" aria-labelledby="viewMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="viewMaterialModalLabel"><i class="ti ti-eye me-2"></i><span id="vMatTitle">Material</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewMaterialBody">
                <!-- populated by JS -->
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Edit Material
     ================================================================ -->
<div class="modal fade" id="editMaterialModal" tabindex="-1" aria-labelledby="editMaterialModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#3b6d11;color:#fff;">
                <h5 class="modal-title" id="editMaterialModalLabel"><i class="ti ti-pencil me-2"></i>Edit Material</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editMatId">
                <input type="hidden" id="editMatType">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editMatTitle" required>
                </div>
                <!-- URL field: shown for link/embed -->
                <div class="mb-3" id="editMatUrlWrap" style="display:none;">
                    <label class="form-label fw-semibold small">URL</label>
                    <input type="url" class="form-control" id="editMatUrl" placeholder="https://...">
                </div>
                <!-- File replacement: shown for file type -->
                <div class="mb-3" id="editMatFileWrap" style="display:none;">
                    <label class="form-label fw-semibold small">Replace File <span class="text-muted">(optional)</span></label>
                    <input type="file" class="form-control" id="editMatFile" accept=".jpg,.jpeg,.png,.pdf,.mp4">
                    <div class="form-text">Leave blank to keep existing file. Max 10MB (50MB for MP4).</div>
                </div>
                <div id="editMatError" class="alert alert-danger" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#3b6d11;color:#fff;border:none;" onclick="submitEditMaterial()">
                    <i class="ti ti-device-floppy me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: View Activity
     ================================================================ -->
<div class="modal fade" id="viewActivityModal" tabindex="-1" aria-labelledby="viewActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title" id="viewActivityModalLabel"><i class="ti ti-eye me-2"></i><span id="vActTitle">Activity</span></h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body" id="viewActivityBody">
                <!-- populated by JS -->
            </div>
            <div class="modal-footer d-flex justify-content-between">
                <a href="#" id="vActLearnerPreviewBtn" target="_blank" class="btn btn-sm" style="background:#6c5ce7;color:#fff;border:none;border-radius:6px;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;">
                    <i class="ti ti-device-gamepad me-1"></i>Open Learner's Interactive Preview
                </a>
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" style="border-radius:6px;">Close</button>
            </div>
        </div>
    </div>
</div>

<!-- ================================================================
     MODAL: Edit Activity
     ================================================================ -->
<div class="modal fade" id="editActivityModal" tabindex="-1" aria-labelledby="editActivityModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header" style="background:#3b6d11;color:#fff;">
                <h5 class="modal-title" id="editActivityModalLabel"><i class="ti ti-pencil me-2"></i>Edit Activity</h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            <div class="modal-body">
                <input type="hidden" id="editActId">
                <div class="mb-3">
                    <label class="form-label fw-semibold small">Title <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="editActTitle" required>
                </div>
                <div class="mb-3">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <label class="form-label fw-semibold small mb-0">Instructions / Mga Panuto</label>
                        <span class="badge bg-light text-primary border" style="font-size:0.7rem;"><i class="ti ti-typography me-1"></i>WYSIWYG: Tables &amp; Images Supported</span>
                    </div>
                    <textarea class="form-control" id="editActInstructions" rows="4" placeholder="Instructions for the learner..."></textarea>
                </div>
                <div class="row g-2">
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Max Score</label>
                        <input type="number" class="form-control" id="editActMaxScore" min="0">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-semibold small">Due Date <span class="text-muted">(optional)</span></label>
                        <input type="date" class="form-control" id="editActDueDate">
                    </div>
                </div>
                <div id="editActError" class="alert alert-danger mt-3" style="display:none;"></div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn" style="background:#3b6d11;color:#fff;border:none;" onclick="submitEditActivity()">
                    <i class="ti ti-device-floppy me-1"></i>Save Changes
                </button>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL: Interactive Lesson Studio Choice (Connected to Lesson Plan)
     ============================================================ -->
<div class="modal fade" id="interactiveLessonChoiceModal" tabindex="-1" aria-labelledby="interactiveLessonChoiceModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 540px;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden; border: none; box-shadow: 0 10px 30px rgba(0,0,0,0.15);">
            <div class="modal-header py-3 px-4" style="background:#1e4072; color:#fff;">
                <div>
                    <h5 class="modal-title fw-bold mb-0" id="interactiveLessonChoiceModalLabel" style="font-size: 1.1rem;">
                        <i class="ti ti-presentation me-2"></i>Interactive Lesson Slides Studio
                    </h5>
                    <small class="text-white-50" style="font-size: 0.78rem;">Select a Lesson Plan to create or edit interactive slides.</small>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
            </div>
            
            <div class="modal-body p-4">
                <!-- Step 1: Select Lesson Plan (Same as Upload File, Embed, Link) -->
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark mb-1">
                        Lesson Plan <span class="text-danger">*</span>
                    </label>
                    <select class="form-select fw-semibold" id="interactiveChoiceLpSelect" onchange="onInteractiveLpSelectChanged(this)" style="border-radius: 6px;">
                        <option value="">— Select Lesson Plan —</option>
                        <?php foreach ($lessonPlans as $lp): 
                            $lpId = (int)$lp['id'];
                            $lpDomain = $lp['pdsp_domain'] ?? '';
                            $domainLabel = $domainLabels[$lpDomain] ?? ucwords(str_replace('_', ' ', $lpDomain));
                            $slideCount = 0;
                            foreach ($materials as $m) {
                                if (($m['material_type'] ?? '') === 'interactive' && (int)($m['lesson_plan_id'] ?? 0) === $lpId) {
                                    $slideCount = (int)($m['slide_count'] ?? 0);
                                }
                            }
                        ?>
                            <option value="<?php echo $lpId; ?>" 
                                    data-domain-key="<?php echo htmlspecialchars($lpDomain); ?>" 
                                    data-domain-label="<?php echo htmlspecialchars($domainLabel); ?>" 
                                    data-slides="<?php echo $slideCount; ?>">
                                <?php echo htmlspecialchars($lp['title']); ?> (<?php echo htmlspecialchars($domainLabel); ?>)
                            </option>
                        <?php endforeach; ?>
                    </select>
                    <div class="form-text small text-muted mt-1">
                        <i class="ti ti-info-circle me-1"></i>Like File Upload, Interactive Lessons connect directly to a Lesson Plan linked to an IEP Step Objective.
                    </div>
                </div>

                <!-- Info Box displayed dynamically when a Lesson Plan is selected -->
                <div id="interactiveLpSelectedInfoBox" class="p-3 rounded-3 border bg-light mb-3" style="display: none;">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <span class="small text-muted fw-semibold" style="font-size: 0.78rem;">Linked PDSP Domain:</span>
                        <span class="badge bg-primary text-white rounded-pill px-2.5 py-1 fw-bold" id="interactiveLpDomainBadge" style="font-size: 0.75rem;">
                            Daily Living Skills
                        </span>
                    </div>

                    <div class="d-flex justify-content-between align-items-center mb-3">
                        <span class="small text-muted fw-semibold" style="font-size: 0.78rem;">Current Slides:</span>
                        <span class="badge bg-white text-dark border rounded-pill px-2.5 py-1 fw-semibold" id="interactiveLpSlideBadge" style="font-size: 0.75rem;">
                            3 Slides
                        </span>
                    </div>

                    <div id="interactiveSlideActionPrompt" class="mb-3">
                        <!-- Dynamic explanation -->
                    </div>

                    <div class="d-flex flex-column gap-2">
                        <button type="button" class="btn btn-primary fw-bold py-2 rounded-2 w-100" id="btnOpenInteractiveStudio" onclick="openChosenInteractiveStudio()">
                            <i class="ti ti-presentation me-1"></i> Open Studio
                        </button>
                        <button type="button" class="btn btn-outline-danger fw-semibold py-1.5 rounded-2 w-100" id="btnStartEmptyInteractiveSlides" onclick="startEmptyInteractiveSlides()" style="display: none; font-size: 0.8125rem;">
                            <i class="ti ti-trash me-1"></i> Clear &amp; Start with Empty Slides (0 Slides)
                        </button>
                    </div>
                </div>

                <!-- Accordion / Toggle: Create a brand new Lesson Plan linked to an IEP Step Objective -->
                <div class="border-top pt-3 mt-3">
                    <button type="button" class="btn btn-link text-decoration-none p-0 small fw-bold text-primary d-flex align-items-center" onclick="toggleNewLessonPlanForm()">
                        <i class="ti ti-plus-circle me-1.5" id="toggleNewLpIcon"></i>
                        <span>Need to create a new Lesson Plan for an IEP Step Objective?</span>
                    </button>

                    <div id="newLessonPlanStepForm" class="mt-3 p-3 rounded-3 border bg-light" style="display: none;">
                        <h6 class="fw-bold small text-dark mb-2">New Lesson Plan for IEP</h6>
                        
                        <div class="mb-2">
                            <label class="form-label small fw-bold text-dark mb-1">Lesson Plan Title <span class="text-danger">*</span></label>
                            <input type="text" class="form-control form-control-sm" id="newStepLpTitle" placeholder="e.g. Lesson <?php echo count($lessonPlans) + 1; ?>: Proper Handwashing" style="border-radius: 6px;">
                        </div>

                        <div class="mb-2">
                            <label class="form-label small fw-bold text-dark mb-1">IEP Step Objective &amp; PDSP Domain <span class="text-danger">*</span></label>
                            <select class="form-select form-select-sm" id="newStepLpDomain" style="border-radius: 6px;">
                                <?php 
                                foreach ($domainLabels as $key => $lbl): 
                                ?>
                                    <option value="<?php echo $key; ?>"><?php echo htmlspecialchars($lbl); ?></option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small" style="font-size: 0.72rem;">The PDSP domain aligns with the IEP step objective.</div>
                        </div>

                        <button type="button" class="btn btn-sm btn-success w-100 fw-bold py-1.5 rounded-2 mt-2" id="btnCreateStepLp" onclick="createStepLessonPlanAndOpenStudio()">
                            <i class="ti ti-sparkles me-1"></i> Create Lesson Plan &amp; Open Studio
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ============================================================
     MODAL: Multi-Page Lesson Builder (Wide Accessible Layout)
     ============================================================ -->
<div class="modal fade" id="lessonPagesModal" tabindex="-1" aria-labelledby="lessonPagesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-scrollable" style="max-width: 95vw; width: 1480px; margin: 1.5rem auto;">
        <div class="modal-content" style="border-radius: 12px; overflow: hidden;">
            <div class="modal-header d-flex justify-content-between align-items-center" style="background:#1e4072;color:#fff;">
                <h5 class="modal-title d-flex align-items-center mb-0" id="lessonPagesModalLabel" style="font-size: 1.15rem; font-weight:700;">
                    <i class="ti ti-layout-grid me-2"></i>Interactive Lesson Slides Builder &mdash; <span id="lpModalLessonTitle" class="ms-1 fw-bold">Lesson</span>
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <a href="#" id="btnPreviewLearnerView" target="_blank" class="btn btn-sm btn-outline-light d-flex align-items-center gap-1" style="font-size:0.8rem; border-radius:6px; font-weight:600;">
                        <i class="ti ti-external-link"></i><span>View as Learner</span>
                    </a>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                </div>
            </div>
            <div class="modal-body p-3 p-md-4">
                <!-- Lesson Plan Selector Dropdown -->
                <div class="card mb-3 border bg-light shadow-none" style="border-radius:8px;">
                    <div class="card-body py-2 px-3 d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <div class="d-flex align-items-center gap-2">
                            <label class="form-label fw-bold small mb-0 text-nowrap" style="color:#1e4072;">
                                <i class="ti ti-book me-1"></i>Selected Lesson Plan:
                            </label>
                            <select class="form-select form-select-sm fw-semibold" id="lpModalLpSelect" style="min-width: 260px; border-radius:6px;" onchange="onLpModalSelectChange(this.value)">
                                <?php if (empty($lessonPlans)): ?>
                                    <option value="">— No lesson plans available —</option>
                                <?php else: ?>
                                    <?php foreach ($lessonPlans as $lp): ?>
                                        <option value="<?php echo (int)$lp['id']; ?>"><?php echo htmlspecialchars($lp['title']); ?></option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>
                        <span class="small text-muted"><i class="ti ti-info-circle me-1"></i>Create, edit, and organize multi-page interactive slides with tables, images, FSL videos and guide questions.</span>
                    </div>
                </div>

                <div class="row g-3">
                    <!-- Left: Page List & Outline -->
                    <div class="col-lg-4">
                        <div class="card border h-100 shadow-sm" style="border-radius:8px;">
                            <div class="card-header bg-light d-flex justify-content-between align-items-center py-2">
                                <span class="fw-bold small text-dark"><i class="ti ti-list-numbers me-1 text-primary"></i>Lesson Slides Outline</span>
                                <button type="button" class="btn btn-sm d-flex align-items-center gap-1" style="background:#1e4072;color:#fff;font-size:0.75rem;border-radius:6px;" onclick="resetLessonPageForm()">
                                    <i class="ti ti-plus"></i><span>New Slide</span>
                                </button>
                            </div>
                            <div class="card-body p-2" style="max-height: 600px; overflow-y: auto;">
                                <div id="lessonPagesEmpty" class="text-center py-4 text-muted small">
                                    <i class="ti ti-layout-grid-add" style="font-size: 2rem; color: #cbd5e1;"></i>
                                    <p class="mt-2 mb-0">No slides created yet. Use the form on the right to build your first lesson slide.</p>
                                </div>
                                <div id="lessonPagesContainer" class="d-flex flex-column gap-2"></div>
                            </div>
                        </div>
                    </div>

                    <!-- Right: Slide Editor Form (Expanded 8 Columns) -->
                    <div class="col-lg-8">
                        <div class="card border shadow-sm" style="border-radius:8px;">
                            <div class="card-header bg-light py-2 d-flex justify-content-between align-items-center">
                                <span class="fw-bold small text-dark" id="lpPageFormTitle"><i class="ti ti-edit me-1 text-success"></i>Add New Lesson Slide</span>
                                <span class="badge bg-secondary" id="lpPageNumBadge" style="font-size:0.7rem;">New</span>
                            </div>
                            <div class="card-body p-3">
                                <form id="lessonPageForm" enctype="multipart/form-data" onsubmit="event.preventDefault(); submitLessonPage();">
                                    <input type="hidden" id="lpPageId" value="">
                                    <input type="hidden" id="lpPageLpId" value="">

                                    <div class="mb-2">
                                        <label class="form-label fw-semibold small mb-1">Slide / Page Title <span class="text-danger">*</span></label>
                                        <input type="text" class="form-control form-control-sm" id="lpPageTitle" placeholder="e.g. Slide 1: Introduction to Daily Greetings in FSL" required style="border-radius:6px;">
                                    </div>

                                    <div class="mb-3">
                                        <div class="d-flex justify-content-between align-items-center mb-1">
                                            <label class="form-label fw-semibold small mb-0">Lesson Narrative, Tables &amp; Content <span class="text-danger">*</span></label>
                                            <span class="badge bg-light text-primary border" style="font-size:0.7rem;"><i class="ti ti-table me-1"></i>WYSIWYG: Tables &amp; Images Supported</span>
                                        </div>
                                        <textarea class="form-control form-control-sm" id="lpPageContent" rows="6" placeholder="Write lesson slide narrative, story, instructions, tables, or add images..."></textarea>
                                        <div class="form-text mt-1 d-flex justify-content-between align-items-center flex-wrap gap-1" style="font-size: 0.72rem;">
                                            <span><i class="ti ti-info-circle me-1"></i>Insert tables or place images between paragraphs using the toolbar.</span>
                                            <span class="text-muted">Tip: Use <code>[fsl:word]</code> to embed interactive sign demos.</span>
                                        </div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label fw-semibold small mb-1 text-primary">
                                            <i class="ti ti-help me-1"></i>Guide Questions / Reflection Prompts <span class="text-muted fw-normal">(optional)</span>
                                        </label>
                                        <textarea class="form-control form-control-sm" id="lpPageGuideQuestions" rows="2" placeholder="e.g. 1. Can you practice signing 'HELLO'?&#10;2. How would you greet your teacher in the morning?" style="border-radius:6px;"></textarea>
                                        <div class="form-text mt-0" style="font-size: 0.72rem;">Interactive check-in questions displayed in a highlighted callout on this slide.</div>
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label fw-semibold small mb-1"><i class="ti ti-video me-1"></i>Media Attachment / Sign Language Video</label>
                                        <select class="form-select form-select-sm mb-2" id="lpPageMediaType" onchange="toggleLessonPageMediaInputs()" style="border-radius:6px;">
                                            <option value="none">No media</option>
                                            <option value="image">Image (Upload JPG, PNG, GIF, WebP)</option>
                                            <option value="video">FSL Video (Upload MP4 or WebM)</option>
                                            <option value="embed">Embed Video (YouTube or Google Drive URL)</option>
                                        </select>

                                        <!-- Media File Upload Input -->
                                        <div id="lpPageFileUploadWrap" style="display:none;" class="mb-2">
                                            <label class="form-label small text-muted mb-1">Select file to upload:</label>
                                            <input type="file" class="form-control form-control-sm" id="lpPageMediaFile" accept="image/*,video/mp4,video/webm" style="border-radius:6px;">
                                            <div id="lpCurrentMediaPreview" class="small mt-1 text-muted"></div>
                                        </div>

                                        <!-- Media URL / Embed Input -->
                                        <div id="lpPageUrlWrap" style="display:none;" class="mb-2">
                                            <label class="form-label small text-muted mb-1">Paste video or embed URL:</label>
                                            <input type="url" class="form-control form-control-sm" id="lpPageMediaPath" placeholder="https://www.youtube.com/watch?v=... or Google Drive URL" style="border-radius:6px;">
                                        </div>
                                    </div>

                                    <div id="lpPageFormError" class="alert alert-danger py-1 px-2 small mb-2" style="display:none; border-radius:6px;"></div>

                                    <div class="d-flex justify-content-between align-items-center mt-3 pt-2 border-top">
                                        <button type="button" class="btn btn-sm btn-outline-secondary d-flex align-items-center gap-1" onclick="resetLessonPageForm()" style="border-radius:6px; font-size:0.8rem;">
                                            <i class="ti ti-rotate-clockwise"></i><span>Reset</span>
                                        </button>
                                        <button type="submit" class="btn btn-sm d-flex align-items-center gap-1" id="btnSaveLessonPage" style="background:#1e4072;color:#fff;border:none;border-radius:6px; font-size:0.8rem; font-weight:600; padding: 0.35rem 1rem;">
                                            <i class="ti ti-device-floppy"></i><span>Save Slide</span>
                                        </button>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div class="modal-footer py-2">
                <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal" style="border-radius:6px;">Close Builder</button>
            </div>
        </div>
    </div>
</div>

<script>
const BASE   = '<?php echo addslashes($basePath); ?>';
const IEP_ID = <?php echo (int)$iep['id']; ?>;

// ================================================================
// LESSON PAGES / SLIDES BUILDER (WITH SUMMERNOTE RICH TEXT & TABLES)
// ================================================================
let currentLpPages = [];
let activeEditPageId = null;

function initSummernoteEditor() {
    if (typeof $ !== 'undefined' && $.fn.summernote) {
        if (!$('#lpPageContent').hasClass('summernote-initialized')) {
            $('#lpPageContent').summernote({
                placeholder: 'Write lesson slide narrative, story, instructions, tables, or add images...',
                tabsize: 2,
                height: 280,
                toolbar: [
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['font', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        if (!files || !files.length) return;
                        for (let i = 0; i < files.length; i++) {
                            uploadEditorImage(files[i], $(this));
                        }
                    }
                }
            });
            $('#lpPageContent').addClass('summernote-initialized');
        }
    }
}

function uploadEditorImage(file, $editor) {
    const fd = new FormData();
    fd.append('image', file);

    fetch(BASE + '/iep/implementation/upload-slide-image', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.url) {
            $editor.summernote('insertImage', data.url);
        } else {
            // Fallback to base64
            const reader = new FileReader();
            reader.onloadend = function() {
                $editor.summernote('insertImage', reader.result);
            };
            reader.readAsDataURL(file);
        }
    })
    .catch(err => {
        console.error('Image upload failed, fallback to base64:', err);
        const reader = new FileReader();
        reader.onloadend = function() {
            $editor.summernote('insertImage', reader.result);
        };
        reader.readAsDataURL(file);
    });
}

function openLessonPagesModal(lpId, lpTitle) {
    if (!lpId) {
        openInteractiveLessonChoiceModal();
        return;
    }
    // Navigate directly to the dedicated full-page Lesson Slides Studio!
    window.location.href = BASE + '/iep/implementation/lesson/' + lpId + '/builder';
}


function onLpModalSelectChange(newLpId) {
    const selectEl = document.getElementById('lpModalLpSelect');
    const lpTitle = (selectEl && selectEl.selectedIndex >= 0) ? selectEl.options[selectEl.selectedIndex].text : 'Lesson';
    document.getElementById('lpModalLessonTitle').textContent = lpTitle;
    document.getElementById('lpPageLpId').value = newLpId;
    
    const previewBtn = document.getElementById('btnPreviewLearnerView');
    if (previewBtn) {
        previewBtn.href = BASE + '/learning/lesson/' + newLpId;
    }

    resetLessonPageForm();
    loadLessonPages(newLpId);
}

function toggleLessonPageMediaInputs() {
    const type = document.getElementById('lpPageMediaType').value;
    const fileWrap = document.getElementById('lpPageFileUploadWrap');
    const urlWrap = document.getElementById('lpPageUrlWrap');

    if (type === 'image' || type === 'video') {
        fileWrap.style.display = 'block';
        urlWrap.style.display = 'none';
    } else if (type === 'embed') {
        fileWrap.style.display = 'none';
        urlWrap.style.display = 'block';
    } else {
        fileWrap.style.display = 'none';
        urlWrap.style.display = 'none';
    }
}

function resetLessonPageForm() {
    activeEditPageId = null;
    document.getElementById('lpPageId').value = '';
    document.getElementById('lpPageTitle').value = '';
    
    initSummernoteEditor();
    if (typeof $ !== 'undefined' && $('#lpPageContent').hasClass('summernote-initialized')) {
        $('#lpPageContent').summernote('code', '');
    } else {
        document.getElementById('lpPageContent').value = '';
    }

    document.getElementById('lpPageGuideQuestions').value = '';
    document.getElementById('lpPageMediaType').value = 'none';
    document.getElementById('lpPageMediaPath').value = '';
    document.getElementById('lpPageMediaFile').value = '';
    document.getElementById('lpCurrentMediaPreview').innerHTML = '';
    document.getElementById('lpPageFormError').style.display = 'none';
    document.getElementById('lpPageFormTitle').innerHTML = '<i class="ti ti-plus me-1 text-primary"></i>Add New Lesson Slide';
    document.getElementById('lpPageNumBadge').textContent = 'New';
    document.getElementById('lpPageNumBadge').className = 'badge bg-secondary';
    document.getElementById('btnSaveLessonPage').innerHTML = '<i class="ti ti-device-floppy me-1"></i>Save Slide';
    toggleLessonPageMediaInputs();
    highlightActiveSlideCard(null);
}

function highlightActiveSlideCard(pageId) {
    document.querySelectorAll('.lp-slide-item').forEach(el => {
        el.classList.remove('border-primary', 'bg-light', 'shadow-sm');
    });
    if (pageId) {
        const item = document.getElementById('lpPageItem_' + pageId);
        if (item) {
            item.classList.add('border-primary', 'bg-light', 'shadow-sm');
        }
    }
}

async function loadLessonPages(lpId) {
    const container = document.getElementById('lessonPagesContainer');
    const empty = document.getElementById('lessonPagesEmpty');
    container.innerHTML = '<div class="text-center py-3 text-muted small"><i class="spinner-border spinner-border-sm me-1"></i>Loading slides...</div>';

    try {
        const res = await fetch(BASE + '/iep/implementation/lesson-plan/' + lpId + '/pages');
        const data = await res.json();
        if (data.success) {
            currentLpPages = data.pages || [];
            renderLessonPagesList(currentLpPages);
        } else {
            container.innerHTML = '<div class="alert alert-danger p-2 small">' + escHtml(data.message || 'Could not load pages.') + '</div>';
        }
    } catch (e) {
        container.innerHTML = '<div class="alert alert-danger p-2 small">Error loading slides.</div>';
    }
}

function renderLessonPagesList(pages) {
    const container = document.getElementById('lessonPagesContainer');
    const empty = document.getElementById('lessonPagesEmpty');

    if (!pages || pages.length === 0) {
        container.innerHTML = '';
        empty.style.display = 'block';
        return;
    }

    empty.style.display = 'none';
    container.innerHTML = '';

    pages.forEach((p, idx) => {
        const item = document.createElement('div');
        item.className = 'card bg-white p-2 border rounded lp-slide-item';
        item.style.cursor = 'pointer';
        item.style.transition = 'all 0.15s ease';
        item.id = 'lpPageItem_' + p.id;

        let mediaBadge = '';
        if (p.media_type === 'image') {
            mediaBadge = '<span class="badge bg-info text-dark" style="font-size:0.65rem;"><i class="ti ti-photo me-1"></i>Image</span>';
        } else if (p.media_type === 'video') {
            mediaBadge = '<span class="badge bg-primary" style="font-size:0.65rem;"><i class="ti ti-video me-1"></i>Video</span>';
        } else if (p.media_type === 'embed') {
            mediaBadge = '<span class="badge bg-danger" style="font-size:0.65rem;"><i class="ti ti-brand-youtube me-1"></i>Embed</span>';
        }

        // Clean text preview (strip HTML tags for outline snippet)
        const textSnippet = (p.content || '').replace(/<[^>]*>?/gm, '');

        item.innerHTML = `
            <div class="d-flex justify-content-between align-items-start gap-2">
                <div class="flex-grow-1 min-width-0" onclick="editLessonPageById(${p.id})">
                    <div class="d-flex align-items-center gap-1 mb-1">
                        <span class="badge" style="background:#1e4072;font-size:0.7rem;">Slide ${idx + 1}</span>
                        ${mediaBadge}
                        ${p.guide_questions ? '<span class="badge bg-warning text-dark" style="font-size:0.65rem;"><i class="ti ti-help me-1"></i>Questions</span>' : ''}
                    </div>
                    <h6 class="fw-bold mb-1 text-dark text-truncate" style="font-size:0.85rem;" title="${escHtml(p.title)}">
                        ${escHtml(p.title)}
                    </h6>
                    <p class="text-muted small mb-0 text-truncate" style="font-size:0.75rem;">
                        ${escHtml(textSnippet.substring(0, 80))}...
                    </p>
                </div>
                <div class="d-flex flex-column gap-1 flex-shrink-0">
                    <button type="button" class="btn btn-sm btn-outline-primary py-0 px-2" style="font-size:0.75rem; border-radius:4px;" onclick="editLessonPageById(${p.id})" title="Edit Slide">
                        <i class="ti ti-pencil"></i>
                    </button>
                    <button type="button" class="btn btn-sm btn-outline-danger py-0 px-2" style="font-size:0.75rem; border-radius:4px;" onclick="event.stopPropagation(); deleteLessonPage(${p.id}, ${p.lesson_plan_id})" title="Delete Slide">
                        <i class="ti ti-trash"></i>
                    </button>
                </div>
            </div>
        `;
        container.appendChild(item);
    });

    if (activeEditPageId) {
        highlightActiveSlideCard(activeEditPageId);
    }
}

function editLessonPageById(pageId) {
    const p = currentLpPages.find(item => Number(item.id) === Number(pageId));
    if (!p) return;
    editLessonPage(p);
}

function editLessonPage(p) {
    activeEditPageId = p.id;
    document.getElementById('lpPageId').value = p.id;
    document.getElementById('lpPageLpId').value = p.lesson_plan_id;
    document.getElementById('lpPageTitle').value = p.title || '';
    
    initSummernoteEditor();
    if (typeof $ !== 'undefined' && $('#lpPageContent').hasClass('summernote-initialized')) {
        $('#lpPageContent').summernote('code', p.content || '');
    } else {
        document.getElementById('lpPageContent').value = p.content || '';
    }

    document.getElementById('lpPageGuideQuestions').value = p.guide_questions || '';
    document.getElementById('lpPageMediaType').value = p.media_type || 'none';
    document.getElementById('lpPageMediaPath').value = (p.media_type === 'embed' ? p.media_path : '') || '';
    document.getElementById('lpPageMediaFile').value = '';
    document.getElementById('lpPageFormError').style.display = 'none';
    
    document.getElementById('lpPageFormTitle').innerHTML = '<i class="ti ti-pencil me-1 text-warning"></i>Edit Lesson Slide #' + p.page_number;
    document.getElementById('lpPageNumBadge').textContent = 'Slide #' + p.page_number;
    document.getElementById('lpPageNumBadge').className = 'badge bg-warning text-dark';
    document.getElementById('btnSaveLessonPage').innerHTML = '<i class="ti ti-device-floppy me-1"></i>Update Slide';

    const prevEl = document.getElementById('lpCurrentMediaPreview');
    if (p.media_path && (p.media_type === 'image' || p.media_type === 'video')) {
        prevEl.innerHTML = `<span class="text-success"><i class="ti ti-check me-1"></i>Current media: <code>${escHtml(p.media_path)}</code></span> (Upload new file to replace)`;
    } else {
        prevEl.innerHTML = '';
    }

    toggleLessonPageMediaInputs();
    highlightActiveSlideCard(p.id);
}

async function submitLessonPage() {
    const pageId = document.getElementById('lpPageId').value;
    const lpId = document.getElementById('lpPageLpId').value;
    const title = document.getElementById('lpPageTitle').value.trim();
    
    let content = '';
    if (typeof $ !== 'undefined' && $('#lpPageContent').hasClass('summernote-initialized')) {
        content = $('#lpPageContent').summernote('code');
    } else {
        content = document.getElementById('lpPageContent').value.trim();
    }

    const guideQuestions = document.getElementById('lpPageGuideQuestions').value.trim();
    const mediaType = document.getElementById('lpPageMediaType').value;
    const mediaPath = document.getElementById('lpPageMediaPath').value.trim();
    const mediaFile = document.getElementById('lpPageMediaFile').files[0];
    const errEl = document.getElementById('lpPageFormError');

    errEl.style.display = 'none';

    if (!title) {
        errEl.textContent = 'Please enter a slide title.';
        errEl.style.display = 'block';
        return;
    }

    const formData = new FormData();
    formData.append('title', title);
    formData.append('content', content);
    formData.append('guide_questions', guideQuestions);
    formData.append('media_type', mediaType);
    formData.append('media_path', mediaPath);
    if (mediaFile) {
        formData.append('media_file', mediaFile);
    }

    const btn = document.getElementById('btnSaveLessonPage');
    btn.disabled = true;
    btn.innerHTML = '<i class="spinner-border spinner-border-sm me-1"></i>Saving...';

    const endpoint = pageId 
        ? (BASE + '/iep/implementation/lesson-plan/page/' + pageId + '/update')
        : (BASE + '/iep/implementation/lesson-plan/' + lpId + '/page/add');

    try {
        const res = await fetch(endpoint, {
            method: 'POST',
            body: formData
        });
        const data = await res.json();
        btn.disabled = false;
        btn.innerHTML = pageId ? '<i class="ti ti-device-floppy me-1"></i>Update Slide' : '<i class="ti ti-device-floppy me-1"></i>Save Slide';

        if (data.success) {
            hasModifiedLessonPages = true;
            Swal.fire({
                icon: 'success',
                title: 'Saved!',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            });
            resetLessonPageForm();
            loadLessonPages(lpId);
        } else {
            errEl.textContent = data.message || 'Failed to save slide.';
            errEl.style.display = 'block';
        }
    } catch (e) {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Save Slide';
        errEl.textContent = 'Network or server error.';
        errEl.style.display = 'block';
    }
}

async function deleteLessonPage(pageId, lpId) {
    const result = await Swal.fire({
        title: 'Delete this slide?',
        text: 'This slide will be permanently removed from this lesson plan.',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete it'
    });

    if (!result.isConfirmed) return;

    try {
        const res = await fetch(BASE + '/iep/implementation/lesson-plan/page/' + pageId + '/delete', {
            method: 'POST'
        });
        const data = await res.json();
        if (data.success) {
            hasModifiedLessonPages = true;
            Swal.fire({
                icon: 'success',
                title: 'Deleted',
                text: data.message,
                timer: 1500,
                showConfirmButton: false
            });
            loadLessonPages(lpId);
            resetLessonPageForm();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Error', text: 'Network or server error.', confirmButtonColor: '#a01422' });
    }
}

let hasModifiedLessonPages = false;

document.addEventListener('DOMContentLoaded', function() {
    const lpModal = document.getElementById('lessonPagesModal');
    if (lpModal) {
        lpModal.addEventListener('hidden.bs.modal', function () {
            if (hasModifiedLessonPages) {
                window.location.reload();
            }
        });
    }
});

// ================================================================
// VIEW / EDIT MATERIALS & ACTIVITIES
// ================================================================

function viewMaterial(mat) {
    document.getElementById('vMatTitle').textContent = mat.title;
    const body = document.getElementById('viewMaterialBody');

    if (mat.material_type === 'file' && mat.file_path) {
        const url = BASE + '/' + mat.file_path;
        const ext = mat.file_path.split('.').pop().toLowerCase();
        if (['jpg','jpeg','png','gif'].includes(ext)) {
            body.innerHTML = `<img src="${url}" class="img-fluid rounded" alt="${mat.title}">`;
        } else if (ext === 'pdf') {
            body.innerHTML = `<iframe src="${url}" style="width:100%;height:500px;border:none;"></iframe>
                <div class="text-center mt-2"><a href="${url}" target="_blank" class="btn btn-sm btn-outline-primary"><i class="ti ti-external-link me-1"></i>Open in new tab</a></div>`;
        } else if (ext === 'mp4') {
            body.innerHTML = `<video controls class="w-100 rounded"><source src="${url}" type="video/mp4">Your browser does not support video.</video>`;
        } else {
            body.innerHTML = `<div class="text-center py-4"><i class="ti ti-file" style="font-size:3rem;color:#6c757d;"></i><p class="mt-2">Preview not available. <a href="${url}" target="_blank">Download file</a></p></div>`;
        }
    } else if (mat.material_type === 'link' && mat.external_url) {
        body.innerHTML = `<div class="text-center py-4">
            <i class="ti ti-external-link" style="font-size:3rem;color:#1e4072;"></i>
            <p class="mt-2 mb-3">External link: <strong>${mat.external_url}</strong></p>
            <a href="${mat.external_url}" target="_blank" rel="noopener" class="btn" style="background:#1e4072;color:#fff;border:none;">
                <i class="ti ti-external-link me-1"></i>Open Link
            </a></div>`;
    } else if (mat.material_type === 'embed' && mat.external_url) {
        let embedUrl = mat.external_url;
        const ytMatch = embedUrl.match(/(?:youtube\.com\/watch\?v=|youtu\.be\/)([\w-]+)/);
        if (ytMatch) embedUrl = `https://www.youtube.com/embed/${ytMatch[1]}`;
        const gdMatch = embedUrl.match(/\/d\/([^/]+)/);
        if (gdMatch && embedUrl.includes('drive.google.com')) embedUrl = `https://drive.google.com/file/d/${gdMatch[1]}/preview`;
        body.innerHTML = `<div class="ratio ratio-16x9"><iframe src="${embedUrl}" allowfullscreen frameborder="0"></iframe></div>`;
    } else {
        body.innerHTML = '<p class="text-muted text-center py-3">No preview available for this material.</p>';
    }

    new bootstrap.Modal(document.getElementById('viewMaterialModal')).show();
}

function openEditMaterial(mat) {
    document.getElementById('editMatId').value   = mat.id;
    document.getElementById('editMatType').value  = mat.material_type;
    document.getElementById('editMatTitle').value = mat.title;
    document.getElementById('editMatError').style.display = 'none';
    document.getElementById('editMatFile').value  = '';

    const urlWrap  = document.getElementById('editMatUrlWrap');
    const fileWrap = document.getElementById('editMatFileWrap');
    if (mat.material_type === 'file') {
        urlWrap.style.display  = 'none';
        fileWrap.style.display = 'block';
    } else {
        urlWrap.style.display  = 'block';
        fileWrap.style.display = 'none';
        document.getElementById('editMatUrl').value = mat.external_url || '';
    }
    new bootstrap.Modal(document.getElementById('editMaterialModal')).show();
}

function submitEditMaterial() {
    const id    = document.getElementById('editMatId').value;
    const type  = document.getElementById('editMatType').value;
    const title = document.getElementById('editMatTitle').value.trim();
    const errEl = document.getElementById('editMatError');
    errEl.style.display = 'none';
    if (!title) { errEl.textContent = 'Title is required.'; errEl.style.display = 'block'; return; }

    const fileInput = document.getElementById('editMatFile');
    const hasFile   = fileInput.files && fileInput.files.length > 0;

    if (type === 'file' || hasFile) {
        const fd = new FormData();
        fd.append('title', title);
        if (hasFile) fd.append('file', fileInput.files[0]);
        fetch(`${BASE}/iep/implementation/material/${id}/edit`, { method: 'POST', body: fd })
            .then(r => r.json()).then(handleEditMaterialResponse).catch(handleEditError);
    } else {
        const url = document.getElementById('editMatUrl').value.trim();
        fetch(`${BASE}/iep/implementation/material/${id}/edit`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ title, external_url: url })
        }).then(r => r.json()).then(handleEditMaterialResponse).catch(handleEditError);
    }
}

function handleEditMaterialResponse(data) {
    if (!data.success) {
        const errEl = document.getElementById('editMatError');
        errEl.textContent = data.message || 'Save failed.';
        errEl.style.display = 'block';
        return;
    }
    bootstrap.Modal.getInstance(document.getElementById('editMaterialModal')).hide();
    const mat = data.material;
    const row = document.getElementById('matRow_' + mat.id);
    if (row) row.querySelectorAll('td')[1].textContent = mat.title;
}

function handleEditError(err) { alert('Network error: ' + err.message); }

// ================================================================
// VIEW / EDIT ACTIVITIES
// ================================================================
const actTypeLabels = {
    multiple_choice: 'Multiple Choice', true_false: 'True / False',
    fill_in_blanks: 'Fill in the Blanks', matching: 'Matching',
    drag_drop_sort: 'Drag & Drop Sort', image_label: 'Image Label',
    flashcards: 'Flashcards', sequencing: 'Sequencing'
};

function viewActivity(act) {
    document.getElementById('vActTitle').textContent = act.title;
    const body = document.getElementById('viewActivityBody');
    let data = act.activity_data;
    if (typeof data === 'string') { try { data = JSON.parse(data); } catch(e) { data = {}; } }

    let html = `
        <div class="mb-3 d-flex flex-wrap gap-2">
            <span class="badge" style="background:#1e4072;">${actTypeLabels[act.activity_type] || act.activity_type}</span>
            ${act.max_score ? `<span class="badge bg-secondary">Max Score: ${act.max_score}</span>` : ''}
            ${act.due_date  ? `<span class="badge bg-warning text-dark">Due: ${act.due_date}</span>` : ''}
        </div>
        ${act.instructions ? `<div class="alert alert-light small mb-3"><strong>Instructions:</strong> ${act.instructions}</div>` : ''}
        <hr>
        <h6 class="fw-semibold mb-3" style="color:#1e4072;">Activity Content</h6>`;

    switch (act.activity_type) {
        case 'multiple_choice':
            (data.questions || []).forEach((q, qi) => {
                html += `<div class="mb-3">`;
                if (q.image) {
                    html += `<div class="mb-2"><img src="${q.image}" class="img-fluid rounded border shadow-sm" style="max-height:160px;"></div>`;
                }
                html += `<strong>Q${qi+1}:</strong> ${q.text || ''}<ul class="mt-1">`;
                (q.options || []).forEach(o => {
                    const optText = o.text || '';
                    const optImg = o.image ? `<img src="${o.image}" class="rounded border ms-1" style="height:28px;vertical-align:middle;">` : '';
                    html += `<li style="color:${o.is_correct ? '#3b6d11' : 'inherit'}">${o.is_correct ? '✓ ' : ''}${optText} ${optImg}</li>`;
                });
                html += '</ul></div>';
            });
            break;
        case 'true_false':
            if (data.image) {
                html += `<div class="mb-2"><img src="${data.image}" class="img-fluid rounded border shadow-sm" style="max-height:160px;"></div>`;
            }
            html += `<p><strong>Statement:</strong> ${data.statement || ''}</p>`;
            html += `<p><strong>Answer:</strong> <span class="badge bg-success">${(data.correct_answer || '').toUpperCase()}</span></p>`;
            break;
        case 'fill_in_blanks':
            (data.sentences || []).forEach((s, i) => {
                html += `<div class="mb-2"><strong>${i+1}.</strong> ${s.text} <span class="badge bg-success ms-1">${(s.answers || []).join(' / ')}</span></div>`;
            });
            break;
        case 'matching':
            (data.sets || [{title: 'Matching Set 1', pairs: data.pairs || []}]).forEach((set, si) => {
                html += `<div class="fw-semibold small mb-1">${set.title || 'Matching Set ' + (si + 1)}</div>`;
                html += '<div class="table-responsive"><table class="table table-sm table-bordered"><thead><tr><th>Left</th><th>Right</th></tr></thead><tbody>';
                (set.pairs || []).forEach(p => { 
                    const leftImg = p.left_image ? `<img src="${p.left_image}" class="rounded border ms-1" style="height:26px;">` : '';
                    const rightImg = p.right_image ? `<img src="${p.right_image}" class="rounded border ms-1" style="height:26px;">` : '';
                    html += `<tr><td>${p.left || ''} ${leftImg}</td><td>${p.right || ''} ${rightImg}</td></tr>`; 
                });
                html += '</tbody></table></div>';
            });
            break;
        case 'drag_drop_sort': case 'sequencing': {
            const sequenceSets = data.sets || [{title: 'Question 1', items: data.items || data.steps || []}];
            sequenceSets.forEach((set, si) => {
                const list = set.items || set.steps || [];
                html += `<div class="fw-semibold small mb-1">${set.title || 'Question ' + (si + 1)}</div>`;
                list.forEach((item, i) => { 
                    const itemText = item.text || item;
                    const itemImg = item.image ? `<img src="${item.image}" class="rounded border ms-1" style="height:24px;">` : '';
                    html += `<div class="mb-1"><span class="badge bg-secondary me-2">${i+1}</span>${itemText} ${itemImg}</div>`; 
                });
            });
            break;
        }
        case 'flashcards':
            (data.cards || []).forEach(c => {
                const fImg = c.front_image ? `<img src="${c.front_image}" class="rounded border ms-1" style="height:32px;">` : '';
                const bImg = c.back_image ? `<img src="${c.back_image}" class="rounded border ms-1" style="height:32px;">` : '';
                html += `<div class="mb-2 p-2 border rounded"><strong>Front:</strong> ${c.front || ''} ${fImg} &nbsp;→&nbsp; <strong>Back:</strong> ${c.back || ''} ${bImg}</div>`;
            });
            break;
        case 'image_label':
            if (data.image_path) html += `<img src="${BASE}/${data.image_path}" class="img-fluid rounded mb-2" alt="activity image">`;
            (data.labels || []).forEach(l => { html += `<div class="small text-muted">Label at (${l.x},${l.y}): <strong>${l.answer}</strong></div>`; });
            break;
        default:
            html += `<pre class="bg-light p-2 rounded small">${JSON.stringify(data, null, 2)}</pre>`;
    }

    body.innerHTML = html;

    const previewBtn = document.getElementById('vActLearnerPreviewBtn');
    if (previewBtn) {
        if (act.id && !act.is_f2f) {
            previewBtn.href = BASE + '/learning/activity/' + act.id;
            previewBtn.style.display = 'inline-flex';
        } else {
            previewBtn.style.display = 'none';
        }
    }

    new bootstrap.Modal(document.getElementById('viewActivityModal')).show();
}

function initEditActSummernote() {
    // legacy helper stub
}
</script>

<!-- ================================================================
     STYLES
     ================================================================ -->
<style>
/* ---- Material type cards ---- */
.material-type-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 20px 16px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    background: #fff;
    user-select: none;
}
.material-type-card:hover,
.material-type-card:focus {
    border-color: #a01422;
    box-shadow: 0 0 0 3px rgba(160,20,34,0.12);
    transform: translateY(-2px);
    outline: none;
}
.material-type-icon { font-size: 2rem; margin-bottom: 8px; }
.material-type-title { font-weight: 700; color: #1e4072; font-size: 0.9rem; margin-bottom: 4px; }
.material-type-desc  { font-size: 0.78rem; color: #6c757d; }

/* ---- Activity type cards ---- */
.activity-type-card {
    border: 2px solid #dee2e6;
    border-radius: 8px;
    padding: 14px 10px;
    text-align: center;
    cursor: pointer;
    transition: border-color 0.2s, box-shadow 0.2s, transform 0.15s;
    background: #fff;
    user-select: none;
    height: 100%;
}
.activity-type-card:hover,
.activity-type-card:focus {
    border-color: #a01422;
    box-shadow: 0 0 0 3px rgba(160,20,34,0.12);
    outline: none;
}
.activity-type-card.selected {
    border-color: #a01422;
    background: #fff5f5;
    box-shadow: 0 0 0 3px rgba(160,20,34,0.18);
}
.activity-type-icon { font-size: 1.6rem; color: #1e4072; margin-bottom: 6px; display: block; }
.activity-type-label { font-weight: 700; color: #1e4072; font-size: 0.82rem; margin-bottom: 3px; }
.activity-type-desc  { font-size: 0.72rem; color: #6c757d; }

/* ---- Builder drag handles ---- */
.drag-handle {
    cursor: grab;
    color: #adb5bd;
    padding: 0 6px;
    font-size: 1.1rem;
}
.drag-handle:active { cursor: grabbing; }
.builder-item-row {
    display: flex;
    align-items: center;
    gap: 6px;
    margin-bottom: 8px;
    background: #fff;
    border: 1px solid #dee2e6;
    border-radius: 6px;
    padding: 6px 8px;
}
.builder-item-row.drag-over { border-color: #a01422; background: #fff5f5; }

/* ---- Lesson plan option cards ---- */
.lp-option-card.selected-option {
    border-color: #a01422 !important;
    background: #fff5f5;
}

/* ---- Image label canvas ---- */
#imageLabelCanvas {
    position: relative;
    display: inline-block;
    cursor: crosshair;
}
#imageLabelCanvas img { max-width: 100%; border-radius: 6px; display: block; }
.label-marker {
    position: absolute;
    width: 22px;
    height: 22px;
    background: #a01422;
    border-radius: 50%;
    border: 2px solid #fff;
    transform: translate(-50%, -50%);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #fff;
    font-size: 0.65rem;
    font-weight: 700;
    box-shadow: 0 2px 6px rgba(0,0,0,0.3);
}

/* ---- Responsive ---- */
@media (max-width: 768px) {
    .lp-option-card { margin-bottom: 8px; }
    #matCameraWrap { display: block !important; }
}
</style>


<!-- ================================================================
     JAVASCRIPT — Part 1: Config, Helpers, Lesson Plans
     ================================================================ -->
<script>
'use strict';

/* ----------------------------------------------------------------
   Helpers
   ---------------------------------------------------------------- */
function escHtml(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}
function escAttr(str) {
    if (str === null || str === undefined) return '';
    return String(str)
        .replace(/\\/g, '\\\\')
        .replace(/'/g, "\\'")
        .replace(/"/g, '&quot;');
}

function showToast(icon, title, text) {
    Swal.fire({
        toast: true,
        position: 'top-end',
        icon: icon,
        title: title,
        text: text || '',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
    });
}

function showLoading(title) {
    Swal.fire({
        title: title || 'Please wait…',
        allowOutsideClick: false,
        allowEscapeKey: false,
        didOpen: () => Swal.showLoading(),
    });
}

async function postJSON(url, data) {
    const res = await fetch(url, {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify(data),
    });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

async function postForm(url, formData) {
    const res = await fetch(url, { method: 'POST', body: formData });
    if (!res.ok) throw new Error('HTTP ' + res.status);
    return res.json();
}

/* ----------------------------------------------------------------
   Lesson Plan — Upload Document (from card button)
   ---------------------------------------------------------------- */
function openUploadDocModal(lpId, lpTitle) {
    document.getElementById('uploadDocLpId').value = lpId;
    document.getElementById('uploadDocLpTitle').textContent = lpTitle;
    new bootstrap.Modal(document.getElementById('uploadDocModal')).show();
}

async function submitUploadDoc() {
    const lpId = document.getElementById('uploadDocLpId').value;
    const modal = document.getElementById('uploadDocModal');
    const fileInput = modal.querySelector('input[type="file"]');

    if (!fileInput || !fileInput.files.length) {
        Swal.fire({ icon: 'warning', title: 'No file selected', text: 'Please choose a file to upload.', confirmButtonColor: '#a01422' });
        return;
    }

    const fd = new FormData();
    fd.append('document', fileInput.files[0]);
    fd.append('lesson_plan_id', lpId);
    fd.append('iep_id', IEP_ID);

    showLoading('Uploading document…');

    try {
        const data = await postForm(BASE + '/iep/implementation/lesson-plan/upload-doc', fd);
        Swal.close();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('uploadDocModal'))?.hide();
            showToast('success', 'Document uploaded!');
            setTimeout(() => location.reload(), 800);
        } else {
            Swal.fire({ icon: 'error', title: 'Upload failed', text: data.message || 'Could not upload document.', confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Lesson Plan — Publish
   ---------------------------------------------------------------- */
function confirmPublish(lpId, lpTitle) {
    Swal.fire({
        icon: 'question',
        title: 'Publish lesson plan?',
        html: 'This will make <strong>' + lpTitle + '</strong> visible to assigned learners.',
        showCancelButton: true,
        confirmButtonColor: '#3b6d11',
        cancelButtonColor: '#6c757d',
        confirmButtonText: '<i class="ti ti-send"></i> Publish',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        showLoading('Publishing…');
        try {
            const data = await postJSON(BASE + '/iep/implementation/lesson-plan/' + lpId + '/publish', { lesson_plan_id: lpId });
            Swal.close();
            if (data.success) {
                const card = document.getElementById('lp-' + lpId);
                if (card) {
                    const badge = card.querySelector('.badge.bg-secondary');
                    if (badge) {
                        badge.style.background = '#3b6d11';
                        badge.className = 'badge';
                        badge.innerHTML = '<i class="ti ti-circle-check me-1"></i>Published';
                    }
                    const publishBtn = card.querySelector('button[onclick*="confirmPublish"]');
                    if (publishBtn) publishBtn.remove();
                    const cardEl = card.querySelector('.card');
                    if (cardEl) cardEl.style.borderLeftColor = '#3b6d11';
                }
                showToast('success', 'Published!', lpTitle + ' is now live.');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
        }
    });
}

/* ----------------------------------------------------------------
   Lesson Plan — Delete
   ---------------------------------------------------------------- */
function confirmDeleteLessonPlan(lpId, lpTitle) {
    Swal.fire({
        icon: 'warning',
        title: 'Delete lesson plan?',
        html: 'This will permanently delete <strong>' + lpTitle + '</strong> and all its materials and activities.',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        showLoading('Deleting…');
        try {
            const data = await postJSON(BASE + '/iep/implementation/lesson-plan/' + lpId + '/delete', { lesson_plan_id: lpId });
            Swal.close();
            if (data.success) {
                const card = document.getElementById('lp-' + lpId);
                if (card) card.remove();
                showToast('success', 'Deleted', lpTitle + ' was removed.');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
        }
    });
}
</script>


<!-- ================================================================
     JAVASCRIPT — Part 2: Materials
     ================================================================ -->
<script>
/* ----------------------------------------------------------------
   Materials — open modal by type
   ---------------------------------------------------------------- */
function openMaterialModal(type) {
    if (type === 'interactive') {
        openInteractiveLessonChoiceModal();
        return;
    }
    const modalIds = { file: 'matFileModal', link: 'matLinkModal', embed: 'matEmbedModal' };
    const id = modalIds[type];
    if (id) {
        let el = document.getElementById(id);
        if (el) {
            let modal = bootstrap.Modal.getOrCreateInstance(el);
            modal.show();
        }
    }
}

function openInteractiveLessonChoiceModal() {
    const el = document.getElementById('interactiveLessonChoiceModal');
    if (el) {
        const select = document.getElementById('interactiveChoiceLpSelect');
        if (select) {
            select.value = '';
            onInteractiveLpSelectChanged(select);
        }
        const modal = bootstrap.Modal.getOrCreateInstance(el);
        modal.show();
    }
}

function onInteractiveLpSelectChanged(select) {
    const infoBox = document.getElementById('interactiveLpSelectedInfoBox');
    if (!select || !select.value) {
        if (infoBox) infoBox.style.display = 'none';
        return;
    }

    const opt = select.options[select.selectedIndex];
    const domainLabel = opt.getAttribute('data-domain-label') || 'General';
    const slideCount = parseInt(opt.getAttribute('data-slides') || '0', 10);

    const domainBadge = document.getElementById('interactiveLpDomainBadge');
    const slideBadge = document.getElementById('interactiveLpSlideBadge');
    const promptEl = document.getElementById('interactiveSlideActionPrompt');
    const clearBtn = document.getElementById('btnStartEmptyInteractiveSlides');
    const openBtn = document.getElementById('btnOpenInteractiveStudio');

    if (domainBadge) domainBadge.textContent = domainLabel;
    if (slideBadge) slideBadge.textContent = slideCount > 0 ? (slideCount + ' Slides') : '0 Slides (None yet)';

    if (promptEl) {
        if (slideCount > 0) {
            promptEl.innerHTML = `<div class="alert alert-info py-2 px-3 small mb-0"><i class="ti ti-info-circle me-1"></i> This lesson plan currently has <strong>${slideCount} slides</strong>. You can continue editing or clear them to start fresh.</div>`;
            if (clearBtn) {
                clearBtn.style.display = 'block';
                clearBtn.innerHTML = `<i class="ti ti-trash me-1"></i> Clear &amp; Start with Empty Slides (0 Slides)`;
            }
            if (openBtn) {
                openBtn.innerHTML = `<i class="ti ti-presentation me-1"></i> Open Studio (Edit ${slideCount} Slides)`;
            }
        } else {
            promptEl.innerHTML = `<div class="alert alert-warning py-2 px-3 small mb-0"><i class="ti ti-sparkles me-1"></i> <strong>No interactive slides yet</strong> for this lesson plan. Opening the studio will start with an empty slide deck.</div>`;
            if (clearBtn) clearBtn.style.display = 'none';
            if (openBtn) {
                openBtn.innerHTML = `<i class="ti ti-sparkles me-1"></i> Open Studio (Start with Empty Slides)`;
            }
        }
    }

    if (infoBox) infoBox.style.display = 'block';
}

function openChosenInteractiveStudio() {
    const select = document.getElementById('interactiveChoiceLpSelect');
    if (!select || !select.value) {
        alert('Please select a Lesson Plan first.');
        return;
    }
    window.location.href = BASE + '/iep/implementation/lesson/' + select.value + '/builder';
}

function startEmptyInteractiveSlides() {
    const select = document.getElementById('interactiveChoiceLpSelect');
    if (!select || !select.value) {
        alert('Please select a Lesson Plan first.');
        return;
    }
    const lpId = select.value;
    const opt = select.options[select.selectedIndex];
    const lpTitle = opt ? opt.textContent.trim() : 'Lesson Plan';

    Swal.fire({
        title: 'Clear Existing Slides?',
        text: `Are you sure you want to delete the current slides of "${lpTitle}" and start with 0 / empty slides?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, Start with Empty Slides',
        cancelButtonText: 'Cancel'
    }).then((result) => {
        if (result.isConfirmed) {
            fetch(`${BASE}/iep/implementation/lesson-plan/${lpId}/pages/clear`, {
                method: 'POST',
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            })
            .then(r => r.json())
            .then(data => {
                if (data.success) {
                    window.location.href = `${BASE}/iep/implementation/lesson/${lpId}/builder`;
                } else {
                    alert('Could not clear slides: ' + (data.message || 'Error occurred'));
                }
            })
            .catch(err => {
                console.error(err);
                window.location.href = `${BASE}/iep/implementation/lesson/${lpId}/builder`;
            });
        }
    });
}

function toggleNewLessonPlanForm() {
    const form = document.getElementById('newLessonPlanStepForm');
    const icon = document.getElementById('toggleNewLpIcon');
    if (!form) return;
    const isHidden = form.style.display === 'none';
    form.style.display = isHidden ? 'block' : 'none';
    if (icon) {
        icon.className = isHidden ? 'ti ti-minus-circle me-1.5' : 'ti ti-plus-circle me-1.5';
    }
}

function createStepLessonPlanAndOpenStudio() {
    const titleInput = document.getElementById('newStepLpTitle');
    const domainInput = document.getElementById('newStepLpDomain');
    const title = titleInput ? titleInput.value.trim() : '';
    const domain = domainInput ? domainInput.value.trim() : 'daily_living_skills';

    if (!title) {
        alert('Please enter a title for the new lesson plan.');
        if (titleInput) titleInput.focus();
        return;
    }

    const btn = document.getElementById('btnCreateStepLp');
    if (btn) {
        btn.disabled = true;
        btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Creating Lesson Plan...';
    }

    fetch(BASE + '/iep/implementation/lesson-plan/create', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/json',
            'X-Requested-With': 'XMLHttpRequest'
        },
        body: JSON.stringify({
            title: title,
            pdsp_domain: domain,
            assignment_type: 'individual',
            iep_id: <?php echo (int)$iepId; ?>,
            student_id: <?php echo (int)($iep['student_id'] ?? 0); ?>
        })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success && data.lesson_plan_id) {
            window.location.href = BASE + '/iep/implementation/lesson/' + data.lesson_plan_id + '/builder';
        } else {
            if (btn) {
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-sparkles me-1"></i> Create Lesson Plan &amp; Open Studio';
            }
            alert('Failed to create lesson plan: ' + (data.message || 'Error occurred'));
        }
    })
    .catch(err => {
        if (btn) {
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-sparkles me-1"></i> Create Lesson Plan &amp; Open Studio';
        }
        console.error('Create lesson plan error:', err);
        alert('A network error occurred.');
    });
}

/* ----------------------------------------------------------------
   Materials — detect embed type
   ---------------------------------------------------------------- */
function detectEmbedType(url) {
    const hint = document.getElementById('matEmbedTypeHint');
    if (!hint) return;
    if (url.includes('youtube.com') || url.includes('youtu.be')) {
        hint.textContent = '✓ Detected: YouTube';
        hint.style.color = '#3b6d11';
    } else if (url.includes('drive.google.com')) {
        hint.textContent = '✓ Detected: Google Drive';
        hint.style.color = '#3b6d11';
    } else if (url.length > 5) {
        hint.textContent = 'Other embed URL';
        hint.style.color = '#6c757d';
    } else {
        hint.textContent = '';
    }
}

/* ----------------------------------------------------------------
   Materials — append row to table
   ---------------------------------------------------------------- */
function appendMaterialRow(mat) {
    const typeIcons  = { file: '📁', link: '🔗', embed: '▶' };
    const typeBg     = { file: '#1e4072', link: '#6c757d', embed: '#a01422' };
    const icon       = typeIcons[mat.material_type] || '📁';
    const bg         = typeBg[mat.material_type]    || '#6c757d';
    const typeLabel  = mat.material_type.charAt(0).toUpperCase() + mat.material_type.slice(1);

    // Find lesson plan title
    const lpSel = document.getElementById('matFileLessonPlan') ||
                  document.getElementById('matLinkLessonPlan') ||
                  document.getElementById('matEmbedLessonPlan');
    let lpTitle = '—';
    if (lpSel) {
        const opt = lpSel.querySelector('option[value="' + mat.lesson_plan_id + '"]');
        if (opt) lpTitle = opt.textContent.trim();
    }

    const tbody = document.getElementById('materialsTableBody');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.id = 'matRow_' + mat.id;
    const matJson = JSON.stringify(mat).replace(/"/g, '&quot;');
    tr.innerHTML = `
        <td class="text-center">${icon}</td>
        <td><a href="javascript:void(0)" class="fw-semibold text-decoration-none text-dark" onclick='viewMaterial(${matJson})'>${escHtml(mat.title)}</a></td>
        <td><span class="badge" style="background:${bg};font-size:0.7rem;">${typeLabel}</span></td>
        <td class="text-muted">${escHtml(lpTitle)}</td>
        <td>
            <div class="d-flex gap-1 flex-wrap">
                <button class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                        onclick='viewMaterial(${matJson})'>
                    <i class="ti ti-eye me-1"></i>View
                </button>
                <button class="btn btn-sm" style="background:#3b6d11;color:#fff;border:none;font-size:0.75rem;"
                        onclick='openEditMaterial(${matJson})'>
                    <i class="ti ti-pencil me-1"></i>Edit
                </button>
                <button class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.75rem;"
                        onclick="confirmDeleteMaterial(${mat.id}, '${escAttr(mat.title)}')">
                    <i class="ti ti-trash me-1"></i>Delete
                </button>
            </div>
        </td>`;
    tbody.appendChild(tr);

    // Show table, hide empty state
    document.getElementById('materialsList').style.display = '';
    const empty = document.getElementById('materialsEmptyState');
    if (empty) empty.style.display = 'none';
}

function escHtml(str) {
    const d = document.createElement('div');
    d.textContent = str || '';
    return d.innerHTML;
}
function escAttr(str) {
    return (str || '').replace(/'/g, "\\'").replace(/"/g, '&quot;');
}

/* ----------------------------------------------------------------
   Materials — Submit File
   ---------------------------------------------------------------- */
async function submitMaterialFile() {
    const lpId     = document.getElementById('matFileLessonPlan').value;
    const title    = document.getElementById('matFileTitle').value.trim();
    const fileInput = document.getElementById('matFileInput');
    const cameraInput = document.getElementById('matCameraInput');
    const errEl    = document.getElementById('matFileError');

    // Hide previous error
    if (errEl) errEl.style.display = 'none';

    if (!lpId)  { Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#a01422' }); return; }
    if (!title) { Swal.fire({ icon: 'warning', title: 'Enter a title', confirmButtonColor: '#a01422' }); return; }

    // Determine which input has a file
    let selectedFile = null;
    if (fileInput && fileInput.files && fileInput.files.length > 0) {
        selectedFile = fileInput.files[0];
    } else if (cameraInput && cameraInput.files && cameraInput.files.length > 0) {
        selectedFile = cameraInput.files[0];
    }

    if (!selectedFile) {
        Swal.fire({ icon: 'warning', title: 'No file selected', text: 'Please choose a file to upload.', confirmButtonColor: '#a01422' });
        return;
    }

    // Validate file size
    const ext = selectedFile.name.split('.').pop().toLowerCase();
    const maxBytes = ext === 'mp4' ? 50 * 1024 * 1024 : 10 * 1024 * 1024;
    if (selectedFile.size > maxBytes) {
        const limit = ext === 'mp4' ? '50MB' : '10MB';
        Swal.fire({ icon: 'warning', title: 'File too large', text: 'Maximum size is ' + limit + ' for this file type.', confirmButtonColor: '#a01422' });
        return;
    }

    // Validate file type
    const allowed = ['jpg', 'jpeg', 'png', 'pdf', 'mp4'];
    if (!allowed.includes(ext)) {
        Swal.fire({ icon: 'warning', title: 'Invalid file type', text: 'Only JPG, PNG, PDF, and MP4 are allowed.', confirmButtonColor: '#a01422' });
        return;
    }

    const fd = new FormData();
    fd.append('lesson_plan_id', lpId);
    fd.append('material_type', 'file');
    fd.append('title', title);
    fd.append('file', selectedFile);

    showLoading('Uploading material…');
    try {
        const data = await postForm(BASE + '/iep/implementation/material/add', fd);
        Swal.close();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('matFileModal'))?.hide();
            appendMaterialRow(data.material);
            showToast('success', 'Material added!');
            // Reset form
            document.getElementById('matFileTitle').value = '';
            document.getElementById('matFileLessonPlan').value = '';
            if (fileInput) fileInput.value = '';
            if (cameraInput) cameraInput.value = '';
        } else {
            Swal.fire({ icon: 'error', title: 'Upload failed', text: data.message || 'Could not upload file.', confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Materials — Submit Link
   ---------------------------------------------------------------- */
async function submitMaterialLink() {
    const lpId  = document.getElementById('matLinkLessonPlan').value;
    const title = document.getElementById('matLinkTitle').value.trim();
    const url   = document.getElementById('matLinkUrl').value.trim();

    if (!lpId)  { Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#a01422' }); return; }
    if (!title) { Swal.fire({ icon: 'warning', title: 'Enter a title', confirmButtonColor: '#a01422' }); return; }
    if (!url)   { Swal.fire({ icon: 'warning', title: 'Enter a URL', confirmButtonColor: '#a01422' }); return; }

    showLoading('Saving material…');
    try {
        const data = await postJSON(BASE + '/iep/implementation/material/add', {
            lesson_plan_id: parseInt(lpId), material_type: 'link', title, external_url: url,
        });
        Swal.close();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('matLinkModal'))?.hide();
            appendMaterialRow(data.material);
            showToast('success', 'Link added!');
            document.getElementById('matLinkTitle').value = '';
            document.getElementById('matLinkUrl').value   = '';
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Materials — Submit Embed
   ---------------------------------------------------------------- */
async function submitMaterialEmbed() {
    const lpId  = document.getElementById('matEmbedLessonPlan').value;
    const title = document.getElementById('matEmbedTitle').value.trim();
    const url   = document.getElementById('matEmbedUrl').value.trim();

    if (!lpId)  { Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#a01422' }); return; }
    if (!title) { Swal.fire({ icon: 'warning', title: 'Enter a title', confirmButtonColor: '#a01422' }); return; }
    if (!url)   { Swal.fire({ icon: 'warning', title: 'Enter a URL', confirmButtonColor: '#a01422' }); return; }

    showLoading('Saving embed…');
    try {
        const data = await postJSON(BASE + '/iep/implementation/material/add', {
            lesson_plan_id: parseInt(lpId), material_type: 'embed', title, external_url: url,
        });
        Swal.close();
        if (data.success) {
            bootstrap.Modal.getInstance(document.getElementById('matEmbedModal'))?.hide();
            appendMaterialRow(data.material);
            showToast('success', 'Embed added!');
            document.getElementById('matEmbedTitle').value = '';
            document.getElementById('matEmbedUrl').value   = '';
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Materials — Delete
   ---------------------------------------------------------------- */
function confirmDeleteMaterial(matId, matTitle) {
    Swal.fire({
        icon: 'warning',
        title: 'Delete material?',
        html: 'Remove <strong>' + matTitle + '</strong>?',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        showLoading('Deleting…');
        try {
            const data = await postJSON(BASE + '/iep/implementation/material/' + matId + '/delete', { material_id: matId });
            Swal.close();
            if (data.success) {
                const row = document.getElementById('matRow_' + matId);
                if (row) row.remove();
                showToast('success', 'Deleted', matTitle + ' removed.');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
        }
    });
}
</script>


<!-- ================================================================
     JAVASCRIPT — Part 3: Activity Builder
     ================================================================ -->
<script>
/* ----------------------------------------------------------------
   Activity type selection
   ---------------------------------------------------------------- */
let selectedActivityType = null;
let editingActivityId = null;

const activityTypeLabels = {
    multiple_choice: 'Multiple Choice',
    true_false:      'True / False',
    fill_in_blanks:  'Fill in the Blanks',
    matching:        'Matching',
    drag_drop_sort:  'Drag & Drop Sort',
    image_label:     'Image Label',
    flashcards:      'Flashcards',
    sequencing:      'Sequencing',
};

function initActivitySummernote() {
    if (typeof $ !== 'undefined' && $.fn.summernote) {
        if (!$('#builderInstructions').hasClass('summernote-initialized')) {
            $('#builderInstructions').summernote({
                placeholder: 'Instructions for the learner (tables, formatted text, and images supported)...',
                tabsize: 2,
                height: 160,
                toolbar: [
                    ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
                    ['font', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture']],
                    ['view', ['fullscreen', 'codeview']]
                ],
                callbacks: {
                    onImageUpload: function(files) {
                        if (!files || !files.length) return;
                        for (let i = 0; i < files.length; i++) {
                            uploadEditorImage(files[i], $(this));
                        }
                    }
                }
            });
            $('#builderInstructions').addClass('summernote-initialized');
        }
    }
}

function selectActivityType(type) {
    editingActivityId = null;
    selectedActivityType = type;

    // Reset save button label & builder header & preview button
    const saveBtn = document.querySelector('#activityBuilder button[onclick="saveActivity()"]');
    if (saveBtn) saveBtn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Save Activity';

    const builderPreviewBtn = document.getElementById('builderLearnerPreviewBtn');
    if (builderPreviewBtn) {
        builderPreviewBtn.classList.remove('d-flex');
        builderPreviewBtn.classList.add('d-none');
    }

    // Update card selection styles
    document.querySelectorAll('.activity-type-card').forEach(c => c.classList.remove('selected'));
    const card = document.getElementById('actCard_' + type);
    if (card) card.classList.add('selected');

    // Update builder label
    document.getElementById('builderTypeLabel').textContent = activityTypeLabels[type] || type;

    // Show/hide max score (hidden for flashcards)
    const maxScoreWrap = document.getElementById('builderMaxScoreWrap');
    if (maxScoreWrap) maxScoreWrap.style.display = type === 'flashcards' ? 'none' : '';

    // Render type-specific builder
    renderBuilderTypeArea(type);

    // Sync Face-to-Face fields
    if (document.getElementById('builderIsF2F')) {
        document.getElementById('builderIsF2F').checked = false;
        toggleF2FFields();
    }

    if (type === 'multiple_choice') addMCQuestion();
    if (type === 'true_false') addTFStatement();
    if (type === 'fill_in_blanks') addFibQuestion();
    if (type === 'matching') addMatchingSet();
    if (type === 'drag_drop_sort') addSortingQuestion();
    if (type === 'flashcards') addFlashcardSet();
    if (type === 'sequencing') addSequenceQuestion();

    // Initialize WYSIWYG editor on instructions field
    initActivitySummernote();

    // Slide builder into view
    const builder = document.getElementById('activityBuilder');
    builder.style.display = 'block';
    builder.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
}

function toggleF2FFields() {
    const isF2F = document.getElementById('builderIsF2F') && document.getElementById('builderIsF2F').checked;
    const maxScoreWrap = document.getElementById('builderMaxScoreWrap');
    const typeArea = document.getElementById('builderTypeArea');
    
    let f2fNote = document.getElementById('builderF2FNote');
    if (!f2fNote) {
        f2fNote = document.createElement('div');
        f2fNote.id = 'builderF2FNote';
        f2fNote.className = 'alert alert-info py-2 px-3 small mb-3';
        f2fNote.innerHTML = '<i class="ti ti-info-circle me-1"></i> <strong>Face-to-Face Mode:</strong> Teacher will rate this activity manually during or after the classroom session.';
        typeArea.parentNode.insertBefore(f2fNote, typeArea);
    }
    
    if (isF2F) {
        if (maxScoreWrap) maxScoreWrap.style.display = 'none';
        typeArea.style.display = 'none';
        f2fNote.style.display = 'block';
    } else {
        if (maxScoreWrap) {
            maxScoreWrap.style.display = selectedActivityType === 'flashcards' ? 'none' : '';
        }
        typeArea.style.display = 'block';
        f2fNote.style.display = 'none';
    }
}

function closeActivityBuilder() {
    document.getElementById('activityBuilder').style.display = 'none';
    document.querySelectorAll('.activity-type-card').forEach(c => c.classList.remove('selected'));
    selectedActivityType = null;
    editingActivityId = null;

    document.getElementById('builderTypeLabel').textContent = 'Activity Builder';
    const saveBtn = document.querySelector('#activityBuilder button[onclick="saveActivity()"]');
    if (saveBtn) saveBtn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Save Activity';

    const builderPreviewBtn = document.getElementById('builderLearnerPreviewBtn');
    if (builderPreviewBtn) {
        builderPreviewBtn.classList.remove('d-flex');
        builderPreviewBtn.classList.add('d-none');
    }

    document.getElementById('builderTitle').value = '';
    document.getElementById('builderDueDate').value = '';
    document.getElementById('builderMaxScore').value = '10';
    if (document.getElementById('builderIsF2F')) {
        document.getElementById('builderIsF2F').checked = false;
        toggleF2FFields();
    }
    if (typeof $ !== 'undefined' && $('#builderInstructions').hasClass('summernote-initialized')) {
        $('#builderInstructions').summernote('code', '');
    } else {
        document.getElementById('builderInstructions').value = '';
    }
}

function openEditActivity(act) {
    editingActivityId = act.id;
    selectedActivityType = act.activity_type;

    // Highlight corresponding activity type card
    document.querySelectorAll('.activity-type-card').forEach(c => c.classList.remove('selected'));
    const card = document.getElementById('actCard_' + act.activity_type);
    if (card) card.classList.add('selected');

    // Header label
    const typeLabel = activityTypeLabels[act.activity_type] || act.activity_type;
    document.getElementById('builderTypeLabel').innerHTML = `<i class="ti ti-edit me-1"></i>Edit Activity: <span class="fw-normal text-muted">${typeLabel}</span>`;

    // Preview as Learner button in builder header
    const builderPreviewBtn = document.getElementById('builderLearnerPreviewBtn');
    if (builderPreviewBtn) {
        if (act.id && !act.is_f2f) {
            builderPreviewBtn.href = `${BASE}/learning/activity/${act.id}`;
            builderPreviewBtn.classList.remove('d-none');
            builderPreviewBtn.classList.add('d-flex');
        } else {
            builderPreviewBtn.classList.remove('d-flex');
            builderPreviewBtn.classList.add('d-none');
        }
    }

    // Populate common inputs
    document.getElementById('builderLessonPlan').value = act.lesson_plan_id || '';
    document.getElementById('builderTitle').value = act.title || '';

    initActivitySummernote();
    if (typeof $ !== 'undefined' && $('#builderInstructions').hasClass('summernote-initialized')) {
        $('#builderInstructions').summernote('code', act.instructions || '');
    } else {
        document.getElementById('builderInstructions').value = act.instructions || '';
    }

    document.getElementById('builderDueDate').value = act.due_date ? act.due_date.substring(0, 10) : '';
    document.getElementById('builderMaxScore').value = act.max_score || 0;

    const isF2F = !!parseInt(act.is_f2f);
    if (document.getElementById('builderIsF2F')) {
        document.getElementById('builderIsF2F').checked = isF2F;
        toggleF2FFields();
    }

    const maxScoreWrap = document.getElementById('builderMaxScoreWrap');
    if (maxScoreWrap && !isF2F) {
        maxScoreWrap.style.display = act.activity_type === 'flashcards' ? 'none' : '';
    }

    // Render builder type area & populate content
    renderBuilderTypeArea(act.activity_type);

    let data = act.activity_data;
    if (typeof data === 'string') {
        try { data = JSON.parse(data); } catch(e) { data = {}; }
    }
    data = data || {};
    populateBuilderData(act.activity_type, data);

    // Update Save button text
    const saveBtn = document.querySelector('#activityBuilder button[onclick="saveActivity()"]');
    if (saveBtn) saveBtn.innerHTML = '<i class="ti ti-device-floppy me-1"></i>Save Changes';

    // Display builder and smooth scroll
    const builder = document.getElementById('activityBuilder');
    builder.style.display = 'block';
    builder.scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function populateBuilderData(type, data) {
    if (!data) return;

    switch (type) {
        case 'multiple_choice': {
            const list = document.getElementById('mcQuestionList');
            if (list) list.innerHTML = '';
            const questions = data.questions || [];
            if (questions.length > 0) {
                questions.forEach(q => {
                    addMCQuestion();
                    const qDiv = document.getElementById('mcQ_' + mcQCount);
                    if (qDiv) {
                        const textInp = qDiv.querySelector('.mc-question-text');
                        if (textInp) textInp.value = q.text || '';
                        if (q.image) {
                            const imgInp = qDiv.querySelector('.mc-question-image');
                            const preview = qDiv.querySelector('.mc-q-img-preview');
                            if (imgInp) imgInp.value = q.image;
                            if (preview) {
                                const img = preview.querySelector('img');
                                if (img) img.src = q.image;
                                preview.style.setProperty('display', 'flex', 'important');
                            }
                        }
                        const ptsInp = qDiv.querySelector('.mc-points');
                        if (ptsInp) ptsInp.value = q.points !== undefined ? q.points : 1;

                        const optsContainer = document.getElementById('mcQ_' + mcQCount + '_opts');
                        if (optsContainer) {
                            optsContainer.innerHTML = '';
                            (q.options || []).forEach(opt => {
                                addMCOption(optsContainer.id);
                                const optRows = optsContainer.querySelectorAll('.mc-option-row');
                                const lastRow = optRows[optRows.length - 1];
                                if (lastRow) {
                                    const optText = lastRow.querySelector('.mc-option-text');
                                    if (optText) optText.value = opt.text || '';
                                    const optRadio = lastRow.querySelector('.mc-correct-radio');
                                    if (optRadio) optRadio.checked = !!opt.isCorrect || !!opt.is_correct;
                                    if (opt.image) {
                                        const optImgInp = lastRow.querySelector('.mc-option-image');
                                        const optPreview = lastRow.querySelector('.mc-opt-img-preview');
                                        if (optImgInp) optImgInp.value = opt.image;
                                        if (optPreview) {
                                            const oimg = optPreview.querySelector('img');
                                            if (oimg) oimg.src = opt.image;
                                            optPreview.style.setProperty('display', 'flex', 'important');
                                        }
                                    }
                                }
                            });
                        }
                    }
                });
            }
            break;
        }

        case 'true_false': {
            const container = document.getElementById('tfStatements');
            if (container) container.innerHTML = '';
            const questions = data.questions && data.questions.length > 0
                ? data.questions
                : (data.statement ? [{ statement: data.statement, image: data.image, answer: data.answer || data.correct_answer, points: data.points }] : []);

            if (questions.length > 0) {
                questions.forEach(q => {
                    addTFStatement();
                    const rows = document.querySelectorAll('.tf-statement-row');
                    const lastRow = rows[rows.length - 1];
                    if (lastRow) {
                        const stmtInp = lastRow.querySelector('.tf-statement-text');
                        if (stmtInp) stmtInp.value = q.statement || q.text || '';
                        if (q.image) {
                            const imgInp = lastRow.querySelector('.tf-statement-image');
                            const preview = lastRow.querySelector('.tf-img-preview');
                            if (imgInp) imgInp.value = q.image;
                            if (preview) {
                                const img = preview.querySelector('img');
                                if (img) img.src = q.image;
                                preview.style.setProperty('display', 'flex', 'important');
                            }
                        }
                        const ans = String(q.answer !== undefined ? q.answer : (q.correct_answer || 'true')).toLowerCase();
                        const radios = lastRow.querySelectorAll('.tf-answer-radio');
                        radios.forEach(r => { if (r.value === ans) r.checked = true; });
                        const pts = lastRow.querySelector('.tf-points');
                        if (pts) pts.value = q.points !== undefined ? q.points : 1;
                    }
                });
            }
            break;
        }

        case 'fill_in_blanks': {
            const container = document.getElementById('fibQuestions');
            if (container) container.innerHTML = '';
            const mode = data.answer_mode || 'word_bank';
            document.querySelectorAll('.fib-mode-radio').forEach(r => { if (r.value === mode) r.checked = true; });
            toggleFibModeFields();
            const distInp = document.getElementById('fibDistractors');
            if (distInp) distInp.value = Array.isArray(data.distractors) ? data.distractors.join(', ') : (data.distractors || '');

            const sentences = data.sentences && data.sentences.length > 0
                ? data.sentences
                : (data.sentence ? [{ text: data.sentence, answers: data.answers, points: data.points }] : []);

            if (sentences.length > 0) {
                sentences.forEach(s => {
                    addFibQuestion();
                    const rows = document.querySelectorAll('.fib-question-row');
                    const lastRow = rows[rows.length - 1];
                    if (lastRow) {
                        const sentInp = lastRow.querySelector('.fib-sentence');
                        if (sentInp) {
                            sentInp.value = s.text || '';
                            updateFibPreview(sentInp);
                        }
                        const ansInputs = lastRow.querySelectorAll('.fib-answer-input');
                        (s.answers || []).forEach((ans, ai) => {
                            if (ansInputs[ai]) ansInputs[ai].value = ans;
                        });
                        const ptsInp = lastRow.querySelector('.fib-points');
                        if (ptsInp) ptsInp.value = s.points !== undefined ? s.points : 1;
                    }
                });
            }
            break;
        }

        case 'matching': {
            const container = document.getElementById('matchingSets');
            if (container) container.innerHTML = '';
            const sets = data.sets && data.sets.length > 0
                ? data.sets
                : [{ title: 'Matching Set 1', pairs: data.pairs || [], points: data.points || 1 }];

            sets.forEach(set => {
                addMatchingSet();
                const setEls = document.querySelectorAll('.matching-set');
                const lastSet = setEls[setEls.length - 1];
                if (lastSet) {
                    const titleInp = lastSet.querySelector('.matching-set-title');
                    if (titleInp) titleInp.value = set.title || '';
                    const ptsInp = lastSet.querySelector('.matching-points');
                    if (ptsInp) ptsInp.value = set.points !== undefined ? set.points : 1;
                    const pairsWrap = lastSet.querySelector('.matching-pairs');
                    if (pairsWrap) {
                        pairsWrap.innerHTML = '';
                        (set.pairs || []).forEach(p => {
                            addMatchingPair(lastSet.id);
                            const pairRows = lastSet.querySelectorAll('.matching-pair');
                            const lastPair = pairRows[pairRows.length - 1];
                            if (lastPair) {
                                const leftInp = lastPair.querySelector('.matching-left');
                                if (leftInp) leftInp.value = p.left || '';
                                if (p.left_image) {
                                    const lImgInp = lastPair.querySelector('.matching-left-image');
                                    const lPrev = lastPair.querySelector('.match-left-preview');
                                    if (lImgInp) lImgInp.value = p.left_image;
                                    if (lPrev) {
                                        const img = lPrev.querySelector('img');
                                        if (img) img.src = p.left_image;
                                        lPrev.style.setProperty('display', 'flex', 'important');
                                    }
                                }
                                const rightInp = lastPair.querySelector('.matching-right');
                                if (rightInp) rightInp.value = p.right || '';
                                if (p.right_image) {
                                    const rImgInp = lastPair.querySelector('.matching-right-image');
                                    const rPrev = lastPair.querySelector('.match-right-preview');
                                    if (rImgInp) rImgInp.value = p.right_image;
                                    if (rPrev) {
                                        const img = rPrev.querySelector('img');
                                        if (img) img.src = p.right_image;
                                        rPrev.style.setProperty('display', 'flex', 'important');
                                    }
                                }
                            }
                        });
                    }
                }
            });
            break;
        }

        case 'drag_drop_sort': {
            const container = document.getElementById('sortingQuestions');
            if (container) container.innerHTML = '';
            const sets = data.sets && data.sets.length > 0
                ? data.sets
                : [{ title: 'Sorting Question 1', items: data.items || [], points: data.points || 1 }];

            sets.forEach(set => {
                addSortingQuestion();
                const setEls = document.querySelectorAll('.sorting-question');
                const lastSet = setEls[setEls.length - 1];
                if (lastSet) {
                    const titleInp = lastSet.querySelector('.sorting-title');
                    if (titleInp) titleInp.value = set.title || '';
                    const ptsInp = lastSet.querySelector('.sorting-points');
                    if (ptsInp) ptsInp.value = set.points !== undefined ? set.points : 1;
                    const itemsWrap = lastSet.querySelector('.drag-drop-items');
                    if (itemsWrap) {
                        itemsWrap.innerHTML = '';
                        (set.items || []).forEach(item => {
                            addDragDropItem(lastSet.id);
                            const itemRows = lastSet.querySelectorAll('.drag-drop-item');
                            const lastItem = itemRows[itemRows.length - 1];
                            if (lastItem) {
                                const txtInp = lastItem.querySelector('.drag-drop-text');
                                if (txtInp) txtInp.value = item.text || item;
                                if (item.image) {
                                    const imgInp = lastItem.querySelector('.drag-drop-image');
                                    const prev = lastItem.querySelector('.drag-item-preview');
                                    if (imgInp) imgInp.value = item.image;
                                    if (prev) {
                                        const img = prev.querySelector('img');
                                        if (img) img.src = item.image;
                                        prev.style.setProperty('display', 'flex', 'important');
                                    }
                                }
                            }
                        });
                    }
                }
            });
            break;
        }

        case 'sequencing': {
            const container = document.getElementById('sequenceQuestions');
            if (container) container.innerHTML = '';
            const sets = data.sets && data.sets.length > 0
                ? data.sets
                : [{ title: 'Sequence Question 1', steps: data.steps || [], points: data.points || 1 }];

            sets.forEach(set => {
                addSequenceQuestion();
                const setEls = document.querySelectorAll('.sequence-question');
                const lastSet = setEls[setEls.length - 1];
                if (lastSet) {
                    const titleInp = lastSet.querySelector('.sequence-title');
                    if (titleInp) titleInp.value = set.title || '';
                    const ptsInp = lastSet.querySelector('.sequence-points');
                    if (ptsInp) ptsInp.value = set.points !== undefined ? set.points : 1;
                    const stepsWrap = lastSet.querySelector('.sequence-steps');
                    if (stepsWrap) {
                        stepsWrap.innerHTML = '';
                        (set.steps || []).forEach(step => {
                            addSequencingStep(lastSet.id);
                            const stepRows = lastSet.querySelectorAll('.sequencing-step');
                            const lastStep = stepRows[stepRows.length - 1];
                            if (lastStep) {
                                const txtInp = lastStep.querySelector('.sequencing-text');
                                if (txtInp) txtInp.value = step.text || step;
                                if (step.image) {
                                    const imgInp = lastStep.querySelector('.sequencing-image');
                                    const prev = lastStep.querySelector('.seq-item-preview');
                                    if (imgInp) imgInp.value = step.image;
                                    if (prev) {
                                        const img = prev.querySelector('img');
                                        if (img) img.src = step.image;
                                        prev.style.setProperty('display', 'flex', 'important');
                                    }
                                }
                            }
                        });
                    }
                }
            });
            break;
        }

        case 'flashcards': {
            const container = document.getElementById('flashcardSets');
            if (container) container.innerHTML = '';
            const sets = data.sets && data.sets.length > 0
                ? data.sets
                : [{ title: 'Flashcard Set 1', cards: data.cards || [] }];

            sets.forEach(set => {
                addFlashcardSet();
                const setEls = document.querySelectorAll('.flashcard-set');
                const lastSet = setEls[setEls.length - 1];
                if (lastSet) {
                    const titleInp = lastSet.querySelector('.flashcard-set-title');
                    if (titleInp) titleInp.value = set.title || '';
                    const listWrap = lastSet.querySelector('.flashcard-list');
                    if (listWrap) {
                        listWrap.innerHTML = '';
                        (set.cards || []).forEach(c => {
                            addFlashcard(lastSet.id);
                            const cardRows = lastSet.querySelectorAll('.flashcard-row');
                            const lastCard = cardRows[cardRows.length - 1];
                            if (lastCard) {
                                const fInp = lastCard.querySelector('.flashcard-front');
                                if (fInp) fInp.value = c.front || '';
                                if (c.front_image) {
                                    const fImgInp = lastCard.querySelector('.flashcard-front-image');
                                    const fPrev = lastCard.querySelector('.fc-front-preview');
                                    if (fImgInp) fImgInp.value = c.front_image;
                                    if (fPrev) {
                                        const img = fPrev.querySelector('img');
                                        if (img) img.src = c.front_image;
                                        fPrev.style.setProperty('display', 'flex', 'important');
                                    }
                                }
                                const bInp = lastCard.querySelector('.flashcard-back');
                                if (bInp) bInp.value = c.back || '';
                                if (c.back_image) {
                                    const bImgInp = lastCard.querySelector('.flashcard-back-image');
                                    const bPrev = lastCard.querySelector('.fc-back-preview');
                                    if (bImgInp) bImgInp.value = c.back_image;
                                    if (bPrev) {
                                        const img = bPrev.querySelector('img');
                                        if (img) img.src = c.back_image;
                                        bPrev.style.setProperty('display', 'flex', 'important');
                                    }
                                }
                            }
                        });
                    }
                }
            });
            break;
        }

        case 'image_label': {
            const descInp = document.getElementById('imageLabelDescription');
            if (descInp) descInp.value = data.description || '';
            const ptsInp = document.getElementById('imageLabelPoints');
            if (ptsInp) ptsInp.value = data.points || 1;
            const imgPath = data.image_path || (data.image ? data.image : '');
            if (imgPath) {
                const canvas = document.getElementById('imageLabelCanvas');
                const fullImgUrl = imgPath.startsWith('http') || imgPath.startsWith('data:') ? imgPath : (BASE + '/' + imgPath);
                canvas.innerHTML = `<img src="${fullImgUrl}" id="imageLabelImg" style="max-width:100%;border-radius:6px;display:block;" alt="Label image">`;
                document.getElementById('imageLabelPreviewWrap').style.display = '';
                imageLabelMarkers = [];
                document.getElementById('imageLabelAnswers').innerHTML = '';
                const markers = data.markers || data.labels || [];
                markers.forEach((m, mi) => {
                    const idx = mi + 1;
                    imageLabelMarkers.push({ x: m.x, y: m.y, answer: m.answer || '' });
                    const marker = document.createElement('div');
                    marker.className = 'label-marker';
                    marker.style.left = m.x + '%';
                    marker.style.top  = m.y + '%';
                    marker.textContent = idx;
                    canvas.appendChild(marker);

                    const answersDiv = document.getElementById('imageLabelAnswers');
                    const row = document.createElement('div');
                    row.className = 'd-flex align-items-center gap-2 mb-2';
                    row.innerHTML = `
                        <span class="badge" style="background:#a01422;min-width:24px;">${idx}</span>
                        <input type="text" class="form-control form-control-sm image-label-answer"
                               value="${escAttr(m.answer || '')}" data-idx="${mi}" placeholder="Answer for label ${idx}"
                               oninput="imageLabelMarkers[${mi}].answer = this.value; updateLabelPillsPreview();">`;
                    answersDiv.appendChild(row);
                });
                updateLabelPillsPreview();
            }
            break;
        }
    }
    initDragHandles();
}

/* ----------------------------------------------------------------
   Builder type-specific areas
   ---------------------------------------------------------------- */
function renderBuilderTypeArea(type) {
    const area = document.getElementById('builderTypeArea');
    area.innerHTML = '';

    switch (type) {
        case 'multiple_choice': area.innerHTML = buildMultipleChoice(); break;
        case 'true_false':      area.innerHTML = buildTrueFalse();      break;
        case 'fill_in_blanks':  area.innerHTML = buildFillInBlanks();   break;
        case 'matching':        area.innerHTML = buildMatching();        break;
        case 'drag_drop_sort':  area.innerHTML = buildDragDropSort();    break;
        case 'image_label':     area.innerHTML = buildImageLabel();      break;
        case 'flashcards':      area.innerHTML = buildFlashcards();      break;
        case 'sequencing':      area.innerHTML = buildSequencing();      break;
    }
    initDragHandles();
}

/* ---- Image upload helper for activity items ---- */
function handleItemImageUpload(fileInput, previewClass) {
    if (!fileInput.files || !fileInput.files.length) return;
    const file = fileInput.files[0];
    const parent = fileInput.closest('.mc-q-image-wrap, .mc-option-row, .tf-image-wrap, .match-item-wrap, .fc-item-wrap, .seq-item-wrap, .drag-item-wrap, .builder-item-row, .border') || fileInput.parentElement.parentElement;
    const previewContainer = parent.querySelector('.' + previewClass);
    const hiddenInput = parent.querySelector('input[type="hidden"]');

    const fd = new FormData();
    fd.append('image', file);

    const label = fileInput.closest('label');
    const origHtml = label ? label.innerHTML : '';
    if (label) label.innerHTML = '<span class="spinner-border spinner-border-sm"></span>';

    fetch(BASE + '/iep/implementation/upload-slide-image', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        if (label) label.innerHTML = origHtml;
        if (data.success && data.url) {
            if (hiddenInput) hiddenInput.value = data.url;
            if (previewContainer) {
                const img = previewContainer.querySelector('img');
                if (img) img.src = data.url;
                previewContainer.style.setProperty('display', 'flex', 'important');
            }
        } else {
            const reader = new FileReader();
            reader.onload = function(e) {
                if (hiddenInput) hiddenInput.value = e.target.result;
                if (previewContainer) {
                    const img = previewContainer.querySelector('img');
                    if (img) img.src = e.target.result;
                    previewContainer.style.setProperty('display', 'flex', 'important');
                }
            };
            reader.readAsDataURL(file);
        }
    })
    .catch(err => {
        if (label) label.innerHTML = origHtml;
        const reader = new FileReader();
        reader.onload = function(e) {
            if (hiddenInput) hiddenInput.value = e.target.result;
            if (previewContainer) {
                const img = previewContainer.querySelector('img');
                if (img) img.src = e.target.result;
                previewContainer.style.setProperty('display', 'flex', 'important');
            }
        };
        reader.readAsDataURL(file);
    });
}

function removeItemImage(btn) {
    const previewContainer = btn.closest('.d-flex');
    const parent = previewContainer.parentElement;
    const hiddenInput = parent.querySelector('input[type="hidden"]');
    const fileInput = parent.querySelector('input[type="file"]');
    if (hiddenInput) hiddenInput.value = '';
    if (fileInput) fileInput.value = '';
    if (previewContainer) previewContainer.style.setProperty('display', 'none', 'important');
}

/* ---- Multiple Choice ---- */
function buildMultipleChoice() {
    return `
    <div id="mcQuestions">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small" style="color:#1e4072;">Questions</span>
            <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                    onclick="addMCQuestion()">
                <i class="ti ti-plus me-1"></i>Add Question
            </button>
        </div>
        <div id="mcQuestionList"></div>
    </div>`;
}

let mcQCount = 0;
function addMCQuestion() {
    mcQCount++;
    const qId = 'mcQ_' + mcQCount;
    const div = document.createElement('div');
    div.className = 'border rounded p-3 mb-3';
    div.id = qId;
    div.style.background = '#fff';
    div.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small">Question ${mcQCount}</span>
            <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.7rem;"
                    onclick="document.getElementById('${qId}').remove()">
                <i class="ti ti-x me-1"></i>Remove
            </button>
        </div>
        <input type="text" class="form-control form-control-sm mb-2 mc-question-text"
               placeholder="Enter question text" required>
        <div class="d-flex align-items-center gap-2 mb-2 mc-q-image-wrap">
            <label class="btn btn-sm btn-outline-primary py-0 px-2 d-flex align-items-center gap-1 mb-0" style="font-size:0.75rem;cursor:pointer;">
                <i class="ti ti-photo"></i> <span>Attach Question Image</span>
                <input type="file" accept="image/*" class="d-none mc-q-image-file" onchange="handleItemImageUpload(this, 'mc-q-img-preview')">
            </label>
            <input type="hidden" class="mc-question-image" value="">
            <div class="mc-q-img-preview d-flex align-items-center gap-1" style="display:none !important;">
                <img src="" style="height:36px; width:auto; border-radius:4px; border:1px solid #dee2e6; object-fit:cover;">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItemImage(this)" title="Remove image" style="font-size:0.7rem;"><i class="ti ti-x"></i></button>
            </div>
        </div>
        <div class="mb-2">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="small text-muted">Options (mark correct with radio, optional image per choice)</span>
                <button type="button" class="btn btn-sm" style="background:#6c757d;color:#fff;border:none;font-size:0.7rem;"
                        onclick="addMCOption('${qId}_opts')">
                    <i class="ti ti-plus me-1"></i>Add Option
                </button>
            </div>
            <div id="${qId}_opts"></div>
        </div>
        <div class="d-flex align-items-center gap-2">
            <label class="form-label small mb-0">Points:</label>
            <input type="number" class="form-control form-control-sm mc-points" style="width:80px;" min="0" value="1">
        </div>`;
    document.getElementById('mcQuestionList').appendChild(div);
    // Add 2 default options
    addMCOption(qId + '_opts');
    addMCOption(qId + '_opts');
}

let mcOptCount = 0;
function addMCOption(containerId) {
    const container = document.getElementById(containerId);
    if (!container) return;
    const opts = container.querySelectorAll('.mc-option-row');
    if (opts.length >= 5) {
        showToast('warning', 'Max 5 options per question'); return;
    }
    mcOptCount++;
    const row = document.createElement('div');
    row.className = 'builder-item-row mc-option-row';
    const radioName = 'mc_correct_' + containerId;
    row.innerHTML = `
        <input type="radio" name="${radioName}" class="form-check-input mc-correct-radio" title="Mark as correct">
        <input type="text" class="form-control form-control-sm mc-option-text" placeholder="Option text">
        <label class="btn btn-sm btn-outline-secondary py-0 px-2 d-flex align-items-center gap-1 mb-0" style="font-size:0.72rem;cursor:pointer;" title="Attach Choice Image">
            <i class="ti ti-photo"></i>
            <input type="file" accept="image/*" class="d-none mc-opt-image-file" onchange="handleItemImageUpload(this, 'mc-opt-img-preview')">
        </label>
        <input type="hidden" class="mc-option-image" value="">
        <div class="mc-opt-img-preview d-flex align-items-center gap-1" style="display:none !important;">
            <img src="" style="height:28px; width:auto; border-radius:4px; border:1px solid #dee2e6; object-fit:cover;">
            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItemImage(this)" title="Remove image" style="font-size:0.65rem;"><i class="ti ti-x"></i></button>
        </div>
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.mc-option-row').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    container.appendChild(row);
}

/* ---- True / False ---- */
function buildTrueFalse() {
    return `
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Statements</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;" onclick="addTFStatement()">
            <i class="ti ti-plus me-1"></i>Add Statement
        </button>
    </div>
    <div id="tfStatements"></div>`;
}

let tfCount = 0;
function addTFStatement() {
    tfCount++;
    const id = 'tfStatement_' + tfCount;
    const row = document.createElement('div');
    row.className = 'border rounded p-3 mb-2 tf-statement-row';
    row.style.background = '#fff';
    row.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small">Statement ${tfCount}</span>
            <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.7rem;" onclick="this.closest('.tf-statement-row').remove()">
                <i class="ti ti-x me-1"></i>Remove
            </button>
        </div>
        <input type="text" class="form-control form-control-sm mb-2 tf-statement-text" placeholder="Enter true/false statement">
        <div class="d-flex align-items-center gap-2 mb-2 tf-image-wrap">
            <label class="btn btn-sm btn-outline-primary py-0 px-2 d-flex align-items-center gap-1 mb-0" style="font-size:0.75rem;cursor:pointer;">
                <i class="ti ti-photo"></i> <span>Attach Statement Image</span>
                <input type="file" accept="image/*" class="d-none tf-image-file" onchange="handleItemImageUpload(this, 'tf-img-preview')">
            </label>
            <input type="hidden" class="tf-statement-image" value="">
            <div class="tf-img-preview d-flex align-items-center gap-1" style="display:none !important;">
                <img src="" style="height:36px; width:auto; border-radius:4px; border:1px solid #dee2e6; object-fit:cover;">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItemImage(this)" title="Remove image" style="font-size:0.7rem;"><i class="ti ti-x"></i></button>
            </div>
        </div>
        <div class="d-flex flex-wrap align-items-center gap-3">
            <label class="small fw-semibold mb-0">Correct Answer:</label>
            <label class="form-check-label small"><input class="form-check-input tf-answer-radio" type="radio" name="${id}_answer" value="true" checked> True</label>
            <label class="form-check-label small"><input class="form-check-input tf-answer-radio" type="radio" name="${id}_answer" value="false"> False</label>
            <label class="small fw-semibold mb-0 ms-2">Points:</label>
            <input type="number" class="form-control form-control-sm tf-points" style="width:80px;" min="0" value="1">
        </div>`;
    document.getElementById('tfStatements').appendChild(row);
}

/* ---- Fill in the Blanks ---- */
function buildFillInBlanks() {
    return `
    <div class="alert alert-info py-2 small mb-3">
        <i class="ti ti-info-circle me-1"></i>Use <code>___</code> (three underscores) to mark blanks in your sentence.
    </div>
    <div class="mb-3">
        <label class="form-label small fw-semibold">Answer Mode</label>
        <div class="d-flex gap-3">
            <label class="form-check-label small"><input type="radio" class="form-check-input fib-mode-radio" name="fib_mode" value="word_bank" checked onchange="toggleFibModeFields()"> Word Bank</label>
            <label class="form-check-label small"><input type="radio" class="form-check-input fib-mode-radio" name="fib_mode" value="free_type" onchange="toggleFibModeFields()"> Free Type</label>
        </div>
    </div>
    <div class="mb-3" id="fibDistractorsWrap">
        <label class="form-label small fw-semibold">Distractor Words (comma-separated, optional)</label>
        <input type="text" class="form-control form-control-sm" id="fibDistractors" placeholder="e.g. red, blue, running">
        <small class="text-muted small">Wrong options shown in the pool to increase difficulty</small>
    </div>
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Blank Questions</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;" onclick="addFibQuestion()">
            <i class="ti ti-plus me-1"></i>Add Blank Question
        </button>
    </div>
    <div id="fibQuestions"></div>`;
}

function toggleFibModeFields() {
    const isWordBank = document.querySelector('.fib-mode-radio:checked')?.value === 'word_bank';
    const wrap = document.getElementById('fibDistractorsWrap');
    if (wrap) wrap.style.display = isWordBank ? 'block' : 'none';
}


let fibQCount = 0;
function addFibQuestion() {
    fibQCount++;
    const id = 'fibQ_' + fibQCount;
    const row = document.createElement('div');
    row.className = 'border rounded p-3 mb-2 fib-question-row';
    row.style.background = '#fff';
    row.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="fw-semibold small">Blank Question ${fibQCount}</span>
            <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.7rem;" onclick="this.closest('.fib-question-row').remove()">
                <i class="ti ti-x me-1"></i>Remove
            </button>
        </div>
        <input type="text" class="form-control form-control-sm mb-2 fib-sentence" placeholder="e.g. The ___ is red." oninput="updateFibPreview(this)">
        <div class="fib-preview mt-2 p-2 border rounded small" style="background:#f8f9fa;min-height:32px;"></div>
        <label class="form-label small fw-semibold mt-2">Answers for each blank</label>
        <div class="fib-answers"></div>
        <div class="mt-2 d-flex align-items-center gap-2">
            <label class="form-label small mb-0">Points:</label>
            <input type="number" class="form-control form-control-sm fib-points" style="width:80px;" min="0" value="1">
        </div>`;
    document.getElementById('fibQuestions').appendChild(row);
}

function updateFibPreview(input) {
    const row = input.closest('.fib-question-row');
    const sentence = input.value || '';
    const preview  = row.querySelector('.fib-preview');
    const answersDiv = row.querySelector('.fib-answers');
    if (!preview || !answersDiv) return;

    // Highlight blanks
    const highlighted = sentence.replace(/___/g, '<span style="background:#a01422;color:#fff;padding:0 4px;border-radius:3px;">___</span>');
    preview.innerHTML = highlighted || '<span class="text-muted">Preview will appear here…</span>';

    // Count blanks and render answer inputs
    const blanks = (sentence.match(/___/g) || []).length;
    const existing = answersDiv.querySelectorAll('.fib-answer-input');
    const diff = blanks - existing.length;

    if (diff > 0) {
        for (let i = 0; i < diff; i++) {
            const idx = existing.length + i + 1;
            const inp = document.createElement('input');
            inp.type = 'text';
            inp.className = 'form-control form-control-sm fib-answer-input mb-1';
            inp.placeholder = 'Answer for blank ' + idx;
            answersDiv.appendChild(inp);
        }
    } else if (diff < 0) {
        const toRemove = answersDiv.querySelectorAll('.fib-answer-input');
        for (let i = toRemove.length - 1; i >= blanks; i--) {
            toRemove[i].remove();
        }
    }
}

/* ---- Matching ---- */
function buildMatching() {
    return `
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Matching Sets</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;" onclick="addMatchingSet()">
            <i class="ti ti-plus me-1"></i>Add Matching Set
        </button>
    </div>
    <div id="matchingSets"></div>`;
}

let matchPairCount = 0;
let matchingSetCount = 0;
function addMatchingSet() {
    matchingSetCount++;
    const setId = 'matchingSet_' + matchingSetCount;
    const set = document.createElement('div');
    set.className = 'border rounded p-3 mb-3 matching-set';
    set.id = setId;
    set.style.background = '#fff';
    set.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <input type="text" class="form-control form-control-sm matching-set-title" value="Matching Set ${matchingSetCount}" style="max-width:220px;">
            <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.7rem;" onclick="this.closest('.matching-set').remove()">
                <i class="ti ti-x me-1"></i>Remove Set
            </button>
        </div>
        <div class="matching-pairs"></div>
        <div class="d-flex align-items-center gap-2 mt-2">
            <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;" onclick="addMatchingPair('${setId}')">
                <i class="ti ti-plus me-1"></i>Add Pair
            </button>
            <label class="form-label small mb-0">Points per pair:</label>
            <input type="number" class="form-control form-control-sm matching-points" style="width:80px;" min="0" value="1">
        </div>`;
    document.getElementById('matchingSets').appendChild(set);
    addMatchingPair(setId);
}
function addMatchingPair(setId) {
    matchPairCount++;
    const set = document.getElementById(setId);
    if (!set) return;
    const row = document.createElement('div');
    row.className = 'builder-item-row matching-pair';
    row.draggable = true;
    row.innerHTML = `
        <span class="drag-handle"><i class="ti ti-grip-vertical"></i></span>
        <div class="d-flex align-items-center gap-1 flex-grow-1 match-item-wrap">
            <input type="text" class="form-control form-control-sm matching-left" placeholder="Left item text">
            <label class="btn btn-sm btn-outline-secondary py-0 px-2 mb-0" style="font-size:0.72rem;cursor:pointer;" title="Attach Left Image">
                <i class="ti ti-photo"></i>
                <input type="file" accept="image/*" class="d-none" onchange="handleItemImageUpload(this, 'match-left-preview')">
            </label>
            <input type="hidden" class="matching-left-image" value="">
            <div class="match-left-preview d-flex align-items-center gap-1" style="display:none !important;">
                <img src="" style="height:26px; border-radius:3px; border:1px solid #dee2e6; object-fit:cover;">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItemImage(this)" style="font-size:0.65rem;"><i class="ti ti-x"></i></button>
            </div>
        </div>
        <span class="text-muted small px-1">→</span>
        <div class="d-flex align-items-center gap-1 flex-grow-1 match-item-wrap">
            <input type="text" class="form-control form-control-sm matching-right" placeholder="Right item text">
            <label class="btn btn-sm btn-outline-secondary py-0 px-2 mb-0" style="font-size:0.72rem;cursor:pointer;" title="Attach Right Image">
                <i class="ti ti-photo"></i>
                <input type="file" accept="image/*" class="d-none" onchange="handleItemImageUpload(this, 'match-right-preview')">
            </label>
            <input type="hidden" class="matching-right-image" value="">
            <div class="match-right-preview d-flex align-items-center gap-1" style="display:none !important;">
                <img src="" style="height:26px; border-radius:3px; border:1px solid #dee2e6; object-fit:cover;">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItemImage(this)" style="font-size:0.65rem;"><i class="ti ti-x"></i></button>
            </div>
        </div>
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.matching-pair').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    set.querySelector('.matching-pairs').appendChild(row);
    initDragHandles();
}

/* ---- Drag & Drop Sort ---- */
function buildDragDropSort() {
    return `
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Sorting Questions</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                onclick="addSortingQuestion()">
            <i class="ti ti-plus me-1"></i>Add Sorting Question
        </button>
    </div>
    <div id="sortingQuestions"></div>`;
}

let sortingQuestionCount = 0;
function addSortingQuestion() {
    sortingQuestionCount++;
    const setId = 'sortingQuestion_' + sortingQuestionCount;
    const set = document.createElement('div');
    set.className = 'border rounded p-3 mb-3 sorting-question';
    set.id = setId;
    set.style.background = '#fff';
    set.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <input type="text" class="form-control form-control-sm sorting-title" value="Sorting Question ${sortingQuestionCount}" style="max-width:240px;">
            <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.7rem;" onclick="this.closest('.sorting-question').remove()">
                <i class="ti ti-x me-1"></i>Remove Question
            </button>
        </div>
        <div class="small text-muted mb-2">Add items in the correct order. Learners will arrange them during the mission.</div>
        <div class="drag-drop-items"></div>
        <div class="d-flex align-items-center gap-2 mt-2">
            <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;" onclick="addDragDropItem('${setId}')">
                <i class="ti ti-plus me-1"></i>Add Item
            </button>
            <label class="form-label small mb-0">Points:</label>
            <input type="number" class="form-control form-control-sm sorting-points" style="width:80px;" min="0" value="1">
        </div>`;
    document.getElementById('sortingQuestions').appendChild(set);
    addDragDropItem(setId);
    addDragDropItem(setId);
}

function addDragDropItem(setId) {
    const set = document.getElementById(setId);
    if (!set) return;
    const row = document.createElement('div');
    row.className = 'builder-item-row drag-drop-item';
    row.draggable = true;
    row.innerHTML = `
        <span class="drag-handle"><i class="ti ti-grip-vertical"></i></span>
        <div class="d-flex align-items-center gap-1 flex-grow-1 drag-item-wrap">
            <input type="text" class="form-control form-control-sm drag-drop-text" placeholder="Item text">
            <label class="btn btn-sm btn-outline-secondary py-0 px-2 mb-0" style="font-size:0.72rem;cursor:pointer;" title="Attach Image">
                <i class="ti ti-photo"></i>
                <input type="file" accept="image/*" class="d-none" onchange="handleItemImageUpload(this, 'drag-item-preview')">
            </label>
            <input type="hidden" class="drag-drop-image" value="">
            <div class="drag-item-preview d-flex align-items-center gap-1" style="display:none !important;">
                <img src="" style="height:26px; border-radius:3px; border:1px solid #dee2e6; object-fit:cover;">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItemImage(this)" style="font-size:0.65rem;"><i class="ti ti-x"></i></button>
            </div>
        </div>
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.drag-drop-item').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    set.querySelector('.drag-drop-items').appendChild(row);
    initDragHandles();
}

/* ---- Image Label ---- */
let imageLabelMarkers = [];
function buildImageLabel() {
    return `
    <div class="mb-3">
        <label class="form-label small fw-semibold">Upload Image * (JPG/PNG, max 5MB)</label>
        <input type="file" class="form-control form-control-sm" id="imageLabelFile"
               accept=".jpg,.jpeg,.png" onchange="previewImageLabel(this)">
    </div>
    <div class="mb-3">
        <label class="form-label small fw-semibold">Image Description (for visually impaired accessibility fallback)</label>
        <textarea class="form-control form-control-sm" id="imageLabelDescription" rows="2" placeholder="Describe what is happening in the image..."></textarea>
    </div>
    <div id="imageLabelPreviewWrap" style="display:none;">
        <div class="d-flex justify-content-between align-items-center mb-2">
            <span class="small text-muted">Click on the image to place a label marker</span>
            <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                    onclick="addImageLabelMarker()">
                <i class="ti ti-map-pin me-1"></i>Add Label
            </button>
        </div>
        <div id="imageLabelCanvas" class="mb-3" onclick="placeMarkerOnClick(event)"></div>
        <div id="imageLabelAnswers"></div>
        <div id="imageLabelPillsPreview" class="mt-3">
            <label class="form-label small fw-semibold">Label Preview (Pills):</label>
            <div class="d-flex flex-wrap gap-2" id="imageLabelPillsContainer"></div>
        </div>
    </div>
    <div class="mt-2 d-flex align-items-center gap-2">
        <label class="form-label small mb-0">Points per label:</label>
        <input type="number" class="form-control form-control-sm" id="imageLabelPoints" style="width:80px;" min="0" value="1">
    </div>`;
}

function previewImageLabel(input) {
    if (!input.files.length) return;
    const file = input.files[0];
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire({ icon: 'warning', title: 'File too large', text: 'Max 5MB for image labels.', confirmButtonColor: '#a01422' });
        input.value = '';
        return;
    }
    const reader = new FileReader();
    reader.onload = function (e) {
        const canvas = document.getElementById('imageLabelCanvas');
        canvas.innerHTML = `<img src="${e.target.result}" id="imageLabelImg" style="max-width:100%;border-radius:6px;display:block;" alt="Label image">`;
        document.getElementById('imageLabelPreviewWrap').style.display = '';
        imageLabelMarkers = [];
        document.getElementById('imageLabelAnswers').innerHTML = '';
        updateLabelPillsPreview();
    };
    reader.readAsDataURL(file);
}

let markerClickMode = false;
function addImageLabelMarker() {
    markerClickMode = true;
    const canvas = document.getElementById('imageLabelCanvas');
    if (canvas) canvas.style.cursor = 'crosshair';
    showToast('info', 'Click on the image to place a marker');
}

function updateLabelPillsPreview() {
    const container = document.getElementById('imageLabelPillsContainer');
    if (!container) return;
    container.innerHTML = '';
    imageLabelMarkers.forEach(m => {
        const text = (m.answer || '').trim();
        if (text) {
            const pill = document.createElement('span');
            pill.className = 'badge bg-secondary text-white px-2 py-1 me-1 mb-1';
            pill.style.fontSize = '0.75rem';
            pill.textContent = text;
            container.appendChild(pill);
        }
    });
}

function placeMarkerOnClick(event) {
    if (!markerClickMode) return;
    markerClickMode = false;
    const canvas = document.getElementById('imageLabelCanvas');
    if (!canvas) return;
    canvas.style.cursor = 'default';

    const rect = canvas.getBoundingClientRect();
    const x = ((event.clientX - rect.left) / rect.width * 100).toFixed(1);
    const y = ((event.clientY - rect.top)  / rect.height * 100).toFixed(1);
    const idx = imageLabelMarkers.length + 1;

    imageLabelMarkers.push({ x, y, answer: '' });

    // Place marker dot
    const marker = document.createElement('div');
    marker.className = 'label-marker';
    marker.style.left = x + '%';
    marker.style.top  = y + '%';
    marker.textContent = idx;
    canvas.appendChild(marker);

    // Add answer input
    const answersDiv = document.getElementById('imageLabelAnswers');
    const row = document.createElement('div');
    row.className = 'd-flex align-items-center gap-2 mb-2';
    row.innerHTML = `
        <span class="badge" style="background:#a01422;min-width:24px;">${idx}</span>
        <input type="text" class="form-control form-control-sm image-label-answer"
               data-idx="${idx - 1}" placeholder="Answer for label ${idx}"
               oninput="imageLabelMarkers[${idx - 1}].answer = this.value; updateLabelPillsPreview();">`;
    answersDiv.appendChild(row);
    updateLabelPillsPreview();
}


/* ---- Flashcards ---- */
function buildFlashcards() {
    return `
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Flashcard Sets</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                onclick="addFlashcardSet()">
            <i class="ti ti-plus me-1"></i>Add Flashcard Set
        </button>
    </div>
    <div id="flashcardSets"></div>`;
}

let flashcardCount = 0;
let flashcardSetCount = 0;
function addFlashcardSet() {
    flashcardSetCount++;
    const setId = 'flashcardSet_' + flashcardSetCount;
    const set = document.createElement('div');
    set.className = 'border rounded p-3 mb-3 flashcard-set';
    set.id = setId;
    set.style.background = '#fff';
    set.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <input type="text" class="form-control form-control-sm flashcard-set-title" value="Flashcard Set ${flashcardSetCount}" style="max-width:240px;">
            <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.7rem;" onclick="this.closest('.flashcard-set').remove()">
                <i class="ti ti-x me-1"></i>Remove Set
            </button>
        </div>
        <div class="flashcard-list"></div>
        <button type="button" class="btn btn-sm mt-2" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;" onclick="addFlashcard('${setId}')">
            <i class="ti ti-plus me-1"></i>Add Card
        </button>`;
    document.getElementById('flashcardSets').appendChild(set);
    addFlashcard(setId);
}

function addFlashcard(setId) {
    if (!setId) {
        const existing = document.querySelector('.flashcard-set');
        if (!existing) {
            addFlashcardSet();
            return;
        }
        setId = existing.id;
    }
    const set = document.getElementById(setId);
    if (!set) return;
    flashcardCount++;
    const row = document.createElement('div');
    row.className = 'builder-item-row flashcard-row';
    row.innerHTML = `
        <div class="flex-grow-1">
            <div class="row g-2">
                <div class="col-6 fc-item-wrap">
                    <input type="text" class="form-control form-control-sm flashcard-front mb-1" placeholder="Front text (question/term)">
                    <div class="d-flex align-items-center gap-1">
                        <label class="btn btn-sm btn-outline-secondary py-0 px-2 mb-0" style="font-size:0.72rem;cursor:pointer;" title="Attach Front Image">
                            <i class="ti ti-photo me-1"></i><span>Front Image</span>
                            <input type="file" accept="image/*" class="d-none" onchange="handleItemImageUpload(this, 'fc-front-preview')">
                        </label>
                        <input type="hidden" class="flashcard-front-image" value="">
                        <div class="fc-front-preview d-flex align-items-center gap-1" style="display:none !important;">
                            <img src="" style="height:28px; border-radius:4px; border:1px solid #dee2e6; object-fit:cover;">
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItemImage(this)" style="font-size:0.65rem;"><i class="ti ti-x"></i></button>
                        </div>
                    </div>
                </div>
                <div class="col-6 fc-item-wrap">
                    <input type="text" class="form-control form-control-sm flashcard-back mb-1" placeholder="Back text (answer/definition)">
                    <div class="d-flex align-items-center gap-1">
                        <label class="btn btn-sm btn-outline-secondary py-0 px-2 mb-0" style="font-size:0.72rem;cursor:pointer;" title="Attach Back Image">
                            <i class="ti ti-photo me-1"></i><span>Back Image</span>
                            <input type="file" accept="image/*" class="d-none" onchange="handleItemImageUpload(this, 'fc-back-preview')">
                        </label>
                        <input type="hidden" class="flashcard-back-image" value="">
                        <div class="fc-back-preview d-flex align-items-center gap-1" style="display:none !important;">
                            <img src="" style="height:28px; border-radius:4px; border:1px solid #dee2e6; object-fit:cover;">
                            <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItemImage(this)" style="font-size:0.65rem;"><i class="ti ti-x"></i></button>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.flashcard-row').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    set.querySelector('.flashcard-list').appendChild(row);
}

/* ---- Sequencing ---- */
function buildSequencing() {
    return `
    <div class="d-flex justify-content-between align-items-center mb-2">
        <span class="fw-semibold small" style="color:#1e4072;">Sequence Questions</span>
        <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;"
                onclick="addSequenceQuestion()">
            <i class="ti ti-plus me-1"></i>Add Sequence Question
        </button>
    </div>
    <div id="sequenceQuestions"></div>`;
}

let sequenceQuestionCount = 0;
function addSequenceQuestion() {
    sequenceQuestionCount++;
    const setId = 'sequenceQuestion_' + sequenceQuestionCount;
    const set = document.createElement('div');
    set.className = 'border rounded p-3 mb-3 sequence-question';
    set.id = setId;
    set.style.background = '#fff';
    set.innerHTML = `
        <div class="d-flex justify-content-between align-items-center mb-2">
            <input type="text" class="form-control form-control-sm sequence-title" value="Sequence Question ${sequenceQuestionCount}" style="max-width:240px;">
            <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.7rem;" onclick="this.closest('.sequence-question').remove()">
                <i class="ti ti-x me-1"></i>Remove Question
            </button>
        </div>
        <div class="small text-muted mb-2">Add steps in the correct order. Learners will arrange them during the mission.</div>
        <div class="sequence-steps"></div>
        <div class="d-flex align-items-center gap-2 mt-2">
            <button type="button" class="btn btn-sm" style="background:#1e4072;color:#fff;border:none;font-size:0.75rem;" onclick="addSequencingStep('${setId}')">
                <i class="ti ti-plus me-1"></i>Add Step
            </button>
            <label class="form-label small mb-0">Points:</label>
            <input type="number" class="form-control form-control-sm sequence-points" style="width:80px;" min="0" value="1">
        </div>`;
    document.getElementById('sequenceQuestions').appendChild(set);
    addSequencingStep(setId);
    addSequencingStep(setId);
}

function addSequencingStep(setId) {
    const set = document.getElementById(setId);
    if (!set) return;
    const row = document.createElement('div');
    row.className = 'builder-item-row sequencing-step';
    row.draggable = true;
    row.innerHTML = `
        <span class="drag-handle"><i class="ti ti-grip-vertical"></i></span>
        <div class="d-flex align-items-center gap-1 flex-grow-1 seq-item-wrap">
            <input type="text" class="form-control form-control-sm sequencing-text" placeholder="Step text">
            <label class="btn btn-sm btn-outline-secondary py-0 px-2 mb-0" style="font-size:0.72rem;cursor:pointer;" title="Attach Image">
                <i class="ti ti-photo"></i>
                <input type="file" accept="image/*" class="d-none" onchange="handleItemImageUpload(this, 'seq-item-preview')">
            </label>
            <input type="hidden" class="sequencing-image" value="">
            <div class="seq-item-preview d-flex align-items-center gap-1" style="display:none !important;">
                <img src="" style="height:26px; border-radius:3px; border:1px solid #dee2e6; object-fit:cover;">
                <button type="button" class="btn btn-sm btn-outline-danger py-0 px-1" onclick="removeItemImage(this)" style="font-size:0.65rem;"><i class="ti ti-x"></i></button>
            </div>
        </div>
        <button type="button" class="btn btn-sm" style="background:#a01422;color:#fff;border:none;font-size:0.65rem;"
                onclick="this.closest('.sequencing-step').remove()">
            <i class="ti ti-x me-1"></i>Remove
        </button>`;
    set.querySelector('.sequence-steps').appendChild(row);
    initDragHandles();
}
</script>


<!-- ================================================================
     JAVASCRIPT — Part 4: Drag handles, Save Activity, Delete Activity
     ================================================================ -->
<script>
/* ----------------------------------------------------------------
   HTML5 Drag-and-drop for builder rows
   ---------------------------------------------------------------- */
function initDragHandles() {
    document.querySelectorAll('.builder-item-row[draggable="true"]').forEach(row => {
        row.addEventListener('dragstart', onDragStart);
        row.addEventListener('dragover',  onDragOver);
        row.addEventListener('dragleave', onDragLeave);
        row.addEventListener('drop',      onDrop);
        row.addEventListener('dragend',   onDragEnd);
    });
}

let dragSrc = null;

function onDragStart(e) {
    dragSrc = this;
    e.dataTransfer.effectAllowed = 'move';
    this.style.opacity = '0.5';
}
function onDragOver(e) {
    e.preventDefault();
    e.dataTransfer.dropEffect = 'move';
    this.classList.add('drag-over');
    return false;
}
function onDragLeave() {
    this.classList.remove('drag-over');
}
function onDrop(e) {
    e.stopPropagation();
    if (dragSrc !== this) {
        const parent = this.parentNode;
        const srcIdx  = Array.from(parent.children).indexOf(dragSrc);
        const destIdx = Array.from(parent.children).indexOf(this);
        if (srcIdx < destIdx) {
            parent.insertBefore(dragSrc, this.nextSibling);
        } else {
            parent.insertBefore(dragSrc, this);
        }
    }
    this.classList.remove('drag-over');
    return false;
}
function onDragEnd() {
    this.style.opacity = '';
    document.querySelectorAll('.builder-item-row').forEach(r => r.classList.remove('drag-over'));
}

/* ----------------------------------------------------------------
   Collect activity data from builder
   ---------------------------------------------------------------- */
function collectActivityData() {
    const type = selectedActivityType;
    let data = {};

    switch (type) {
        case 'multiple_choice': {
            const questions = [];
            document.querySelectorAll('#mcQuestionList > div').forEach(qDiv => {
                const text    = qDiv.querySelector('.mc-question-text')?.value.trim() || '';
                const image   = qDiv.querySelector('.mc-question-image')?.value.trim() || '';
                const points  = parseInt(qDiv.querySelector('.mc-points')?.value || '1');
                const options = [];
                qDiv.querySelectorAll('.mc-option-row').forEach(optRow => {
                    options.push({
                        text:      optRow.querySelector('.mc-option-text')?.value.trim() || '',
                        image:     optRow.querySelector('.mc-option-image')?.value.trim() || '',
                        isCorrect: optRow.querySelector('.mc-correct-radio')?.checked || false,
                    });
                });
                questions.push({ text, image, options, points });
            });
            data = { questions };
            break;
        }
        case 'true_false': {
            const questions = [];
            document.querySelectorAll('.tf-statement-row').forEach(row => {
                questions.push({
                    statement: row.querySelector('.tf-statement-text')?.value.trim() || '',
                    image:     row.querySelector('.tf-statement-image')?.value.trim() || '',
                    answer:    row.querySelector('.tf-answer-radio:checked')?.value || 'true',
                    points:    parseInt(row.querySelector('.tf-points')?.value || '1'),
                });
            });
            const first = questions[0] || {};
            data = {
                questions,
                statement: first.statement || '',
                image:     first.image || '',
                answer:    first.answer || 'true',
                points:    first.points || 1,
            };
            break;
        }
        case 'fill_in_blanks': {
            const sentences = [];
            document.querySelectorAll('.fib-question-row').forEach(row => {
                const answers = [];
                row.querySelectorAll('.fib-answer-input').forEach(inp => answers.push(inp.value.trim()));
                sentences.push({
                    text: row.querySelector('.fib-sentence')?.value.trim() || '',
                    answers,
                    points: parseInt(row.querySelector('.fib-points')?.value || '1'),
                });
            });
            const first = sentences[0] || {};
            const mode = document.querySelector('.fib-mode-radio:checked')?.value || 'word_bank';
            const distractorsVal = document.getElementById('fibDistractors')?.value || '';
            const distractors = distractorsVal.split(',').map(s => s.trim()).filter(s => s.length > 0);
            data = {
                sentences,
                sentence: first.text || '',
                answers: first.answers || [],
                points: first.points || 1,
                answer_mode: mode,
                distractors: distractors
            };
            break;
        }
        case 'matching': {
            const sets = [];
            document.querySelectorAll('.matching-set').forEach((set, index) => {
                const pairs = [];
                set.querySelectorAll('.matching-pair').forEach(row => {
                    pairs.push({
                        left:        row.querySelector('.matching-left')?.value.trim()        || '',
                        left_image:  row.querySelector('.matching-left-image')?.value.trim()  || '',
                        right:       row.querySelector('.matching-right')?.value.trim()       || '',
                        right_image: row.querySelector('.matching-right-image')?.value.trim() || '',
                    });
                });
                sets.push({
                    title: set.querySelector('.matching-set-title')?.value.trim() || 'Matching Set ' + (index + 1),
                    pairs,
                    points: parseInt(set.querySelector('.matching-points')?.value || '1'),
                });
            });
            data = { sets, pairs: sets[0]?.pairs || [], points: sets[0]?.points || 1 };
            break;
        }
        case 'drag_drop_sort': {
            const sets = [];
            document.querySelectorAll('.sorting-question').forEach((set, setIndex) => {
                const items = [];
                set.querySelectorAll('.drag-drop-item').forEach((row, idx) => {
                    items.push({ 
                        text:  row.querySelector('.drag-drop-text')?.value.trim() || '', 
                        image: row.querySelector('.drag-drop-image')?.value.trim() || '',
                        order: idx + 1 
                    });
                });
                sets.push({
                    title: set.querySelector('.sorting-title')?.value.trim() || 'Sorting Question ' + (setIndex + 1),
                    items,
                    points: parseInt(set.querySelector('.sorting-points')?.value || '1'),
                });
            });
            data = { sets, items: sets[0]?.items || [], points: sets[0]?.points || 1 };
            break;
        }
        case 'image_label': {
            data = {
                labels:  imageLabelMarkers,
                markers: imageLabelMarkers,
                points:  parseInt(document.getElementById('imageLabelPoints')?.value || '1'),
                description: document.getElementById('imageLabelDescription')?.value.trim() || '',
            };
            break;
        }
        case 'flashcards': {
            const sets = [];
            document.querySelectorAll('.flashcard-set').forEach((set, index) => {
                const cards = [];
                set.querySelectorAll('.flashcard-row').forEach(row => {
                    cards.push({
                        front:       row.querySelector('.flashcard-front')?.value.trim()       || '',
                        front_image: row.querySelector('.flashcard-front-image')?.value.trim() || '',
                        back:        row.querySelector('.flashcard-back')?.value.trim()        || '',
                        back_image:  row.querySelector('.flashcard-back-image')?.value.trim()  || '',
                    });
                });
                sets.push({
                    title: set.querySelector('.flashcard-set-title')?.value.trim() || 'Flashcard Set ' + (index + 1),
                    cards,
                });
            });
            data = { sets, cards: sets[0]?.cards || [] };
            break;
        }
        case 'sequencing': {
            const sets = [];
            document.querySelectorAll('.sequence-question').forEach((set, setIndex) => {
                const steps = [];
                set.querySelectorAll('.sequencing-step').forEach((row, idx) => {
                    steps.push({ 
                        text:  row.querySelector('.sequencing-text')?.value.trim() || '', 
                        image: row.querySelector('.sequencing-image')?.value.trim() || '',
                        order: idx + 1 
                    });
                });
                sets.push({
                    title: set.querySelector('.sequence-title')?.value.trim() || 'Sequence Question ' + (setIndex + 1),
                    steps,
                    points: parseInt(set.querySelector('.sequence-points')?.value || '1'),
                });
            });
            data = { sets, steps: sets[0]?.steps || [], points: sets[0]?.points || 1 };
            break;
        }
    }
    return data;
}

function validateActivityData(type, data) {
    if (type === 'multiple_choice') {
        const questions = (data.questions || []).filter(q => (q.text || '').trim() !== '' || (q.image || '').trim() !== '');
        if (!questions.length) return 'Add at least one multiple-choice question.';
        for (const [idx, q] of questions.entries()) {
            const options = (q.options || []).filter(o => (o.text || '').trim() !== '' || (o.image || '').trim() !== '');
            if (options.length < 2) return 'Question ' + (idx + 1) + ' needs at least two options.';
            if (!options.some(o => !!o.isCorrect)) return 'Question ' + (idx + 1) + ' needs one correct answer.';
        }
    }
    if (type === 'true_false') {
        const questions = (data.questions || []).filter(q => (q.statement || '').trim() !== '' || (q.image || '').trim() !== '');
        if (!questions.length) return 'Add at least one true/false statement.';
    }
    if (type === 'fill_in_blanks') {
        const sentences = (data.sentences || []).filter(s => (s.text || '').trim() !== '');
        if (!sentences.length) return 'Add at least one fill-in-the-blank question.';
        for (const [idx, s] of sentences.entries()) {
            const blankCount = ((s.text || '').match(/___/g) || []).length;
            const answers = (s.answers || []).filter(a => (a || '').trim() !== '');
            if (!blankCount) return 'Blank question ' + (idx + 1) + ' needs at least one ___.';
            if (answers.length < blankCount) return 'Blank question ' + (idx + 1) + ' needs an answer for every blank.';
        }
    }
    if (type === 'matching') {
        const sets = data.sets || [{pairs: data.pairs || []}];
        if (!sets.length) return 'Add at least one matching set.';
        for (const [idx, set] of sets.entries()) {
            const pairs = (set.pairs || []).filter(p => ((p.left || '').trim() !== '' || (p.left_image || '').trim() !== '') && ((p.right || '').trim() !== '' || (p.right_image || '').trim() !== ''));
            if (pairs.length < 1) return 'Matching set ' + (idx + 1) + ' needs at least one complete pair.';
        }
    }
    if (type === 'drag_drop_sort') {
        const sets = data.sets || [{items: data.items || []}];
        if (!sets.length) return 'Add at least one sorting question.';
        for (const [idx, set] of sets.entries()) {
            const items = (set.items || []).filter(i => (i.text || '').trim() !== '' || (i.image || '').trim() !== '');
            if (items.length < 2) return 'Sorting question ' + (idx + 1) + ' needs at least two items.';
        }
    }
    if (type === 'sequencing') {
        const sets = data.sets || [{steps: data.steps || []}];
        if (!sets.length) return 'Add at least one sequence question.';
        for (const [idx, set] of sets.entries()) {
            const steps = (set.steps || []).filter(s => (s.text || '').trim() !== '' || (s.image || '').trim() !== '');
            if (steps.length < 2) return 'Sequence question ' + (idx + 1) + ' needs at least two steps.';
        }
    }
    if (type === 'flashcards') {
        const sets = data.sets || [{cards: data.cards || []}];
        if (!sets.length) return 'Add at least one flashcard set.';
        for (const [idx, set] of sets.entries()) {
            const cards = (set.cards || []).filter(c => ((c.front || '').trim() !== '' || (c.front_image || '').trim() !== '') && ((c.back || '').trim() !== '' || (c.back_image || '').trim() !== ''));
            if (cards.length < 1) return 'Flashcard set ' + (idx + 1) + ' needs at least one complete card.';
        }
    }
    if (type === 'image_label') {
        const fileInput = document.getElementById('imageLabelFile');
        const hasExistingImg = !!document.getElementById('imageLabelImg');
        if ((!fileInput || !fileInput.files.length) && !hasExistingImg) {
            return 'Please upload an image before saving this activity.';
        }
        const labels = (data.labels || []).filter(l => (l.answer || '').trim() !== '');
        if (labels.length < 1) return 'Add at least one image label marker and answer.';
    }
    return '';
}

/* ----------------------------------------------------------------
   Save Activity
   ---------------------------------------------------------------- */
async function saveActivity() {
    const lpId         = document.getElementById('builderLessonPlan').value;
    const title        = document.getElementById('builderTitle').value.trim();
    
    let instructions   = '';
    if (typeof $ !== 'undefined' && $('#builderInstructions').hasClass('summernote-initialized')) {
        instructions = $('#builderInstructions').summernote('code');
    } else {
        instructions = document.getElementById('builderInstructions').value.trim();
    }

    const dueDate      = document.getElementById('builderDueDate').value || null;
    const isF2F        = document.getElementById('builderIsF2F') && document.getElementById('builderIsF2F').checked ? 1 : 0;
    const maxScore     = (!isF2F && selectedActivityType !== 'flashcards')
                         ? parseInt(document.getElementById('builderMaxScore')?.value || '0')
                         : 0;

    if (!lpId) {
        Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#a01422' }); return;
    }
    if (!title) {
        Swal.fire({ icon: 'warning', title: 'Enter a title', confirmButtonColor: '#a01422' }); return;
    }
    if (!selectedActivityType) {
        Swal.fire({ icon: 'warning', title: 'Select an activity type', confirmButtonColor: '#a01422' }); return;
    }

    let activityData = [];
    if (!isF2F) {
        activityData = collectActivityData();
        const validationMessage = validateActivityData(selectedActivityType, activityData);
        if (validationMessage) {
            Swal.fire({ icon: 'warning', title: 'Check activity details', text: validationMessage, confirmButtonColor: '#a01422' });
            return;
        }
    }

    showLoading(editingActivityId ? 'Updating activity…' : 'Saving activity…');
    try {
        let data;
        const targetUrl = editingActivityId 
            ? `${BASE}/iep/implementation/activity/${editingActivityId}/edit` 
            : `${BASE}/iep/implementation/activity/create`;

        if (selectedActivityType === 'image_label') {
            const fileInput = document.getElementById('imageLabelFile');
            const file = fileInput && fileInput.files ? fileInput.files[0] : null;
            const formData = new FormData();
            formData.append('lesson_plan_id', parseInt(lpId));
            formData.append('title', title);
            formData.append('instructions', instructions);
            formData.append('activity_type', selectedActivityType);
            formData.append('activity_data', JSON.stringify(activityData));
            formData.append('max_score', maxScore);
            formData.append('is_f2f', isF2F);
            if (dueDate) formData.append('due_date', dueDate);
            if (file) {
                formData.append('image_file', file);
            }
            data = await postForm(targetUrl, formData);
        } else {
            data = await postJSON(targetUrl, {
                lesson_plan_id: parseInt(lpId),
                title,
                instructions,
                activity_type: selectedActivityType,
                activity_data: activityData,
                max_score:     maxScore,
                due_date:      dueDate,
                is_f2f:        isF2F,
            });
        }

        Swal.close();
        if (data.success) {
            const lpSel = document.getElementById('builderLessonPlan');
            let lpTitle = '—';
            if (lpSel) {
                const opt = lpSel.querySelector('option[value="' + lpId + '"]');
                if (opt) lpTitle = opt.textContent.trim();
            }

            const updatedAct = {
                id:               editingActivityId || data.activity_id || data.activity?.id,
                title:            title,
                instructions:     instructions,
                activity_type:    selectedActivityType,
                activity_data:    data.activity?.activity_data || activityData,
                lesson_plan_id:   parseInt(lpId),
                lesson_plan_title: lpTitle,
                due_date:         dueDate,
                max_score:        maxScore,
                is_f2f:           isF2F,
            };

            const existingRow = document.getElementById('actRow_' + updatedAct.id);
            if (existingRow && editingActivityId) {
                const color = actTypeColors[updatedAct.activity_type] || '#6c757d';
                const typeLabel = activityTypeLabels[updatedAct.activity_type] || updatedAct.activity_type;
                const tds = existingRow.querySelectorAll('td');
                if (tds.length >= 6) {
                    tds[0].textContent = updatedAct.title;
                    tds[1].innerHTML = `
                        <span class="badge" style="background:${color};font-size:0.7rem;">${escHtml(typeLabel)}</span>
                        ${updatedAct.is_f2f ? '<span class="badge bg-success ms-1" style="font-size:0.7rem;">F2F</span>' : ''}
                    `;
                    tds[2].textContent = lpTitle;
                    tds[3].textContent = updatedAct.due_date ? updatedAct.due_date : '—';
                    tds[4].textContent = updatedAct.is_f2f ? '—' : (updatedAct.max_score || 0);
                    
                    const btnWrap = tds[5].querySelector('.action-btn-wrap') || tds[5].querySelector('.d-flex');
                    if (btnWrap) {
                        btnWrap.innerHTML = '';

                        if (updatedAct.id && !updatedAct.is_f2f) {
                            const previewBtn = document.createElement('a');
                            previewBtn.className = 'btn btn-sm';
                            previewBtn.target = '_blank';
                            previewBtn.href = `${BASE}/learning/activity/${updatedAct.id}`;
                            previewBtn.style.cssText = 'background:#6c5ce7;color:#fff;border:none;font-size:0.75rem;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;';
                            previewBtn.innerHTML = '<i class="ti ti-device-gamepad me-1"></i>Preview';
                            previewBtn.title = "Open Learner's Interactive Preview";
                            btnWrap.appendChild(previewBtn);
                        }

                        const viewBtn = document.createElement('button');
                        viewBtn.className = 'btn btn-sm';
                        viewBtn.style.cssText = 'background:#1e4072;color:#fff;border:none;font-size:0.75rem;border-radius:6px;';
                        viewBtn.innerHTML = '<i class="ti ti-eye me-1"></i>View';
                        viewBtn.onclick = () => viewActivity(updatedAct);
                        btnWrap.appendChild(viewBtn);

                        const editBtn = document.createElement('button');
                        editBtn.className = 'btn btn-sm';
                        editBtn.style.cssText = 'background:#3b6d11;color:#fff;border:none;font-size:0.75rem;border-radius:6px;';
                        editBtn.innerHTML = '<i class="ti ti-pencil me-1"></i>Edit';
                        editBtn.onclick = () => openEditActivity(updatedAct);
                        btnWrap.appendChild(editBtn);

                        const delBtn = document.createElement('button');
                        delBtn.className = 'btn btn-sm';
                        delBtn.style.cssText = 'background:#a01422;color:#fff;border:none;font-size:0.75rem;border-radius:6px;';
                        delBtn.innerHTML = '<i class="ti ti-trash me-1"></i>Delete';
                        delBtn.onclick = () => confirmDeleteActivity(updatedAct.id, updatedAct.title);
                        btnWrap.appendChild(delBtn);
                    }
                }
                showToast('success', 'Activity updated successfully!');
            } else {
                appendActivityRow(updatedAct);
                showToast('success', 'Activity saved!');
            }

            closeActivityBuilder();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
        }
    } catch (e) {
        Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
    }
}

/* ----------------------------------------------------------------
   Append activity row to table
   ---------------------------------------------------------------- */
const actTypeColors = {
    multiple_choice: '#1e4072', true_false: '#3b6d11', fill_in_blanks: '#a01422',
    matching: '#6c757d', drag_drop_sort: '#e67e22', image_label: '#8e44ad',
    flashcards: '#2980b9', sequencing: '#16a085',
};

function appendActivityRow(act) {
    const color     = actTypeColors[act.activity_type] || '#6c757d';
    const typeLabel = activityTypeLabels[act.activity_type] || act.activity_type;

    // Find lesson plan title
    const lpSel = document.getElementById('builderLessonPlan');
    let lpTitle = act.lesson_plan_title || '—';
    if (lpTitle === '—' && lpSel && act.lesson_plan_id) {
        const opt = lpSel.querySelector('option[value="' + act.lesson_plan_id + '"]');
        if (opt) lpTitle = opt.textContent.trim();
    }
    act.lesson_plan_title = lpTitle;

    const tbody = document.getElementById('activitiesTableBody');
    if (!tbody) return;

    const tr = document.createElement('tr');
    tr.id = 'actRow_' + act.id;
    tr.innerHTML = `
        <td class="fw-semibold">${escHtml(act.title)}</td>
        <td>
            <span class="badge" style="background:${color};font-size:0.7rem;">${escHtml(typeLabel)}</span>
            ${act.is_f2f ? '<span class="badge bg-success ms-1" style="font-size:0.7rem;">F2F</span>' : ''}
        </td>
        <td class="text-muted">${escHtml(lpTitle)}</td>
        <td class="text-muted">${act.due_date ? escHtml(act.due_date) : '—'}</td>
        <td>${act.is_f2f ? '—' : (act.max_score || 0)}</td>
        <td>
            <div class="d-flex gap-1 flex-wrap action-btn-wrap"></div>
        </td>`;

    const btnWrap = tr.querySelector('.action-btn-wrap');
    
    if (act.id && !act.is_f2f) {
        const previewBtn = document.createElement('a');
        previewBtn.className = 'btn btn-sm';
        previewBtn.target = '_blank';
        previewBtn.href = `${BASE}/learning/activity/${act.id}`;
        previewBtn.style.cssText = 'background:#6c5ce7;color:#fff;border:none;font-size:0.75rem;border-radius:6px;text-decoration:none;display:inline-flex;align-items:center;';
        previewBtn.innerHTML = '<i class="ti ti-device-gamepad me-1"></i>Preview';
        previewBtn.title = "Open Learner's Interactive Preview";
        btnWrap.appendChild(previewBtn);
    }

    const viewBtn = document.createElement('button');
    viewBtn.className = 'btn btn-sm';
    viewBtn.style.cssText = 'background:#1e4072;color:#fff;border:none;font-size:0.75rem;border-radius:6px;';
    viewBtn.innerHTML = '<i class="ti ti-eye me-1"></i>View';
    viewBtn.onclick = () => viewActivity(act);
    btnWrap.appendChild(viewBtn);

    const editBtn = document.createElement('button');
    editBtn.className = 'btn btn-sm';
    editBtn.style.cssText = 'background:#3b6d11;color:#fff;border:none;font-size:0.75rem;border-radius:6px;';
    editBtn.innerHTML = '<i class="ti ti-pencil me-1"></i>Edit';
    editBtn.onclick = () => openEditActivity(act);
    btnWrap.appendChild(editBtn);

    const delBtn = document.createElement('button');
    delBtn.className = 'btn btn-sm';
    delBtn.style.cssText = 'background:#a01422;color:#fff;border:none;font-size:0.75rem;border-radius:6px;';
    delBtn.innerHTML = '<i class="ti ti-trash me-1"></i>Delete';
    delBtn.onclick = () => confirmDeleteActivity(act.id, act.title);
    btnWrap.appendChild(delBtn);

    tbody.appendChild(tr);

    document.getElementById('activitiesList').style.display = '';
    const empty = document.getElementById('activitiesEmptyState');
    if (empty) empty.style.display = 'none';
}

/* ----------------------------------------------------------------
   Delete Activity
   ---------------------------------------------------------------- */
function confirmDeleteActivity(actId, actTitle) {
    Swal.fire({
        icon: 'warning',
        title: 'Delete activity?',
        html: 'Remove <strong>' + actTitle + '</strong>?',
        showCancelButton: true,
        confirmButtonColor: '#a01422',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Yes, delete',
    }).then(async (result) => {
        if (!result.isConfirmed) return;
        showLoading('Deleting…');
        try {
            const data = await postJSON(BASE + '/iep/implementation/activity/' + actId + '/delete', { activity_id: actId });
            Swal.close();
            if (data.success) {
                const row = document.getElementById('actRow_' + actId);
                if (row) row.remove();
                showToast('success', 'Deleted', actTitle + ' removed.');
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
            }
        } catch (e) {
            Swal.fire({ icon: 'error', title: 'Network error', text: e.message, confirmButtonColor: '#a01422' });
        }
    });
}

/* ----------------------------------------------------------------
   Import Activity CSV
   ---------------------------------------------------------------- */
function openImportActivityModal() {
    new bootstrap.Modal(document.getElementById('importActivityModal')).show();
}

function downloadActivityCsvTemplate(e) {
    e.preventDefault();
    const csvContent = "data:text/csv;charset=utf-8," 
        + "title,instructions,type,max_score,question1,q1_option1,q1_option2,q1_correct,question2,q2_option1,q2_option2,q2_correct\n"
        + "Math Quiz,Answer the following,multiple_choice,10,1+1=?,1,2,2,2+2=?,3,4,2\n"
        + "Science Fact,Is the Earth flat?,true_false,5,The Earth is round,true\n";
    const encodedUri = encodeURI(csvContent);
    const link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "activity_template.csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}

function submitImportActivity() {
    const lpId = document.getElementById('importActivityLessonPlan').value;
    const fileInput = document.getElementById('importActivityFile');
    
    if (!lpId) {
        Swal.fire({ icon: 'warning', title: 'Select a lesson plan', confirmButtonColor: '#3b6d11' }); return;
    }
    if (!fileInput.files.length) {
        Swal.fire({ icon: 'warning', title: 'Select a CSV file', confirmButtonColor: '#3b6d11' }); return;
    }
    
    const formData = new FormData();
    formData.append('lesson_plan_id', lpId);
    formData.append('iep_id', IEP_ID);
    formData.append('csv_file', fileInput.files[0]);

    const btn = document.querySelector('#importActivityModal .btn[onclick="submitImportActivity()"]');
    if (btn) btn.disabled = true;

    showLoading('Importing activities...');

    fetch(BASE + '/iep/implementation/activity/import', {
        method: 'POST',
        body: formData
    })
    .then(r => r.json())
    .then(data => {
        Swal.close();
        if (btn) btn.disabled = false;
        if (data.success) {
            Swal.fire({
                icon: 'success',
                title: 'Imported!',
                text: data.message,
                confirmButtonColor: '#3b6d11'
            }).then(() => {
                window.location.reload();
            });
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: data.message, confirmButtonColor: '#a01422' });
        }
    })
    .catch(e => {
        Swal.close();
        if (btn) btn.disabled = false;
        Swal.fire({ icon: 'error', title: 'Network Error', text: 'Could not connect.', confirmButtonColor: '#a01422' });
    });
}

/* ----------------------------------------------------------------
   Init on DOMContentLoaded
   ---------------------------------------------------------------- */
document.addEventListener('DOMContentLoaded', function () {
    initDragHandles();

    // Keyboard accessibility for type cards
    document.querySelectorAll('.activity-type-card, .material-type-card').forEach(card => {
        card.addEventListener('keydown', function (e) {
            if (e.key === 'Enter' || e.key === ' ') {
                e.preventDefault();
                this.click();
            }
        });
    });
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

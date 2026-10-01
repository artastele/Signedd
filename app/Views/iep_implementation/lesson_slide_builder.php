<?php
// SignED — Dedicated Lesson Slides Studio & FSL Interactive Aids Builder
// Full-page 3-column workspace for SPED Teachers

require_once __DIR__ . '/../layouts/header.php';
require_once __DIR__ . '/../layouts/sidebar.php';
require_once __DIR__ . '/../layouts/topbar.php';

$studentName = trim(($iep['first_name'] ?? '') . ' ' . ($iep['last_name'] ?? ''));
$studentCode = $iep['student_id'] ?? ($iep['lrn'] ?? '');
$domainName  = $lessonPlan['pdsp_domain'] ?? 'General';
$lpId        = (int)$lessonPlan['id'];
$iepId       = (int)($lessonPlan['iep_id'] ?? $iep['id'] ?? 0);
?>

<!-- Summernote Lite (Rich Text / WYSIWYG Editor for Lesson Pages) -->
<link href="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.css" rel="stylesheet">
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script src="https://cdn.jsdelivr.net/npm/summernote@0.8.18/dist/summernote-lite.min.js"></script>

<style>
/* Lesson Studio Studio Styles */
.lesson-studio-shell {
    padding: 0.75rem 1.25rem;
    min-height: calc(100vh - 60px);
    background-color: #f8fafc;
}

.studio-card {
    background: #ffffff;
    border-radius: 10px;
    border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0, 0, 0, 0.03);
    overflow: hidden;
}

.studio-card-header {
    padding: 8px 14px;
    background: #f8fafc;
    border-bottom: 1px solid #e2e8f0;
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* Slide Deck Navigator (Left Col) */
.slide-deck-list {
    max-height: calc(100vh - 230px);
    overflow-y: auto;
    padding: 8px;
}

.slide-deck-item {
    background: #ffffff;
    border: 2px solid #e2e8f0;
    border-radius: 8px;
    padding: 10px 12px;
    margin-bottom: 8px;
    cursor: pointer;
    transition: all 0.18s ease;
    position: relative;
}

.slide-deck-item:hover {
    border-color: #93c5fd;
    background: #f0f9ff;
    transform: translateY(-1px);
    box-shadow: 0 2px 6px rgba(0, 0, 0, 0.05);
}

.slide-deck-item.active {
    border-color: #0284c7 !important;
    background: #e0f2fe !important;
    box-shadow: 0 3px 8px rgba(2, 132, 199, 0.18) !important;
}

.slide-item-number {
    font-size: 0.72rem;
    font-weight: 700;
    padding: 2px 7px;
    border-radius: 9999px;
    background: #0284c7;
    color: #ffffff;
}

.slide-deck-item:not(.active) .slide-item-number {
    background: #e2e8f0;
    color: #475569;
}

.slide-item-title {
    font-weight: 600;
    font-size: 0.88rem;
    color: #1e293b;
    margin: 4px 0 2px;
    white-space: nowrap;
    overflow: hidden;
    text-overflow: ellipsis;
}

.slide-item-preview {
    font-size: 0.75rem;
    color: #64748b;
    display: -webkit-box;
    -webkit-line-clamp: 2;
    -webkit-box-orient: vertical;
    overflow: hidden;
    line-height: 1.35;
}

/* FSL Sign Library (Right Col) */
.fsl-library-list {
    max-height: calc(100vh - 250px);
    overflow-y: auto;
    padding: 8px;
}

.fsl-sign-card {
    background: #ffffff;
    border: 1.5px solid #fed7aa;
    border-radius: 10px;
    padding: 12px;
    margin-bottom: 10px;
    transition: all 0.15s ease;
    box-shadow: 0 2px 6px rgba(217, 119, 6, 0.06);
}

.fsl-sign-card:hover {
    border-color: #f59e0b;
    background: #fffbeb;
    transform: translateY(-2px);
    box-shadow: 0 4px 12px rgba(217, 119, 6, 0.15);
}

.fsl-sign-badge {
    font-size: 0.7rem;
    font-weight: 600;
    padding: 2px 8px;
    border-radius: 9999px;
    background: #ffedd5;
    color: #9a3412;
}

.fsl-insert-btn {
    border-radius: 9999px !important;
    font-size: 0.8rem !important;
    font-weight: 700 !important;
    padding: 4px 12px !important;
    background: #f59e0b !important;
    color: #451a03 !important;
    border: none !important;
    box-shadow: 0 2px 0 #d97706 !important;
    transition: all 0.15s ease !important;
}

.fsl-insert-btn:hover {
    background: #d97706 !important;
    color: #ffffff !important;
    transform: translateY(-1px) !important;
}

/* Summernote custom button */
.note-btn-fsl {
    background: #fef3c7 !important;
    color: #92400e !important;
    border: 1px solid #f59e0b !important;
    font-weight: 700 !important;
    border-radius: 6px !important;
}

.note-btn-fsl:hover {
    background: #fde68a !important;
    color: #78350f !important;
}

/* Clean, lightweight styling on shape containers in Summernote */
.note-editable .shape-container,
.note-editable [class*="shape-"],
.note-editable .rounded-4:not(.modal *),
.note-editable .rounded-pill:not(.fsl-word):not(.badge) {
    resize: horizontal !important;
    overflow: auto !important;
    min-width: 180px !important;
    max-width: 100% !important;
    cursor: text;
    position: relative;
    transition: background 0.2s ease, border-color 0.2s ease;
}

.note-editable .shape-active-focus {
    outline: 2px solid #0284c7 !important;
    outline-offset: 2px !important;
}

/* Ensure FSL words look like beautiful interactive pill buttons inside the editor */
.note-editable .fsl-word {
    display: inline-flex !important;
    align-items: center !important;
    gap: 4px !important;
    background: #fef3c7 !important;
    color: #92400e !important;
    border: 1.5px solid #f59e0b !important;
    border-radius: 9999px !important;
    padding: 2px 10px !important;
    margin: 0 3px !important;
    font-weight: 700 !important;
    font-size: 0.9em !important;
    vertical-align: middle !important;
    box-shadow: 0 1px 3px rgba(245, 158, 11, 0.2) !important;
}
.note-editable .fsl-word::after {
    content: " 🖐️";
    font-size: 0.8em;
}

/* Center Guide Indicator for Slide Layout Positioning */
.note-editor.show-center-guide .note-editing-area {
    position: relative !important;
}

.note-editor.show-center-guide .note-editing-area::before {
    content: "";
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50%;
    width: 0;
    border-left: 2px dashed rgba(2, 132, 199, 0.45);
    z-index: 5;
    pointer-events: none;
}

.note-editor.show-center-guide .note-editing-area::after {
    content: "CENTER 50%";
    position: absolute;
    top: 6px;
    left: 50%;
    transform: translateX(-50%);
    background: #0284c7;
    color: #ffffff;
    font-size: 0.62rem;
    font-weight: 700;
    padding: 1px 7px;
    border-radius: 9999px;
    z-index: 6;
    pointer-events: none;
    letter-spacing: 0.5px;
    box-shadow: 0 1px 4px rgba(2, 132, 199, 0.25);
}
</style>

<div class="main-content">
    <div class="lesson-studio-shell">
        
        <!-- Consolidated Sleek Studio Top Header -->
        <div class="studio-card px-3 py-2 mb-2.5 bg-white shadow-sm">
            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2">
                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <a href="<?php echo htmlspecialchars($basePath); ?>/iep/implementation/workspace/<?php echo $iepId; ?>" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-1 fw-semibold" title="Back to Workspace">
                        <i class="ph-bold ph-arrow-left me-1"></i> Back to Workspace
                    </a>
                    <div class="d-flex align-items-center gap-1.5 ms-1">
                        <span class="badge bg-primary text-white rounded-pill px-2 py-0.5" style="font-size: 0.72rem;">STUDIO</span>
                        <h4 class="fw-bold text-dark mb-0 fs-6 text-truncate" style="max-width: 440px;" title="<?php echo htmlspecialchars($lessonPlan['title'] ?? 'Lesson'); ?>">
                            <i class="ph-bold ph-presentation me-1 text-primary"></i> <?php echo htmlspecialchars($lessonPlan['title'] ?? 'Lesson'); ?>
                        </h4>
                    </div>
                    <span class="badge bg-primary-subtle text-primary px-2.5 py-0.5 rounded-pill fw-semibold" style="font-size: 0.74rem;">
                        <i class="ph-bold ph-compass me-1"></i> <?php echo htmlspecialchars(str_replace('_', ' ', $domainName)); ?>
                    </span>
                    <?php if ($studentName): ?>
                        <span class="badge bg-light text-dark border px-2.5 py-0.5 rounded-pill fw-semibold d-none d-md-inline" style="font-size: 0.74rem;">
                            <i class="ph-bold ph-user me-1 text-primary"></i> <?php echo htmlspecialchars($studentName); ?>
                        </span>
                    <?php endif; ?>
                </div>

                <div class="d-flex align-items-center gap-2 flex-wrap">
                    <span class="badge bg-light text-secondary border px-2.5 py-1" id="totalSlidesCountBadge" style="font-size: 0.76rem;">
                        <?php echo count($pages); ?> Slides
                    </span>
                    <a href="<?php echo htmlspecialchars($basePath); ?>/learning/lesson/<?php echo $lpId; ?>" target="_blank" class="btn btn-xs btn-outline-primary rounded-pill px-2.5 py-1 fw-bold">
                        <i class="ph-bold ph-eye me-1"></i> Preview as Learner <i class="ph-bold ph-arrow-square-out ms-0.5"></i>
                    </a>
                    <button type="button" class="btn btn-xs btn-success rounded-pill px-3 py-1 fw-bold" onclick="saveActiveSlide()">
                        <i class="ph-bold ph-floppy-disk me-1"></i> Save Slide
                    </button>
                </div>
            </div>
        </div>

        <!-- Floating Re-open Button when Slide Deck is Hidden -->
        <button type="button" id="btnOpenSlideDeck" class="btn btn-light border shadow-sm px-2.5 py-1.5 fw-semibold text-dark" onclick="toggleSlideDeck()" style="display:none; position: fixed; left: 14px; top: 116px; z-index: 1040; border-radius: 6px; font-size: 0.8125rem;">
            <i class="ph-bold ph-slides me-1 text-primary"></i> Slide Deck <i class="ph-bold ph-caret-right ms-1"></i>
        </button>

        <!-- Floating Re-open Button when FSL Sign Library is Hidden -->
        <button type="button" id="btnOpenFslLibrary" class="btn btn-warning shadow-sm px-2.5 py-1.5 fw-semibold" onclick="toggleFslLibrary()" style="display:none; position: fixed; right: 14px; top: 116px; z-index: 1040; border-radius: 6px; font-size: 0.8125rem; background: #fef3c7; color: #92400e; border: 1px solid #fcd34d;">
            <i class="ph-bold ph-hand-waving me-1"></i> FSL Sign Library <i class="ph-bold ph-caret-left ms-1"></i>
        </button>

        <!-- 3-Column Studio Workspace -->
        <div class="row g-2.5" id="studioWorkspaceRow">
            
            <!-- Column 1: Slide Deck Navigator (25% / col-lg-3) -->
            <div class="col-lg-3" id="slideDeckCol">
                <div class="studio-card h-100 d-flex flex-column">
                    <div class="studio-card-header">
                        <span class="fw-bold small text-dark"><i class="ph-bold ph-slides me-1 text-primary"></i>Slide Deck</span>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-xs btn-primary px-2 py-0.5 fw-semibold" onclick="startNewSlide()" style="font-size: 0.75rem; border-radius: 6px;">
                                <i class="ph-bold ph-plus me-0.5"></i> New
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-secondary px-2 py-0.5 fw-semibold" onclick="toggleSlideDeck()" title="Hide Slide Deck to expand editor" style="font-size: 0.75rem; border-radius: 6px;">
                                <i class="ph-bold ph-caret-left me-0.5"></i> Hide
                            </button>
                        </div>
                    </div>
                    
                    <div class="slide-deck-list flex-grow-1" id="slideDeckList">
                        <!-- Populated by JS -->
                    </div>
                    
                    <div class="p-2 border-top bg-light text-center">
                        <button type="button" class="btn btn-xs btn-outline-primary w-100 rounded-pill fw-bold" onclick="startNewSlide()">
                            <i class="ph-bold ph-plus-circle me-1"></i> Add Slide
                        </button>
                    </div>
                </div>
            </div>

            <!-- Column 2: Slide Canvas & Editor (47% / col-lg-6) -->
            <div class="col-lg-6" id="slideEditorCol">
                <div class="studio-card h-100 d-flex flex-column">
                    <div class="studio-card-header">
                        <div class="d-flex align-items-center gap-2">
                            <span class="slide-item-number" id="activeSlideBadge">Slide 1</span>
                            <span class="fw-bold small text-dark" id="activeSlideHeaderTitle">Edit Slide Content</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-0.5" id="deleteSlideBtn" onclick="deleteActiveSlide()" style="display: none; font-size: 0.75rem;">
                                <i class="ph-bold ph-trash me-1"></i> Delete
                            </button>
                        </div>
                    </div>

                    <div class="p-3 flex-grow-1 overflow-auto" style="max-height: calc(100vh - 270px);">
                        <form id="slideForm" onsubmit="event.preventDefault(); saveActiveSlide();">
                            <input type="hidden" id="slideId" value="">
                            
                            <!-- Slide Title -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-dark mb-1">
                                    Slide Title <span class="text-danger">*</span>
                                </label>
                                <input type="text" class="form-control form-control-sm" id="slideTitleInput" placeholder="e.g. Morning Greetings at the Dining Table" required style="border-radius: 8px; font-weight: 600;">
                            </div>

                            <!-- Summernote Editor -->
                            <div class="mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1 flex-wrap gap-1">
                                    <label class="form-label fw-bold small text-dark mb-0">
                                        Slide Content &amp; Interactive Words <span class="text-danger">*</span>
                                    </label>
                                    <div class="d-flex align-items-center gap-1.5 flex-wrap">
                                        <button type="button" class="btn btn-xs btn-outline-secondary text-dark fw-semibold rounded-pill px-2.5 py-1" onclick="insertSampleInteractiveTemplate()" title="Insert interactive story sample" style="font-size: 0.75rem;">
                                            <i class="ph-bold ph-file-text me-1"></i> Sample Story
                                        </button>

                                        <button type="button" class="btn btn-xs btn-outline-danger rounded-pill px-2 py-1" onclick="clearEditorContent()" title="Papasa tanang sulod sa editor" style="font-size: 0.75rem;">
                                            <i class="ph-bold ph-trash me-0.5"></i> Clear
                                        </button>
                                    </div>
                                </div>

                                <!-- Live Shape Quick Adjust Toolbar (Always Available above Editor) -->
                                <div id="shapeAdjustToolbar" class="p-2 mb-2 border rounded-3 shadow-sm align-items-center justify-content-between flex-wrap gap-2" style="display: flex; border-color: #cbd5e1 !important; background: #f8fafc !important;">
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <span class="small fw-bold text-dark">
                                            <i class="ph-bold ph-sliders text-primary me-1"></i> Adjust Shape:
                                        </span>
                                        <div class="btn-group btn-group-xs">
                                            <button type="button" class="btn btn-xs btn-outline-secondary fw-semibold" onclick="adjustActiveShapeWidth('360px')" title="Gamay nga gidak-on (360px)">
                                                <i class="ph-bold ph-arrows-in-line-horizontal me-0.5"></i> Gamay
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary fw-semibold" onclick="adjustActiveShapeWidth('540px')" title="Sakto nga gidak-on (540px)">
                                                <i class="ph-bold ph-arrows-out-line-horizontal me-0.5"></i> Sakto
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary fw-semibold" onclick="adjustActiveShapeWidth('100%')" title="Lapad / Full Width (100%)">
                                                <i class="ph-bold ph-arrows-horizontal me-0.5"></i> Lapad
                                            </button>
                                        </div>

                                        <span class="text-muted">|</span>

                                        <!-- Pwesto / Alignment options -->
                                        <span class="small text-muted">Pwesto:</span>
                                        <div class="btn-group btn-group-xs">
                                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="adjustActiveShapeAlign('left')" title="I-pwesto sa Wala (Left)">
                                                <i class="ph-bold ph-text-align-left"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="adjustActiveShapeAlign('center')" title="I-pwesto sa Tunga (Center)">
                                                <i class="ph-bold ph-text-align-center"></i>
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary" onclick="adjustActiveShapeAlign('right')" title="I-pwesto sa Tuo (Right)">
                                                <i class="ph-bold ph-text-align-right"></i>
                                            </button>
                                        </div>

                                        <span class="text-muted">|</span>

                                        <!-- Balhin Ibabaw / Ilalom -->
                                        <div class="btn-group btn-group-xs">
                                            <button type="button" class="btn btn-xs btn-outline-secondary fw-semibold" onclick="moveActiveShape('up')" title="Isaka sa ibabaw">
                                                <i class="ph-bold ph-arrow-up me-0.5"></i> Isaka
                                            </button>
                                            <button type="button" class="btn btn-xs btn-outline-secondary fw-semibold" onclick="moveActiveShape('down')" title="Ipaubos sa ilalom">
                                                <i class="ph-bold ph-arrow-down me-0.5"></i> Ipaubos
                                            </button>
                                        </div>

                                        <span class="text-muted">|</span>

                                        <!-- Kolor options -->
                                        <span class="small text-muted">Kolor:</span>
                                        <div class="d-flex align-items-center gap-1">
                                            <button type="button" class="btn btn-xs rounded-circle p-0" onclick="adjustActiveShapeTheme('yellow')" style="width:22px; height:22px; background:#fffbeb; border:2px solid #f59e0b;" title="Dilaw / Yellow"></button>
                                            <button type="button" class="btn btn-xs rounded-circle p-0" onclick="adjustActiveShapeTheme('green')" style="width:22px; height:22px; background:#f0fdf4; border:2px solid #86efac;" title="Berde / Green"></button>
                                            <button type="button" class="btn btn-xs rounded-circle p-0" onclick="adjustActiveShapeTheme('blue')" style="width:22px; height:22px; background:#eff6ff; border:2px solid #93c5fd;" title="Asul / Blue"></button>
                                            <button type="button" class="btn btn-xs rounded-circle p-0" onclick="adjustActiveShapeTheme('neutral')" style="width:22px; height:22px; background:#f8fafc; border:2px solid #cbd5e1;" title="Neutral / Puti"></button>
                                        </div>
                                    </div>

                                    <div class="d-flex align-items-center gap-1.5">
                                        <button type="button" class="btn btn-xs btn-primary text-white fw-semibold" id="btnToggleCenterGuide" onclick="toggleCenterGuide()" title="Ipakita o itago ang Center Guide Indicator (Tunga sa Slide)">
                                            <i class="ph-bold ph-split-vertical me-1"></i> Center Guide: <span id="centerGuideState">ON</span>
                                        </button>
                                        <button type="button" class="btn btn-xs btn-outline-danger fw-semibold" onclick="deleteActiveShape()" title="Papasa kining maong shape">
                                            <i class="ph-bold ph-trash me-1"></i> Papasa
                                        </button>
                                    </div>
                                </div>

                                <textarea id="slideContentEditor"></textarea>
                            </div>

                            <!-- Guide Questions / Check-in Prompts -->
                            <div class="mb-3">
                                <label class="form-label fw-bold small text-primary mb-1">
                                    <i class="ph-bold ph-chat-circle-dots me-1"></i> Guide Questions / Check-in Prompts <span class="text-muted fw-normal">(optional)</span>
                                </label>
                                <textarea class="form-control form-control-sm" id="slideGuideInput" rows="2" placeholder="e.g. 1. What did Ana say when greeting her parents?&#10;2. Can you imitate the sign for 'Good Morning'?" style="border-radius: 8px;"></textarea>
                            </div>

                            <!-- Hidden inputs for media compatibility -->
                            <input type="hidden" id="slideMediaTypeSelect" value="none">
                            <input type="hidden" id="slideMediaUrlInput" value="">
                            <input type="file" id="slideMediaFileInput" style="display: none;">

                            <!-- Action Buttons -->
                            <div class="d-flex align-items-center justify-content-between pt-2 border-top">
                                <div class="d-flex align-items-center gap-1.5" id="saveStatusIndicator">
                                    <span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size:0.75rem;">
                                        <i class="ph-bold ph-cloud-check me-1"></i> Awtomatikong Na-save
                                    </span>
                                </div>
                                <div class="d-flex gap-2">
                                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" onclick="startNewSlide()">
                                        <i class="ph-bold ph-plus me-1"></i> Bag-ong Slide
                                    </button>
                                    <button type="button" class="btn btn-sm btn-primary rounded-pill px-3 fw-bold" id="saveSlideSubmitBtn" onclick="saveActiveSlide(true)">
                                        <i class="ph-bold ph-floppy-disk me-1"></i> I-save Karon
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Column 3: FSL Sign & Interactive Aid Library Drawer (28% / col-lg-3) -->
            <div class="col-lg-3" id="fslLibraryCol">
                <div class="studio-card h-100 d-flex flex-column">
                    <div class="studio-card-header bg-warning-subtle text-warning-emphasis">
                        <div class="d-flex align-items-center gap-1.5">
                            <i class="ph-bold ph-hand-waving fs-5 text-warning"></i>
                            <span class="fw-bold small">FSL Sign Library</span>
                        </div>
                        <div class="d-flex align-items-center gap-1">
                            <button type="button" class="btn btn-xs btn-primary rounded-pill px-2.5 py-1 fw-bold" onclick="openAddCustomSignModal()" title="Upload your own video for a sign">
                                <i class="ph-bold ph-video-camera me-1"></i>+ Upload Sign
                            </button>
                            <button type="button" class="btn btn-xs btn-outline-dark rounded-pill px-2 py-1 fw-bold" onclick="toggleFslLibrary()" title="Hide FSL Library to expand slide editor">
                                <i class="ph-bold ph-caret-right me-0.5"></i> Hide
                            </button>
                        </div>
                    </div>

                    <div class="p-2 border-bottom bg-light">
                        <!-- Search Box -->
                        <div class="input-group input-group-sm mb-1.5">
                            <span class="input-group-text bg-white border-end-0" style="border-radius: 9999px 0 0 9999px;"><i class="ph-bold ph-magnifying-glass text-muted"></i></span>
                            <input type="text" class="form-control border-start-0" id="fslSearchInput" placeholder="Search signs (e.g. Father, Mother, Thank You)..." oninput="filterFslSigns()" style="border-radius: 0 9999px 9999px 0;">
                        </div>

                        <!-- Drag cue & Count badge -->
                        <div class="d-flex align-items-center justify-content-between px-1 mb-1.5" style="font-size: 0.72rem;">
                            <span class="text-muted"><i class="ph-bold ph-hand-pointing text-warning me-1"></i> Drag card or click insert</span>
                            <span class="badge bg-warning text-dark rounded-pill fw-bold" id="fslCountBadge"><?php echo count($fslSigns); ?> Signs</span>
                        </div>

                        <!-- Category Filter Pills -->
                        <div class="d-flex gap-1 overflow-auto pb-1" id="fslCategoryPills">
                            <button type="button" class="btn btn-xs btn-warning rounded-pill px-2.5 py-0.5 fw-bold fsl-cat-filter active" data-cat="all" onclick="setFslCategory('all', this)">
                                All
                            </button>
                            <?php foreach ($fslCategories as $cat): ?>
                                <button type="button" class="btn btn-xs btn-outline-secondary rounded-pill px-2.5 py-0.5 fw-semibold fsl-cat-filter" data-cat="<?php echo htmlspecialchars($cat); ?>" onclick="setFslCategory('<?php echo htmlspecialchars($cat); ?>', this)">
                                    <?php echo htmlspecialchars(ucfirst($cat)); ?>
                                </button>
                            <?php endforeach; ?>
                        </div>
                    </div>

                    <!-- Sign Cards List -->
                    <div class="fsl-library-list flex-grow-1" id="fslSignsContainer">
                        <?php foreach ($fslSigns as $sign): ?>
                            <div class="fsl-sign-card" draggable="true" data-word="<?php echo htmlspecialchars(strtolower($sign['word'])); ?>" data-cat="<?php echo htmlspecialchars($sign['category'] ?? 'General'); ?>" data-video="<?php echo htmlspecialchars($sign['video_path'] ?? ''); ?>" data-desc="<?php echo htmlspecialchars($sign['description'] ?? ''); ?>">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <div class="d-flex align-items-center gap-1.5">
                                        <span class="text-muted" title="Can be dragged into editor" style="cursor: grab;"><i class="ph-bold ph-dots-six-vertical"></i></span>
                                        <strong class="text-dark text-capitalize" style="font-size: 0.95rem;">
                                            <?php echo htmlspecialchars(ucfirst($sign['word'])); ?>
                                        </strong>
                                    </div>
                                    <span class="fsl-sign-badge"><?php echo htmlspecialchars(ucfirst($sign['category'] ?? 'General')); ?></span>
                                </div>
                                <?php if (!empty($sign['description'])): ?>
                                    <p class="small text-muted mb-2" style="font-size: 0.76rem; line-height: 1.3;">
                                        <?php echo htmlspecialchars($sign['description']); ?>
                                    </p>
                                <?php endif; ?>
                                <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                                    <button type="button" class="btn btn-link btn-xs text-primary p-0 fw-semibold" onclick="previewFslSign('<?php echo htmlspecialchars(addslashes($sign['word'])); ?>')">
                                        <i class="ph-bold ph-play-circle me-1"></i> Watch
                                    </button>
                                    <button type="button" class="btn btn-xs fsl-insert-btn" onmousedown="saveCurrentSelection(); event.preventDefault();" onclick="insertFslSignIntoSlide('<?php echo htmlspecialchars(addslashes($sign['word'])); ?>', this.closest('.fsl-sign-card'))">
                                        <i class="ph-bold ph-plus me-1"></i> Insert to Slide
                                    </button>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>

                    <div class="p-2 border-top bg-light text-center small text-muted">
                        <i class="ph-bold ph-info me-1"></i> Click "Insert to Slide" or drag directly into the editor.
                    </div>
                </div>
            </div>

        </div>
    </div>
</div>

<script>
const LP_ID = <?php echo $lpId; ?>;
const IEP_ID = <?php echo $iepId; ?>;
const BASE = '<?php echo $basePath; ?>';
let LESSON_PAGES = <?php echo json_encode($pages ?: []); ?>;
const ALL_FSL_SIGNS = <?php echo json_encode($fslSigns ?: []); ?>;
let activeSlideIndex = 0;
let currentCategoryFilter = 'all';

let selectedModalSignWord = '';
let selectedModalSignVideo = '';
let selectedModalSignDesc = '';
let selectedModalSignCategory = '';

let currentActiveShape = null;

// ── Initialize Summernote on Slide Content ──
$(document).ready(function() {
    initSummernote();
    renderSlideDeck();
    if (LESSON_PAGES.length > 0) {
        loadSlideIntoEditor(0);
    } else {
        startNewSlide();
    }

    // Detect click on any shape inside Summernote to show the Shape Adjust Toolbar
    $(document).on('click', '.note-editable', function(e) {
        const shape = e.target.closest('[data-shape-box="true"], .shape-container, [class*="shape-"], .p-3\\.5, .rounded-4, .rounded-pill:not(.fsl-word):not(.badge)');
        if (shape && !shape.classList.contains('fsl-word')) {
            $('.note-editable .shape-active-focus').removeClass('shape-active-focus');
            shape.classList.add('shape-active-focus');
            currentActiveShape = shape;
            const tb = document.getElementById('shapeAdjustToolbar');
            if (tb) tb.style.display = 'flex';
        }
    });
});

let lastSelectedText = '';

function saveCurrentSelection() {
    try {
        const range = $('#slideContentEditor').summernote('createRange');
        if (range) {
            const txt = range.toString().trim();
            if (txt) {
                lastSelectedText = txt;
                $('#slideContentEditor').summernote('saveRange');
            }
        }
    } catch(e) {}
}

function initSummernote() {
    // Custom Toolbar Button for Inserting Simple Shapes
    const ShapesButton = function (context) {
        const ui = $.summernote.ui;
        const button = ui.buttonGroup([
            ui.button({
                className: 'dropdown-toggle',
                contents: '<i class="ph-bold ph-shapes text-primary me-1"></i> <span style="font-weight:700; color:#1e4072;">Shapes</span>',
                tooltip: 'Insert a simple shape container (Box, Speech Bubble, Pill, etc.)',
                data: {
                    toggle: 'dropdown'
                }
            }),
            ui.dropdown({
                className: 'drop-default dropdown-menu shadow-sm',
                contents: `
                    <div class="px-3 py-1 text-muted small fw-bold text-uppercase border-bottom" style="font-size:0.68rem;">Pili ug Shape:</div>
                    <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="insertShapeIntoEditor('box')">
                        <i class="ph-bold ph-square text-primary"></i> <span><strong>Rounded Box</strong> <small class="text-muted d-block" style="font-size:0.7rem;">Kahon nga linginon</small></span>
                    </a>
                    <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="insertShapeIntoEditor('bubble')">
                        <i class="ph-bold ph-chat-circle-dots text-success"></i> <span><strong>Speech Bubble</strong> <small class="text-muted d-block" style="font-size:0.7rem;">Chat / Story balloon</small></span>
                    </a>
                    <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="insertShapeIntoEditor('pill')">
                        <i class="ph-bold ph-pill text-warning"></i> <span><strong>Big Pill</strong> <small class="text-muted d-block" style="font-size:0.7rem;">Oblong container</small></span>
                    </a>
                    <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="insertShapeIntoEditor('two_boxes')">
                        <i class="ph-bold ph-columns" style="color:#9333ea;"></i> <span><strong>2 Side-by-Side Boxes</strong> <small class="text-muted d-block" style="font-size:0.7rem;">Duha ka kahon</small></span>
                    </a>
                    <a class="dropdown-item py-1.5 d-flex align-items-center gap-2" href="javascript:void(0)" onclick="insertShapeIntoEditor('note')">
                        <i class="ph-bold ph-lightbulb text-info"></i> <span><strong>Note / Reminder</strong> <small class="text-muted d-block" style="font-size:0.7rem;">Pahinumdom nga kahon</small></span>
                    </a>
                `
            })
        ]);
        return button.render();
    };

    // Custom Toolbar Button for FSL Sign Linking / Insertion
    const FslButton = function (context) {
        const ui = $.summernote.ui;
        const button = ui.button({
            contents: '<i class="ph-bold ph-hand-waving text-warning me-1"></i> <span style="font-weight:700; color:#92400e;">FSL Sign</span>',
            tooltip: 'Link FSL Video Sign to selected text or insert sign',
            click: function () {
                saveCurrentSelection();
                const range = $('#slideContentEditor').summernote('createRange');
                const selectedText = (range ? range.toString().trim() : '') || lastSelectedText || '';
                openLinkFslSignModal(selectedText);
            }
        });
        return button.render();
    };

    $('#slideContentEditor').summernote({
        placeholder: 'Write slide content, story, instructions, table, or insert FSL video signs...',
        height: 380,
        tabsize: 2,
        toolbar: [
            ['shapes', ['shapesButton']],
            ['fsl', ['fslButton']],
            ['style', ['style', 'bold', 'italic', 'underline', 'clear']],
            ['font', ['color']],
            ['para', ['ul', 'ol', 'paragraph']],
            ['table', ['table']],
            ['insert', ['link', 'picture', 'video']],
            ['view', ['fullscreen', 'codeview', 'help']]
        ],
        buttons: {
            shapesButton: ShapesButton,
            fslButton: FslButton
        },
        callbacks: {
            onChange: function(contents, $editable) {
                saveCurrentSelection();
                triggerAutoSave();
            },
            onKeyup: function(e) {
                saveCurrentSelection();
                triggerAutoSave();
            },
            onMouseup: function(e) {
                saveCurrentSelection();
            },
            onImageUpload: function(files) {
                if (!files || !files.length) return;
                for (let i = 0; i < files.length; i++) {
                    uploadImageToServer(files[i], $(this));
                }
            }
        }
    });

    // Enable Center Guide by default so teachers can align their slide layout cleanly
    $('.note-editor').addClass('show-center-guide');

    // Auto-save when editing Title or Guide Questions
    document.getElementById('slideTitleInput').addEventListener('input', triggerAutoSave);
    document.getElementById('slideGuideInput').addEventListener('input', triggerAutoSave);
}

function uploadImageToServer(file, $editor) {
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
            const reader = new FileReader();
            reader.onloadend = () => $editor.summernote('insertImage', reader.result);
            reader.readAsDataURL(file);
        }
    })
    .catch(() => {
        const reader = new FileReader();
        reader.onloadend = () => $editor.summernote('insertImage', reader.result);
        reader.readAsDataURL(file);
    });
}

// ── Render Slide Deck Left Navigator ──
function renderSlideDeck() {
    const listEl = document.getElementById('slideDeckList');
    document.getElementById('totalSlidesCountBadge').textContent = LESSON_PAGES.length + ' Slides Total';

    if (!LESSON_PAGES || LESSON_PAGES.length === 0) {
        listEl.innerHTML = `
            <div class="text-center py-4 text-muted">
                <i class="ph-bold ph-slides fs-2 mb-2 d-block text-secondary"></i>
                <p class="small mb-0">No slides yet in this lesson.<br>Click <strong>+ New</strong> above to add one.</p>
            </div>
        `;
        return;
    }

    let html = '';
    LESSON_PAGES.forEach((p, idx) => {
        const isActive = (idx === activeSlideIndex);
        const stripText = (p.content || '').replace(/<[^>]*>?/gm, ' ').trim();
        const preview = stripText.length > 80 ? stripText.substring(0, 80) + '...' : (stripText || 'No content entered...');

        html += `
            <div class="slide-deck-item ${isActive ? 'active' : ''}" onclick="loadSlideIntoEditor(${idx})">
                <div class="d-flex justify-content-between align-items-center">
                    <span class="slide-item-number">Slide ${idx + 1}</span>
                    <div class="d-flex align-items-center gap-1">
                        ${idx > 0 ? `<button type="button" class="btn btn-link btn-xs p-0 text-muted" title="Move up" onclick="event.stopPropagation(); moveSlideOrder(${idx}, -1);"><i class="ph-bold ph-caret-up"></i></button>` : ''}
                        ${idx < LESSON_PAGES.length - 1 ? `<button type="button" class="btn btn-link btn-xs p-0 text-muted" title="Move down" onclick="event.stopPropagation(); moveSlideOrder(${idx}, 1);"><i class="ph-bold ph-caret-down"></i></button>` : ''}
                    </div>
                </div>
                <div class="slide-item-title">${escapeHtml(p.title || 'Slide ' + (idx + 1))}</div>
                <div class="slide-item-preview">${escapeHtml(preview)}</div>
            </div>
        `;
    });

    listEl.innerHTML = html;
}

// ── Load Slide into Canvas Form ──
function loadSlideIntoEditor(idx) {
    if (!LESSON_PAGES[idx]) return;
    activeSlideIndex = idx;
    const p = LESSON_PAGES[idx];

    document.getElementById('slideId').value = p.id;
    document.getElementById('slideTitleInput').value = p.title || '';
    $('#slideContentEditor').summernote('code', p.content || '');
    document.getElementById('slideGuideInput').value = p.guide_questions || '';

    // Media
    const mediaType = p.media_type || 'none';
    document.getElementById('slideMediaTypeSelect').value = mediaType;
    toggleMediaInputFields();

    const currentNoteEl = document.getElementById('mediaFileCurrentNote');
    if (p.media_path && (mediaType === 'image' || mediaType === 'video')) {
        currentNoteEl.innerHTML = `<span class="text-success"><i class="ph-bold ph-check-circle me-1"></i> Current file:</span> <code>${escapeHtml(p.media_path)}</code>`;
    } else {
        currentNoteEl.innerHTML = '';
    }

    if (mediaType === 'embed') {
        document.getElementById('slideMediaUrlInput').value = p.media_path || '';
    }

    // UI Badges
    document.getElementById('activeSlideBadge').textContent = 'Slide ' + (idx + 1);
    document.getElementById('activeSlideHeaderTitle').textContent = p.title || 'Edit Slide Content';
    document.getElementById('deleteSlideBtn').style.display = 'inline-flex';
    document.getElementById('saveStatusIndicator').textContent = 'Loaded Slide ' + (idx + 1) + '. Ready to edit.';

    renderSlideDeck();
}

// ── Start Fresh New Slide ──
function startNewSlide() {
    activeSlideIndex = -1;
    document.getElementById('slideId').value = '';
    document.getElementById('slideTitleInput').value = '';
    $('#slideContentEditor').summernote('code', '');
    document.getElementById('slideGuideInput').value = '';
    document.getElementById('slideMediaTypeSelect').value = 'none';
    toggleMediaInputFields();

    document.getElementById('activeSlideBadge').textContent = 'New Slide';
    document.getElementById('activeSlideHeaderTitle').textContent = 'Add Slide Content';
    document.getElementById('deleteSlideBtn').style.display = 'none';
    document.getElementById('saveStatusIndicator').textContent = 'Ready for new slide.';

    renderSlideDeck();
    document.getElementById('slideTitleInput').focus();
}

// ── Auto-Save Engine (Debounced) ──
let autoSaveTimer = null;
let isAutoSaving = false;

function triggerAutoSave() {
    const title = document.getElementById('slideTitleInput').value.trim();
    const content = $('#slideContentEditor').summernote('code');
    
    // Don't auto-save completely empty canvases
    if (!title && (!content || content === '<p><br></p>')) {
        return;
    }

    const indicator = document.getElementById('saveStatusIndicator');
    if (indicator) {
        indicator.innerHTML = '<span class="badge bg-warning-subtle text-dark border border-warning-subtle rounded-pill px-2.5 py-1" style="font-size:0.75rem;"><i class="ph-bold ph-arrows-clockwise me-1 spin"></i> Nag-save...</span>';
    }

    clearTimeout(autoSaveTimer);
    autoSaveTimer = setTimeout(() => {
        saveActiveSlide(false); // background silent auto-save
    }, 1500);
}

// ── Save Active Slide (AJAX) ──
function saveActiveSlide(isManual = true) {
    const titleInput = document.getElementById('slideTitleInput');
    let title = titleInput.value.trim();
    const content = $('#slideContentEditor').summernote('code');
    const guideQuestions = document.getElementById('slideGuideInput').value.trim();
    const slideId = document.getElementById('slideId').value;

    if (!title) {
        if (isManual) {
            alert('Palihug pagbutang una ug Slide Title.');
            titleInput.focus();
            return;
        } else {
            title = 'Slide ' + (LESSON_PAGES.length + 1);
            titleInput.value = title;
        }
    }

    if (isAutoSaving) return;
    isAutoSaving = true;

    const saveBtn = document.getElementById('saveSlideSubmitBtn');
    if (isManual && saveBtn) {
        saveBtn.disabled = true;
        saveBtn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Nag-save...';
    }

    const fd = new FormData();
    fd.append('title', title);
    fd.append('content', content);
    fd.append('guide_questions', guideQuestions);
    fd.append('media_type', 'none');

    const isUpdate = Boolean(slideId);
    const url = isUpdate 
        ? `${BASE}/iep/implementation/lesson-plan/page/${slideId}/update` 
        : `${BASE}/iep/implementation/lesson-plan/${LP_ID}/page/add`;

    fetch(url, {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        isAutoSaving = false;
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="ph-bold ph-floppy-disk me-1"></i> I-save Karon';
        }

        if (data.success) {
            const pageId = data.page_id || slideId;
            if (pageId && !slideId) {
                document.getElementById('slideId').value = pageId;
            }

            const indicator = document.getElementById('saveStatusIndicator');
            if (indicator) {
                indicator.innerHTML = '<span class="badge bg-success-subtle text-success border border-success-subtle rounded-pill px-2.5 py-1 fw-semibold" style="font-size:0.75rem;"><i class="ph-bold ph-cloud-check me-1"></i> Awtomatikong Na-save (' + new Date().toLocaleTimeString([], {hour: '2-digit', minute:'2-digit'}) + ')</span>';
            }

            // Silently refresh slide deck thumbnail without reloading editor content
            silentRefreshDeck();
        } else {
            if (isManual) {
                alert('Failed to save slide: ' + (data.message || 'Error occurred'));
            }
        }
    })
    .catch(err => {
        isAutoSaving = false;
        if (saveBtn) {
            saveBtn.disabled = false;
            saveBtn.innerHTML = '<i class="ph-bold ph-floppy-disk me-1"></i> I-save Karon';
        }
        console.error('Save error:', err);
    });
}

// ── Silent Refresh of Left Deck without blowing away active inputs ──
function silentRefreshDeck() {
    fetch(`${BASE}/iep/implementation/lesson-plan/${LP_ID}/pages`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.pages) {
                LESSON_PAGES = data.pages;
                renderSlideDeck();
            }
        })
        .catch(() => {});
}

// ── Refresh Pages List from Server ──
function refreshPagesFromServer(targetPageId) {
    fetch(`${BASE}/iep/implementation/lesson-plan/${LP_ID}/pages`)
        .then(r => r.json())
        .then(data => {
            if (data.success && data.pages) {
                LESSON_PAGES = data.pages;
                let targetIdx = 0;
                if (targetPageId) {
                    const found = LESSON_PAGES.findIndex(p => String(p.id) === String(targetPageId));
                    if (found >= 0) targetIdx = found;
                }
                loadSlideIntoEditor(targetIdx);
            }
        });
}

// ── Delete Active Slide ──
function deleteActiveSlide() {
    const slideId = document.getElementById('slideId').value;
    if (!slideId) return;

    if (!confirm('Are you sure you want to delete this slide?')) {
        return;
    }

    fetch(`${BASE}/iep/implementation/lesson-plan/page/${slideId}/delete`, {
        method: 'POST'
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            refreshPagesFromServer();
        } else {
            alert('Failed to delete slide: ' + (data.message || 'Error'));
        }
    });
}

// ── Toggle Media Input ──
function toggleMediaInputFields() {
    const val = document.getElementById('slideMediaTypeSelect').value;
    document.getElementById('mediaUploadWrap').style.display = (val === 'image' || val === 'video') ? 'block' : 'none';
    document.getElementById('mediaUrlWrap').style.display = (val === 'embed') ? 'block' : 'none';
}

// ── Responsive Multi-Drawer Width Recalculation ──
function recalcEditorWidth() {
    const deckCol = document.getElementById('slideDeckCol');
    const fslCol  = document.getElementById('fslLibraryCol');
    const editor  = document.getElementById('slideEditorCol');

    const deckHidden = deckCol ? deckCol.classList.contains('d-none') : false;
    const fslHidden  = fslCol ? fslCol.classList.contains('d-none') : false;

    editor.classList.remove('col-lg-6', 'col-lg-9', 'col-lg-12');

    if (deckHidden && fslHidden) {
        editor.classList.add('col-lg-12'); // 100% full width widescreen editor!
    } else if (deckHidden || fslHidden) {
        editor.classList.add('col-lg-9');
    } else {
        editor.classList.add('col-lg-6');
    }
}

// ── Toggle Slide Deck Navigator (Left Drawer) ──
function toggleSlideDeck() {
    const col = document.getElementById('slideDeckCol');
    const openBtn = document.getElementById('btnOpenSlideDeck');
    const isHidden = col.classList.contains('d-none');
    if (isHidden) {
        col.classList.remove('d-none');
        if (openBtn) openBtn.style.display = 'none';
    } else {
        col.classList.add('d-none');
        if (openBtn) openBtn.style.display = 'inline-flex';
    }
    recalcEditorWidth();
}

// ── Toggle FSL Sign Library Drawer (Right Drawer) ──
function toggleFslLibrary() {
    const col = document.getElementById('fslLibraryCol');
    const openBtn = document.getElementById('btnOpenFslLibrary');
    const isHidden = col.classList.contains('d-none');
    if (isHidden) {
        col.classList.remove('d-none');
        if (openBtn) openBtn.style.display = 'none';
    } else {
        col.classList.add('d-none');
        if (openBtn) openBtn.style.display = 'inline-flex';
    }
    recalcEditorWidth();
}

// ── Insert or Link FSL Sign into Slide Editor ──
function insertFslSignIntoSlide(word, cardEl = null) {
    const cleanWord = word.trim();
    let videoPath = '';
    let desc = '';
    let category = 'General';

    if (cardEl) {
        videoPath = cardEl.dataset.video || '';
        desc = cardEl.dataset.desc || '';
        category = cardEl.dataset.cat || 'General';
    } else {
        const found = ALL_FSL_SIGNS.find(s => s.word.toLowerCase() === cleanWord.toLowerCase());
        if (found) {
            videoPath = found.video_path || '';
            desc = found.description || '';
            category = found.category || 'General';
        }
    }

    // Check if teacher currently has text highlighted in the Summernote editor!
    let selectedText = '';
    try {
        const range = $('#slideContentEditor').summernote('createRange');
        selectedText = range ? range.toString().trim() : '';
    } catch(e) {}

    if (!selectedText && lastSelectedText) {
        selectedText = lastSelectedText;
    }

    if (selectedText) {
        try {
            $('#slideContentEditor').summernote('restoreRange');
        } catch(e) {}
        // Teacher highlighted text! Attach the FSL video directly to that highlighted text!
        const fslTagHtml = `&nbsp;<span class="fsl-word" data-word="${escapeHtml(cleanWord.toLowerCase())}" data-video="${escapeHtml(videoPath)}" data-category="${escapeHtml(category)}" data-desc="${escapeHtml(desc)}" title="FSL Sign: ${escapeHtml(cleanWord)}">${escapeHtml(selectedText)}</span>&nbsp;`;
        $('#slideContentEditor').summernote('pasteHTML', fslTagHtml);
        lastSelectedText = '';

        const toastIndicator = document.getElementById('saveStatusIndicator');
        toastIndicator.innerHTML = `<span class="text-success fw-bold"><i class="ph-bold ph-check-circle me-1"></i> Linked FSL Video "${escapeHtml(cleanWord)}" to text "${escapeHtml(selectedText)}"!</span>`;
        setTimeout(() => { toastIndicator.textContent = 'Ready to save.'; }, 3500);
        return;
    }

    // If NO text was highlighted, open the Link Modal pre-filled with this sign so teacher can confirm or customize display text!
    let defaultDisplayText = cleanWord;
    if (desc && desc.includes('-')) {
        const parts = desc.split('-');
        if (parts[0].trim().length > 0) defaultDisplayText = parts[0].trim();
    }
    openLinkFslSignModal(defaultDisplayText, cleanWord, videoPath, desc, category);
}

// ── Modal for Linking FSL Sign Video with Custom/Tagalog Text ──
function openLinkFslSignModal(prefillText = '', signWord = '', videoPath = '', desc = '', category = '') {
    const modalEl = document.getElementById('linkFslSignModal');
    const displayInput = document.getElementById('linkFslDisplayText');
    displayInput.value = prefillText || '';

    if (signWord) {
        selectModalSign(signWord, videoPath, desc, category);
    } else {
        const clean = (prefillText || '').toLowerCase().trim();
        let matched = null;
        if (clean) {
            matched = ALL_FSL_SIGNS.find(s => s.word.toLowerCase() === clean);
            if (!matched) {
                matched = ALL_FSL_SIGNS.find(s => (s.description || '').toLowerCase().includes(clean));
            }
        }
        if (matched) {
            selectModalSign(matched.word, matched.video_path, matched.description, matched.category);
        } else if (ALL_FSL_SIGNS.length > 0) {
            selectModalSign(ALL_FSL_SIGNS[0].word, ALL_FSL_SIGNS[0].video_path, ALL_FSL_SIGNS[0].description, ALL_FSL_SIGNS[0].category);
        }
    }

    document.getElementById('linkFslSearchInput').value = '';
    renderModalSignList();

    const bsModal = bootstrap.Modal.getOrCreateInstance(modalEl);
    bsModal.show();
    setTimeout(() => {
        displayInput.focus();
        displayInput.select();
    }, 300);
}

function selectModalSign(word, video, desc, category) {
    selectedModalSignWord = word;
    selectedModalSignVideo = video || '';
    selectedModalSignDesc = desc || '';
    selectedModalSignCategory = category || 'General';

    const emptyState = document.getElementById('dropzoneEmptyState');
    const selState = document.getElementById('dropzoneSelectedState');
    if (emptyState) emptyState.style.display = 'none';
    if (selState) selState.style.display = 'flex';

    const wordEl = document.getElementById('linkedSignWord');
    const catEl = document.getElementById('linkedSignCategory');
    const descEl = document.getElementById('linkedSignDesc');
    if (wordEl) wordEl.textContent = word.charAt(0).toUpperCase() + word.slice(1);
    if (catEl) catEl.textContent = category;
    if (descEl) descEl.textContent = desc || ('FSL Sign for ' + word);

    document.querySelectorAll('#linkFslPickerList .modal-sign-row').forEach(row => {
        if (row.dataset.word === word.toLowerCase()) {
            row.classList.add('bg-warning-subtle', 'border-warning');
        } else {
            row.classList.remove('bg-warning-subtle', 'border-warning');
        }
    });
}

function previewCurrentModalSign() {
    if (selectedModalSignWord) {
        previewFslSign(selectedModalSignWord);
    }
}

function renderModalSignList(filter = '') {
    const listContainer = document.getElementById('linkFslPickerList');
    if (!listContainer) return;
    const query = filter.toLowerCase().trim();
    let html = '';

    const filtered = ALL_FSL_SIGNS.filter(s => {
        if (!query) return true;
        return s.word.toLowerCase().includes(query) ||
               (s.description || '').toLowerCase().includes(query) ||
               (s.category || '').toLowerCase().includes(query);
    });

    if (filtered.length === 0) {
        listContainer.innerHTML = `<div class="p-3 text-center text-muted small">No signs found for "${escapeHtml(filter)}".</div>`;
        return;
    }

    filtered.forEach(s => {
        const isSelected = s.word.toLowerCase() === (selectedModalSignWord || '').toLowerCase();
        html += `
            <div class="modal-sign-row d-flex align-items-center justify-content-between p-2 mb-1 rounded-2 border ${isSelected ? 'bg-warning-subtle border-warning' : 'bg-light'}" data-word="${escapeHtml(s.word.toLowerCase())}" style="cursor: pointer;" onclick="selectModalSign('${escapeJs(s.word)}', '${escapeJs(s.video_path)}', '${escapeJs(s.description)}', '${escapeJs(s.category)}')">
                <div class="d-flex align-items-center gap-2">
                    <span class="badge bg-secondary-subtle text-dark border px-2 py-0.5 rounded-pill" style="font-size:0.7rem;">${escapeHtml(s.category || 'General')}</span>
                    <strong class="text-dark" style="font-size:0.92rem;">${escapeHtml(s.word.charAt(0).toUpperCase() + s.word.slice(1))}</strong>
                    ${s.description ? `<span class="small text-muted d-none d-md-inline" style="font-size:0.78rem;">— ${escapeHtml(s.description)}</span>` : ''}
                </div>
                <div class="d-flex gap-1">
                    <button type="button" class="btn btn-xs btn-link text-primary p-0 fw-semibold me-2" onclick="event.stopPropagation(); previewFslSign('${escapeJs(s.word)}')">
                        <i class="ph-bold ph-play-circle me-0.5"></i> Watch
                    </button>
                    <button type="button" class="btn btn-xs btn-outline-warning text-dark rounded-pill px-2 py-0.5 fw-bold ${isSelected ? 'btn-warning' : ''}">
                        ${isSelected ? '<i class="ph-bold ph-check"></i> Selected' : 'Select'}
                    </button>
                </div>
            </div>
        `;
    });

    listContainer.innerHTML = html;
}

function filterModalSignList() {
    const q = document.getElementById('linkFslSearchInput').value;
    renderModalSignList(q);
}

function applyFslSignLink() {
    const displayText = document.getElementById('linkFslDisplayText').value.trim();
    const signWord = (selectedModalSignWord || '').trim();
    const videoPath = (selectedModalSignVideo || '').trim();
    const desc = (selectedModalSignDesc || '').trim();
    const category = (selectedModalSignCategory || '').trim();

    if (!displayText) {
        alert('Please enter the display text to show on the slide.');
        document.getElementById('linkFslDisplayText').focus();
        return;
    }
    if (!signWord) {
        alert('Please select an FSL Video Sign to link.');
        return;
    }

    const modalEl = document.getElementById('linkFslSignModal');
    bootstrap.Modal.getOrCreateInstance(modalEl).hide();

    try {
        $('#slideContentEditor').summernote('restoreRange');
    } catch(e) {}

    // Insert tag into Summernote
    const tagHtml = `&nbsp;<span class="fsl-word" data-word="${escapeHtml(signWord.toLowerCase())}" data-video="${escapeHtml(videoPath)}" data-category="${escapeHtml(category)}" data-desc="${escapeHtml(desc)}" title="FSL Sign: ${escapeHtml(signWord)}">${escapeHtml(displayText)}</span>&nbsp;`;
    $('#slideContentEditor').summernote('pasteHTML', tagHtml);
    lastSelectedText = '';

    const toastIndicator = document.getElementById('saveStatusIndicator');
    toastIndicator.innerHTML = `<span class="text-success fw-bold"><i class="ph-bold ph-check-circle me-1"></i> Linked FSL Sign "${escapeHtml(signWord)}" to text "${escapeHtml(displayText)}"!</span>`;
    setTimeout(() => { toastIndicator.textContent = 'Ready to save.'; }, 3500);
}

function handleDragOverDropzone(e) {
    e.preventDefault();
    e.currentTarget.classList.add('bg-warning-subtle');
}
function handleDragLeaveDropzone(e) {
    e.currentTarget.classList.remove('bg-warning-subtle');
}
function handleDropOnDropzone(e) {
    e.preventDefault();
    e.currentTarget.classList.remove('bg-warning-subtle');
    try {
        const raw = e.dataTransfer.getData('application/json');
        if (raw) {
            const data = JSON.parse(raw);
            selectModalSign(data.word, data.video, data.desc, data.category);
            const dispInput = document.getElementById('linkFslDisplayText');
            if (!dispInput.value.trim() && data.displayText) {
                dispInput.value = data.displayText;
            }
        }
    } catch(err) {
        console.log('Drop error:', err);
    }
}

// Global Dragstart Listener for FSL Cards
document.addEventListener('dragstart', function(e) {
    const card = e.target.closest('.fsl-sign-card');
    if (!card) return;
    const word = card.dataset.word || '';
    const video = card.dataset.video || '';
    const desc = card.dataset.desc || '';
    const cat = card.dataset.cat || 'General';

    let displayText = word;
    if (desc && desc.includes('-')) {
        const parts = desc.split('-');
        if (parts[0].trim().length > 0) displayText = parts[0].trim();
    }

    const tagHtml = `&nbsp;<span class="fsl-word" data-word="${escapeHtml(word.toLowerCase())}" data-video="${escapeHtml(video)}" data-category="${escapeHtml(cat)}" data-desc="${escapeHtml(desc)}" title="FSL Sign: ${escapeHtml(word)}">${escapeHtml(displayText)}</span>&nbsp;`;

    e.dataTransfer.setData('text/html', tagHtml);
    e.dataTransfer.setData('text/plain', displayText);
    e.dataTransfer.setData('application/json', JSON.stringify({
        word: word,
        video: video,
        desc: desc,
        category: cat,
        displayText: displayText
    }));
});

// ── Preview FSL Sign Video Modal ──
function previewFslSign(word) {
    if (typeof window.openFSLModal === 'function') {
        window.openFSLModal(word);
    } else {
        alert('FSL Sign: ' + word);
    }
}

// ── Filter FSL Signs in Drawer ──
function filterFslSigns() {
    const search = (document.getElementById('fslSearchInput').value || '').toLowerCase().trim();
    const cards = document.querySelectorAll('#fslSignsContainer .fsl-sign-card');
    let visibleCount = 0;

    cards.forEach(card => {
        const word = (card.dataset.word || '').toLowerCase();
        const cat = (card.dataset.cat || '').toLowerCase();
        const desc = (card.dataset.desc || '').toLowerCase();

        const matchSearch = !search || word.includes(search) || desc.includes(search);
        const matchCat = (currentCategoryFilter === 'all') || cat === currentCategoryFilter.toLowerCase();

        if (matchSearch && matchCat) {
            card.style.display = 'block';
            visibleCount++;
        } else {
            card.style.display = 'none';
        }
    });

    document.getElementById('fslCountBadge').textContent = visibleCount + ' Signs';
}

function setFslCategory(cat, btn) {
    currentCategoryFilter = cat;
    document.querySelectorAll('.fsl-cat-filter').forEach(b => {
        b.classList.remove('active', 'btn-warning');
        b.classList.add('btn-outline-secondary');
    });
    btn.classList.remove('btn-outline-secondary');
    btn.classList.add('active', 'btn-warning');
    filterFslSigns();
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text || '';
    return div.innerHTML;
}

function escapeJs(str) {
    if (!str) return '';
    return String(str).replace(/\\/g, '\\\\').replace(/'/g, "\\'");
}

// ── Navigation Sidebar Auto-Collapse in Studio Mode ──
$(document).ready(function() {
    if (!document.body.classList.contains('sidebar-collapsed')) {
        document.body.classList.add('sidebar-collapsed');
    }
    const arrowIcon = document.getElementById('sidebarArrowIcon');
    if (arrowIcon) arrowIcon.className = 'ph-bold ph-caret-right';
    const arrowBtn = document.getElementById('sidebarCollapseArrow');
    if (arrowBtn) arrowBtn.setAttribute('title', 'Show Sidebar');
});

// ── Dynamic Workspace Column Expansion (Slide Deck & FSL Library Toggles) ──
let isSlideDeckVisible = true;
let isFslLibraryVisible = true;

function recalcEditorWidth() {
    const editorCol = document.getElementById('slideEditorCol');
    if (!editorCol) return;

    editorCol.classList.remove('col-lg-6', 'col-lg-9', 'col-lg-12');

    if (isSlideDeckVisible && isFslLibraryVisible) {
        editorCol.classList.add('col-lg-6');
    } else if (!isSlideDeckVisible && !isFslLibraryVisible) {
        editorCol.classList.add('col-lg-12');
    } else {
        editorCol.classList.add('col-lg-9');
    }
}

function toggleSlideDeck() {
    isSlideDeckVisible = !isSlideDeckVisible;
    const col = document.getElementById('slideDeckCol');
    const openBtn = document.getElementById('btnOpenSlideDeck');
    if (col) col.style.display = isSlideDeckVisible ? 'block' : 'none';
    if (openBtn) openBtn.style.display = isSlideDeckVisible ? 'none' : 'inline-flex';
    recalcEditorWidth();
}

function toggleFslLibrary() {
    isFslLibraryVisible = !isFslLibraryVisible;
    const col = document.getElementById('fslLibraryCol');
    const openBtn = document.getElementById('btnOpenFslLibrary');
    if (col) col.style.display = isFslLibraryVisible ? 'block' : 'none';
    if (openBtn) openBtn.style.display = isFslLibraryVisible ? 'none' : 'inline-flex';
    recalcEditorWidth();
}

// ── Teacher Custom FSL Sign Video Upload ──
function openAddCustomSignModal() {
    document.getElementById('customSignForm').reset();
    document.getElementById('uploadSignProgress').style.display = 'none';
    const modalEl = document.getElementById('addCustomSignModal');
    const modal = bootstrap.Modal.getOrCreateInstance(modalEl);
    modal.show();
}

function submitCustomFslSign() {
    const word = document.getElementById('customSignWord').value.trim();
    const category = document.getElementById('customSignCategory').value.trim() || 'Custom Signs';
    const desc = document.getElementById('customSignDesc').value.trim();
    const fileInput = document.getElementById('customSignVideoFile');

    if (!word) {
        alert('Please enter the sign word / vocabulary.');
        document.getElementById('customSignWord').focus();
        return;
    }

    if (!fileInput.files || fileInput.files.length === 0) {
        alert('Please select a video file for the sign.');
        return;
    }

    const btn = document.getElementById('btnSubmitCustomSign');
    btn.disabled = true;
    document.getElementById('uploadSignProgress').style.display = 'block';

    const fd = new FormData();
    fd.append('word', word);
    fd.append('category', category);
    fd.append('description', desc);
    fd.append('video_file', fileInput.files[0]);

    fetch(BASE + '/iep/implementation/fsl/upload-sign', {
        method: 'POST',
        body: fd
    })
    .then(r => r.json())
    .then(data => {
        btn.disabled = false;
        document.getElementById('uploadSignProgress').style.display = 'none';

        if (data.success && data.sign) {
            const modalEl = document.getElementById('addCustomSignModal');
            bootstrap.Modal.getOrCreateInstance(modalEl).hide();

            const sign = data.sign;
            const container = document.getElementById('fslSignsContainer');
            const cardHtml = `
                <div class="fsl-sign-card" data-word="${escapeHtml(sign.word.toLowerCase())}" data-cat="${escapeHtml(sign.category)}" data-video="${escapeHtml(sign.video_path)}" data-desc="${escapeHtml(sign.description || '')}" style="background: #f0fdf4; border-color: #22c55e;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <strong class="text-dark text-capitalize" style="font-size: 0.95rem;">
                            ${escapeHtml(sign.word.charAt(0).toUpperCase() + sign.word.slice(1))}
                        </strong>
                        <span class="fsl-sign-badge" style="background:#dcfce7; color:#15803d;">Teacher Added</span>
                    </div>
                    ${sign.description ? `<p class="small text-muted mb-2" style="font-size: 0.76rem; line-height: 1.3;">${escapeHtml(sign.description)}</p>` : ''}
                    <div class="d-flex justify-content-between align-items-center mt-2 pt-1 border-top">
                        <button type="button" class="btn btn-link btn-xs text-primary p-0 fw-semibold" onclick="previewFslSign('${escapeJs(sign.word)}')">
                            <i class="ph-bold ph-play-circle me-1"></i> Watch
                        </button>
                        <button type="button" class="btn btn-xs fsl-insert-btn" onclick="insertFslSignIntoSlide('${escapeJs(sign.word)}')">
                            <i class="ph-bold ph-plus me-1"></i> Insert to Slide
                        </button>
                    </div>
                </div>
            `;
            container.insertAdjacentHTML('afterbegin', cardHtml);

            // Update badge
            const total = document.querySelectorAll('#fslSignsContainer .fsl-sign-card').length;
            document.getElementById('fslCountBadge').textContent = total + ' Signs';

            alert('Successfully uploaded FSL video for "' + word + '"!');
        } else {
            alert('Failed to upload video: ' + (data.message || 'Error occurred'));
        }
    })
    .catch(err => {
        btn.disabled = false;
        document.getElementById('uploadSignProgress').style.display = 'none';
        console.error('Upload sign error:', err);
        alert('A network error occurred while uploading.');
    });
}

// ── Insert Sample Interactive Template (like ONE TEST sample) ──
function insertSampleInteractiveTemplate() {
    const currentContent = $('#slideContentEditor').summernote('code');
    if (currentContent && currentContent.trim() !== '<p><br></p>' && currentContent.trim() !== '') {
        if (!confirm('Gusto ba nimo i-load ang sample interactive template? Mapulihan ang karon nga text sa editor.')) {
            return;
        }
    }

    const titleInput = document.getElementById('slideTitleInput');
    if (!titleInput.value.trim()) {
        titleInput.value = 'Magandang Umaga sa Hapag-Kainan';
    }

    const sampleHtml = `
<div class="text-center mb-4">
    <span style="font-size: 3rem; display: block;">☀️ 🍞 🥛</span>
    <h3 class="fw-bold text-primary mt-2">Magandang Umaga sa Hapag-Kainan!</h3>
    <p class="text-muted small">Pindutin ang mga makukulay na pindutan para mapanood ang tunay na galaw ng kamay (FSL Video)!</p>
</div>

<div class="p-4 bg-light rounded-4 border shadow-sm mb-4 text-center">
    <p class="fs-5 lh-lg text-dark mb-3">
        Maagang nagising si Ana. Masaya siyang bumati ng:
    </p>
    <div class="my-3">
        <button type="button" class="btn btn-warning btn-lg px-4 py-2 rounded-pill shadow-sm fsl-word fw-bold text-dark" data-word="good morning">
            ☀️ Good Morning (Magandang Umaga)
        </button>
    </div>
    <p class="fs-5 lh-lg text-dark mb-0">
        Nakangiti ring sumagot ang kanyang 
        <button type="button" class="btn btn-outline-danger btn-sm rounded-pill fsl-word fw-bold mx-1" data-word="mother">👩 Nanay (Mother)</button> 
        at ang kanyang 
        <button type="button" class="btn btn-outline-primary btn-sm rounded-pill fsl-word fw-bold mx-1" data-word="father">👨 Tatay (Father)</button>.
    </p>
</div>

<div class="alert alert-primary border-0 rounded-4 d-flex align-items-center gap-3">
    <span style="font-size: 2rem;">💡</span>
    <div>
        <strong>Subukan Natin:</strong> Pindutin ang <em>"Good Morning"</em> upang panoorin kung paano bumati gamit ang Filipino Sign Language!
    </div>
</div>
`.trim();

    $('#slideContentEditor').summernote('code', sampleHtml);
    const toastIndicator = document.getElementById('saveStatusIndicator');
    if (toastIndicator) {
        toastIndicator.innerHTML = '<span class="text-success fw-bold"><i class="ph-bold ph-check-circle me-1"></i> Na-load na ang Interactive Sample Template! Pwede na nimo i-save o usbon ang mga pulong.</span>';
    }
}

// ── Insert Simple Shapes into Editor ──
let isInsertingShape = false;
function insertShapeIntoEditor(shapeType, event) {
    if (event) {
        event.preventDefault();
        event.stopPropagation();
    }
    if (isInsertingShape) return;
    isInsertingShape = true;
    setTimeout(() => { isInsertingShape = false; }, 350);

    let shapeHtml = '';

    switch (shapeType) {
        case 'box':
            shapeHtml = `
<div data-shape-box="true" class="shape-container p-3 my-2 rounded-4 shadow-sm text-center" style="background-color: #f8fafc; border: 2px solid #cbd5e1; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;">
    <p class="fs-5 text-dark mb-0 fw-semibold" style="margin: 0;">
        I-type dinhi ang text sa kahon...
    </p>
</div>
<p><br></p>
`;
            break;

        case 'box_wide':
            shapeHtml = `
<div data-shape-box="true" class="shape-container p-3 my-2 rounded-4 shadow-sm text-center" style="background-color: #f8fafc; border: 2px solid #cbd5e1; width: 100%; margin-left: auto; margin-right: auto; padding: 16px;">
    <p class="fs-5 text-dark mb-0 fw-semibold" style="margin: 0;">
        I-type dinhi ang text sa lapad nga kahon...
    </p>
</div>
<p><br></p>
`;
            break;

        case 'bubble':
            shapeHtml = `
<div data-shape-box="true" class="shape-container p-3 my-2 rounded-4 shadow-sm text-start" style="background-color: #f0fdf4; border: 2px solid #86efac; max-width: 540px; margin-left: auto; margin-right: auto; padding: 16px;">
    <div class="small fw-bold text-success mb-1">
        💬 Gisulti sa Kwento:
    </div>
    <p class="fs-5 text-dark mb-0" style="margin: 0;">
        "I-type dinhi ang gisulti sa bata o magtutudlo..."
    </p>
</div>
<p><br></p>
`;
            break;

        case 'pill':
            shapeHtml = `
<div data-shape-box="true" class="shape-container p-2.5 my-2 rounded-pill shadow-sm text-center" style="background-color: #fffbeb; border: 2.5px solid #f59e0b; max-width: 480px; margin-left: auto; margin-right: auto; padding: 12px 24px;">
    <span class="fs-5 fw-bold text-dark" style="display: inline-block;">
        ☀️ I-type dinhi ang pulong o pagtimbaya...
    </span>
</div>
<p><br></p>
`;
            break;

        case 'two_boxes':
            shapeHtml = `
<div class="row g-2.5 my-2" style="max-width: 600px; margin-left: auto; margin-right: auto;">
    <div class="col-6">
        <div data-shape-box="true" class="shape-container p-3 rounded-4 shadow-sm text-center h-100" style="background-color: #eff6ff; border: 2px solid #93c5fd; padding: 14px;">
            <strong class="text-primary d-block mb-1">Kahon 1</strong>
            <p class="text-dark mb-0" style="margin:0;">I-type dinhi ang pulong 1...</p>
        </div>
    </div>
    <div class="col-6">
        <div data-shape-box="true" class="shape-container p-3 rounded-4 shadow-sm text-center h-100" style="background-color: #f0fdf4; border: 2px solid #86efac; padding: 14px;">
            <strong class="text-success d-block mb-1">Kahon 2</strong>
            <p class="text-dark mb-0" style="margin:0;">I-type dinhi ang pulong 2...</p>
        </div>
    </div>
</div>
<p><br></p>
`;
            break;

        case 'note':
            shapeHtml = `
<div data-shape-box="true" class="shape-container p-3 my-2 rounded-4 d-flex align-items-center gap-3 shadow-sm text-start" style="background-color: #eff6ff; border: 2px solid #93c5fd; max-width: 560px; margin-left: auto; margin-right: auto; padding: 14px;">
    <span style="font-size: 1.8rem; line-height: 1;">💡</span>
    <div>
        <strong class="text-primary d-block" style="font-size: 0.95rem;">Hinumdumi / Note:</strong>
        <span class="text-dark">I-type dinhi ang panudlo para sa bata...</span>
    </div>
</div>
<p><br></p>
`;
            break;
    }

    if (shapeHtml) {
        $('#slideContentEditor').summernote('pasteHTML', shapeHtml.trim());
        const toastIndicator = document.getElementById('saveStatusIndicator');
        if (toastIndicator) {
            toastIndicator.innerHTML = '<span class="text-success fw-bold"><i class="ph-bold ph-check-circle me-1"></i> Na-insert ang Shape! Pwede na nimo i-type ang mga pulong sa sulod.</span>';
        }
    }
}

// ── Clear Editor Content ──
function clearEditorContent() {
    if (confirm('Sigurado ka nga gusto nimo papason ang sulod sa slide editor para makasugod ug limpyo?')) {
        $('#slideContentEditor').summernote('code', '<p><br></p>');
        const toast = document.getElementById('saveStatusIndicator');
        if (toast) toast.innerHTML = '<span class="text-muted">Gipapas ang sulod sa editor.</span>';
        hideShapeAdjustToolbar();
    }
}

// ── Live Shape Adjustment Helpers ──
function getActiveShapeOrFallback() {
    if (currentActiveShape && document.body.contains(currentActiveShape)) {
        return currentActiveShape;
    }
    // Try to find the focused or last shape inside Summernote
    const focused = document.querySelector('.note-editable .shape-active-focus');
    if (focused) return focused;
    return document.querySelector('.note-editable [data-shape-box="true"], .note-editable .shape-container, .note-editable [class*="shape-"], .note-editable .rounded-4');
}

function adjustActiveShapeWidth(widthVal) {
    const shape = getActiveShapeOrFallback();
    if (shape) {
        currentActiveShape = shape;
        shape.style.width = widthVal;
        if (widthVal === '100%') {
            shape.style.maxWidth = '100%';
        } else {
            shape.style.maxWidth = widthVal;
        }
        triggerAutoSave();
    }
}

function adjustActiveShapeTheme(theme) {
    const shape = getActiveShapeOrFallback();
    if (shape) {
        currentActiveShape = shape;
        if (theme === 'yellow') {
            shape.style.setProperty('background', '#fffbeb', 'important');
            shape.style.setProperty('background-color', '#fffbeb', 'important');
            shape.style.setProperty('border-color', '#f59e0b', 'important');
        } else if (theme === 'green') {
            shape.style.setProperty('background', '#f0fdf4', 'important');
            shape.style.setProperty('background-color', '#f0fdf4', 'important');
            shape.style.setProperty('border-color', '#86efac', 'important');
        } else if (theme === 'blue') {
            shape.style.setProperty('background', '#eff6ff', 'important');
            shape.style.setProperty('background-color', '#eff6ff', 'important');
            shape.style.setProperty('border-color', '#93c5fd', 'important');
        } else {
            shape.style.setProperty('background', '#f8fafc', 'important');
            shape.style.setProperty('background-color', '#f8fafc', 'important');
            shape.style.setProperty('border-color', '#cbd5e1', 'important');
        }
        triggerAutoSave();
    }
}

function deleteActiveShape() {
    const shape = getActiveShapeOrFallback();
    if (!shape) {
        alert('Palihug i-click una ang shape nga gusto nimong papason.');
        return;
    }
    if (confirm('Sigurado ka nga papason kini nga shape?')) {
        shape.remove();
        currentActiveShape = null;
        triggerAutoSave();
    }
}

function adjustActiveShapeAlign(align) {
    const shape = getActiveShapeOrFallback();
    if (shape) {
        currentActiveShape = shape;
        if (align === 'left') {
            shape.style.marginLeft = '0';
            shape.style.marginRight = 'auto';
            shape.style.textAlign = 'left';
        } else if (align === 'right') {
            shape.style.marginLeft = 'auto';
            shape.style.marginRight = '0';
            shape.style.textAlign = 'right';
        } else {
            // center
            shape.style.marginLeft = 'auto';
            shape.style.marginRight = 'auto';
            shape.style.textAlign = 'center';
        }
        triggerAutoSave();
    }
}

function moveActiveShape(direction) {
    const shape = getActiveShapeOrFallback();
    if (!shape) return;

    if (direction === 'up') {
        const prev = shape.previousElementSibling;
        if (prev) {
            shape.parentNode.insertBefore(shape, prev);
            triggerAutoSave();
        }
    } else if (direction === 'down') {
        const next = shape.nextElementSibling;
        if (next) {
            shape.parentNode.insertBefore(next, shape);
            triggerAutoSave();
        }
    }
}

function hideShapeAdjustToolbar() {
    if (currentActiveShape) {
        currentActiveShape.classList.remove('shape-active-focus');
    }
}

// ── Center Guide Indicator Toggle ──
function toggleCenterGuide() {
    const editor = document.querySelector('.note-editor');
    if (!editor) return;
    editor.classList.toggle('show-center-guide');
    const isOn = editor.classList.contains('show-center-guide');
    const stateSpan = document.getElementById('centerGuideState');
    const btn = document.getElementById('btnToggleCenterGuide');
    if (stateSpan) stateSpan.textContent = isOn ? 'ON' : 'OFF';
    if (btn) {
        if (isOn) {
            btn.classList.add('btn-primary', 'text-white');
            btn.classList.remove('btn-outline-primary');
        } else {
            btn.classList.remove('btn-primary', 'text-white');
            btn.classList.add('btn-outline-primary');
        }
    }
}



// ── Automatic FSL Sign Highlighting Engine ──
function autoHighlightFslSigns() {
    saveCurrentSelection();

    const rawHtml = $('#slideContentEditor').summernote('code');
    if (!rawHtml || rawHtml.trim() === '<p><br></p>' || !rawHtml.trim()) {
        alert('Palihug pag-type una ug text o pulong sa sulod sa slide editor.');
        return;
    }

    if (!ALL_FSL_SIGNS || ALL_FSL_SIGNS.length === 0) {
        alert('Walay FSL vocabulary nga nakit-an sa database.');
        return;
    }

    // Build vocabulary matching dictionary (supports English word and Tagalog description keywords)
    const signDict = [];
    ALL_FSL_SIGNS.forEach(s => {
        if (!s.word) return;
        const w = s.word.trim();
        signDict.push({
            matchWord: w,
            targetSign: s
        });

        // Check Tagalog keywords in description e.g. "Nanay - Babaeng magulang"
        if (s.description && s.description.includes('-')) {
            const tagWord = s.description.split('-')[0].trim();
            if (tagWord && tagWord.length > 2 && !signDict.some(x => x.matchWord.toLowerCase() === tagWord.toLowerCase())) {
                signDict.push({
                    matchWord: tagWord,
                    targetSign: s
                });
            }
        }
    });

    // Sort descending by word length so compound phrases match first (e.g. "anak na babae" before "anak")
    signDict.sort((a, b) => b.matchWord.length - a.matchWord.length);

    // Create DOM wrapper to manipulate safely without breaking tags/attributes
    const tempDiv = document.createElement('div');
    tempDiv.innerHTML = rawHtml;

    let matchCount = 0;

    function walk(node) {
        if (node.nodeType === Node.TEXT_NODE) {
            let text = node.nodeValue || '';
            if (!text || !text.trim()) return;

            // Normalize non-breaking spaces (\u00a0 / &nbsp;) from rich text editor
            const normalizedText = text.replace(/\u00A0/g, ' ');

            for (const item of signDict) {
                const searchWord = item.matchWord.trim();
                if (!searchWord) continue;

                const escaped = searchWord.replace(/[.*+?^${}()|[\]\\]/g, '\\$&').replace(/\s+/g, '[\\s\\u00A0]+');
                const regex = new RegExp('(^|[\\s\\u00A0,.!?;:\'"\\(\\)\\[\\]{}<>-])(' + escaped + ')(?=$|[\\s\\u00A0,.!?;:\'"\\(\\)\\[\\]{}<>-])', 'i');
                const match = regex.exec(normalizedText);

                if (match) {
                    const matchIndex = match.index + match[1].length;
                    const matchedStr = match[2];

                    const beforeText = text.substring(0, matchIndex);
                    const afterText = text.substring(matchIndex + matchedStr.length);

                    const span = document.createElement('span');
                    span.className = 'fsl-word';
                    span.dataset.word = item.targetSign.word.toLowerCase();
                    span.dataset.video = item.targetSign.video_path || '';
                    span.dataset.category = item.targetSign.category || 'General';
                    span.dataset.desc = item.targetSign.description || '';
                    span.title = 'FSL Sign: ' + item.targetSign.word;
                    span.textContent = matchedStr;

                    const parent = node.parentNode;
                    if (beforeText) {
                        parent.insertBefore(document.createTextNode(beforeText), node);
                    }
                    parent.insertBefore(span, node);

                    if (afterText) {
                        const nextNode = document.createTextNode(afterText);
                        parent.insertBefore(nextNode, node);
                        parent.removeChild(node);
                        walk(nextNode);
                    } else {
                        parent.removeChild(node);
                    }

                    matchCount++;
                    return;
                }
            }
        } else if (node.nodeType === Node.ELEMENT_NODE) {
            if (node.classList.contains('fsl-word') || (node.tagName === 'BUTTON' && node.classList.contains('fsl-word'))) {
                return;
            }
            if (node.tagName === 'SCRIPT' || node.tagName === 'STYLE') {
                return;
            }
            const children = Array.from(node.childNodes);
            for (const child of children) {
                walk(child);
            }
        }
    }

    walk(tempDiv);

    if (matchCount > 0) {
        $('#slideContentEditor').summernote('code', tempDiv.innerHTML);
        const toastIndicator = document.getElementById('saveStatusIndicator');
        if (toastIndicator) {
            toastIndicator.innerHTML = `<span class="text-success fw-bold"><i class="ph-bold ph-sparkle me-1"></i> Awtomatikong na-highlight ang ${matchCount} ka pulong nga naay FSL video sign!</span>`;
            setTimeout(() => { toastIndicator.textContent = 'Ready to save.'; }, 4000);
        }
    } else {
        alert('Walay bag-ong pulong nga nakit-an nga tugma sa FSL Library. Siguroa nga husto ang spelling sama sa mga pulong sa FSL Sign Library (pananglitan: "Alam ko", "Nanay", "Tatay", "Good morning", "Salamat", "Kumusta").');
    }
}
</script>

<!-- Modal: Upload Custom FSL Sign Video -->
<div class="modal fade" id="addCustomSignModal" tabindex="-1" aria-labelledby="addCustomSignModalLabel" aria-hidden="true" style="z-index: 1080;">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header px-4 py-3 text-white" style="background: linear-gradient(135deg, #1e4072 0%, #a01422 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="ph-bold ph-video-camera fs-5 text-warning"></i>
                    <h5 class="modal-title fw-bold mb-0 text-white" id="addCustomSignModalLabel">Upload Custom FSL Video</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="customSignForm" onsubmit="event.preventDefault(); submitCustomFslSign();">
                <div class="modal-body p-4 bg-light">
                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">
                            Sign Word / Vocabulary <span class="text-danger">*</span>
                        </label>
                        <input type="text" class="form-control form-control-sm" id="customSignWord" placeholder="e.g. Teacher, Wash Hands, Read, Thank You..." required style="border-radius: 8px;">
                        <div class="form-text small">This word will appear on the slide and be searchable in the library.</div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">Category</label>
                        <input type="text" class="form-control form-control-sm" id="customSignCategory" list="fslCatOptions" placeholder="e.g. School Terms, Daily Living, Greetings, Food..." style="border-radius: 8px;">
                        <datalist id="fslCatOptions">
                            <?php foreach ($fslCategories as $cat): ?>
                                <option value="<?php echo htmlspecialchars($cat); ?>">
                            <?php endforeach; ?>
                            <option value="Custom Signs">
                            <option value="Daily Living">
                            <option value="School Terms">
                        </datalist>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-bold small text-dark mb-1">Description / Instructions (optional)</label>
                        <textarea class="form-control form-control-sm" id="customSignDesc" rows="2" placeholder="e.g. Demonstration of the sign for this word or concept..." style="border-radius: 8px;"></textarea>
                    </div>

                    <div class="mb-3 p-3 bg-white rounded-3 border">
                        <label class="form-label fw-bold small text-dark mb-1">
                            <i class="ph-bold ph-video me-1 text-primary"></i> Sign Video File (MP4, WebM, MOV) <span class="text-danger">*</span>
                        </label>
                        <input type="file" class="form-control form-control-sm" id="customSignVideoFile" accept="video/mp4,video/webm,video/quicktime,video/mov" required style="border-radius: 8px;">
                        <div class="form-text small text-muted">You can upload videos recorded from a camera, phone, or screen.</div>
                    </div>

                    <div id="uploadSignProgress" class="alert alert-info py-2 px-3 small" style="display: none;">
                        <span class="spinner-border spinner-border-sm me-1"></span> Uploading video to FSL Library...
                    </div>
                </div>
                <div class="modal-footer px-4 py-3 bg-white border-top">
                    <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-sm btn-primary rounded-pill px-4 fw-bold" id="btnSubmitCustomSign">
                        <i class="ph-bold ph-upload-simple me-1"></i> Save to FSL Library
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal: Link FSL Video Sign to Text (English/Filipino Custom Text) -->
<div class="modal fade" id="linkFslSignModal" tabindex="-1" aria-labelledby="linkFslSignModalLabel" aria-hidden="true" style="z-index: 1085;">
    <div class="modal-dialog modal-dialog-centered modal-lg">
        <div class="modal-content border-0 shadow-lg rounded-4 overflow-hidden">
            <div class="modal-header px-4 py-3 text-white" style="background: linear-gradient(135deg, #1e4072 0%, #a01422 100%);">
                <div class="d-flex align-items-center gap-2">
                    <i class="ph-bold ph-hand-waving fs-5 text-warning"></i>
                    <h5 class="modal-title fw-bold mb-0 text-white" id="linkFslSignModalLabel">Link FSL Video Sign to Text</h5>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <div class="modal-body p-4 bg-light">
                <!-- Step 1: Display Text -->
                <div class="mb-3">
                    <label class="form-label fw-bold small text-dark mb-1">
                        Display Text on Slide <span class="text-danger">*</span>
                    </label>
                    <input type="text" class="form-control form-control-lg fw-bold text-primary" id="linkFslDisplayText" placeholder="e.g. Good Morning (or Magandang Umaga)" style="border-radius: 8px;">
                    <div class="form-text small text-muted">
                        This is what the student will read on the slide (e.g. <em>"Magandang Umaga"</em> or <em>"Good Morning"</em>). Tapping it will open the linked FSL video.
                    </div>
                </div>

                <!-- Dropzone / Selected Sign Display -->
                <div id="linkFslDropzone" class="p-3 text-center border border-2 border-dashed rounded-3 bg-white mb-3 text-muted transition-all" style="border-color: #cbd5e1; cursor: pointer;" ondragover="handleDragOverDropzone(event)" ondragleave="handleDragLeaveDropzone(event)" ondrop="handleDropOnDropzone(event)">
                    <div id="dropzoneEmptyState">
                        <i class="ph-bold ph-hand-pointing fs-3 text-warning mb-1 d-block"></i>
                        <span class="fw-semibold text-dark">Drag & drop a sign here from the library</span>
                        <div class="small text-muted">or select from the search list below</div>
                    </div>
                    <div id="dropzoneSelectedState" style="display: none;" class="d-flex align-items-center justify-content-between text-start p-2 bg-warning-subtle rounded-3 border border-warning">
                        <div>
                            <span class="badge bg-warning text-dark me-2" id="linkedSignCategory">Greeting</span>
                            <strong class="text-dark" id="linkedSignWord" style="font-size: 1.05rem;">Good Morning</strong>
                            <div class="small text-muted mt-0.5" id="linkedSignDesc">Good Morning - Morning greeting</div>
                        </div>
                        <button type="button" class="btn btn-sm btn-outline-dark rounded-pill px-3 fw-bold" onclick="previewCurrentModalSign()">
                            <i class="ph-bold ph-play-circle me-1 text-primary"></i> Watch
                        </button>
                    </div>
                </div>

                <!-- Search & Filter FSL Signs -->
                <div class="mb-2">
                    <label class="form-label fw-bold small text-dark mb-1">
                        Select an FSL Video Sign from the Library
                    </label>
                    <div class="input-group input-group-sm mb-2">
                        <span class="input-group-text bg-white border-end-0" style="border-radius: 9999px 0 0 9999px;"><i class="ph-bold ph-magnifying-glass text-muted"></i></span>
                        <input type="text" class="form-control border-start-0" id="linkFslSearchInput" placeholder="Search sign vocabulary (e.g. morning, father, eat, thank you)..." oninput="filterModalSignList()" style="border-radius: 0 9999px 9999px 0;">
                    </div>
                </div>

                <!-- Quick Pick Scrollable List -->
                <div class="bg-white rounded-3 border p-2 overflow-auto" id="linkFslPickerList" style="max-height: 220px;">
                    <!-- Rendered by JS -->
                </div>
            </div>
            <div class="modal-footer px-4 py-3 bg-white border-top justify-content-between">
                <button type="button" class="btn btn-sm btn-outline-secondary rounded-pill px-3" data-bs-dismiss="modal">Cancel</button>
                <button type="button" class="btn btn-sm btn-warning rounded-pill px-4 fw-bold shadow-sm" id="btnApplyFslSignLink" onclick="applyFslSignLink()">
                    <i class="ph-bold ph-check me-1"></i> Link &amp; Insert into Slide
                </button>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>


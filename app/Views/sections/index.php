<?php
// Part of: SignED — Stage 2 Section Management View
// Last modified: 2026-08-21
$pageTitle = 'Section Management — SignED';
require_once __DIR__ . '/../layouts/header.php';
?>
<style>
    .page-header-card {
        background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
        color: #ffffff;
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    .btn-modern {
        height: 38px;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 6px;
        padding: 0.375rem 0.875rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .section-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        transition: transform 0.2s ease, box-shadow 0.2s ease;
    }
    .section-card:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(0,0,0,0.06);
    }
    .progress-slim {
        height: 6px;
        border-radius: 3px;
    }
</style>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid p-0">

                <!-- Page Header -->
                <div class="page-header-card d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-grid-3x3-gap-fill text-warning me-2"></i>SPED Section Management</h4>
                        <p class="text-white-50 mb-0 small">Create sections, configure classroom max capacity, and monitor round-robin enrollee distribution.</p>
                    </div>

                    <div>
                        <button type="button" class="btn btn-primary btn-modern shadow-sm" data-bs-toggle="modal" data-bs-target="#createSectionModal">
                            <i class="bi bi-plus-circle-fill"></i> Create New Section
                        </button>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Round-Robin Info Banner -->
                <div class="alert alert-info border-0 shadow-sm mb-4 d-flex align-items-center gap-3">
                    <i class="bi bi-arrow-repeat fs-3 text-info"></i>
                    <div>
                        <strong>Automatic Round-Robin Distribution Active:</strong>
                        <div class="small text-muted">When teachers approve new enrollees, the system automatically assigns learners across active sections in rotational order (Section 1 ➔ Section 2 ➔ Section 3...), ensuring balanced class sizes and respecting max capacity limits.</div>
                    </div>
                </div>

                <!-- Sections Grid -->
                <div class="row g-3 mb-4">
                    <?php if (empty($sections)): ?>
                        <div class="col-12">
                            <div class="card border-0 shadow-sm text-center py-5">
                                <div class="card-body">
                                    <i class="bi bi-inbox display-4 text-secondary mb-3 d-block"></i>
                                    <h5 class="fw-bold">No Sections Created Yet</h5>
                                    <p class="text-muted small">Click the "Create New Section" button above to establish your school's SPED sections.</p>
                                </div>
                            </div>
                        </div>
                    <?php else: ?>
                        <?php foreach ($sections as $s): ?>
                            <?php 
                                $enrolled = (int)($s['enrolled_students_count'] ?? $s['current_count']);
                                $maxCap = max(1, (int)$s['max_capacity']);
                                $percent = min(100, round(($enrolled / $maxCap) * 100));
                                $isFull = $enrolled >= $maxCap;
                            ?>
                            <div class="col-md-6 col-lg-4">
                                <div class="card section-card bg-white h-100 shadow-sm">
                                    <div class="card-body p-3 d-flex flex-column">
                                        <div class="d-flex align-items-start justify-content-between mb-2">
                                            <div>
                                                <span class="badge bg-primary-subtle text-primary fw-semibold mb-1" style="font-size: 0.75rem;">
                                                    <?= htmlspecialchars($s['grade_level']) ?>
                                                </span>
                                                <h5 class="fw-bold mb-0 text-dark"><?= htmlspecialchars($s['section_name']) ?></h5>
                                            </div>
                                            <?php if ($isFull): ?>
                                                <span class="badge bg-danger">Full Capacity</span>
                                            <?php else: ?>
                                                <span class="badge bg-success-subtle text-success border border-success-subtle">
                                                    <?= ($maxCap - $enrolled) ?> slots left
                                                </span>
                                            <?php endif; ?>
                                        </div>

                                        <div class="small text-muted mb-3">
                                            <div><i class="bi bi-person-badge me-1"></i> Adviser: <strong><?= htmlspecialchars($s['adviser_name'] ?? 'Not Assigned') ?></strong></div>
                                            <?php if (!empty($s['room_number'])): ?>
                                                <div><i class="bi bi-door-open me-1"></i> Room: <?= htmlspecialchars($s['room_number']) ?></div>
                                            <?php endif; ?>
                                        </div>

                                        <div class="mt-auto pt-2 border-top">
                                            <div class="d-flex justify-content-between small text-muted mb-1">
                                                <span>Enrolled: <strong><?= $enrolled ?></strong> / <?= $maxCap ?> learners</span>
                                                <span><?= $percent ?>%</span>
                                            </div>
                                            <div class="progress progress-slim mb-3">
                                                <div class="progress-bar <?= $isFull ? 'bg-danger' : ($percent > 75 ? 'bg-warning' : 'bg-primary') ?>" 
                                                     style="width: <?= $percent ?>%"></div>
                                            </div>

                                            <div class="d-flex justify-content-end gap-2">
                                                <button type="button" class="btn btn-outline-secondary btn-sm" 
                                                        data-bs-toggle="modal" data-bs-target="#editModal<?= $s['id'] ?>">
                                                    <i class="bi bi-pencil"></i> Edit
                                                </button>
                                                <form method="POST" action="<?= $basePath ?>/sections/delete/<?= $s['id'] ?>" 
                                                      onsubmit="return confirm('Are you sure you want to archive this section?');" class="d-inline">
                                                    <button type="submit" class="btn btn-outline-danger btn-sm">
                                                        <i class="bi bi-archive"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Edit Modal for Section -->
                            <div class="modal fade" id="editModal<?= $s['id'] ?>" tabindex="-1">
                                <div class="modal-dialog">
                                    <div class="modal-content">
                                        <form method="POST" action="<?= $basePath ?>/sections/update/<?= $s['id'] ?>">
                                            <div class="modal-header bg-light">
                                                <h6 class="modal-title fw-bold"><i class="bi bi-pencil-square me-2"></i>Edit Section: <?= htmlspecialchars($s['section_name']) ?></h6>
                                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                            </div>
                                            <div class="modal-body">
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Section Name <span class="text-danger">*</span></label>
                                                    <input type="text" name="section_name" class="form-control" value="<?= htmlspecialchars($s['section_name']) ?>" required>
                                                </div>
                                                <div class="row g-2 mb-3">
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Grade Level</label>
                                                        <input type="text" name="grade_level" class="form-control" value="<?= htmlspecialchars($s['grade_level']) ?>">
                                                    </div>
                                                    <div class="col-md-6">
                                                        <label class="form-label fw-semibold">Room Number</label>
                                                        <input type="text" name="room_number" class="form-control" value="<?= htmlspecialchars($s['room_number'] ?? '') ?>">
                                                    </div>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Max Capacity (Learners) <span class="text-danger">*</span></label>
                                                    <input type="number" name="max_capacity" class="form-control" value="<?= $maxCap ?>" min="1" max="50" required>
                                                    <small class="text-muted">Round-robin will not assign more than this limit.</small>
                                                </div>
                                                <div class="mb-3">
                                                    <label class="form-label fw-semibold">Assigned Adviser Teacher</label>
                                                    <select name="adviser_teacher_id" class="form-select">
                                                        <option value="">-- Unassigned --</option>
                                                        <?php foreach ($teachers as $t): ?>
                                                            <option value="<?= $t['id'] ?>" <?= ((int)($s['adviser_teacher_id'] ?? 0) === (int)$t['id']) ? 'selected' : '' ?>>
                                                                <?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['email']) ?>)
                                                            </option>
                                                        <?php endforeach; ?>
                                                    </select>
                                                </div>
                                            </div>
                                            <div class="modal-footer bg-light">
                                                <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                                                <button type="submit" class="btn btn-primary btn-sm">Save Changes</button>
                                            </div>
                                        </form>
                                    </div>
                                </div>
                            </div>

                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>

    </div> <!-- End .container-fluid -->
</div> <!-- End .main-content -->


    <!-- Create Section Modal -->
    <div class="modal fade" id="createSectionModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content">
                <form method="POST" action="<?= $basePath ?>/sections/store">
                    <div class="modal-header bg-primary text-white">
                        <h6 class="modal-title fw-bold"><i class="bi bi-plus-circle me-2"></i>Create New SPED Section</h6>
                        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Section Name <span class="text-danger">*</span></label>
                            <input type="text" name="section_name" class="form-control" placeholder="e.g. Grade 1 - Rosal" required>
                        </div>
                        <div class="row g-2 mb-3">
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Grade Level</label>
                                <input type="text" name="grade_level" class="form-control" value="SPED Non-Graded" placeholder="e.g. SPED Grade 1">
                            </div>
                            <div class="col-md-6">
                                <label class="form-label fw-semibold">Room Number</label>
                                <input type="text" name="room_number" class="form-control" placeholder="e.g. Room 102">
                            </div>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Max Capacity (Learners) <span class="text-danger">*</span></label>
                            <input type="number" name="max_capacity" class="form-control" value="15" min="1" max="50" required>
                            <small class="text-muted">Standard SPED recommended ratio: 10-15 learners per section.</small>
                        </div>
                        <div class="mb-3">
                            <label class="form-label fw-semibold">Assigned Adviser Teacher</label>
                            <select name="adviser_teacher_id" class="form-select">
                                <option value="">-- Select SPED Teacher --</option>
                                <?php foreach ($teachers as $t): ?>
                                    <option value="<?= $t['id'] ?>">
                                        <?= htmlspecialchars($t['name']) ?> (<?= htmlspecialchars($t['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Cancel</button>
                        <button type="submit" class="btn btn-primary btn-sm">Create Section</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>


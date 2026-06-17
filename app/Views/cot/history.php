<?php
$pageTitle = 'Classroom Observations History - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Classroom Observations History</h1>
        <?php if ($role === 'master_teacher'): ?>
            <a href="<?= $basePath ?>/cot/observations/schedule" class="btn text-white fw-semibold" style="background-color: #a01422;">
                <i class="bi bi-calendar-plus"></i> Schedule Observation
            </a>
        <?php endif; ?>
    </div>

    <!-- Filtering Panel -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?= $basePath ?>/cot/observations" class="row g-3 align-items-end">
                <!-- School Year -->
                <div class="col-md-3">
                    <label for="school_year" class="form-label small text-muted">School Year</label>
                    <select class="form-select" id="school_year" name="school_year">
                        <option value="">-- All School Years --</option>
                        <?php foreach ($schoolYears as $sy): ?>
                            <option value="<?= htmlspecialchars($sy) ?>" <?= ($_GET['school_year'] ?? '') === $sy ? 'selected' : '' ?>>
                                <?= htmlspecialchars($sy) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Quarter -->
                <div class="col-md-3">
                    <label for="quarter" class="form-label small text-muted">Quarter</label>
                    <select class="form-select" id="quarter" name="quarter">
                        <option value="">-- All Quarters --</option>
                        <option value="1st Quarter" <?= ($_GET['quarter'] ?? '') === '1st Quarter' ? 'selected' : '' ?>>1st Quarter</option>
                        <option value="2nd Quarter" <?= ($_GET['quarter'] ?? '') === '2nd Quarter' ? 'selected' : '' ?>>2nd Quarter</option>
                        <option value="3rd Quarter" <?= ($_GET['quarter'] ?? '') === '3rd Quarter' ? 'selected' : '' ?>>3rd Quarter</option>
                        <option value="4th Quarter" <?= ($_GET['quarter'] ?? '') === '4th Quarter' ? 'selected' : '' ?>>4th Quarter</option>
                    </select>
                </div>

                <!-- Observed Teacher -->
                <div class="col-md-3">
                    <label for="observed_teacher_id" class="form-label small text-muted">Observed Teacher</label>
                    <select class="form-select" id="observed_teacher_id" name="observed_teacher_id">
                        <option value="">-- All Teachers --</option>
                        <?php foreach ($teachers as $teacher): ?>
                            <option value="<?= $teacher['id'] ?>" <?= ($_GET['observed_teacher_id'] ?? '') == $teacher['id'] ? 'selected' : '' ?>>
                                <?= htmlspecialchars($teacher['name']) ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Actions -->
                <div class="col-md-3 d-flex gap-2">
                    <button type="submit" class="btn text-white w-100 fw-semibold" style="background-color: #1e4072;">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                    <a href="<?= $basePath ?>/cot/observations" class="btn btn-outline-secondary w-50">
                        Reset
                    </a>
                </div>
            </form>
        </div>
    </div>

    <!-- Observations List -->
    <div class="card border-0 shadow-sm">
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th class="ps-4">Observed Teacher</th>
                            <th>Subject &amp; Grade Level</th>
                            <th>School Year &amp; Quarter</th>
                            <th>Observation Round</th>
                            <th>Scheduled Date</th>
                            <th>Status</th>
                            <th>Avg Rating</th>
                            <th class="pe-4 text-end">Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($observations)): ?>
                            <?php foreach ($observations as $obs): 
                                $avg = $obs['average_score'];
                            ?>
                                <tr>
                                    <td class="ps-4">
                                        <strong><?= htmlspecialchars($obs['observed_teacher_name']) ?></strong>
                                        <?php if ($role === 'admin'): ?>
                                            <div class="text-muted small">Observed by: <?= htmlspecialchars($obs['observer_name']) ?></div>
                                        <?php endif; ?>
                                    </td>
                                    <td><?= htmlspecialchars($obs['subject_grade_level']) ?></td>
                                    <td>
                                        <div><?= htmlspecialchars($obs['school_year']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($obs['quarter']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= $obs['observation_number'] == 1 ? '1st Round' : '2nd Round' ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div><?= date('Y-m-d', strtotime($obs['scheduled_at'])) ?></div>
                                        <small class="text-muted"><?= date('g:i A', strtotime($obs['scheduled_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($obs['status'] === 'scheduled'): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis px-2.5 py-1">Scheduled</span>
                                        <?php elseif ($obs['status'] === 'in_progress'): ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis px-2.5 py-1">In Progress</span>
                                        <?php elseif ($obs['status'] === 'finalized'): ?>
                                            <span class="badge bg-success-subtle text-success-emphasis px-2.5 py-1">Finalized</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <?php if ($obs['status'] === 'finalized' && $avg !== null): ?>
                                            <strong class="text-navy fs-6"><?= number_format($avg, 2) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted small">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <?php if ($obs['status'] !== 'finalized'): ?>
                                            <?php if ($role === 'master_teacher' && $obs['observer_id'] === $userId): ?>
                                                <a href="<?= $basePath ?>/cot/observations/<?= $obs['id'] ?>/rate" 
                                                   class="btn btn-sm text-white px-3 fw-semibold" style="background-color: #a01422;">
                                                    <i class="bi bi-clipboard-pulse"></i> Rate Live
                                                </a>
                                            <?php else: ?>
                                                <span class="text-muted small">Incomplete</span>
                                            <?php endif; ?>
                                        <?php else: ?>
                                            <a href="<?= $basePath ?>/cot/observations/<?= $obs['id'] ?>/view" 
                                               class="btn btn-sm btn-outline-secondary px-3">
                                                <i class="bi bi-eye"></i> View Result
                                            </a>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="8" class="text-center py-5 text-muted">
                                    <i class="bi bi-journal-x fs-1 d-block mb-2"></i>
                                    No classroom observation records found matching filters.
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

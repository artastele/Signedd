<?php
$pageTitle = 'My Classroom Observations - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="mb-1">My Classroom Observations</h1>
            <p class="text-muted mb-0">View your observation schedule and sign off on completed results.</p>
        </div>
    </div>

    <?php if (!empty($success)): ?>
        <div class="alert alert-success alert-dismissible fade show">
            <?= htmlspecialchars($success) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>
    <?php if (!empty($error)): ?>
        <div class="alert alert-danger alert-dismissible fade show">
            <?= htmlspecialchars($error) ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Filters -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <form method="get" action="<?= $basePath ?>/cot/observations" class="row g-3 align-items-end">
                <div class="col-md-4">
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
                <div class="col-md-4">
                    <label for="quarter" class="form-label small text-muted">Quarter</label>
                    <select class="form-select" id="quarter" name="quarter">
                        <option value="">-- All Quarters --</option>
                        <?php foreach (['1st Quarter', '2nd Quarter', '3rd Quarter', '4th Quarter'] as $q): ?>
                            <option value="<?= $q ?>" <?= ($_GET['quarter'] ?? '') === $q ? 'selected' : '' ?>><?= $q ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-4 d-flex gap-2">
                    <button type="submit" class="btn text-white w-100 fw-semibold" style="background-color: #1e4072;">
                        <i class="bi bi-filter"></i> Apply Filters
                    </button>
                    <a href="<?= $basePath ?>/cot/observations" class="btn btn-outline-secondary">Reset</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Pending signature (priority) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header text-white py-3" style="background-color: #a01422;">
            <h5 class="mb-0"><i class="bi bi-pencil-square me-2"></i>Results Ready for Your Signature</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($pendingSignoff)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Subject &amp; Grade Level</th>
                                <th>School Year &amp; Quarter</th>
                                <th>Observer</th>
                                <th>Scheduled Date</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pendingSignoff as $obs): ?>
                                <tr>
                                    <td class="ps-4"><?= htmlspecialchars($obs['subject_grade_level']) ?></td>
                                    <td>
                                        <div><?= htmlspecialchars($obs['school_year']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($obs['quarter']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($obs['observer_name']) ?></td>
                                    <td>
                                        <div><?= date('M d, Y', strtotime($obs['scheduled_at'])) ?></div>
                                        <small class="text-muted"><?= date('g:i A', strtotime($obs['scheduled_at'])) ?></small>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="<?= $basePath ?>/cot/observations/<?= $obs['id'] ?>/sign-off"
                                           class="btn btn-sm text-white fw-semibold px-3" style="background-color: #a01422;">
                                            <i class="bi bi-pencil-square"></i> Review &amp; Sign
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-check-circle fs-3 d-block mb-2"></i>
                    No observation results waiting for your signature.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Upcoming schedule -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header text-white py-3" style="background-color: #1e4072;">
            <h5 class="mb-0"><i class="bi bi-calendar-event me-2"></i>My Observation Schedule</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($scheduledObservations)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Subject &amp; Grade Level</th>
                                <th>School Year &amp; Quarter</th>
                                <th>Round</th>
                                <th>Observer</th>
                                <th>Scheduled Date</th>
                                <th>Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($scheduledObservations as $obs): ?>
                                <tr>
                                    <td class="ps-4"><?= htmlspecialchars($obs['subject_grade_level']) ?></td>
                                    <td>
                                        <div><?= htmlspecialchars($obs['school_year']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($obs['quarter']) ?></small>
                                    </td>
                                    <td>
                                        <span class="badge bg-light text-dark border">
                                            <?= $obs['observation_number'] == 1 ? '1st Round' : '2nd Round' ?>
                                        </span>
                                    </td>
                                    <td><?= htmlspecialchars($obs['observer_name']) ?></td>
                                    <td>
                                        <div><?= date('M d, Y', strtotime($obs['scheduled_at'])) ?></div>
                                        <small class="text-muted"><?= date('g:i A', strtotime($obs['scheduled_at'])) ?></small>
                                    </td>
                                    <td>
                                        <?php if ($obs['status'] === 'scheduled'): ?>
                                            <span class="badge bg-info-subtle text-info-emphasis">Scheduled</span>
                                        <?php else: ?>
                                            <span class="badge bg-warning-subtle text-warning-emphasis">In Progress</span>
                                        <?php endif; ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-calendar-x fs-3 d-block mb-2"></i>
                    No upcoming observations scheduled.
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Signed / finalized results -->
    <div class="card border-0 shadow-sm">
        <div class="card-header bg-light py-3">
            <h5 class="mb-0 text-secondary"><i class="bi bi-file-earmark-check me-2"></i>Signed Results Summary</h5>
        </div>
        <div class="card-body p-0">
            <?php if (!empty($finalizedObservations)): ?>
                <div class="table-responsive">
                    <table class="table table-hover align-middle mb-0">
                        <thead class="table-light">
                            <tr>
                                <th class="ps-4">Subject &amp; Grade Level</th>
                                <th>School Year &amp; Quarter</th>
                                <th>Observer</th>
                                <th>Avg Rating</th>
                                <th class="pe-4 text-end">Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($finalizedObservations as $obs): ?>
                                <tr>
                                    <td class="ps-4"><?= htmlspecialchars($obs['subject_grade_level']) ?></td>
                                    <td>
                                        <div><?= htmlspecialchars($obs['school_year']) ?></div>
                                        <small class="text-muted"><?= htmlspecialchars($obs['quarter']) ?></small>
                                    </td>
                                    <td><?= htmlspecialchars($obs['observer_name']) ?></td>
                                    <td>
                                        <?php if ($obs['average_score'] !== null): ?>
                                            <strong><?= number_format((float)$obs['average_score'], 2) ?></strong>
                                        <?php else: ?>
                                            <span class="text-muted">—</span>
                                        <?php endif; ?>
                                    </td>
                                    <td class="pe-4 text-end">
                                        <a href="<?= $basePath ?>/cot/observations/<?= $obs['id'] ?>/view"
                                           class="btn btn-sm btn-outline-secondary px-3">
                                            <i class="bi bi-eye"></i> View Summary
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php else: ?>
                <div class="text-center py-4 text-muted">
                    <i class="bi bi-journal-x fs-3 d-block mb-2"></i>
                    No signed observation results yet.
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

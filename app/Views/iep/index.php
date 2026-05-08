<?php
// DO NOT ALTER WITHOUT APPROVAL -- Process 5
// Last modified: 2026-05-08
// Part of: SPED LMS -- IEP Repository (list all IEPs)

$pageTitle = 'IEP Repository - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
$role     = $_SESSION['role'];
$basePath = BASE_PATH;
$statusColors = ['draft'=>'#6c757d','signing'=>'#ffc107','signed'=>'#3b6d11','locked'=>'#a01422'];
?>
<body data-logged-in="true">
<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-2">
        <div>
            <h1 class="mb-1" style="color:#1e4072;">
                <i class="bi bi-archive me-2"></i>IEP Repository
            </h1>
            <p class="text-muted mb-0">Individualized Education Plans</p>
        </div>
        <?php if (in_array($role, ['sped_teacher','admin']) && !empty($eligibleStudents)): ?>
        <div class="dropdown">
            <button class="btn dropdown-toggle" style="background:#a01422;color:white;"
                    data-bs-toggle="dropdown">
                <i class="bi bi-plus-lg me-1"></i>New IEP
            </button>
            <ul class="dropdown-menu dropdown-menu-end shadow">
                <?php foreach ($eligibleStudents as $s): ?>
                <li>
                    <a class="dropdown-item" href="<?php echo $basePath; ?>/iep/create?student_id=<?php echo $s['id']; ?>">
                        <strong><?php echo htmlspecialchars($s['student_name']); ?></strong>
                        <small class="text-muted d-block">LRN: <?php echo htmlspecialchars($s['lrn']); ?></small>
                    </a>
                </li>
                <?php endforeach; ?>
            </ul>
        </div>
        <?php endif; ?>
    </div>

    <?php if (!empty($_SESSION['success'])): ?>
    <div class="alert alert-success"><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
    <?php endif; ?>
    <?php if (!empty($_SESSION['error'])): ?>
    <div class="alert alert-danger"><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
    <?php endif; ?>

    <!-- Filters -->
    <form method="GET" class="row g-2 mb-4">
        <div class="col-auto">
            <select name="school_year" class="form-select form-select-sm" style="min-width:140px;">
                <option value="">All School Years</option>
                <?php
                $years = array_unique(array_column($ieps ?? [], 'school_year'));
                foreach ($years as $y): ?>
                <option value="<?php echo htmlspecialchars($y); ?>"
                        <?php echo ($filterYear ?? '') === $y ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($y); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <select name="status" class="form-select form-select-sm">
                <option value="">All Statuses</option>
                <?php foreach (['draft','signing','signed','locked'] as $st): ?>
                <option value="<?php echo $st; ?>" <?php echo ($filterStatus ?? '') === $st ? 'selected' : ''; ?>>
                    <?php echo ucfirst($st); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="col-auto">
            <button type="submit" class="btn btn-sm" style="background:#1e4072;color:white;">
                <i class="bi bi-funnel me-1"></i>Filter
            </button>
            <a href="<?php echo $basePath; ?>/iep" class="btn btn-sm btn-outline-secondary ms-1">Clear</a>
        </div>
    </form>

    <!-- IEP Table -->
    <div class="card">
        <div class="card-header" style="background:#1e4072;color:white;">
            <h5 class="mb-0"><i class="bi bi-table me-2"></i>
                IEPs (<?php echo count($ieps ?? []); ?>)
            </h5>
        </div>
        <div class="card-body p-0">
            <?php if (empty($ieps)): ?>
            <div class="text-center py-5 text-muted">
                <i class="bi bi-inbox" style="font-size:2.5rem;"></i>
                <p class="mt-2">No IEPs found.</p>
                <?php if (in_array($role, ['sped_teacher','admin'])): ?>
                <p class="text-muted small">Students with a signed PDSP will appear in the "New IEP" dropdown above.</p>
                <?php endif; ?>
            </div>
            <?php else: ?>
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background:#1e4072;color:white;">
                        <tr>
                            <th>Student</th>
                            <th>School Year</th>
                            <th>Status</th>
                            <th>Re-evaluation Date</th>
                            <th>Drafted By</th>
                            <th>Created</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($ieps as $iep): ?>
                    <tr>
                        <td>
                            <strong><?php echo htmlspecialchars($iep['student_name']); ?></strong><br>
                            <small class="text-muted">LRN: <?php echo htmlspecialchars($iep['lrn']); ?></small>
                        </td>
                        <td><?php echo htmlspecialchars($iep['school_year']); ?></td>
                        <td>
                            <span class="badge" style="background:<?php echo $statusColors[$iep['status']] ?? '#6c757d'; ?>;">
                                <?php echo ucfirst($iep['status']); ?>
                            </span>
                            <?php if (!empty($iep['re_evaluation_date']) && strtotime($iep['re_evaluation_date']) < time() && in_array($iep['status'],['signed','locked'])): ?>
                            <span class="badge ms-1" style="background:#a01422;">Re-eval Passed</span>
                            <?php endif; ?>
                        </td>
                        <td>
                            <?php echo !empty($iep['re_evaluation_date'])
                                ? date('M d, Y', strtotime($iep['re_evaluation_date']))
                                : '<span class="text-muted">Not set</span>'; ?>
                        </td>
                        <td><?php echo htmlspecialchars($iep['drafted_by_name']); ?></td>
                        <td><?php echo date('M d, Y', strtotime($iep['created_at'])); ?></td>
                        <td>
                            <div class="d-flex gap-1 flex-wrap">
                                <a href="<?php echo $basePath; ?>/iep/form/<?php echo $iep['id']; ?>"
                                   class="btn btn-sm" style="background:#1e4072;color:white;">
                                    <i class="bi bi-eye me-1"></i>View
                                </a>
                                <?php if (in_array($iep['status'],['signed','locked']) && $role !== 'parent'): ?>
                                <a href="<?php echo $basePath; ?>/iep/form/<?php echo $iep['id']; ?>"
                                   class="btn btn-sm btn-outline-secondary" onclick="window.print()">
                                    <i class="bi bi-download me-1"></i>Print
                                </a>
                                <?php endif; ?>
                                <?php if (in_array($iep['status'],['signed','locked']) && in_array($role,['sped_teacher','admin'])): ?>
                                <form method="POST" action="<?php echo $basePath; ?>/iep/new-cycle" class="d-inline"
                                      onsubmit="return confirm('Start a new IEP cycle? The current IEP will be preserved.')">
                                    <input type="hidden" name="iep_id" value="<?php echo $iep['id']; ?>">
                                    <button type="submit" class="btn btn-sm btn-outline-danger">
                                        <i class="bi bi-arrow-repeat me-1"></i>New Cycle
                                    </button>
                                </form>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

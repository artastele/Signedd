<?php
$pageTitle = 'Login Attempt Logs - SPED LMS';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-shield-lock"></i> Login Attempt Logs
    </h1>

    <!-- Statistics Cards -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="card" style="border-left: 4px solid #1e4072;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Total Attempts (24h)</h6>
                    <h3 class="mb-0"><?php echo number_format($stats['total']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="border-left: 4px solid #3b6d11;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Successful Logins (24h)</h6>
                    <h3 class="mb-0 text-success"><?php echo number_format($stats['successful']); ?></h3>
                </div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="card" style="border-left: 4px solid #a01422;">
                <div class="card-body">
                    <h6 class="text-muted mb-2">Failed Attempts (24h)</h6>
                    <h3 class="mb-0 text-danger"><?php echo number_format($stats['failed']); ?></h3>
                </div>
            </div>
        </div>
    </div>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo $basePath; ?>/admin/login-logs" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Result</label>
                    <select name="result" class="form-select">
                        <option value="">All Results</option>
                        <option value="success" <?php echo ($_GET['result'] ?? '') === 'success' ? 'selected' : ''; ?>>Success</option>
                        <option value="failure" <?php echo ($_GET['result'] ?? '') === 'failure' ? 'selected' : ''; ?>>Failure</option>
                    </select>
                </div>
                    <select name="status" class="form-select">
                        <option value="all" <?php echo ($status ?? 'all') === 'all' ? 'selected' : ''; ?>>All</option>
                        <option value="success" <?php echo ($status ?? '') === 'success' ? 'selected' : ''; ?>>Success</option>
                        <option value="failure" <?php echo ($status ?? '') === 'failure' ? 'selected' : ''; ?>>Failure</option>
                    </select>
                </div>
                <div class="col-md-3">
                    <label class="form-label">Limit</label>
                    <select name="limit" class="form-select">
                        <option value="50" <?php echo ($limit ?? 50) == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo ($limit ?? 50) == 100 ? 'selected' : ''; ?>>100</option>
                        <option value="200" <?php echo ($limit ?? 50) == 200 ? 'selected' : ''; ?>>200</option>
                        <option value="500" <?php echo ($limit ?? 50) == 500 ? 'selected' : ''; ?>>500</option>
                    </select>
                </div>
                <div class="col-md-4">
                    <label class="form-label">Search Email</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by email..." value="<?php echo htmlspecialchars($search ?? ''); ?>">
                </div>
                <div class="col-md-2 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="bi bi-funnel"></i> Filter
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Logs Table -->
    <div class="card">
        <div class="card-body">
            <div class="table-responsive">
                <table class="table table-hover">
                    <thead style="background-color: #1e4072; color: white;">
                        <tr>
                            <th>ID</th>
                            <th>Email</th>
                            <th>Status</th>
                            <th>IP Address</th>
                            <th>User Agent</th>
                            <th>Attempted At</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">No login attempts found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id']; ?></td>
                                    <td><?php echo htmlspecialchars($log['email']); ?></td>
                                    <td>
                                        <?php if ($log['status'] === 'success'): ?>
                                            <span class="badge bg-success">Success</span>
                                        <?php else: ?>
                                            <span class="badge bg-danger">Failure</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></code>
                                    </td>
                                    <td>
                                        <small class="text-muted" title="<?php echo htmlspecialchars($log['user_agent'] ?? 'N/A'); ?>">
                                            <?php echo htmlspecialchars(substr($log['user_agent'] ?? 'N/A', 0, 50)); ?>...
                                        </small>
                                    </td>
                                    <td>
                                        <small><?php echo date('M j, Y g:i A', strtotime($log['attempted_at'])); ?></small>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

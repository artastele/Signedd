<?php
$pageTitle = 'Activity Logs - SPED LMS';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-activity"></i> Activity Logs
    </h1>

    <!-- Filters -->
    <div class="card mb-4">
        <div class="card-body">
            <form method="GET" action="<?php echo $basePath; ?>/admin/activity-logs" class="row g-3">
                <div class="col-md-3">
                    <label class="form-label">Action Type</label>
                    <select name="action_type" class="form-select">
                        <option value="all" <?php echo ($_GET['action_type'] ?? 'all') === 'all' ? 'selected' : ''; ?>>All Actions</option>
                        <?php foreach ($actions as $act): ?>
                            <option value="<?php echo htmlspecialchars($act); ?>" <?php echo ($_GET['action_type'] ?? '') === $act ? 'selected' : ''; ?>>
                                <?php echo ucwords(str_replace('_', ' ', $act)); ?>
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-md-2">
                    <label class="form-label">Limit</label>
                    <select name="limit" class="form-select">
                        <option value="50" <?php echo ($_GET['limit'] ?? 50) == 50 ? 'selected' : ''; ?>>50</option>
                        <option value="100" <?php echo ($_GET['limit'] ?? 50) == 100 ? 'selected' : ''; ?>>100</option>
                        <option value="200" <?php echo ($_GET['limit'] ?? 50) == 200 ? 'selected' : ''; ?>>200</option>
                        <option value="500" <?php echo ($_GET['limit'] ?? 50) == 500 ? 'selected' : ''; ?>>500</option>
                    </select>
                </div>
                <div class="col-md-5">
                    <label class="form-label">Search</label>
                    <input type="text" name="search" class="form-control" placeholder="Search by user, email, or details..." value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
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
                            <th>User</th>
                            <th>Action</th>
                            <th>Description</th>
                            <th>IP Address</th>
                            <th>Date & Time</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($logs)): ?>
                            <tr>
                                <td colspan="6" class="text-center text-muted py-4">
                                    <i class="bi bi-inbox" style="font-size: 2rem;"></i>
                                    <p class="mb-0 mt-2">No activity logs found</p>
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($logs as $log): ?>
                                <tr>
                                    <td><?php echo $log['id']; ?></td>
                                    <td>
                                        <div>
                                            <strong><?php echo htmlspecialchars($log['user_name'] ?? 'Unknown'); ?></strong>
                                        </div>
                                        <small class="text-muted"><?php echo htmlspecialchars($log['user_email'] ?? 'N/A'); ?></small>
                                    </td>
                                    <td>
                                        <span class="badge" style="background-color: #1e4072;">
                                            <?php echo ucwords(str_replace('_', ' ', $log['action'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <small><?php echo htmlspecialchars($log['description']); ?></small>
                                    </td>
                                    <td>
                                        <code><?php echo htmlspecialchars($log['ip_address'] ?? 'N/A'); ?></code>
                                    </td>
                                    <td>
                                        <small><?php echo date('M j, Y g:i A', strtotime($log['created_at'])); ?></small>
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

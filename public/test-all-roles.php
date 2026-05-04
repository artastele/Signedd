<?php
// TEST SCRIPT: All Roles Dashboard & Session Timeout Test
// This script tests if all roles can access their dashboards and session timeout works

// Define BASE_PATH first
$scriptName = dirname($_SERVER['SCRIPT_NAME']);
$basePath = $scriptName === '/' ? '' : $scriptName;
define('BASE_PATH', $basePath);

// Start session
require_once __DIR__ . '/../app/Middleware/SessionMiddleware.php';
SessionMiddleware::start();

require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>All Roles Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #a01422; }
        h2 { color: #1e4072; border-bottom: 2px solid #1e4072; padding-bottom: 10px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #1e4072; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; color: white; }
        .badge-general { background: #6c757d; }
        .badge-parent { background: #17a2b8; }
        .badge-sped_teacher { background: #28a745; }
        .badge-guidance { background: #ffc107; color: #000; }
        .badge-principal { background: #007bff; }
        .badge-master_teacher { background: #6f42c1; }
        .badge-admin { background: #dc3545; }
        .badge-active { background: #28a745; }
        .badge-inactive { background: #6c757d; }
        button { background: #a01422; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #8a1120; }
        .test-result { padding: 10px; margin: 5px 0; border-radius: 4px; }
        .test-pass { background: #d4edda; border-left: 4px solid #28a745; }
        .test-fail { background: #f8d7da; border-left: 4px solid #dc3545; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🧪 All Roles Dashboard & Session Test</h1>
        <p><strong>Date:</strong> <?php echo date('F j, Y g:i A'); ?></p>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="warning">
                ⚠️ You are not logged in. Please <a href="<?php echo BASE_PATH; ?>/login">login</a> first.
            </div>
        <?php else: ?>
            
            <h2>1. Current Session</h2>
            <table>
                <tr>
                    <th>Property</th>
                    <th>Value</th>
                </tr>
                <tr>
                    <td><strong>User ID</strong></td>
                    <td><?php echo $_SESSION['user_id']; ?></td>
                </tr>
                <tr>
                    <td><strong>Name</strong></td>
                    <td><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td><strong>Email</strong></td>
                    <td><?php echo htmlspecialchars($_SESSION['user_email'] ?? 'N/A'); ?></td>
                </tr>
                <tr>
                    <td><strong>Current Role</strong></td>
                    <td>
                        <span class="badge badge-<?php echo $_SESSION['role']; ?>">
                            <?php echo strtoupper(str_replace('_', ' ', $_SESSION['role'])); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Email Verified</strong></td>
                    <td>
                        <?php if (isset($_SESSION['email_verified']) && $_SESSION['email_verified']): ?>
                            <span class="badge badge-active">YES</span>
                        <?php else: ?>
                            <span class="badge badge-inactive">NO</span>
                        <?php endif; ?>
                    </td>
                </tr>
            </table>

        <?php endif; ?>

        <h2>2. All Users by Role</h2>
        <?php
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->query("
                SELECT role, COUNT(*) as count
                FROM users
                GROUP BY role
                ORDER BY 
                    CASE role
                        WHEN 'admin' THEN 1
                        WHEN 'principal' THEN 2
                        WHEN 'guidance' THEN 3
                        WHEN 'sped_teacher' THEN 4
                        WHEN 'master_teacher' THEN 5
                        WHEN 'parent' THEN 6
                        ELSE 7
                    END
            ");
            $roleCounts = $stmt->fetchAll();
        ?>
            <table>
                <tr>
                    <th>Role</th>
                    <th>Count</th>
                    <th>Dashboard File</th>
                    <th>Status</th>
                </tr>
                <?php
                $dashboardFiles = [
                    'admin' => 'admin.php',
                    'principal' => 'principal.php',
                    'guidance' => 'guidance.php',
                    'sped_teacher' => 'teacher.php',
                    'master_teacher' => 'master_teacher.php',
                    'parent' => 'parent.php',
                    'general' => 'general.php'
                ];
                
                foreach ($roleCounts as $roleCount):
                    $role = $roleCount['role'];
                    $dashboardFile = $dashboardFiles[$role] ?? 'general.php';
                    $filePath = __DIR__ . '/../app/Views/dashboard/' . $dashboardFile;
                    $fileExists = file_exists($filePath);
                ?>
                <tr>
                    <td>
                        <span class="badge badge-<?php echo $role; ?>">
                            <?php echo strtoupper(str_replace('_', ' ', $role)); ?>
                        </span>
                    </td>
                    <td><?php echo $roleCount['count']; ?> users</td>
                    <td><code><?php echo $dashboardFile; ?></code></td>
                    <td>
                        <?php if ($fileExists): ?>
                            <span class="badge badge-active">✓ EXISTS</span>
                        <?php else: ?>
                            <span class="badge badge-inactive">✗ MISSING</span>
                        <?php endif; ?>
                    </td>
                </tr>
                <?php endforeach; ?>
            </table>
        <?php
        } catch (Exception $e) {
            echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
        }
        ?>

        <h2>3. Dashboard Access Test</h2>
        <div class="info">
            <strong>Test:</strong> Can you access the dashboard for your current role?
        </div>
        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="test-result test-pass">
                ✅ <strong>Session Active</strong> - You can test dashboard access<br>
                <a href="<?php echo BASE_PATH; ?>/dashboard"><button>Go to Dashboard</button></a>
            </div>
        <?php else: ?>
            <div class="test-result test-fail">
                ❌ <strong>Not Logged In</strong> - Please login first
            </div>
        <?php endif; ?>

        <h2>4. Session Timeout Test</h2>
        <div class="info">
            <strong>How it works:</strong>
            <ol>
                <li>Session timeout is set to 15 minutes</li>
                <li>When timeout occurs, you should be redirected to: <code><?php echo BASE_PATH; ?>/login?timeout=1</code></li>
                <li>You should see a yellow alert: "Your session has expired. Please log in again."</li>
            </ol>
        </div>

        <?php if (isset($_SESSION['user_id'])): ?>
            <div class="test-result test-pass">
                ✅ <strong>Session Active</strong><br>
                Last Activity: <?php echo isset($_SESSION['last_activity']) ? date('g:i:s A', $_SESSION['last_activity']) : 'Not set'; ?><br>
                Time Remaining: <?php 
                    $remaining = SessionMiddleware::getTimeRemaining();
                    $minutes = floor($remaining / 60);
                    $seconds = $remaining % 60;
                    echo "{$minutes} minutes, {$seconds} seconds";
                ?>
            </div>
            
            <form method="POST" action="">
                <button type="submit" name="expire_session">Expire Session Now (Test Redirect)</button>
            </form>

            <?php
            if (isset($_POST['expire_session'])) {
                // Set last activity to 16 minutes ago (past timeout)
                $_SESSION['last_activity'] = time() - 960; // 16 minutes
                echo '<div class="success">✅ Session expired! <a href="' . BASE_PATH . '/dashboard">Click here</a> to test redirect.</div>';
            }
            ?>
        <?php else: ?>
            <div class="test-result test-fail">
                ❌ <strong>Not Logged In</strong> - Cannot test session timeout
            </div>
        <?php endif; ?>

        <h2>5. Role-Specific Routes Test</h2>
        <table>
            <tr>
                <th>Role</th>
                <th>Key Routes</th>
                <th>Test</th>
            </tr>
            <tr>
                <td><span class="badge badge-admin">ADMIN</span></td>
                <td>
                    /admin<br>
                    /admin/users<br>
                    /admin/role-requests
                </td>
                <td>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'admin'): ?>
                        <a href="<?php echo BASE_PATH; ?>/admin"><button>Test Admin</button></a>
                    <?php else: ?>
                        <span class="text-muted">Login as admin</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><span class="badge badge-principal">PRINCIPAL</span></td>
                <td>
                    /principal/staff-requests
                </td>
                <td>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'principal'): ?>
                        <a href="<?php echo BASE_PATH; ?>/principal/staff-requests"><button>Test Principal</button></a>
                    <?php else: ?>
                        <span class="text-muted">Login as principal</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><span class="badge badge-sped_teacher">SPED TEACHER</span></td>
                <td>
                    /enrollment/review<br>
                    /verification
                </td>
                <td>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'sped_teacher'): ?>
                        <a href="<?php echo BASE_PATH; ?>/enrollment/review"><button>Test SPED Teacher</button></a>
                    <?php else: ?>
                        <span class="text-muted">Login as SPED teacher</span>
                    <?php endif; ?>
                </td>
            </tr>
            <tr>
                <td><span class="badge badge-parent">PARENT</span></td>
                <td>
                    /enrollment<br>
                    /enrollment/status
                </td>
                <td>
                    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'parent'): ?>
                        <a href="<?php echo BASE_PATH; ?>/enrollment"><button>Test Parent</button></a>
                    <?php else: ?>
                        <span class="text-muted">Login as parent</span>
                    <?php endif; ?>
                </td>
            </tr>
        </table>

        <h2>6. Quick Actions</h2>
        <a href="<?php echo BASE_PATH; ?>/dashboard"><button>Dashboard</button></a>
        <a href="<?php echo BASE_PATH; ?>/login"><button>Login Page</button></a>
        <a href="<?php echo BASE_PATH; ?>/logout"><button>Logout</button></a>
        <button onclick="location.reload()">Refresh</button>

        <hr>
        <p><small>Test Script: test-all-roles.php | <a href="test-session-timeout.php">Session Timeout Test</a> | <a href="test-role-update.php">Role Update Test</a></small></p>
    </div>
</body>
</html>

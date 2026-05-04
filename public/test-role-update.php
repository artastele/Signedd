<?php
// TEST SCRIPT: Role Update Detection
// This script helps test if role updates are detected automatically

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
    <title>Role Update Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #a01422; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 10px; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #1e4072; color: white; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-general { background: #6c757d; color: white; }
        .badge-parent { background: #17a2b8; color: white; }
        .badge-sped_teacher { background: #28a745; color: white; }
        .badge-guidance { background: #ffc107; color: #000; }
        .badge-principal { background: #007bff; color: white; }
        .badge-admin { background: #dc3545; color: white; }
        button { background: #a01422; color: white; border: none; padding: 10px 20px; border-radius: 4px; cursor: pointer; margin: 5px; }
        button:hover { background: #8a1120; }
    </style>
    <script>
        // Auto-refresh every 5 seconds to check for role updates
        let countdown = 5;
        function startCountdown() {
            const countdownEl = document.getElementById('countdown');
            setInterval(() => {
                countdown--;
                if (countdown <= 0) {
                    location.reload();
                } else {
                    countdownEl.textContent = countdown;
                }
            }, 1000);
        }
        window.onload = startCountdown;
    </script>
</head>
<body>
    <div class="container">
        <h1>🔄 Role Update Detection Test</h1>
        <p><strong>Date:</strong> <?php echo date('F j, Y g:i A'); ?></p>

        <?php if (!isset($_SESSION['user_id'])): ?>
            <div class="warning">
                ⚠️ You are not logged in. Please <a href="<?php echo BASE_PATH; ?>/login">login</a> first.
            </div>
        <?php else: ?>
            
            <h2>1. Current Session Information</h2>
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
                    <td><strong>Session Role</strong></td>
                    <td>
                        <span class="badge badge-<?php echo $_SESSION['role']; ?>">
                            <?php echo strtoupper(str_replace('_', ' ', $_SESSION['role'])); ?>
                        </span>
                    </td>
                </tr>
                <tr>
                    <td><strong>Last Role Check</strong></td>
                    <td>
                        <?php 
                        if (isset($_SESSION['last_role_check'])) {
                            echo date('g:i:s A', $_SESSION['last_role_check']) . ' (' . (time() - $_SESSION['last_role_check']) . ' seconds ago)';
                        } else {
                            echo 'Not checked yet';
                        }
                        ?>
                    </td>
                </tr>
            </table>

            <h2>2. Database Role Information</h2>
            <?php
            try {
                $db = Database::getInstance()->getConnection();
                $stmt = $db->prepare("SELECT id, name, email, role FROM users WHERE id = :id LIMIT 1");
                $stmt->execute(['id' => $_SESSION['user_id']]);
                $user = $stmt->fetch();
                
                if ($user):
            ?>
                <table>
                    <tr>
                        <th>Property</th>
                        <th>Value</th>
                    </tr>
                    <tr>
                        <td><strong>Name</strong></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Email</strong></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                    </tr>
                    <tr>
                        <td><strong>Database Role</strong></td>
                        <td>
                            <span class="badge badge-<?php echo $user['role']; ?>">
                                <?php echo strtoupper(str_replace('_', ' ', $user['role'])); ?>
                            </span>
                        </td>
                    </tr>
                </table>

                <h2>3. Role Sync Status</h2>
                <?php if ($user['role'] === $_SESSION['role']): ?>
                    <div class="success">
                        ✅ <strong>Roles are in sync!</strong><br>
                        Session role matches database role.
                    </div>
                <?php else: ?>
                    <div class="warning">
                        ⚠️ <strong>Roles are OUT OF SYNC!</strong><br>
                        Session role: <strong><?php echo $_SESSION['role']; ?></strong><br>
                        Database role: <strong><?php echo $user['role']; ?></strong><br>
                        <br>
                        The system will automatically detect this and redirect you to the dashboard within 30 seconds.
                    </div>
                <?php endif; ?>

            <?php
                endif;
            } catch (Exception $e) {
                echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>

            <h2>4. Pending Role Requests</h2>
            <?php
            try {
                $stmt = $db->prepare("
                    SELECT rr.*, u.name as user_name, u.email as user_email
                    FROM role_requests rr
                    JOIN users u ON rr.user_id = u.id
                    WHERE rr.user_id = :user_id
                    ORDER BY rr.created_at DESC
                    LIMIT 5
                ");
                $stmt->execute(['user_id' => $_SESSION['user_id']]);
                $requests = $stmt->fetchAll();
                
                if (empty($requests)):
            ?>
                <div class="info">ℹ️ No role requests found.</div>
            <?php else: ?>
                <table>
                    <tr>
                        <th>Requested Role</th>
                        <th>Status</th>
                        <th>Submitted</th>
                        <th>Reviewed</th>
                    </tr>
                    <?php foreach ($requests as $req): ?>
                    <tr>
                        <td><?php echo ucwords(str_replace('_', ' ', $req['requested_role'])); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $req['status']; ?>">
                                <?php echo strtoupper($req['status']); ?>
                            </span>
                        </td>
                        <td><?php echo date('M j, Y g:i A', strtotime($req['created_at'])); ?></td>
                        <td><?php echo $req['reviewed_at'] ? date('M j, Y g:i A', strtotime($req['reviewed_at'])) : 'Not yet'; ?></td>
                    </tr>
                    <?php endforeach; ?>
                </table>
            <?php endif; ?>
            <?php
            } catch (Exception $e) {
                echo '<div class="error">❌ Error: ' . htmlspecialchars($e->getMessage()) . '</div>';
            }
            ?>

            <h2>5. How It Works</h2>
            <div class="info">
                <strong>Automatic Role Detection:</strong>
                <ol>
                    <li>Every 30 seconds, the system checks if your database role matches your session role</li>
                    <li>If they don't match (e.g., admin approved your SPED teacher request), the system:
                        <ul>
                            <li>Updates your session role automatically</li>
                            <li>Redirects you to the dashboard</li>
                            <li>Shows a success message</li>
                        </ul>
                    </li>
                    <li>You don't need to logout and login again!</li>
                </ol>
            </div>

            <h2>6. Auto-Refresh</h2>
            <div class="info">
                ℹ️ This page will auto-refresh in <strong id="countdown">5</strong> seconds to check for role updates.
            </div>

            <h2>7. Quick Actions</h2>
            <a href="<?php echo BASE_PATH; ?>/dashboard"><button>Go to Dashboard</button></a>
            <a href="<?php echo BASE_PATH; ?>/role/select"><button>Request New Role</button></a>
            <button onclick="location.reload()">Refresh Now</button>

        <?php endif; ?>

        <hr>
        <p><small>Test Script: test-role-update.php</small></p>
    </div>
</body>
</html>

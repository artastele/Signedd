<?php
// Debug enrollment submission
// Shows what data is being sent and what's in the database

session_start();
require_once __DIR__ . '/../config/db.php';

if (!isset($_SESSION['user_id'])) {
    die("Please log in first");
}

$userId = $_SESSION['user_id'];
?>
<!DOCTYPE html>
<html>
<head>
    <title>Enrollment Debug - SPED LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
        .section { margin: 20px 0; }
        .alert { margin: 10px 0; }
    </style>
</head>
<body>
<div class="container mt-4">
    <h1>Enrollment Debug Information</h1>
    <hr>
    
    <!-- SESSION DATA -->
    <div class="section">
        <h3>1. Session Data</h3>
        <pre><?php
        if (isset($_SESSION['error'])) {
            echo "❌ ERROR: " . htmlspecialchars($_SESSION['error']) . "\n\n";
        }
        if (isset($_SESSION['debug_data'])) {
            echo "📦 Debug Data:\n";
            var_export($_SESSION['debug_data']);
        } else {
            echo "No debug data stored\n";
        }
        ?></pre>
    </div>
    
    <!-- DATABASE CHECK -->
    <div class="section">
        <h3>2. Database Table Structure</h3>
        <?php
        try {
            $pdo = Database::getInstance()->getConnection();
            $result = $pdo->query("DESC enrollment_submissions");
            $columns = $result->fetchAll(PDO::FETCH_ASSOC);
            
            echo "<table class='table table-sm table-bordered'>";
            echo "<thead><tr><th>Column</th><th>Type</th><th>Null</th><th>Key</th></tr></thead>";
            echo "<tbody>";
            
            foreach ($columns as $col) {
                echo "<tr>";
                echo "<td><strong>" . $col['Field'] . "</strong></td>";
                echo "<td>" . $col['Type'] . "</td>";
                echo "<td>" . $col['Null'] . "</td>";
                echo "<td>" . $col['Key'] . "</td>";
                echo "</tr>";
            }
            echo "</tbody>";
            echo "</table>";
            
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Database error: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
    
    <!-- RECENT ENROLLMENTS -->
    <div class="section">
        <h3>3. Your Recent Enrollment Records</h3>
        <?php
        try {
            $pdo = Database::getInstance()->getConnection();
            $stmt = $pdo->prepare("SELECT id, last_name, first_name, birth_place, status, created_at FROM enrollment_submissions WHERE parent_id = ? ORDER BY created_at DESC LIMIT 5");
            $stmt->execute([$userId]);
            $enrollments = $stmt->fetchAll(PDO::FETCH_ASSOC);
            
            if (empty($enrollments)) {
                echo "<div class='alert alert-info'>No enrollments found for your account</div>";
            } else {
                echo "<table class='table table-sm table-bordered'>";
                echo "<thead><tr><th>ID</th><th>Name</th><th>Birth Place</th><th>Status</th><th>Created</th></tr></thead>";
                echo "<tbody>";
                
                foreach ($enrollments as $enr) {
                    echo "<tr>";
                    echo "<td>" . $enr['id'] . "</td>";
                    echo "<td>" . htmlspecialchars($enr['first_name'] . " " . $enr['last_name']) . "</td>";
                    echo "<td>" . htmlspecialchars($enr['birth_place'] ?? 'NULL') . "</td>";
                    echo "<td><span class='badge bg-info'>" . $enr['status'] . "</span></td>";
                    echo "<td>" . $enr['created_at'] . "</td>";
                    echo "</tr>";
                }
                echo "</tbody>";
                echo "</table>";
            }
        } catch (Exception $e) {
            echo "<div class='alert alert-danger'>Error fetching enrollments: " . $e->getMessage() . "</div>";
        }
        ?>
    </div>
    
    <!-- PHP ERRORS LOG -->
    <div class="section">
        <h3>4. Check PHP Error Log</h3>
        <p class="alert alert-warning">Last few errors from error_log:</p>
        <pre><?php
        $logFile = __DIR__ . '/../../logs/php_errors.log';
        if (file_exists($logFile)) {
            $lines = file($logFile);
            $recent = array_slice($lines, -20); // Last 20 lines
            echo htmlspecialchars(implode('', $recent));
        } else {
            echo "No PHP error log found at $logFile\n";
            echo "Check phpinfo() or php.ini for error_log location";
        }
        ?></pre>
    </div>
    
    <!-- BACK -->
    <div class="section">
        <a href="<?php echo isset($basePath) ? $basePath : ''; ?>/enrollment/create?type=new" class="btn btn-primary">
            ← Back to Enrollment Form
        </a>
    </div>
</div>
</body>
</html>

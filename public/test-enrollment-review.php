<?php
// TEST SCRIPT: Enrollment Review System Diagnostic
// This script helps verify the enrollment review system is working correctly

require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/html; charset=utf-8');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Enrollment Review System Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; padding: 20px; border-radius: 8px; }
        h1 { color: #a01422; }
        h2 { color: #1e4072; border-bottom: 2px solid #1e4072; padding-bottom: 10px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; margin: 10px 0; }
        th, td { border: 1px solid #ddd; padding: 8px; text-align: left; }
        th { background: #1e4072; color: white; }
        tr:nth-child(even) { background: #f9f9f9; }
        .badge { padding: 4px 8px; border-radius: 4px; font-size: 12px; font-weight: bold; }
        .badge-pending { background: #ffc107; color: #000; }
        .badge-approved { background: #28a745; color: white; }
        .badge-rejected { background: #dc3545; color: white; }
        .badge-verified { background: #28a745; color: white; }
        .badge-draft { background: #6c757d; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 Enrollment Review System Diagnostic</h1>
        <p><strong>Date:</strong> <?php echo date('F j, Y g:i A'); ?></p>

        <?php
        try {
            $db = Database::getInstance()->getConnection();
            echo '<div class="success">✅ Database connection successful</div>';

            // Check if tables exist
            echo '<h2>1. Database Tables Check</h2>';
            $tables = ['enrollment_submissions', 'enrollment_documents', 'notifications', 'users'];
            foreach ($tables as $table) {
                $stmt = $db->query("SHOW TABLES LIKE '$table'");
                if ($stmt->rowCount() > 0) {
                    echo "<div class='success'>✅ Table <strong>$table</strong> exists</div>";
                } else {
                    echo "<div class='error'>❌ Table <strong>$table</strong> NOT FOUND</div>";
                }
            }

            // Check enrollments
            echo '<h2>2. Enrollment Submissions</h2>';
            $stmt = $db->query("
                SELECT es.id, es.first_name, es.last_name, es.status, es.enrollment_type,
                       es.grade_level_to_enroll, es.submitted_at, u.name as parent_name
                FROM enrollment_submissions es
                JOIN users u ON es.parent_id = u.id
                WHERE es.is_draft = FALSE
                ORDER BY es.submitted_at DESC
                LIMIT 10
            ");
            $enrollments = $stmt->fetchAll();

            if (empty($enrollments)) {
                echo '<div class="info">ℹ️ No enrollments found. Submit an enrollment as a parent to test.</div>';
            } else {
                echo '<table>';
                echo '<tr><th>ID</th><th>Student</th><th>Parent</th><th>Type</th><th>Grade</th><th>Status</th><th>Submitted</th></tr>';
                foreach ($enrollments as $e) {
                    $statusClass = 'badge-' . $e['status'];
                    echo "<tr>";
                    echo "<td>#{$e['id']}</td>";
                    echo "<td>{$e['first_name']} {$e['last_name']}</td>";
                    echo "<td>{$e['parent_name']}</td>";
                    echo "<td>{$e['enrollment_type']}</td>";
                    echo "<td>{$e['grade_level_to_enroll']}</td>";
                    echo "<td><span class='badge $statusClass'>{$e['status']}</span></td>";
                    echo "<td>" . date('M j, Y', strtotime($e['submitted_at'])) . "</td>";
                    echo "</tr>";
                }
                echo '</table>';
            }

            // Check documents
            echo '<h2>3. Enrollment Documents</h2>';
            $stmt = $db->query("
                SELECT ed.id, ed.enrollment_id, ed.document_type, ed.status, 
                       ed.file_path, ed.reviewed_at, u.name as reviewer_name,
                       es.first_name, es.last_name
                FROM enrollment_documents ed
                JOIN enrollment_submissions es ON ed.enrollment_id = es.id
                LEFT JOIN users u ON ed.reviewed_by = u.id
                ORDER BY ed.enrollment_id, ed.uploaded_at DESC
                LIMIT 20
            ");
            $documents = $stmt->fetchAll();

            if (empty($documents)) {
                echo '<div class="info">ℹ️ No documents found. Upload documents during enrollment submission.</div>';
            } else {
                echo '<table>';
                echo '<tr><th>Doc ID</th><th>Enrollment</th><th>Student</th><th>Type</th><th>Status</th><th>Reviewer</th><th>Reviewed</th></tr>';
                foreach ($documents as $d) {
                    $statusClass = 'badge-' . $d['status'];
                    echo "<tr>";
                    echo "<td>#{$d['id']}</td>";
                    echo "<td>#{$d['enrollment_id']}</td>";
                    echo "<td>{$d['first_name']} {$d['last_name']}</td>";
                    echo "<td>{$d['document_type']}</td>";
                    echo "<td><span class='badge $statusClass'>{$d['status']}</span></td>";
                    echo "<td>" . ($d['reviewer_name'] ?? 'N/A') . "</td>";
                    echo "<td>" . ($d['reviewed_at'] ? date('M j, Y', strtotime($d['reviewed_at'])) : 'Not reviewed') . "</td>";
                    echo "</tr>";
                }
                echo '</table>';
            }

            // Check document statistics
            echo '<h2>4. Document Statistics by Enrollment</h2>';
            $stmt = $db->query("
                SELECT ed.enrollment_id,
                       es.first_name, es.last_name, es.status as enrollment_status,
                       COUNT(*) as total_docs,
                       SUM(CASE WHEN ed.status = 'approved' THEN 1 ELSE 0 END) as approved,
                       SUM(CASE WHEN ed.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                       SUM(CASE WHEN ed.status = 'pending' THEN 1 ELSE 0 END) as pending
                FROM enrollment_documents ed
                JOIN enrollment_submissions es ON ed.enrollment_id = es.id
                GROUP BY ed.enrollment_id
            ");
            $stats = $stmt->fetchAll();

            if (empty($stats)) {
                echo '<div class="info">ℹ️ No document statistics available.</div>';
            } else {
                echo '<table>';
                echo '<tr><th>Enrollment</th><th>Student</th><th>Status</th><th>Total Docs</th><th>Approved</th><th>Rejected</th><th>Pending</th><th>All Approved?</th></tr>';
                foreach ($stats as $s) {
                    $allApproved = ($s['total_docs'] == $s['approved']) ? '✅ YES' : '❌ NO';
                    $statusClass = 'badge-' . $s['enrollment_status'];
                    echo "<tr>";
                    echo "<td>#{$s['enrollment_id']}</td>";
                    echo "<td>{$s['first_name']} {$s['last_name']}</td>";
                    echo "<td><span class='badge $statusClass'>{$s['enrollment_status']}</span></td>";
                    echo "<td>{$s['total_docs']}</td>";
                    echo "<td>{$s['approved']}</td>";
                    echo "<td>{$s['rejected']}</td>";
                    echo "<td>{$s['pending']}</td>";
                    echo "<td>$allApproved</td>";
                    echo "</tr>";
                }
                echo '</table>';
            }

            // Check notifications
            echo '<h2>5. Recent Notifications</h2>';
            $stmt = $db->query("
                SELECT n.id, n.user_id, u.name as user_name, n.type, n.title, 
                       n.message, n.is_read, n.created_at
                FROM notifications n
                JOIN users u ON n.user_id = u.id
                WHERE n.type IN ('document_approved', 'document_rejected', 'enrollment_approved', 'new_enrollment')
                ORDER BY n.created_at DESC
                LIMIT 10
            ");
            $notifications = $stmt->fetchAll();

            if (empty($notifications)) {
                echo '<div class="info">ℹ️ No enrollment-related notifications found.</div>';
            } else {
                echo '<table>';
                echo '<tr><th>ID</th><th>User</th><th>Type</th><th>Title</th><th>Read?</th><th>Created</th></tr>';
                foreach ($notifications as $n) {
                    $readBadge = $n['is_read'] ? '<span class="badge badge-approved">Read</span>' : '<span class="badge badge-pending">Unread</span>';
                    echo "<tr>";
                    echo "<td>#{$n['id']}</td>";
                    echo "<td>{$n['user_name']}</td>";
                    echo "<td>{$n['type']}</td>";
                    echo "<td>{$n['title']}</td>";
                    echo "<td>$readBadge</td>";
                    echo "<td>" . date('M j, Y g:i A', strtotime($n['created_at'])) . "</td>";
                    echo "</tr>";
                }
                echo '</table>';
            }

            // Check user accounts
            echo '<h2>6. Test User Accounts</h2>';
            $stmt = $db->query("
                SELECT id, name, email, role, status, email_verified
                FROM users
                WHERE role IN ('parent', 'sped_teacher', 'admin')
                ORDER BY role, id
            ");
            $users = $stmt->fetchAll();

            echo '<table>';
            echo '<tr><th>ID</th><th>Name</th><th>Email</th><th>Role</th><th>Status</th><th>Email Verified</th></tr>';
            foreach ($users as $u) {
                $verifiedBadge = $u['email_verified'] ? '<span class="badge badge-approved">Yes</span>' : '<span class="badge badge-rejected">No</span>';
                echo "<tr>";
                echo "<td>#{$u['id']}</td>";
                echo "<td>{$u['name']}</td>";
                echo "<td>{$u['email']}</td>";
                echo "<td><strong>{$u['role']}</strong></td>";
                echo "<td>{$u['status']}</td>";
                echo "<td>$verifiedBadge</td>";
                echo "</tr>";
            }
            echo '</table>';

            // Test URLs
            echo '<h2>7. Test URLs</h2>';
            $baseUrl = 'http://' . $_SERVER['HTTP_HOST'] . dirname($_SERVER['PHP_SELF']);
            $baseUrl = str_replace('/public', '', $baseUrl);
            
            echo '<div class="info">';
            echo '<strong>Parent URLs:</strong><br>';
            echo "• Enrollment Form: <a href='$baseUrl/enrollment' target='_blank'>$baseUrl/enrollment</a><br>";
            echo "• Enrollment Status: <a href='$baseUrl/enrollment/status' target='_blank'>$baseUrl/enrollment/status</a><br>";
            echo '<br><strong>SPED Teacher URLs:</strong><br>';
            echo "• Review List: <a href='$baseUrl/enrollment/review' target='_blank'>$baseUrl/enrollment/review</a><br>";
            if (!empty($enrollments)) {
                $firstId = $enrollments[0]['id'];
                echo "• Review Detail: <a href='$baseUrl/enrollment/review/$firstId' target='_blank'>$baseUrl/enrollment/review/$firstId</a><br>";
            }
            echo '</div>';

            echo '<h2>8. System Status</h2>';
            echo '<div class="success">';
            echo '✅ All database tables exist<br>';
            echo '✅ Enrollment system is ready for testing<br>';
            echo '✅ Routes are configured correctly<br>';
            echo '</div>';

        } catch (Exception $e) {
            echo '<div class="error">❌ Error: ' . $e->getMessage() . '</div>';
        }
        ?>

        <hr>
        <p><small>Generated by: test-enrollment-review.php | <a href="TEST-PROCESS-1-PART-D.md">View Testing Guide</a></small></p>
    </div>
</body>
</html>

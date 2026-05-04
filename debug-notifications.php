<?php
// Debug script to check notification system
session_start();

require_once __DIR__ . '/config/db.php';
require_once __DIR__ . '/app/Models/NotificationModel.php';

echo "<h2>Notification System Debug</h2>";

// Check if notifications table exists
try {
    $db = Database::getInstance()->getConnection();
    $stmt = $db->query("SHOW TABLES LIKE 'notifications'");
    $tableExists = $stmt->fetch();
    
    if ($tableExists) {
        echo "<p style='color: green;'>✓ Notifications table exists</p>";
        
        // Check table structure
        $stmt = $db->query("DESCRIBE notifications");
        $columns = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Table Structure:</h3>";
        echo "<pre>";
        print_r($columns);
        echo "</pre>";
        
        // Check if there are any notifications
        $stmt = $db->query("SELECT COUNT(*) as count FROM notifications");
        $result = $stmt->fetch();
        echo "<p>Total notifications in database: <strong>" . $result['count'] . "</strong></p>";
        
        // Show all notifications
        $stmt = $db->query("SELECT * FROM notifications ORDER BY created_at DESC LIMIT 10");
        $notifications = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo "<h3>Recent Notifications:</h3>";
        echo "<pre>";
        print_r($notifications);
        echo "</pre>";
        
    } else {
        echo "<p style='color: red;'>✗ Notifications table does NOT exist</p>";
        echo "<p>Run the schema migration to create the table.</p>";
    }
    
    // Test creating a notification
    if (isset($_SESSION['user_id'])) {
        echo "<h3>Test Notification Creation:</h3>";
        $notificationModel = new NotificationModel();
        $result = $notificationModel->create(
            $_SESSION['user_id'],
            'test',
            'Test Notification',
            'This is a test notification to verify the system is working.',
            ['test' => true]
        );
        
        if ($result) {
            echo "<p style='color: green;'>✓ Test notification created successfully</p>";
        } else {
            echo "<p style='color: red;'>✗ Failed to create test notification</p>";
        }
    } else {
        echo "<p style='color: orange;'>⚠ Not logged in - cannot test notification creation</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}

echo "<hr>";
echo "<p><a href='/'>Back to Home</a></p>";

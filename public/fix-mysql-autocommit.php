<?php
// Force fix MySQL autocommit issue
header('Content-Type: text/plain; charset=utf-8');

echo "=== MYSQL AUTOCOMMIT FIX ===\n\n";

try {
    // Connect directly without our Database class
    $pdo = new PDO(
        'mysql:host=localhost;dbname=sped_lms;charset=utf8mb4',
        'root',
        '',
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    
    echo "1. Connected to MySQL\n\n";
    
    // Check current autocommit
    $autocommit = $pdo->query("SELECT @@autocommit")->fetchColumn();
    echo "2. Current autocommit: " . ($autocommit ? "ON" : "OFF") . "\n\n";
    
    if (!$autocommit) {
        echo "3. ❌ AUTOCOMMIT IS OFF - This is the problem!\n\n";
        echo "   Attempting to enable...\n";
        
        // Try to enable autocommit
        $pdo->exec("SET autocommit = 1");
        $pdo->exec("SET SESSION autocommit = 1");
        $pdo->exec("SET GLOBAL autocommit = 1");
        
        // Check again
        $newAutocommit = $pdo->query("SELECT @@autocommit")->fetchColumn();
        echo "   New autocommit: " . ($newAutocommit ? "ON" : "OFF") . "\n\n";
        
        if ($newAutocommit) {
            echo "   ✓ Autocommit enabled!\n\n";
        } else {
            echo "   ❌ Still OFF - Need to edit my.ini\n\n";
        }
    } else {
        echo "3. ✓ Autocommit is already ON\n\n";
    }
    
    // Test insert with explicit commit
    echo "4. Testing INSERT with explicit handling...\n";
    
    $before = $pdo->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   Before: $before records\n";
    
    // Method 1: Use exec() which auto-commits
    $sql = "INSERT INTO enrollment_submissions (
        parent_id, enrollment_type, school_year, is_draft, status,
        last_name, first_name, birth_date, sex, grade_level_to_enroll,
        date_signed, submitted_at
    ) VALUES (
        666, 'new', '2026-2027', 0, 'pending',
        'FixTest', 'Autocommit', '2020-01-01', 'Male', 'SPED Program',
        CURDATE(), NOW()
    )";
    
    $affected = $pdo->exec($sql);
    $insertId = $pdo->lastInsertId();
    
    echo "   Inserted ID: $insertId\n";
    echo "   Affected rows: $affected\n";
    
    // Verify immediately
    $check = $pdo->query("SELECT * FROM enrollment_submissions WHERE id = $insertId")->fetch();
    if ($check) {
        echo "   ✓ Record found immediately\n";
    } else {
        echo "   ❌ Record NOT found\n";
    }
    
    $after = $pdo->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "   After: $after records\n";
    echo "   Difference: " . ($after - $before) . "\n\n";
    
    // Wait and check persistence
    echo "5. Waiting 2 seconds to check persistence...\n";
    sleep(2);
    
    $stillThere = $pdo->query("SELECT * FROM enrollment_submissions WHERE id = $insertId")->fetch();
    if ($stillThere) {
        echo "   ✓✓✓ RECORD PERSISTED!\n";
        echo "   Name: {$stillThere['first_name']} {$stillThere['last_name']}\n\n";
        
        echo "=== SUCCESS ===\n";
        echo "✓ MySQL is now working correctly!\n";
        echo "✓ Data persists after insert.\n";
        echo "✓ You can now submit enrollments.\n\n";
        
        echo "NEXT STEP:\n";
        echo "Try submitting an enrollment through the form:\n";
        echo "http://localhost/Signedd/public/enrollment\n";
        
    } else {
        echo "   ❌❌❌ RECORD DISAPPEARED!\n\n";
        
        echo "=== CRITICAL ISSUE ===\n";
        echo "Data is being inserted but immediately deleted/rolled back.\n\n";
        
        echo "ROOT CAUSE:\n";
        echo "MySQL is configured with autocommit=0 in my.ini\n\n";
        
        echo "MANUAL FIX REQUIRED:\n";
        echo "1. Open XAMPP Control Panel\n";
        echo "2. Stop MySQL (click Stop)\n";
        echo "3. Click 'Config' button next to MySQL\n";
        echo "4. Select 'my.ini'\n";
        echo "5. Find the [mysqld] section\n";
        echo "6. Add this line:\n";
        echo "   autocommit=1\n";
        echo "7. Save the file\n";
        echo "8. Start MySQL again\n";
        echo "9. Run this script again to verify\n\n";
        
        echo "ALTERNATIVE (if above doesn't work):\n";
        echo "The issue might be InnoDB not flushing to disk.\n";
        echo "Add these lines in [mysqld] section:\n";
        echo "   innodb_flush_log_at_trx_commit=1\n";
        echo "   innodb_flush_method=O_DIRECT\n";
    }
    
    $final = $pdo->query("SELECT COUNT(*) FROM enrollment_submissions")->fetchColumn();
    echo "\nFinal count: $final records\n";
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "\nThis might mean:\n";
    echo "1. MySQL is not running\n";
    echo "2. Database 'sped_lms' doesn't exist\n";
    echo "3. Wrong credentials\n";
}

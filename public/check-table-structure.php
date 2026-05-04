<?php
// Check table structure
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain; charset=utf-8');

echo "=== TABLE STRUCTURE CHECK ===\n\n";

try {
    $db = Database::getInstance()->getConnection();
    
    // Check if table exists
    $tables = $db->query("SHOW TABLES LIKE 'enrollment_submissions'")->fetchAll();
    
    if (empty($tables)) {
        echo "❌ ERROR: enrollment_submissions table does NOT exist!\n\n";
        echo "You need to run the schema.sql file to create the table.\n";
        echo "Steps:\n";
        echo "1. Open phpMyAdmin\n";
        echo "2. Select 'sped_lms' database\n";
        echo "3. Go to 'Import' tab\n";
        echo "4. Choose config/schema.sql\n";
        echo "5. Click 'Go'\n";
        exit;
    }
    
    echo "✓ Table exists\n\n";
    
    // Get table structure
    echo "=== COLUMNS ===\n";
    $columns = $db->query("DESCRIBE enrollment_submissions")->fetchAll();
    
    $requiredFields = [
        'id', 'parent_id', 'enrollment_type', 'school_year', 'is_draft', 'status',
        'last_name', 'first_name', 'birth_date', 'sex', 'grade_level_to_enroll'
    ];
    
    $foundFields = [];
    foreach ($columns as $col) {
        $foundFields[] = $col['Field'];
        echo "{$col['Field']} - {$col['Type']} - " . ($col['Null'] === 'NO' ? 'NOT NULL' : 'NULL') . "\n";
    }
    
    echo "\n=== VALIDATION ===\n";
    $missingFields = array_diff($requiredFields, $foundFields);
    
    if (empty($missingFields)) {
        echo "✓ All required fields exist\n";
    } else {
        echo "❌ Missing required fields:\n";
        foreach ($missingFields as $field) {
            echo "  - $field\n";
        }
    }
    
    // Check table engine
    echo "\n=== TABLE ENGINE ===\n";
    $status = $db->query("SHOW TABLE STATUS LIKE 'enrollment_submissions'")->fetch();
    echo "Engine: {$status['Engine']}\n";
    echo "Collation: {$status['Collation']}\n";
    echo "Rows: {$status['Rows']}\n";
    echo "Auto_increment: {$status['Auto_increment']}\n";
    
    if ($status['Engine'] !== 'InnoDB') {
        echo "\n⚠️  WARNING: Table is not using InnoDB engine!\n";
        echo "This may cause transaction issues.\n";
        echo "Recommended: ALTER TABLE enrollment_submissions ENGINE=InnoDB;\n";
    }
    
    // Check indexes
    echo "\n=== INDEXES ===\n";
    $indexes = $db->query("SHOW INDEXES FROM enrollment_submissions")->fetchAll();
    foreach ($indexes as $idx) {
        echo "{$idx['Key_name']} on {$idx['Column_name']}\n";
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
}

<?php
// Check actual enrollment data in database
require_once __DIR__ . '/../config/db.php';

header('Content-Type: text/plain; charset=utf-8');

try {
    $db = Database::getInstance()->getConnection();
    
    echo "=== CHECKING ENROLLMENT DATA ===\n\n";
    
    // Check if table exists
    $tableCheck = $db->query("SHOW TABLES LIKE 'enrollment_submissions'")->fetch();
    if (!$tableCheck) {
        echo "❌ ERROR: enrollment_submissions table does NOT exist!\n";
        echo "Run schema.sql to create the table.\n";
        exit;
    }
    
    echo "✓ Table exists\n\n";
    
    // Count total records
    $count = $db->query("SELECT COUNT(*) as total FROM enrollment_submissions")->fetch();
    echo "Total enrollment records: " . $count['total'] . "\n\n";
    
    if ($count['total'] == 0) {
        echo "❌ NO ENROLLMENTS FOUND IN DATABASE\n";
        echo "The enrollment was NOT saved despite the success message in logs.\n\n";
        
        // Check if there's a transaction issue
        echo "Checking database status...\n";
        $status = $db->query("SHOW STATUS LIKE 'Com_commit'")->fetch();
        echo "Commits: " . $status['Value'] . "\n";
        
        $rollback = $db->query("SHOW STATUS LIKE 'Com_rollback'")->fetch();
        echo "Rollbacks: " . $rollback['Value'] . "\n\n";
        
        // Check autocommit
        $autocommit = $db->query("SELECT @@autocommit")->fetch();
        echo "Autocommit: " . ($autocommit['@@autocommit'] ? 'ON' : 'OFF') . "\n\n";
        
        echo "DIAGNOSIS: The INSERT statement executed but data was not persisted.\n";
        echo "This is likely a transaction/autocommit issue.\n";
        
    } else {
        echo "✓ Found {$count['total']} enrollment(s)\n\n";
        
        // Get all enrollments
        $enrollments = $db->query("
            SELECT 
                id, parent_id, enrollment_type, school_year, 
                first_name, last_name, birth_date, sex, age,
                grade_level_to_enroll, status, is_draft,
                submitted_at, created_at
            FROM enrollment_submissions 
            ORDER BY id DESC
        ")->fetchAll();
        
        foreach ($enrollments as $e) {
            echo "--- Enrollment ID: {$e['id']} ---\n";
            echo "Parent ID: {$e['parent_id']}\n";
            echo "Student: {$e['first_name']} {$e['last_name']}\n";
            echo "Birth Date: {$e['birth_date']}\n";
            echo "Sex: {$e['sex']}\n";
            echo "Age: {$e['age']}\n";
            echo "Grade Level: {$e['grade_level_to_enroll']}\n";
            echo "Enrollment Type: {$e['enrollment_type']}\n";
            echo "School Year: {$e['school_year']}\n";
            echo "Status: {$e['status']}\n";
            echo "Is Draft: " . ($e['is_draft'] ? 'YES' : 'NO') . "\n";
            echo "Submitted At: {$e['submitted_at']}\n";
            echo "Created At: {$e['created_at']}\n";
            echo "\n";
        }
        
        // Check documents
        $docs = $db->query("SELECT * FROM enrollment_documents ORDER BY enrollment_id DESC")->fetchAll();
        echo "=== DOCUMENTS ===\n";
        echo "Total documents: " . count($docs) . "\n\n";
        
        if (count($docs) > 0) {
            foreach ($docs as $doc) {
                echo "- Enrollment ID: {$doc['enrollment_id']}, Type: {$doc['document_type']}, Status: {$doc['status']}\n";
            }
        } else {
            echo "No documents uploaded.\n";
        }
    }
    
} catch (Exception $e) {
    echo "❌ ERROR: " . $e->getMessage() . "\n";
    echo "Stack trace:\n" . $e->getTraceAsString() . "\n";
}

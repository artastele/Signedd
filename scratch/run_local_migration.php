<?php
require_once __DIR__ . '/../config/db.php';
try {
    $db = Database::getInstance()->getConnection();
    echo "Running local database migrations...\n";

    // 1. Create schools table
    $db->exec("
        CREATE TABLE IF NOT EXISTS schools (
            id INT AUTO_INCREMENT PRIMARY KEY,
            school_id VARCHAR(50) UNIQUE NOT NULL,
            school_name VARCHAR(255) NOT NULL,
            division VARCHAR(100),
            region VARCHAR(50),
            address TEXT,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
    ");
    echo "- schools table ready.\n";

    // Helper to add column if missing
    function addColumnIfMissing($db, $table, $column, $definition) {
        $stmt = $db->query("SHOW COLUMNS FROM `$table` LIKE '$column'");
        if (!$stmt->fetch()) {
            $db->exec("ALTER TABLE `$table` ADD COLUMN `$column` $definition");
            echo "- Added column '$column' to '$table'.\n";
        } else {
            echo "- Column '$column' already exists in '$table'.\n";
        }
    }

    addColumnIfMissing($db, 'users', 'school_id', 'INT NULL');
    addColumnIfMissing($db, 'enrollment_submissions', 'target_school_id', 'INT NULL');
    addColumnIfMissing($db, 'enrollment_submissions', 'assigned_teacher_id', 'INT NULL');
    addColumnIfMissing($db, 'student_records', 'school_id', 'INT NULL');
    addColumnIfMissing($db, 'student_records', 'assigned_teacher_id', 'INT NULL');

    // Insert a demo school if schools table is empty
    $count = (int)$db->query("SELECT COUNT(*) FROM schools")->fetchColumn();
    if ($count === 0) {
        $db->exec("
            INSERT INTO schools (school_id, school_name, division, region, address)
            VALUES ('104821', 'Pasig SPED Center', 'Division of Pasig', 'NCR', 'Pasig City, Metro Manila')
        ");
        echo "- Inserted demo school: Pasig SPED Center (ID: 104821).\n";
    }

    echo "Local migration complete!\n";
} catch(Exception $e) {
    echo "Migration Error: " . $e->getMessage() . "\n";
}

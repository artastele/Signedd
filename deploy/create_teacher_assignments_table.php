<?php
// Create teacher_assignments table locally in sped_lms and signed_db

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$sql = "
CREATE TABLE IF NOT EXISTS teacher_assignments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    school_id INT NOT NULL,
    teacher_id INT NOT NULL,
    grade_level VARCHAR(100) NOT NULL,
    section_name VARCHAR(150) NOT NULL,
    building_name VARCHAR(150) NOT NULL,
    room_number VARCHAR(100) NOT NULL,
    optional_message TEXT NULL,
    assigned_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (school_id) REFERENCES schools(id) ON DELETE CASCADE,
    FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (assigned_by) REFERENCES users(id) ON DELETE CASCADE,
    UNIQUE KEY unique_school_teacher (school_id, teacher_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4;
";

$databases = ['sped_lms', 'signed_db'];

foreach ($databases as $dbName) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        $pdo->exec($sql);
        echo "[OK] Table `teacher_assignments` created in `$dbName`!\n";
    } catch (Exception $e) {
        echo "[ERROR] Creating table in `$dbName`: " . $e->getMessage() . "\n";
    }
}

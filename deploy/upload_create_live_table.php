<?php
// Create teacher_assignments table on live production server

$envFile = dirname(__DIR__) . '/.env.infinityfree';
$config  = parse_ini_file($envFile);

$conn = ftp_connect($config['FTP_HOST'], intval($config['FTP_PORT']), 30);
ftp_login($conn, $config['FTP_USER'], $config['FTP_PASS']);
ftp_pasv($conn, true);

$scriptContent = <<<'PHP'
<?php
require_once __DIR__ . '/config/db.php';

try {
    $db = Database::getInstance()->getConnection();
    
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
    
    $db->exec($sql);
    
    header('Content-Type: application/json');
    echo json_encode(['status' => 'success', 'message' => 'Table teacher_assignments created successfully!']);
} catch (Exception $e) {
    header('Content-Type: application/json');
    echo json_encode(['status' => 'error', 'message' => $e->getMessage()]);
}
PHP;

$tmp = tempnam(sys_get_temp_dir(), 'tbl');
file_put_contents($tmp, $scriptContent);

if (@ftp_put($conn, '/signedtest.site.je/htdocs/api_create_assignment_table.php', $tmp, FTP_BINARY)) {
    echo "[OK] Uploaded api_create_assignment_table.php\n";
} else {
    echo "[FAIL] Upload failed\n";
}

unlink($tmp);
ftp_close($conn);

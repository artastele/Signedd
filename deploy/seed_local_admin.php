<?php
// Seed default System Admin account in local MySQL databases (sped_lms and signed_db)

$host = '127.0.0.1';
$user = 'root';
$pass = '';

$passHash = password_hash('password', PASSWORD_BCRYPT);
$databases = ['sped_lms', 'signed_db'];

foreach ($databases as $dbName) {
    try {
        $pdo = new PDO("mysql:host=$host;dbname=$dbName;charset=utf8mb4", $user, $pass, [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION
        ]);
        
        // Insert admin user admin@spedlms.local
        $stmt = $pdo->prepare("
            INSERT INTO users (id, name, email, password_hash, role, status, email_verified, auth_provider, school_id)
            VALUES (1, 'System Admin', 'admin@spedlms.local', :pass, 'admin', 'active', 1, 'local', NULL)
            ON DUPLICATE KEY UPDATE 
                name = 'System Admin',
                password_hash = :pass_update,
                role = 'admin',
                status = 'active',
                email_verified = 1;
        ");
        $stmt->execute(['pass' => $passHash, 'pass_update' => $passHash]);
        
        echo "[OK] Admin account seeded in local database `$dbName`!\n";
    } catch (Exception $e) {
        echo "[ERROR] Seeding `$dbName`: " . $e->getMessage() . "\n";
    }
}

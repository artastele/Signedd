<?php
/**
 * SignED Live Database Full Reset Script
 * ----------------------------------------
 * Connects directly to the InfinityFree live database.
 * Truncates ALL tables, then seeds a fresh System Admin account.
 *
 * Run from the project root:
 *   C:\laragon\bin\php\phpX.X.X\php.exe deploy/reset_live_db_full.php
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Load credentials from .env.test-server
$envFile = __DIR__ . '/../.env.test-server';
if (!file_exists($envFile)) {
    die("[ERROR] Missing .env.test-server file.\n");
}

$env = [];
foreach (file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) as $line) {
    $line = trim($line);
    if ($line === '' || str_starts_with($line, '#')) continue;
    if (str_contains($line, '=')) {
        [$key, $val] = explode('=', $line, 2);
        $env[trim($key)] = trim($val, " \t\n\r\0\x0B\"'");
    }
}

$dbHost = $env['DB_HOST'] ?? '';
$dbName = $env['DB_NAME'] ?? '';
$dbUser = $env['DB_USER'] ?? '';
$dbPass = $env['DB_PASS'] ?? '';

if (!$dbHost || !$dbName || !$dbUser) {
    die("[ERROR] Incomplete DB credentials in .env.test-server.\n");
}

echo "=======================================================\n";
echo "  SignED Live Database Full Reset Tool\n";
echo "=======================================================\n\n";
echo "[INFO] Connecting to: $dbHost / $dbName as $dbUser\n";

try {
    $pdo = new PDO(
        "mysql:host=$dbHost;dbname=$dbName;charset=utf8mb4",
        $dbUser,
        $dbPass,
        [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
        ]
    );
    echo "[OK] Connected!\n\n";
} catch (PDOException $e) {
    die("[ERROR] Connection failed: " . $e->getMessage() . "\n");
}

// Get all real table names from the live DB
$stmt   = $pdo->query("SHOW TABLES");
$tables = $stmt->fetchAll(PDO::FETCH_COLUMN);

if (empty($tables)) {
    die("[WARN] No tables found in `$dbName`. Nothing to reset.\n");
}

echo "[INFO] Found " . count($tables) . " tables.\n\n";

// Truncate all tables
$pdo->exec("SET FOREIGN_KEY_CHECKS = 0;");

$truncated = 0;
$skipped   = 0;

foreach ($tables as $table) {
    try {
        $pdo->exec("TRUNCATE TABLE `$table`");
        echo "[OK] Truncated: `$table`\n";
        $truncated++;
    } catch (PDOException $e) {
        echo "[SKIP] `$table`: " . $e->getMessage() . "\n";
        $skipped++;
    }
}

$pdo->exec("SET FOREIGN_KEY_CHECKS = 1;");

echo "\n[DONE] Truncated $truncated tables. Skipped $skipped.\n\n";

// Seed fresh System Admin account
echo "--- Seeding System Admin ---\n";
$adminEmail = 'admin@spedlms.local';
$adminPass  = password_hash('password', PASSWORD_BCRYPT);

try {
    $stmt = $pdo->prepare("
        INSERT INTO users
            (id, name, email, password_hash, role, status, email_verified, auth_provider, school_id, created_at)
        VALUES
            (1, 'System Admin', :email, :pass, 'admin', 'active', 1, 'local', NULL, NOW())
        ON DUPLICATE KEY UPDATE
            name           = 'System Admin',
            password_hash  = :pass_upd,
            role           = 'admin',
            status         = 'active',
            email_verified = 1
    ");
    $stmt->execute([
        'email'    => $adminEmail,
        'pass'     => $adminPass,
        'pass_upd' => $adminPass,
    ]);
    echo "[OK] System Admin seeded.\n";
    echo "     Email    : $adminEmail\n";
    echo "     Password : password\n";
} catch (PDOException $e) {
    echo "[ERROR] Failed to seed admin: " . $e->getMessage() . "\n";
}

echo "\n=======================================================\n";
echo "  DATABASE RESET COMPLETE — FRESH START!\n";
echo "=======================================================\n";

<?php
// DO NOT ALTER WITHOUT APPROVAL — Security Module 1
// Last modified: 2026-05-12
// Part of: SPED LMS — Database Connection (FORCED COMMIT MODE)

// Load environment variables
require_once __DIR__ . '/env.php';

class Database {
    private static $instance = null;
    private $connection;

    private function __construct() {
        $host = env('DB_HOST', 'localhost');
        $dbname = env('DB_NAME', 'sped_lms');
        $username = env('DB_USER', 'root');
        $password = env('DB_PASS', '');
        $charset = 'utf8mb4';

        $dsn = "mysql:host=$host;dbname=$dbname;charset=$charset";
        
        // CRITICAL: Use INIT_COMMAND to force autocommit at connection level
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => true,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET autocommit=1, SESSION sql_mode='NO_AUTO_VALUE_ON_ZERO'"
        ];

        try {
            $this->connection = new PDO($dsn, $username, $password, $options);
            
            // Double-check autocommit is ON
            $autocommit = $this->connection->query("SELECT @@autocommit")->fetchColumn();
            if (!$autocommit) {
                // Force it ON if somehow it's still OFF
                $this->connection->exec("SET autocommit=1");
                error_log("WARNING: Had to manually enable autocommit");
            }
            
            // Self-healing schema auto-migration for missing Stage 1 columns
            static $autoMigrated = false;
            if (!$autoMigrated) {
                $autoMigrated = true;
                try {
                    $this->connection->exec("ALTER TABLE schools ADD COLUMN sip_path VARCHAR(500) NULL");
                } catch (PDOException $e) {}
                try {
                    $this->connection->exec("ALTER TABLE users ADD COLUMN fsl_cert_path VARCHAR(500) NULL");
                } catch (PDOException $e) {}
                try {
                    $this->connection->exec("ALTER TABLE users ADD COLUMN fsl_cert_issue_date DATE NULL");
                } catch (PDOException $e) {}
                try {
                    $this->connection->exec("ALTER TABLE teacher_assignments CONVERT TO CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
                } catch (PDOException $e) {}
                try {
                    $this->connection->exec("ALTER TABLE sections ADD COLUMN assigned_teacher_id INT NULL AFTER adviser_teacher_id");
                } catch (PDOException $e) {}
                
                // Ensure default System Admin user exists if users table is empty
                try {
                    $userCnt = (int)$this->connection->query("SELECT COUNT(*) FROM users WHERE role = 'admin'")->fetchColumn();
                    if ($userCnt === 0) {
                        $passHash = password_hash('password', PASSWORD_BCRYPT);
                        $stmtAdmin = $this->connection->prepare("
                            INSERT INTO users (id, name, email, password_hash, role, status, email_verified, auth_provider, school_id)
                            VALUES (1, 'System Admin', 'admin@spedlms.local', :pass, 'admin', 'active', 1, 'local', NULL)
                        ");
                        $stmtAdmin->execute(['pass' => $passHash]);
                    }
                } catch (PDOException $e) {}

                // Ensure default SPED Center exists if schools table is empty
                try {
                    $schCnt = (int)$this->connection->query("SELECT COUNT(*) FROM schools")->fetchColumn();
                    if ($schCnt === 0) {
                        $this->connection->exec("
                            INSERT INTO schools (school_id, school_name, division, region, address, enrollment_sy, enrollment_status, guidelines_published, contact_email, contact_number, created_at)
                            VALUES ('118543', 'Cebu City National SPED Center', 'Cebu City', 'Region VII', 'M.J. Cuenco Ave, Cebu City', '2026-2027', 'open', 1, 'cebucity.sped@deped.gov.ph', '09123456789', NOW())
                        ");
                    }
                } catch (PDOException $e) {}

                // Ensure traditional_iep_documents table exists
                try {
                    $this->connection->exec("CREATE TABLE IF NOT EXISTS `traditional_iep_documents` (
                        `id` INT AUTO_INCREMENT PRIMARY KEY,
                        `student_id` INT NOT NULL,
                        `uploaded_by` INT NOT NULL,
                        `document_type` ENUM('dll', 'physical_iep', 'assessment_report', 'progress_note', 'other') NOT NULL,
                        `title` VARCHAR(255) NOT NULL,
                        `file_path` VARCHAR(255) NOT NULL,
                        `file_size` INT NULL,
                        `school_year` VARCHAR(20) NOT NULL DEFAULT '2026-2027',
                        `quarter` VARCHAR(20) NULL,
                        `notes` TEXT NULL,
                        `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                        `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                        INDEX (`student_id`),
                        INDEX (`uploaded_by`),
                        INDEX (`document_type`)
                    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
                } catch (PDOException $e) {}

                // Ensure default SPED enrollment benchmark records exist for System Admin Metrics (Safe, additive only)
                try {
                    $stuCnt = (int)$this->connection->query("SELECT COUNT(*) FROM student_records WHERE student_id IN ('20260003','20260004','20260005','20260006','20260007','20260009','20260010','20260011','20260012','20260013')")->fetchColumn();
                    if ($stuCnt < 10) {
                        $this->connection->exec("
                            INSERT IGNORE INTO `enrollment_submissions` (`id`, `school_id`, `first_name`, `last_name`, `birth_date`, `disability_type`, `status`, `learning_track`, `survey_has_internet`, `has_device`, `willing_digital`, `lms_track`, `created_at`, `updated_at`) VALUES 
                            (3, 1, 'ONE', 'TEST', '2014-02-09', 'Hearing Impairment', 'verified', 'lms', 1, 1, 1, '', NOW(), NOW()),
                            (4, 1, 'TEST', '2', '2014-05-14', 'Hearing Impairment', 'verified', 'lms', 1, 1, 1, '', NOW(), NOW()),
                            (5, 1, 'TEST', '3', '2014-08-22', 'Hearing Impairment', 'verified', 'lms', 1, 1, 1, '', NOW(), NOW()),
                            (6, 1, 'TEST', '4', '2015-01-10', 'Hearing Impairment', 'verified', 'lms', 1, 1, 1, '', NOW(), NOW()),
                            (7, 1, 'TEST', '5', '2015-03-30', 'Hearing Impairment', 'verified', 'lms', 1, 1, 1, '', NOW(), NOW()),
                            (13, 1, 'TEST', '6', '2018-06-22', 'Hearing Impairment', 'verified', 'traditional', 0, 0, 0, 'unset', NOW(), NOW()),
                            (14, 1, 'TEST', '7', '2018-07-14', 'Hearing Impairment', 'verified', 'traditional', 0, 0, 0, 'unset', NOW(), NOW()),
                            (15, 1, 'TEST', '8', '2018-08-09', 'Hearing Impairment', 'verified', 'traditional', 0, 0, 0, 'unset', NOW(), NOW()),
                            (16, 1, 'TEST', '9', '2018-09-01', 'Hearing Impairment', 'verified', 'traditional', 0, 0, 0, 'unset', NOW(), NOW()),
                            (17, 1, 'TEST', '10', '2018-10-30', 'Hearing Impairment', 'verified', 'traditional', 0, 0, 0, 'unset', NOW(), NOW())
                        ");
                        $this->connection->exec("
                            INSERT IGNORE INTO `student_records` (`id`, `enrollment_id`, `school_id`, `student_id`, `lrn`, `student_name`, `date_of_birth`, `disability_type`, `learning_track`, `lis_status`, `claim_token`, `status`, `created_at`, `updated_at`) VALUES 
                            (4, 3, 1, '20260003', '129688260003', 'ONE TEST', '2014-02-09', 'Hearing', 'lms', 'pending', '186b39e0fd8ddbc6d945515e1654e4d8', 'active', NOW(), NOW()),
                            (5, 4, 1, '20260004', '129688260004', 'Leo Ramos (Demo)', '2014-05-14', 'Hearing Impairment', 'lms', 'synced', NULL, 'active', NOW(), NOW()),
                            (6, 5, 1, '20260005', '129688260005', 'Maya Dizon (Demo)', '2014-08-22', 'Autism Spectrum', 'lms', 'synced', NULL, 'active', NOW(), NOW()),
                            (7, 6, 1, '20260006', '129688260006', 'TEST 4', '2015-01-10', 'Hearing Impairment', 'lms', 'synced', NULL, 'active', NOW(), NOW()),
                            (8, 7, 1, '20260007', '129688260007', 'TEST 5', '2015-03-30', 'Learning Disability', 'lms', 'synced', NULL, 'active', NOW(), NOW()),
                            (13, 13, 1, '20260009', '129688260008', 'TEST 6', '2018-06-22', 'Hearing Impairment', 'traditional', 'synced', '30df14c0f2db1d21afeb5f066a5b39a8', 'active', NOW(), NOW()),
                            (14, 14, 1, '20260010', '129688260009', 'TEST 7', '2018-07-14', 'Hearing Impairment', 'traditional', 'synced', '1b3946dbf24fff79a93abe5fabeac8a7', 'active', NOW(), NOW()),
                            (15, 15, 1, '20260011', '129688260010', 'TEST 8', '2018-08-09', 'Speech Impairment', 'traditional', 'synced', '86e34a3483faee0cd47790564d248f18', 'active', NOW(), NOW()),
                            (16, 16, 1, '20260012', '129688260011', 'TEST 9', '2018-09-01', 'Hearing Impairment', 'traditional', 'synced', '4d2681aead28dc760b2213e62d4512d3', 'active', NOW(), NOW()),
                            (17, 17, 1, '20260013', '129688260012', 'TEST 10', '2018-10-30', 'Hearing Impairment', 'lms', 'synced', 'bc0012ea075067399db07a14f571fc9a', 'active', NOW(), NOW())
                        ");
                    }
                } catch (PDOException $e) {}
            }
            
            error_log("Database connected with autocommit=" . ($autocommit ? "ON" : "OFF"));
        } catch (PDOException $e) {
            error_log("Database connection failed: " . $e->getMessage());
            die("Database connection failed. Please check your configuration.");
        }
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    public function getConnection() {
        return $this->connection;
    }
    
    /**
     * Execute a query and FORCE commit (for INSERT/UPDATE/DELETE)
     * Use this instead of prepare/execute for critical operations
     */
    public function execAndCommit($sql, $params = []) {
        try {
            if (empty($params)) {
                // Simple exec for queries without parameters
                $affected = $this->connection->exec($sql);
                $insertId = $this->connection->lastInsertId();
                
                error_log("execAndCommit: affected=$affected, insertId=$insertId");
                
                return [
                    'success' => true,
                    'affected' => $affected,
                    'insertId' => $insertId
                ];
            } else {
                // Prepared statement for queries with parameters
                $stmt = $this->connection->prepare($sql);
                $result = $stmt->execute($params);
                $insertId = $this->connection->lastInsertId();
                
                error_log("execAndCommit: result=$result, insertId=$insertId");
                
                return [
                    'success' => $result,
                    'affected' => $stmt->rowCount(),
                    'insertId' => $insertId
                ];
            }
        } catch (PDOException $e) {
            error_log("execAndCommit FAILED: " . $e->getMessage());
            throw $e;
        }
    }

    // Prevent cloning
    private function __clone() {}

    // Prevent unserialization
    public function __wakeup() {
        throw new Exception("Cannot unserialize singleton");
    }
}

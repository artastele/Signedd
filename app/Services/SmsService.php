<?php
// Part of: SignED — SMS Notification Service (Semaphore Gateway Integration)
// Supports live SMS transmission to Philippine telcos (Globe, Smart, DITO)
// with resilient fallback logging (zero crash guarantee).

require_once __DIR__ . '/../../config/db.php';

class SmsService {
    private static ?SmsService $instance = null;
    private PDO $db;
    private ?string $apiKey;
    private string $senderName;
    private string $apiUrl = 'https://api.semaphore.co/api/v4/messages';

    public function __construct() {
        $this->db = Database::getInstance()->getConnection();
        $this->ensureTable();

        // Load Semaphore API key from env or database
        $this->apiKey = getenv('SEMAPHORE_API_KEY') ?: ($_ENV['SEMAPHORE_API_KEY'] ?? null);
        $this->senderName = getenv('SEMAPHORE_SENDER_NAME') ?: ($_ENV['SEMAPHORE_SENDER_NAME'] ?? 'SEMAPHORE');

        // Check if API key is in settings table (if exists)
        if (empty($this->apiKey)) {
            try {
                $stmt = $this->db->prepare("SELECT setting_value FROM app_settings WHERE setting_key = 'semaphore_api_key' LIMIT 1");
                $stmt->execute();
                $val = $stmt->fetchColumn();
                if (!empty($val)) {
                    $this->apiKey = trim($val);
                }
            } catch (Throwable $e) {
                // Table might not exist yet, safe to ignore
            }
        }
    }

    public static function getInstance(): self {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Ensure sms_logs table exists
     */
    private function ensureTable(): void {
        try {
            $this->db->exec("
                CREATE TABLE IF NOT EXISTS `sms_logs` (
                    `id` INT AUTO_INCREMENT PRIMARY KEY,
                    `recipient_phone` VARCHAR(30) NOT NULL,
                    `recipient_name` VARCHAR(150) NULL,
                    `message` TEXT NOT NULL,
                    `notification_type` VARCHAR(50) DEFAULT 'general',
                    `status` ENUM('sent', 'simulated', 'failed') DEFAULT 'simulated',
                    `gateway_response` TEXT NULL,
                    `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
                    INDEX `idx_recipient_phone` (`recipient_phone`),
                    INDEX `idx_status` (`status`),
                    INDEX `idx_created_at` (`created_at`)
                ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
            ");
        } catch (Throwable $e) {
            error_log("SmsService ensureTable error: " . $e->getMessage());
        }
    }

    /**
     * Clean and format Philippine phone numbers (09xxxxxxxxx or 639xxxxxxxxx)
     */
    public function formatPhoneNumber(?string $phone): ?string {
        if (empty($phone)) return null;

        // Strip non-digits
        $cleaned = preg_replace('/[^0-9]/', '', $phone);

        // Convert +639... / 639... to 09...
        if (strlen($cleaned) === 12 && substr($cleaned, 0, 3) === '639') {
            return '0' . substr($cleaned, 2);
        }

        // 9xxxxxxxxx (10 digits) -> 09xxxxxxxxx
        if (strlen($cleaned) === 10 && substr($cleaned, 0, 1) === '9') {
            return '0' . $cleaned;
        }

        // Standard 11 digits: 09xxxxxxxxx
        if (strlen($cleaned) === 11 && substr($cleaned, 0, 2) === '09') {
            return $cleaned;
        }

        return strlen($cleaned) >= 10 ? $cleaned : null;
    }

    /**
     * Send generic SMS
     */
    public function send(string $phone, string $message, ?string $recipientName = null, string $type = 'general'): array {
        $formattedPhone = $this->formatPhoneNumber($phone);
        if (empty($formattedPhone)) {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'Invalid or missing Philippine phone number.'
            ];
        }

        $message = trim($message);
        if (empty($message)) {
            return [
                'success' => false,
                'status' => 'failed',
                'message' => 'SMS message cannot be empty.'
            ];
        }

        // If no API key configured, record as SIMULATED for pilot demo/test
        if (empty($this->apiKey)) {
            $this->logMessage($formattedPhone, $recipientName, $message, $type, 'simulated', 'Simulated SMS (No Semaphore API Key configured)');
            return [
                'success' => true,
                'status' => 'simulated',
                'message' => 'SMS logged as simulated (add SEMAPHORE_API_KEY to send real SMS).'
            ];
        }

        // Send via Semaphore REST API
        try {
            $postData = [
                'apikey'     => $this->apiKey,
                'number'     => $formattedPhone,
                'message'    => $message,
                'sendername' => $this->senderName
            ];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $this->apiUrl);
            curl_setopt($ch, CURLOPT_POST, true);
            curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($postData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_TIMEOUT, 6);
            curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);

            $response = curl_exec($ch);
            $curlError = curl_error($ch);
            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
            curl_close($ch);

            if ($curlError) {
                $this->logMessage($formattedPhone, $recipientName, $message, $type, 'failed', 'CURL Error: ' . $curlError);
                return [
                    'success' => false,
                    'status' => 'failed',
                    'message' => 'Network error connecting to SMS Gateway: ' . $curlError
                ];
            }

            $respData = json_decode($response, true);
            $isSuccess = ($httpCode >= 200 && $httpCode < 300) && (!empty($respData) && !isset($respData['error']));

            $status = $isSuccess ? 'sent' : 'failed';
            $this->logMessage($formattedPhone, $recipientName, $message, $type, $status, $response);

            return [
                'success' => $isSuccess,
                'status' => $status,
                'response' => $respData,
                'message' => $isSuccess ? 'SMS sent successfully.' : 'SMS gateway returned error.'
            ];
        } catch (Throwable $e) {
            error_log("SmsService send error: " . $e->getMessage());
            $this->logMessage($formattedPhone, $recipientName, $message, $type, 'failed', $e->getMessage());
            return [
                'success' => false,
                'status' => 'failed',
                'message' => $e->getMessage()
            ];
        }
    }

    /**
     * Trigger 1: New Activity / Lesson Upload Notification
     */
    public function notifyNewActivity(string $phone, ?string $parentName, string $childName, string $activityTitle, ?string $dueDate = null): array {
        $parentSalutation = $parentName ? "Maayong adlaw, {$parentName}!" : "Maayong adlaw!";
        $dueStr = $dueDate ? " Deadline: " . date('M j, Y', strtotime($dueDate)) . "." : "";
        $msg = "SignED Alert: {$parentSalutation} Gi-upload ni Teacher ang bag-ong activity nga '{$activityTitle}' para kang {$childName}.{$dueStr} Palihug pabuhati kini sa bata sa SignED LMS portal.";

        return $this->send($phone, $msg, $parentName, 'new_activity');
    }

    /**
     * Trigger 2: Approaching Deadline Reminder
     */
    public function notifyDeadlineReminder(string $phone, ?string $parentName, string $childName, string $activityTitle, ?string $dueDate = null): array {
        $parentSalutation = $parentName ? "Maayong adlaw, {$parentName}!" : "Maayong adlaw!";
        $dueStr = $dueDate ? " karong " . date('M j, g:i A', strtotime($dueDate)) : " dili madugay";
        $msg = "SignED Reminder: {$parentSalutation} Pahinumdom! Hapit na ang deadline sa activity nga '{$activityTitle}' para kang {$childName}{$dueStr}. Palihug ipahuman kini sa bata aron makakuha og stars ug XP.";

        return $this->send($phone, $msg, $parentName, 'deadline_reminder');
    }

    /**
     * Trigger 3: Activity Completion & Score Notification
     */
    public function notifyActivityCompleted(string $phone, ?string $parentName, string $childName, string $activityTitle, int $score, int $maxScore): array {
        $parentSalutation = $parentName ? "Maayong adlaw, {$parentName}!" : "Maayong adlaw!";
        $pct = $maxScore > 0 ? round(($score / $maxScore) * 100) : 100;
        $starCount = ($pct >= 90) ? '⭐⭐⭐' : (($pct >= 75) ? '⭐⭐' : '⭐');
        $msg = "SignED Alert: {$parentSalutation} Nahuman na ni {$childName} ang activity nga '{$activityTitle}'! Marka: {$score}/{$maxScore} {$starCount}. Susiha ang Parent Portal para sa tibuok detalye.";

        return $this->send($phone, $msg, $parentName, 'activity_completed');
    }

    /**
     * Trigger 4: Parent Invitation to Claim Account
     */
    public function notifyInvite(string $phone, ?string $parentName, string $childName, string $claimUrl): array {
        $parentSalutation = $parentName ? "Maayong adlaw, {$parentName}!" : "Maayong adlaw!";
        $msg = "SignED Invitation: {$parentSalutation} Gi-imbitar kamo sa SPED Teacher nga i-connect ang inyong account kang {$childName} sa SignED LMS. I-click kini nga link aron ma-activate: {$claimUrl}";

        return $this->send($phone, $msg, $parentName, 'parent_invite');
    }

    /**
     * Get recent SMS logs (for admin/teacher diagnostic)
     */
    public function getRecentLogs(int $limit = 20): array {
        try {
            $stmt = $this->db->prepare("SELECT * FROM sms_logs ORDER BY id DESC LIMIT :lim");
            $stmt->bindValue(':lim', $limit, PDO::PARAM_INT);
            $stmt->execute();
            return $stmt->fetchAll(PDO::FETCH_ASSOC);
        } catch (Throwable $e) {
            return [];
        }
    }

    private function logMessage(string $phone, ?string $name, string $msg, string $type, string $status, ?string $rawResp = null): void {
        try {
            $stmt = $this->db->prepare("
                INSERT INTO sms_logs 
                    (recipient_phone, recipient_name, message, notification_type, status, gateway_response, created_at)
                VALUES 
                    (:p, :n, :m, :t, :s, :r, NOW())
            ");
            $stmt->execute([
                'p' => $phone,
                'n' => $name,
                'm' => $msg,
                't' => $type,
                's' => $status,
                'r' => $rawResp
            ]);
        } catch (Throwable $e) {
            error_log("Failed to insert into sms_logs: " . $e->getMessage());
        }
    }
}

<?php
// Part of: SignED — Stage 2 Parent Gate Access Security
// Last modified: 2026-08-21

require_once __DIR__ . '/../../config/db.php';

class ParentGateMiddleware {

    /**
     * Check if parent has a verified child enrollment submission
     */
    public static function check(): void {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }

        if (empty($_SESSION['user_id'])) {
            $basePath = defined('BASE_PATH') ? BASE_PATH : '';
            header('Location: ' . $basePath . '/login');
            exit;
        }

        $role = $_SESSION['role'] ?? '';
        if ($role !== 'parent') {
            return; // Gate applies specifically to parent role
        }

        $userId = (int) $_SESSION['user_id'];
        $basePath = defined('BASE_PATH') ? BASE_PATH : '';

        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("
                SELECT status 
                FROM enrollment_submissions 
                WHERE parent_id = :parent_id 
                ORDER BY updated_at DESC 
                LIMIT 1
            ");
            $stmt->execute(['parent_id' => $userId]);
            $submission = $stmt->fetch(PDO::FETCH_ASSOC);

            if (!$submission || $submission['status'] !== 'verified') {
                $_SESSION['error'] = 'Parent Gate Notice: Your child\'s enrollment is currently being reviewed. Access to progress reports and IEP features will open once verification is complete.';
                header('Location: ' . $basePath . '/enrollment/status');
                exit;
            }
        } catch (\Throwable $e) {
            error_log('ParentGateMiddleware error: ' . $e->getMessage());
        }
    }
}

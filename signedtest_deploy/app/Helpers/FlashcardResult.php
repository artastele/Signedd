<?php
// app/Helpers/FlashcardResult.php
// Created for SPED LMS Activity System Overhaul
// Provides methods to compute study retention rates for flashcards (scoring).

require_once __DIR__ . '/../../config/db.php';

class FlashcardResult {
    /**
     * Get active study retention percentage per learner.
     * Calculated as: (sum of confidence scores) / (count of cards * 2) * 100
     *
     * @param int $submission_id
     * @return float
     */
    public static function getRetentionRate(int $submission_id): float {
        try {
            $db = Database::getInstance()->getConnection();
            $stmt = $db->prepare("SELECT flashcard_results FROM lms_submissions WHERE id = :id LIMIT 1");
            $stmt->execute(['id' => $submission_id]);
            $resultsJson = $stmt->fetchColumn();
            
            if (!$resultsJson) {
                return 0.0;
            }
            
            $results = json_decode($resultsJson, true);
            if (!is_array($results) || empty($results)) {
                return 0.0;
            }
            
            $sumConfidence = 0;
            $count = count($results);
            foreach ($results as $card) {
                $sumConfidence += isset($card['confidence']) ? (int)$card['confidence'] : 0;
            }
            
            if ($count === 0) {
                return 0.0;
            }
            
            return round(($sumConfidence / ($count * 2)) * 100.0, 1);
        } catch (\Throwable $e) {
            error_log("FlashcardResult::getRetentionRate error: " . $e->getMessage());
            return 0.0;
        }
    }
}

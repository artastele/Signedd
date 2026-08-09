<?php

class StudentDisplayHelper {
    /**
     * Format internal Student ID for display (YYYYNNNN).
     */
    public static function formatStudentId(?string $studentId): string {
        $id = trim((string)($studentId ?? ''));
        return $id !== '' ? $id : '—';
    }

    /**
     * Format DepEd LRN for display views.
     */
    public static function formatDepEdLrn(?string $lrn): string {
        $value = trim((string)($lrn ?? ''));
        return $value !== '' ? $value : 'Not yet assigned';
    }

    /**
     * Value for HTML attribute / form field (empty string when unset).
     */
    public static function lrnFieldValue(?string $lrn): string {
        return trim((string)($lrn ?? ''));
    }
}

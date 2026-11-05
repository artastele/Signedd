<?php
declare(strict_types=1);

/** New-workflow schools require verified payment, then share one capped active-faculty pool. */
final class InstitutionalTeacherSeats {
    public const LIMIT = 10;
    private const ROLES = ['sped_teacher', 'general_teacher', 'master_teacher', 'guidance'];

    public static function enabled(): bool {
        return getenv('SIGNED_FINANCE_ENABLED') === 'true';
    }

    public static function counted(array $user): bool {
        return (int)($user['school_id'] ?? 0) > 0
            && ($user['status'] ?? null) === 'active'
            && in_array($user['role'] ?? null, self::ROLES, true);
    }

    /** Call inside the transaction that grants the role, status or school. */
    public static function assertAvailable(PDO $db, ?array $before, array $after): void {
        if (!self::enabled() || !self::counted($after)) return;
        if ($before && self::counted($before)
            && (int)$before['school_id'] === (int)$after['school_id']) return;

        $schoolId = (int)$after['school_id'];
        $school = $db->prepare('SELECT id FROM schools WHERE id=? FOR UPDATE');
        $school->execute([$schoolId]);
        if (!$school->fetchColumn()) throw new DomainException('School not found.');

        $required = $db->prepare("SELECT a.action FROM finance_audit a JOIN finance_orders o ON o.id=a.order_id WHERE o.school_id=? AND o.kind='institutional' AND a.action IN ('SCHOOL_LICENSE_REQUIRED','SCHOOL_LICENSE_ENROLLED') ORDER BY (a.action='SCHOOL_LICENSE_ENROLLED') DESC LIMIT 1");
        $required->execute([$schoolId]);
        $licenseState = $required->fetchColumn();
        if (!$licenseState) return; // Existing legacy schools retain their original access.
        if ($licenseState === 'SCHOOL_LICENSE_REQUIRED') {
            throw new DomainException('The school license payment must be verified before faculty accounts can be approved or activated.');
        }

        $count = $db->prepare("SELECT COUNT(*) FROM users WHERE school_id=? AND role IN ('sped_teacher','general_teacher','master_teacher','guidance') AND status='active' AND deleted_at IS NULL");
        $count->execute([$schoolId]);
        if ((int)$count->fetchColumn() >= self::LIMIT) {
            throw new DomainException('This school has reached its 10 active faculty accounts. Deactivate an unused SPED Teacher, General Teacher, Master Teacher, or Guidance account before adding another.');
        }
    }
}

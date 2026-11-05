<?php
declare(strict_types=1);
require_once __DIR__ . '/../app/Services/InstitutionalTeacherSeats.php';

final class SeatStatement extends PDOStatement {
    private $answer;
    private array $rows = [];
    public function __construct(callable $answer) { $this->answer = $answer; }
    public function execute(?array $params = null): bool { $this->rows = ($this->answer)($params ?? []); return true; }
    public function fetchColumn(int $column = 0): mixed { return $this->rows ? array_values($this->rows[0])[$column] : false; }
}
final class SeatDatabase extends PDO {
    public ?string $licenseState = null;
    public int $faculty = 0;
    public int $queries = 0;
    public function __construct() {}
    public function prepare(string $query, array $options = []): PDOStatement|false {
        ++$this->queries;
        return new SeatStatement(function(array $params) use ($query): array {
            if (str_contains($query, 'FROM schools')) return [['id' => $params[0]]];
            if (str_contains($query, 'FROM finance_audit')) return $this->licenseState ? [['action' => $this->licenseState]] : [];
            if (str_contains($query, 'COUNT(*) FROM users')) return [['count' => $this->faculty]];
            throw new RuntimeException('Unexpected seat query');
        });
    }
}
$checks = 0;
function seatCheck(bool $condition): void { global $checks; ++$checks; if (!$condition) throw new RuntimeException('Teacher seat assertion failed: ' . $checks); }
function seatDenied(callable $action, string $message): void { try { $action(); } catch (DomainException $e) { seatCheck(str_contains($e->getMessage(), $message)); return; } seatCheck(false); }
$old = getenv('SIGNED_FINANCE_ENABLED');
try {
    $db = new SeatDatabase();
    $new = ['school_id' => 8, 'role' => 'sped_teacher', 'status' => 'active'];
    putenv('SIGNED_FINANCE_ENABLED=false');
    InstitutionalTeacherSeats::assertAvailable($db, null, $new);
    seatCheck($db->queries === 0); // Disabled Finance must not query finance tables.
    putenv('SIGNED_FINANCE_ENABLED=true');
    $db->faculty = 10;
    InstitutionalTeacherSeats::assertAvailable($db, null, $new);
    seatCheck($db->queries === 2); // Legacy schools are unrestricted.
    $db->licenseState = 'SCHOOL_LICENSE_REQUIRED';
    foreach (['sped_teacher', 'general_teacher', 'master_teacher', 'guidance'] as $role) {
        seatDenied(
            fn() => InstitutionalTeacherSeats::assertAvailable($db, null, ['school_id'=>8,'role'=>$role,'status'=>'active']),
            'payment must be verified'
        );
    }
    $db->licenseState = 'SCHOOL_LICENSE_ENROLLED';
    foreach (['sped_teacher', 'general_teacher', 'master_teacher', 'guidance'] as $role) {
        seatDenied(
            fn() => InstitutionalTeacherSeats::assertAvailable($db, null, ['school_id'=>8,'role'=>$role,'status'=>'active']),
            '10 active faculty'
        );
    }
    $db->faculty = 9;
    foreach (['sped_teacher', 'general_teacher', 'master_teacher', 'guidance'] as $role) {
        InstitutionalTeacherSeats::assertAvailable($db, null, ['school_id'=>8,'role'=>$role,'status'=>'active']);
        seatCheck(true);
    }
    $db->faculty = 10;
    InstitutionalTeacherSeats::assertAvailable($db, $new, ['school_id'=>8,'role'=>'general_teacher','status'=>'active']);
    seatCheck(true); // Existing counted faculty may change counted role, even if at the limit.
    seatDenied(fn() => InstitutionalTeacherSeats::assertAvailable($db, ['school_id'=>8,'role'=>'sped_teacher','status'=>'inactive'], $new), '10 active faculty');
    seatDenied(fn() => InstitutionalTeacherSeats::assertAvailable($db, ['school_id'=>7,'role'=>'sped_teacher','status'=>'active'], $new), '10 active faculty');
    foreach (['principal', 'admin', 'parent', 'learner'] as $role) {
        $before = $db->queries;
        InstitutionalTeacherSeats::assertAvailable($db, null, ['school_id'=>8,'role'=>$role,'status'=>'active']);
        seatCheck($db->queries === $before);
    }
    $before = $db->queries;
    InstitutionalTeacherSeats::assertAvailable($db, null, ['school_id'=>8,'role'=>'general_teacher','status'=>'inactive']);
    seatCheck($db->queries === $before);
    echo "Institutional faculty seat checks passed: $checks\n";
} finally {
    $old === false ? putenv('SIGNED_FINANCE_ENABLED') : putenv('SIGNED_FINANCE_ENABLED=' . $old);
}

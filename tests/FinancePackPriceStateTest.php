<?php
declare(strict_types=1);
// In-memory pricing states only; no database connection, provider, or record writes.
require_once __DIR__ . '/../app/Services/FinanceService.php';

final class PackPriceStateStatement extends PDOStatement {
    private array $rows = [];
    private $handler;
    public function __construct(callable $handler) { $this->handler = $handler; }
    public function execute(?array $params = null): bool { $this->rows = ($this->handler)($params ?? []); return true; }
    public function fetch(int $mode = PDO::FETCH_DEFAULT, int $orientation = PDO::FETCH_ORI_NEXT, int $offset = 0): mixed { return $this->rows[0] ?? false; }
    public function fetchColumn(int $column = 0): mixed { return $this->rows ? array_values($this->rows[0])[$column] ?? false : false; }
}
final class PackPriceStateDatabase extends PDO {
    public array $pack = [];
    public function __construct() {}
    public function prepare(string $sql, array $options = []): PDOStatement|false {
        return new PackPriceStateStatement(function (array $params) use ($sql): array {
            if (str_contains($sql, 'FROM users')) return [['id' => 7, 'name' => 'Synthetic buyer', 'role' => 'sped_teacher', 'school_id' => 1, 'status' => 'active']];
            if (str_contains($sql, 'FROM resource_hub_packs')) return [$this->pack];
            if (str_contains($sql, 'FROM finance_entitlements')) return [];
            throw new RuntimeException('Unexpected pricing fixture query');
        });
    }
}

$oldFlag = getenv('SIGNED_FINANCE_ENABLED');
putenv('SIGNED_FINANCE_ENABLED=true');
try {
    $db = new PackPriceStateDatabase();
    $service = new FinanceService($db);
    $db->pack = ['id' => 1, 'created_by' => 9, 'status' => 'published', 'review_status' => 'PENDING', 'approved_amount' => null];
    if ($service->packAccess(1, 7)['label'] !== 'Awaiting review') throw new RuntimeException('Pending proposal must not be free');
    $db->pack['review_status'] = 'APPROVED';
    if ($service->packAccess(1, 7)['label'] !== 'Price unavailable') throw new RuntimeException('Approved row without approved price must not be free');
    $db->pack['approved_amount'] = 0;
    if ($service->packAccess(1, 7)['label'] !== 'Free') throw new RuntimeException('Approved zero price should be free');
    $db->pack['approved_amount'] = 5900;
    if ($service->packAccess(1, 7)['label'] !== 'Requires Purchase') throw new RuntimeException('Approved premium price needs purchase');
    echo "PASS: 4 isolated pack pricing/access states.\n";
} finally {
    if ($oldFlag === false) putenv('SIGNED_FINANCE_ENABLED');
    else putenv('SIGNED_FINANCE_ENABLED=' . $oldFlag);
}

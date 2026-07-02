<?php
require_once __DIR__ . '/../config/db.php';

$db = Database::getInstance()->getConnection();

$steps = [
    "ALTER TABLE student_records ADD COLUMN student_id VARCHAR(20) NULL AFTER enrollment_id",
    "UPDATE student_records sr
     INNER JOIN (
         SELECT id,
                CONCAT(YEAR(created_at), LPAD(ROW_NUMBER() OVER (PARTITION BY YEAR(created_at) ORDER BY created_at, id), 4, '0')) AS sid
         FROM student_records
         WHERE student_id IS NULL OR student_id = ''
     ) x ON sr.id = x.id
     SET sr.student_id = x.sid",
    "ALTER TABLE student_records MODIFY COLUMN lrn VARCHAR(20) NULL",
    "ALTER TABLE student_records MODIFY COLUMN student_id VARCHAR(20) NOT NULL",
    "ALTER TABLE student_records ADD UNIQUE INDEX idx_student_id_unique (student_id)",
    "ALTER TABLE enrollment_submissions MODIFY COLUMN lrn VARCHAR(20) NULL",
    "ALTER TABLE iep_records ADD COLUMN header_student_id VARCHAR(20) NULL AFTER header_learner_age",
    "UPDATE users u
     INNER JOIN student_records sr ON u.email = CONCAT('learner_', sr.lrn, '@spedlms.local')
     SET u.email = CONCAT('learner_', sr.student_id, '@spedlms.local')
     WHERE u.role = 'learner'
       AND sr.lrn IS NOT NULL
       AND sr.lrn != ''
       AND sr.student_id IS NOT NULL",
];

foreach ($steps as $sql) {
    try {
        $db->exec($sql);
        echo "OK: " . substr(preg_replace('/\s+/', ' ', $sql), 0, 80) . "...\n";
    } catch (PDOException $e) {
        echo "SKIP/WARN: " . $e->getMessage() . "\n";
    }
}

$rows = $db->query('SELECT id, student_id, lrn, student_name FROM student_records LIMIT 5')->fetchAll(PDO::FETCH_ASSOC);
echo "\nSample rows:\n";
print_r($rows);

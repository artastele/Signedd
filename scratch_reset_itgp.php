<?php
$host = 'localhost';
$db   = 'sped_lms';
$user = 'root';
$pass = '';

try {
    $pdo = new PDO("mysql:host=$host;dbname=$db;charset=utf8mb4", $user, $pass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);

    // Find student
    $stmt = $pdo->prepare("SELECT id, student_name FROM student_records WHERE student_name LIKE ?");
    $stmt->execute(['%Miljan Ortega%']);
    $student = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$student) {
        echo "Student not found.\n";
        exit;
    }
    echo "Found student: " . $student['student_name'] . " (ID: " . $student['id'] . ")\n";

    // Find active IEP
    $stmt = $pdo->prepare("SELECT id FROM iep_records WHERE student_id = ? ORDER BY id DESC LIMIT 1");
    $stmt->execute([$student['id']]);
    $iep = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$iep) {
        echo "No IEP found for student.\n";
        exit;
    }
    echo "Found IEP ID: " . $iep['id'] . "\n";

    // Reset student status to active
    $stmt = $pdo->prepare("UPDATE student_records SET status = 'active' WHERE id = ?");
    $stmt->execute([$student['id']]);
    echo "Student status reset to 'active'.\n";

    // Delete class_placements record
    $stmt = $pdo->prepare("DELETE FROM class_placements WHERE student_id = ?");
    $stmt->execute([$student['id']]);
    $deletedPlacements = $stmt->rowCount();
    if ($deletedPlacements > 0) {
        echo "Deleted $deletedPlacements class_placements record(s).\n";
    }

    // Delete ITGP record (or reset it)
    $stmt = $pdo->prepare("DELETE FROM itgp_records WHERE student_id = ?");
    $stmt->execute([$student['id']]);
    $deletedCount = $stmt->rowCount();
    
    if ($deletedCount > 0) {
        echo "Deleted $deletedCount ITGP record(s) for this student.\n";
    } else {
        echo "No ITGP record found to delete.\n";
    }

} catch (PDOException $e) {
    echo "Database error: " . $e->getMessage() . "\n";
}

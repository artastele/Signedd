<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    die('Must be logged in as parent');
}

echo "<h2>Direct Enrollment Submission Test</h2>";
echo "<p>This will submit a minimal enrollment directly to test if the controller works.</p>";

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h3>Processing submission...</h3>";
    
    // Redirect to actual controller
    $_POST['enrollment_type'] = 'new';
    $_POST['school_year'] = '2026-2027';
    $_POST['last_name'] = 'TestChild';
    $_POST['first_name'] = 'John';
    $_POST['birth_date'] = '2015-01-01';
    $_POST['sex'] = 'Male';
    $_POST['grade_level_to_enroll'] = 'Grade 1';
    $_POST['signature_data'] = 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==';
    
    // Include the controller
    require_once __DIR__ . '/../app/Controllers/EnrollmentController.php';
    
    try {
        $controller = new EnrollmentController();
        $controller->submit();
    } catch (Exception $e) {
        echo "<p style='color: red;'>ERROR: " . $e->getMessage() . "</p>";
        echo "<pre>" . $e->getTraceAsString() . "</pre>";
    }
    
} else {
    ?>
    <form method="POST">
        <p>Click the button below to submit a test enrollment:</p>
        <button type="submit" class="btn btn-primary">Submit Test Enrollment</button>
    </form>
    
    <hr>
    <p><a href="test-enrollment-submit.php">Check Current Enrollments</a></p>
    <?php
}
?>

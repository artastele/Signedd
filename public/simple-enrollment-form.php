<?php
session_start();

if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'parent') {
    die('Must be logged in as parent');
}

$basePath = '/Signedd/public';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Form Submitted!</h2>";
    echo "<p>Redirecting to controller...</p>";
    
    // Redirect to actual controller
    header('Location: ' . $basePath . '/enrollment/submit');
    exit;
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Enrollment Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
<div class="container mt-5">
    <h1>Simple Enrollment Form (No JavaScript)</h1>
    <p>This form has NO JavaScript - just pure HTML form submission to test if the controller works.</p>
    
    <form method="POST" action="<?php echo $basePath; ?>/enrollment/submit" enctype="multipart/form-data">
        <input type="hidden" name="enrollment_type" value="new">
        <input type="hidden" name="school_year" value="2026-2027">
        
        <div class="card mb-3">
            <div class="card-body">
                <h3>Required Fields Only</h3>
                
                <div class="mb-3">
                    <label class="form-label">Last Name *</label>
                    <input type="text" class="form-control" name="last_name" value="TestChild" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">First Name *</label>
                    <input type="text" class="form-control" name="first_name" value="Simple" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Birth Date *</label>
                    <input type="date" class="form-control" name="birth_date" value="2015-01-01" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Birth Place *</label>
                    <input type="text" class="form-control" name="birth_place" value="Cebu City, Cebu" required>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Sex *</label>
                    <select class="form-control" name="sex" required>
                        <option value="Male">Male</option>
                        <option value="Female">Female</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Grade Level *</label>
                    <select class="form-control" name="grade_level_to_enroll" required>
                        <option value="Grade 1">Grade 1</option>
                        <option value="Grade 2">Grade 2</option>
                        <option value="Grade 3">Grade 3</option>
                    </select>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">PSA Birth Certificate (Required) *</label>
                    <input type="file" class="form-control" name="psa_birth_cert" accept=".pdf,.jpg,.jpeg,.png" required>
                    <small class="text-muted">Upload any image or PDF file for testing</small>
                </div>
                
                <div class="mb-3">
                    <label class="form-label">Signature *</label>
                    <input type="hidden" name="signature_data" value="data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNk+M9QDwADhgGAWjR9awAAAABJRU5ErkJggg==">
                    <div style="border: 2px solid #ccc; padding: 20px; background: #f9f9f9;">
                        <p>✓ Signature included (dummy data for testing)</p>
                    </div>
                </div>
            </div>
        </div>
        
        <button type="submit" class="btn btn-success btn-lg">
            <i class="bi bi-check-circle"></i> Submit Enrollment (Simple Test)
        </button>
        
        <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-secondary btn-lg">Cancel</a>
    </form>
    
    <hr>
    <p><strong>After submitting:</strong></p>
    <ul>
        <li>If you see a 404 error, the route is broken</li>
        <li>If you see errors, there's a validation issue</li>
        <li>If it redirects to /enrollment/status, check if enrollment was created</li>
    </ul>
</div>
</body>
</html>

<?php
// Test page for enrollment utilities (Part B)
$basePath = rtrim(dirname($_SERVER['SCRIPT_NAME']), '/');
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Test Enrollment Utilities - SPED LMS</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    <style>
        .signature-container {
            border: 2px solid #1e4072;
            border-radius: 8px;
            padding: 15px;
            background: #f8f9fa;
        }
        #signaturePad {
            border: 1px solid #ccc;
            border-radius: 4px;
            background: white;
            cursor: crosshair;
            width: 100%;
            max-width: 600px;
            height: 200px;
        }
        .test-section {
            margin-bottom: 40px;
            padding: 20px;
            border: 1px solid #dee2e6;
            border-radius: 8px;
        }
        .test-section h3 {
            color: #1e4072;
            margin-bottom: 20px;
        }
        .form-step {
            display: none;
        }
        .form-step.active {
            display: block;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="mb-4">
            <i class="bi bi-clipboard-check text-primary"></i>
            Test Enrollment Utilities (Part B)
        </h1>

        <!-- Test 1: Location Dropdowns -->
        <div class="test-section">
            <h3><i class="bi bi-geo-alt"></i> Test 1: Location Dropdowns</h3>
            <p class="text-muted">Test dynamic province → city → barangay loading</p>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label class="form-label">Province</label>
                    <select id="current_province" class="form-select">
                        <option value="">Loading...</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">City/Municipality</label>
                    <select id="current_city" class="form-select">
                        <option value="">Select province first</option>
                    </select>
                </div>
                <div class="col-md-4 mb-3">
                    <label class="form-label">Barangay</label>
                    <select id="current_barangay" class="form-select">
                        <option value="">Select city first</option>
                    </select>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <strong>Expected behavior:</strong>
                <ul class="mb-0">
                    <li>Provinces load automatically on page load</li>
                    <li>Selecting a province loads its cities</li>
                    <li>Selecting a city loads its barangays</li>
                    <li>Check browser console for debug logs</li>
                </ul>
            </div>
        </div>

        <!-- Test 2: Signature Pad -->
        <div class="test-section">
            <h3><i class="bi bi-pen"></i> Test 2: Signature Pad</h3>
            <p class="text-muted">Test digital signature capture</p>

            <div class="signature-container">
                <label class="form-label">Parent/Guardian Signature</label>
                <canvas id="signaturePad"></canvas>
                <div class="mt-2">
                    <button type="button" class="btn btn-secondary btn-sm" onclick="clearSignature()">
                        <i class="bi bi-eraser"></i> Clear Signature
                    </button>
                    <button type="button" class="btn btn-primary btn-sm" onclick="testGetSignature()">
                        <i class="bi bi-check-circle"></i> Get Signature Data
                    </button>
                </div>
            </div>

            <div class="alert alert-info mt-3">
                <strong>Expected behavior:</strong>
                <ul class="mb-0">
                    <li>Draw on the canvas with mouse or touch</li>
                    <li>Clear button removes the signature</li>
                    <li>Get Signature Data shows base64 PNG in console</li>
                </ul>
            </div>
        </div>

        <!-- Test 3: Auto-Save -->
        <div class="test-section">
            <h3><i class="bi bi-save"></i> Test 3: Auto-Save</h3>
            <p class="text-muted">Test auto-save functionality (every 30 seconds)</p>

            <form id="enrollmentForm">
                <div class="mb-3">
                    <label class="form-label">Test Field 1</label>
                    <input type="text" class="form-control" name="test_field_1" placeholder="Type something...">
                </div>
                <div class="mb-3">
                    <label class="form-label">Test Field 2</label>
                    <input type="text" class="form-control" name="test_field_2" placeholder="Type something...">
                </div>
                <button type="button" class="btn btn-primary" onclick="manualSave()">
                    <i class="bi bi-save"></i> Manual Save
                </button>
            </form>

            <div class="alert alert-info mt-3">
                <strong>Expected behavior:</strong>
                <ul class="mb-0">
                    <li>Type in the fields above</li>
                    <li>Auto-save triggers every 30 seconds (check console)</li>
                    <li>Manual save button saves immediately</li>
                    <li>Toast notification appears on save</li>
                    <li><strong>Note:</strong> Save will fail (no controller yet) but you'll see the attempt</li>
                </ul>
            </div>
        </div>

        <!-- Test 4: Multi-Step Navigation -->
        <div class="test-section">
            <h3><i class="bi bi-list-ol"></i> Test 4: Multi-Step Form Navigation</h3>
            <p class="text-muted">Test step-by-step form navigation</p>

            <div class="progress mb-4">
                <div class="progress-bar bg-primary" role="progressbar" style="width: 0%">Step 1 of 8</div>
            </div>

            <div class="form-step active" id="step-1">
                <h5>Step 1: Learner Information</h5>
                <p>This is step 1 content...</p>
            </div>

            <div class="form-step" id="step-2">
                <h5>Step 2: Current Address</h5>
                <p>This is step 2 content...</p>
            </div>

            <div class="form-step" id="step-3">
                <h5>Step 3: Permanent Address</h5>
                <p>This is step 3 content...</p>
            </div>

            <div class="form-step" id="step-4">
                <h5>Step 4: Parent/Guardian Info</h5>
                <p>This is step 4 content...</p>
            </div>

            <div class="form-step" id="step-5">
                <h5>Step 5: Previous School</h5>
                <p>This is step 5 content...</p>
            </div>

            <div class="form-step" id="step-6">
                <h5>Step 6: Enrollment Details</h5>
                <p>This is step 6 content...</p>
            </div>

            <div class="form-step" id="step-7">
                <h5>Step 7: Learning Modality</h5>
                <p>This is step 7 content...</p>
            </div>

            <div class="form-step" id="step-8">
                <h5>Step 8: Document Upload</h5>
                <p>This is step 8 content...</p>
            </div>

            <div class="mt-3">
                <button type="button" id="prevBtn" class="btn btn-secondary" onclick="prevStep()">
                    <i class="bi bi-arrow-left"></i> Previous
                </button>
                <button type="button" id="nextBtn" class="btn btn-primary" onclick="nextStep()">
                    Next <i class="bi bi-arrow-right"></i>
                </button>
                <button type="button" id="submitBtn" class="btn btn-success" style="display: none;">
                    <i class="bi bi-check-circle"></i> Submit
                </button>
            </div>

            <div class="alert alert-info mt-3">
                <strong>Expected behavior:</strong>
                <ul class="mb-0">
                    <li>Click Next/Previous to navigate steps</li>
                    <li>Progress bar updates with each step</li>
                    <li>Previous button hidden on step 1</li>
                    <li>Next button hidden on step 8, Submit button appears</li>
                </ul>
            </div>
        </div>

        <!-- Test 5: API Endpoints -->
        <div class="test-section">
            <h3><i class="bi bi-cloud"></i> Test 5: API Endpoints</h3>
            <p class="text-muted">Test location API endpoints directly</p>

            <div class="mb-3">
                <button class="btn btn-outline-primary btn-sm" onclick="testAPI('provinces')">
                    Test /api/locations/provinces
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="testAPI('cities')">
                    Test /api/locations/cities/Cebu
                </button>
                <button class="btn btn-outline-primary btn-sm" onclick="testAPI('barangays')">
                    Test /api/locations/barangays/Cebu/Cebu City
                </button>
            </div>

            <pre id="apiResult" class="bg-light p-3 border rounded" style="max-height: 300px; overflow-y: auto;">
Click a button above to test API endpoints...
            </pre>

            <div class="alert alert-info mt-3">
                <strong>Expected behavior:</strong>
                <ul class="mb-0">
                    <li>Each button calls the API endpoint</li>
                    <li>JSON response appears in the box above</li>
                    <li>Check browser console for details</li>
                </ul>
            </div>
        </div>

        <!-- Console Log -->
        <div class="alert alert-secondary">
            <h5><i class="bi bi-terminal"></i> Console Log</h5>
            <p class="mb-0">
                Open your browser's Developer Tools (F12) and check the Console tab for debug messages.
                You should see initialization messages and API call logs.
            </p>
        </div>

        <div class="text-center mt-5">
            <a href="<?php echo $basePath; ?>/" class="btn btn-secondary">
                <i class="bi bi-arrow-left"></i> Back to Home
            </a>
        </div>
    </div>

    <!-- Signature Pad Library (CDN) -->
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
    
    <!-- Enrollment Utilities -->
    <script src="<?php echo $basePath; ?>/js/enrollment.js"></script>

    <!-- Test Functions -->
    <script>
        function testGetSignature() {
            const data = getSignatureData();
            if (data) {
                console.log('Signature Data (base64 PNG):', data);
                alert('Signature captured! Check console for base64 data.');
            } else {
                alert('Please draw a signature first.');
            }
        }

        function testAPI(type) {
            const basePath = '<?php echo $basePath; ?>';
            let url = '';
            
            switch(type) {
                case 'provinces':
                    url = basePath + '/api/locations/provinces';
                    break;
                case 'cities':
                    url = basePath + '/api/locations/cities/Cebu';
                    break;
                case 'barangays':
                    url = basePath + '/api/locations/barangays/Cebu/Cebu%20City';
                    break;
            }

            document.getElementById('apiResult').textContent = 'Loading...';

            fetch(url)
                .then(response => response.json())
                .then(data => {
                    document.getElementById('apiResult').textContent = JSON.stringify(data, null, 2);
                    console.log('API Response:', data);
                })
                .catch(error => {
                    document.getElementById('apiResult').textContent = 'Error: ' + error.message;
                    console.error('API Error:', error);
                });
        }

        // Override getBasePath for this test page
        function getBasePath() {
            return '<?php echo $basePath; ?>';
        }
    </script>
</body>
</html>

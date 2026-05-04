<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 5
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP P3 Signature Page

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$p3Id = $_GET['id'] ?? null;
$p3Data = $p3Data ?? [];
$iepData = $p3Data['iep_data'] ?? [];
$signatures = $signatures ?? [];
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-pen"></i> Sign IEP P3 Document
                </h1>
                <p class="text-muted mt-2">Review and sign the final IEP document</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Document Status -->
        <div class="card mb-4">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <p><strong>Document Status:</strong> 
                            <span class="badge" style="background-color: <?php echo $p3Data['status'] === 'signed_approved' ? '#3b6d11' : '#ffc107'; ?>">
                                <?php echo ucfirst(str_replace('_', ' ', $p3Data['status'] ?? 'draft')); ?>
                            </span>
                        </p>
                    </div>
                    <div class="col-md-6">
                        <p><strong>Created:</strong> <?php echo date('M d, Y H:i', strtotime($p3Data['created_at'] ?? 'now')); ?></p>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signature Status -->
        <div class="card mb-4">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0">Signature Status</h5>
            </div>
            <div class="card-body">
                <div class="row">
                    <?php
                    $requiredSigners = ['parent', 'guidance', 'principal', 'school_head', 'ilrc_supervisor'];
                    foreach ($requiredSigners as $signer):
                        $signed = isset($signatures[$signer]) && !empty($signatures[$signer]['signature_data']);
                        $statusColor = $signed ? '#3b6d11' : '#ffc107';
                        $statusText = $signed ? 'Signed' : 'Pending';
                        $icon = $signed ? 'fa-check-circle' : 'fa-clock';
                    ?>
                        <div class="col-md-6 mb-3">
                            <div class="p-3" style="border-left: 4px solid <?php echo $statusColor; ?>; background-color: #f9f9f9; border-radius: 6px;">
                                <p class="mb-1"><strong><?php echo ucfirst($signer); ?></strong></p>
                                <p class="mb-0">
                                    <i class="fas <?php echo $icon; ?>" style="color: <?php echo $statusColor; ?>;"></i>
                                    <span style="color: <?php echo $statusColor; ?>;"><?php echo $statusText; ?></span>
                                </p>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>

        <!-- P3 Document Content -->
        <div class="card mb-4">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0">P3 Document Content</h5>
            </div>
            <div class="card-body">
                <!-- Section 1: Student Information -->
                <div class="mb-4">
                    <h6 style="color: #1e4072; font-weight: bold;">Section 1: Student Information</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Student Name</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo htmlspecialchars($iepData['student_name'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">LRN</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo htmlspecialchars($iepData['lrn'] ?? 'N/A'); ?>
                            </div>
                        </div>
                    </div>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Grade Level</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo htmlspecialchars($iepData['grade_level'] ?? 'N/A'); ?>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">School Year</label>
                            <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                                <?php echo htmlspecialchars($iepData['school_year'] ?? 'N/A'); ?>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Section 2: Present Level of Performance -->
                <div class="mb-4">
                    <h6 style="color: #1e4072; font-weight: bold;">Section 2: Present Level of Performance</h6>
                    <div class="mb-3">
                        <label class="form-label">Academic Performance</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['academic_performance'] ?? 'N/A')); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Behavioral Performance</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['behavioral_performance'] ?? 'N/A')); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Social Performance</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['social_performance'] ?? 'N/A')); ?>
                        </div>
                    </div>
                </div>

                <!-- Section 3: Annual Goals & Objectives -->
                <div class="mb-4">
                    <h6 style="color: #1e4072; font-weight: bold;">Section 3: Annual Goals & Objectives</h6>
                    <div class="mb-3">
                        <label class="form-label">Goal 1</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['goal_1'] ?? 'N/A')); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Goal 2</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['goal_2'] ?? 'N/A')); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Goal 3</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['goal_3'] ?? 'N/A')); ?>
                        </div>
                    </div>
                </div>

                <!-- Section 4: Special Education Services -->
                <div class="mb-4">
                    <h6 style="color: #1e4072; font-weight: bold;">Section 4: Special Education Services</h6>
                    <div class="mb-3">
                        <label class="form-label">Services to be Provided</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['services_provided'] ?? 'N/A')); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Frequency & Duration</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['frequency_duration'] ?? 'N/A')); ?>
                        </div>
                    </div>
                </div>

                <!-- Section 5: Accommodations & Modifications -->
                <div class="mb-4">
                    <h6 style="color: #1e4072; font-weight: bold;">Section 5: Accommodations & Modifications</h6>
                    <div class="mb-3">
                        <label class="form-label">Accommodations</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['accommodations'] ?? 'N/A')); ?>
                        </div>
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Modifications</label>
                        <div class="p-3" style="background-color: #f9f9f9; border-radius: 6px;">
                            <?php echo nl2br(htmlspecialchars($iepData['modifications'] ?? 'N/A')); ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Signature Section -->
        <div class="card mb-4">
            <div class="card-header" style="background-color: #1e4072; color: white;">
                <h5 class="mb-0">Your Signature</h5>
            </div>
            <div class="card-body">
                <form id="signForm" method="POST" action="<?php echo $basePath; ?>/iep/p3/sign-submit">
                    <input type="hidden" name="p3_id" value="<?php echo htmlspecialchars($p3Id); ?>">

                    <div class="mb-3">
                        <label class="form-label">Remarks</label>
                        <textarea class="form-control" name="remarks" rows="3" placeholder="Add any remarks or comments..."></textarea>
                    </div>

                    <div class="mb-3">
                        <label class="form-label">Signature</label>
                        <div id="signaturePad" style="border: 2px solid #1e4072; border-radius: 6px; background-color: #f9f9f9; height: 200px; cursor: crosshair;"></div>
                        <small class="text-muted">Draw your signature above</small>
                        <input type="hidden" name="signature_data" id="signatureData">
                    </div>

                    <div class="row">
                        <div class="col-md-6">
                            <button type="button" class="btn btn-outline-secondary w-100" id="clearSignature">
                                <i class="fas fa-redo"></i> Clear Signature
                            </button>
                        </div>
                        <div class="col-md-6">
                            <button type="submit" class="btn w-100" style="background-color: #a01422; color: white;">
                                <i class="fas fa-check"></i> Submit Signature
                            </button>
                        </div>
                    </div>
                </form>
            </div>
        </div>

        <!-- Action Buttons -->
        <div class="card">
            <div class="card-body">
                <div class="row">
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="window.print()">
                            <i class="fas fa-print"></i> Print Document
                        </button>
                    </div>
                    <div class="col-md-6">
                        <button type="button" class="btn btn-outline-secondary w-100" onclick="history.back()">
                            <i class="fas fa-arrow-left"></i> Back
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Signature Pad Library -->
<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>

<script>
// Initialize Signature Pad
const canvas = document.getElementById('signaturePad');
const signaturePad = new SignaturePad(canvas, {
    backgroundColor: 'rgb(249, 249, 249)',
    penColor: '#1e4072'
});

// Resize canvas to fit container
function resizeCanvas() {
    const ratio = Math.max(window.devicePixelRatio || 1, 1);
    canvas.width = canvas.offsetWidth * ratio;
    canvas.height = canvas.offsetHeight * ratio;
    canvas.getContext('2d').scale(ratio, ratio);
}

resizeCanvas();
window.addEventListener('resize', resizeCanvas);

// Clear signature
document.getElementById('clearSignature').addEventListener('click', function() {
    signaturePad.clear();
});

// Form submission
document.getElementById('signForm').addEventListener('submit', function(e) {
    e.preventDefault();
    
    if (signaturePad.isEmpty()) {
        showAlert('error', 'Please provide your signature');
        return;
    }
    
    // Get signature data
    document.getElementById('signatureData').value = signaturePad.toDataURL();
    
    const formData = new FormData(this);
    
    fetch('<?php echo $basePath; ?>/iep/p3/sign-submit', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            showAlert('success', 'Signature submitted successfully!');
            setTimeout(() => {
                window.location.href = '<?php echo $basePath; ?>/iep/p3/sign';
            }, 1500);
        } else {
            showAlert('error', data.message || 'Error submitting signature');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showAlert('error', 'Error submitting signature');
    });
});

function showAlert(type, message) {
    const alertContainer = document.getElementById('alertContainer');
    const alertClass = type === 'success' ? 'alert-success' : 'alert-danger';
    const icon = type === 'success' ? 'fa-check-circle' : 'fa-exclamation-circle';
    
    alertContainer.innerHTML = `
        <div class="alert ${alertClass} alert-dismissible fade show" role="alert">
            <i class="fas ${icon}"></i> ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    `;
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

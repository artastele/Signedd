<?php
$pageTitle = 'Select Role - SignED';
require_once __DIR__ . '/../layouts/header.php';

$type = $_GET['type'] ?? null;
$oldRole = $_SESSION['old_role'] ?? '';
$oldEmployeeNumber = $_SESSION['old_employee_number'] ?? '';
unset($_SESSION['old_role'], $_SESSION['old_employee_number']);
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex align-items-center justify-content-between mb-4 flex-wrap gap-2">
        <h1 class="mb-0">
            <?php if ($type === 'parent'): ?>
                <i class="bi bi-heart-fill text-danger me-2"></i>Enroll Your Child
            <?php elseif ($type === 'principal'): ?>
                <i class="bi bi-building-fill text-primary me-2"></i>Register New SPED School
            <?php else: ?>
                <i class="bi bi-person-badge-fill text-success me-2"></i>Apply as School Faculty / Staff
            <?php endif; ?>
        </h1>
        <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-outline-secondary fw-bold px-3">
            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
        </a>
    </div>

    <?php if (isset($pendingRequest) && $pendingRequest): ?>
        <!-- Pending Request Alert -->
        <div class="alert alert-warning" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-clock-history"></i> Application Pending
            </h5>
            <p>
                You have a pending application for <strong><?php echo ucwords(str_replace('_', ' ', $pendingRequest['requested_role'])); ?></strong>.
            </p>
            <p class="mb-0">
                <small>Submitted: <?php echo date('F j, Y g:i A', strtotime($pendingRequest['created_at'])); ?></small>
            </p>
            <hr>
            <p class="mb-0">
                <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-sm btn-warning">
                    <i class="bi bi-arrow-left"></i> Back to Dashboard
                </a>
            </p>
        </div>

    <?php elseif ($type === 'parent'): ?>
        <!-- Parent Role & Target School Selection Form -->
        <?php
        require_once __DIR__ . '/../../Models/SchoolModel.php';
        if (!isset($schools) || empty($schools)) {
            $selectSchoolModel = new SchoolModel();
            $schools = $selectSchoolModel->getAllSchools();
        }
        ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="text-center mb-4">
                    <div class="d-inline-flex p-3 bg-danger bg-opacity-10 text-danger rounded-circle mb-3">
                        <i class="bi bi-buildings-fill fs-1"></i>
                    </div>
                    <h3 class="fw-bold text-dark mb-1">Welcome, Parent!</h3>
                    <p class="text-muted">Select your target SPED Center / School before proceeding as a Parent.</p>
                </div>

                <?php if (empty($schools)): ?>
                    <div class="alert alert-warning border border-warning shadow-sm">
                        <h5 class="alert-heading fw-bold"><i class="bi bi-exclamation-triangle-fill me-2"></i> No Registered SPED Centers Available Yet</h5>
                        <p class="mb-0 small">Please wait for a School Head/Principal to register their SPED Center in SignED before proceeding with Parent enrollment.</p>
                    </div>
                    <div class="d-grid mt-3">
                        <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-outline-secondary">
                            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                        </a>
                    </div>
                <?php else: ?>
                    <form method="POST" action="<?php echo $basePath; ?>/role/select-parent">
                        <div class="mb-4">
                            <label for="parent_school_select" class="form-label fw-bold text-dark fs-5 mb-2">
                                <i class="bi bi-geo-alt-fill text-danger me-1"></i> 1. Choose Your Target SPED Center / School *
                            </label>
                            <?php $targetGetSchoolId = isset($_GET['school_id']) ? (int)$_GET['school_id'] : 0; ?>
                            <select class="form-select border-primary fw-semibold text-dark shadow-sm" id="parent_school_select" name="school_id" required onchange="switchParentSelectedSchool(this.value)">
                                <option value="">-- Select Target SPED Center --</option>
                                <?php foreach ($schools as $idx => $sch): 
                                    $isSelected = ($targetGetSchoolId > 0) ? ($sch['id'] == $targetGetSchoolId) : ($idx === 0);
                                ?>
                                    <option value="<?php echo $sch['id']; ?>" <?php echo $isSelected ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($sch['school_name']); ?> (DepEd ID: <?php echo htmlspecialchars($sch['school_id']); ?> — <?php echo htmlspecialchars($sch['division'] ?? 'Division Office'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text small">Selecting a school will lock your parent portal to that SPED Center's official guidelines and faculty roster.</div>
                        </div>

                        <!-- Live School Details Preview Card -->
                        <div class="mb-4">
                            <label class="form-label fw-bold text-dark mb-2">
                                <i class="bi bi-info-circle-fill text-primary me-1"></i> 2. Review School Guidelines, Requirements & Pubmat
                            </label>
                            <?php foreach ($schools as $idx => $sch): ?>
                                <?php 
                                $sSy = $sch['enrollment_sy'] ?? '2026-2027';
                                $sStatus = strtoupper($sch['enrollment_status'] ?? 'OPEN');
                                $sBadgeClass = ($sStatus === 'OPEN') ? 'bg-success' : (($sStatus === 'UPCOMING') ? 'bg-warning text-dark' : 'bg-danger');
                                $sLogoUrl = SchoolModel::getSchoolLogoUrl($sch, $basePath);
                                $isCardVisible = ($targetGetSchoolId > 0) ? ($sch['id'] == $targetGetSchoolId) : ($idx === 0);
                                ?>
                                <div class="school-preview-card p-4 bg-light rounded-3 border" id="school-preview-<?php echo $sch['id']; ?>" style="<?php echo $isCardVisible ? 'display: block;' : 'display: none;'; ?>">
                                    <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3 pb-3 border-bottom">
                                        <div class="d-flex align-items-center gap-3">
                                            <div class="p-1 bg-white rounded-circle shadow-sm border" style="width: 70px; height: 70px; flex-shrink: 0;">
                                                <img src="<?php echo htmlspecialchars($sLogoUrl); ?>" alt="School Seal" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                                            </div>
                                            <div>
                                                <h5 class="fw-bold mb-1 text-dark"><?php echo htmlspecialchars($sch['school_name']); ?></h5>
                                                <div class="small text-muted mb-1">
                                                    <span class="badge bg-secondary me-1">DepEd ID: <?php echo htmlspecialchars($sch['school_id']); ?></span>
                                                    <span class="badge bg-info text-dark me-1"><?php echo htmlspecialchars($sch['division'] ?? 'Division'); ?></span>
                                                    <span class="badge bg-light text-dark border"><?php echo htmlspecialchars($sch['region'] ?? 'Region XI'); ?></span>
                                                </div>
                                                <small class="text-secondary d-block"><i class="bi bi-geo-alt-fill text-danger me-1"></i> <?php echo htmlspecialchars($sch['address'] ?? 'Official Address'); ?></small>
                                            </div>
                                        </div>
                                        <span class="badge <?php echo $sBadgeClass; ?> px-3 py-2 fs-6">
                                            <i class="bi bi-calendar-check me-1"></i> SY <?php echo htmlspecialchars($sSy); ?> — <?php echo $sStatus; ?>
                                        </span>
                                    </div>

                                    <div class="row g-3">
                                        <!-- Contact & Timeline -->
                                        <div class="col-md-5">
                                            <div class="p-3 bg-white rounded-3 h-100 border">
                                                <h6 class="fw-bold text-dark mb-2">
                                                    <i class="bi bi-telephone-option-fill me-1 text-primary"></i> Contact Information & Timeline
                                                </h6>
                                                <ul class="list-unstyled mb-0 small">
                                                    <?php if (!empty($sch['contact_email'])): ?>
                                                        <li class="mb-1.5"><i class="bi bi-envelope-fill text-primary me-1"></i> <a href="mailto:<?php echo htmlspecialchars($sch['contact_email']); ?>" class="text-decoration-none fw-semibold"><?php echo htmlspecialchars($sch['contact_email']); ?></a></li>
                                                    <?php endif; ?>
                                                    <?php if (!empty($sch['contact_number'])): ?>
                                                        <li class="mb-1.5"><i class="bi bi-telephone-fill text-success me-1"></i> <span class="fw-semibold text-dark"><?php echo htmlspecialchars($sch['contact_number']); ?></span></li>
                                                    <?php endif; ?>
                                                    <?php if (!empty($sch['facebook_page'])): ?>
                                                        <li class="mb-1.5"><i class="bi bi-facebook text-primary me-1"></i> <a href="<?php echo htmlspecialchars($sch['facebook_page']); ?>" target="_blank" class="text-decoration-none fw-semibold">Official Facebook Page</a></li>
                                                    <?php endif; ?>
                                                </ul>

                                                <?php if (!empty($sch['enrollment_start_date'])): ?>
                                                    <div class="small text-dark border-top pt-2 mt-2">
                                                        <i class="bi bi-clock-history me-1 text-primary"></i> 
                                                        <strong>Enrollment Timeline:</strong><br>
                                                        <span class="text-secondary"><?php echo date('M j, Y', strtotime($sch['enrollment_start_date'])); ?> <?php echo !empty($sch['enrollment_end_date']) ? ' to ' . date('M j, Y', strtotime($sch['enrollment_end_date'])) : ''; ?></span>
                                                    </div>
                                                <?php endif; ?>
                                            </div>
                                        </div>

                                        <!-- Requirements Checklist -->
                                        <div class="col-md-7">
                                            <div class="p-3 bg-white rounded-3 h-100 border">
                                                <h6 class="fw-bold text-dark mb-2">
                                                    <i class="bi bi-file-earmark-check me-1 text-primary"></i> Requirements & Policy Guidelines
                                                </h6>
                                                <?php 
                                                $sGuidelinesStr = $sch['enrollment_guidelines'] ?? "PSA Birth Certificate\nForm 138/SF10 (Report Card)\nMedical / Diagnostic Evaluation Report\nPWD ID (Optional)";
                                                $sGuidelineItems = array_filter(array_map('trim', explode("\n", $sGuidelinesStr)));
                                                if (!empty($sGuidelineItems)):
                                                ?>
                                                    <ul class="list-unstyled mb-0 small">
                                                         <?php foreach ($sGuidelineItems as $sgItem): 
                                                             $isOpt = (bool) preg_match('/\((?:Optional|optional)\)$/i', $sgItem);
                                                             $cleanText = preg_replace('/\s*\((?:Optional|optional)\)$/i', '', preg_replace('/^[\-\*\•\d+\.\s]+/', '', $sgItem));
                                                         ?>
                                                             <li class="d-flex align-items-center gap-2 mb-1.5 text-secondary">
                                                                 <?php if ($isOpt): ?>
                                                                     <i class="bi bi-info-circle text-muted flex-shrink-0"></i>
                                                                     <span><?php echo htmlspecialchars($cleanText); ?></span>
                                                                     <span class="badge bg-secondary-subtle text-secondary border border-secondary-subtle py-0.5 px-1.5" style="font-size: 0.68rem;">Optional</span>
                                                                 <?php else: ?>
                                                                     <i class="bi bi-check-circle-fill text-success flex-shrink-0"></i>
                                                                     <span><?php echo htmlspecialchars($cleanText); ?></span>
                                                                     <span class="badge bg-danger-subtle text-danger border border-danger-subtle py-0.5 px-1.5" style="font-size: 0.68rem;">Required</span>
                                                                 <?php endif; ?>
                                                             </li>
                                                         <?php endforeach; ?>
                                                    </ul>
                                                <?php else: ?>
                                                    <p class="text-muted small mb-0">No official enrollment guidelines have been published yet.</p>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Pubmat Poster -->
                                    <?php if (!empty($sch['pubmat_path'])): ?>
                                        <div class="border-top pt-3 mt-3 text-center">
                                            <h6 class="fw-bold text-start text-dark small mb-2">
                                                <i class="bi bi-image me-1 text-primary"></i> Official School Enrollment Publicity Poster (Pubmat):
                                            </h6>
                                            <a href="<?php echo $basePath . '/' . ltrim($sch['pubmat_path'], '/'); ?>" target="_blank">
                                                <img src="<?php echo $basePath . '/' . ltrim($sch['pubmat_path'], '/'); ?>" alt="Enrollment Pubmat Poster" class="img-fluid rounded border shadow-sm" style="max-height: 380px; object-fit: contain; width: 100%;">
                                            </a>
                                        </div>
                                    <?php endif; ?>

                                    <!-- Card Footer Note -->
                                    <div class="mt-3 pt-3 border-top">
                                        <small class="text-muted"><i class="bi bi-shield-check text-success me-1"></i> Verified DepEd School</small>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                            <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-outline-secondary px-4">
                                <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                            </a>
                            <button type="submit" class="btn btn-primary fw-semibold px-4">
                                <i class="bi bi-person-plus-fill me-1"></i> Enroll Child Now
                            </button>
                        </div>
                    </form>

                    <script>
                    function switchParentSelectedSchool(schoolId) {
                        document.querySelectorAll('.school-preview-card').forEach(function(card) {
                            card.style.display = 'none';
                        });
                        if (schoolId) {
                            const targetCard = document.getElementById('school-preview-' + schoolId);
                            if (targetCard) {
                                targetCard.style.display = 'block';
                            }
                        }
                    }
                    </script>
                <?php endif; ?>
            </div>
        </div>

    <?php elseif ($type === 'principal'): ?>
        <!-- Principal School Registration Form -->
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                        <i class="bi bi-building-fill fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">Register New SPED Center (Principal)</h4>
                        <p class="text-muted small mb-0">Establish your school in SignED and request System Administrator verification.</p>
                    </div>
                </div>

                <!-- Error Messages -->
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <?php
                $userModel = new UserModel();
                $loggedInUser = isset($_SESSION['user_id']) ? $userModel->findById($_SESSION['user_id']) : null;
                $applicantName = $loggedInUser['name'] ?? ($_SESSION['user_name'] ?? '');
                $applicantEmail = $loggedInUser['email'] ?? ($_SESSION['user_email'] ?? '');
                ?>

                <form method="POST" action="<?php echo $basePath; ?>/role/submit-staff" enctype="multipart/form-data">
                    <input type="hidden" name="requested_role" value="principal">
                    <input type="hidden" name="school_mode" value="new">

                    <!-- SECTION 1: PRINCIPAL DETAILS -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-person-badge me-1"></i> Section 1: Principal / School Head Information
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="applicant_name" class="form-label fw-semibold">Principal Full Name</label>
                                <input type="text" class="form-control bg-light" id="applicant_name" value="<?php echo htmlspecialchars($applicantName); ?>" readonly>
                            </div>
                            <div class="col-md-6">
                                <label for="applicant_email" class="form-label fw-semibold">Email Address</label>
                                <input type="email" class="form-control bg-light" id="applicant_email" value="<?php echo htmlspecialchars($applicantEmail); ?>" readonly>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="principal_rank" class="form-label fw-semibold">Principal Position Designation <span class="text-danger">*</span></label>
                                <select class="form-select" id="principal_rank" name="principal_rank" required>
                                    <option value="">-- Select Position --</option>
                                    <option value="Principal IV">Principal IV</option>
                                    <option value="Principal III">Principal III</option>
                                    <option value="Principal II">Principal II</option>
                                    <option value="Principal I">Principal I</option>
                                    <option value="School Head / TIC">School Head / Teacher-In-Charge (TIC)</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="employee_number" class="form-label fw-semibold">DepEd Employee ID / Plantilla Number <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="employee_number" name="employee_number" required placeholder="e.g. Employee ID 1234567" value="<?php echo htmlspecialchars($oldEmployeeNumber); ?>">
                            </div>
                        </div>
                    </div>

                    <hr class="my-4">

                    <!-- SECTION 2: SCHOOL DETAILS -->
                    <div class="mb-4">
                        <h6 class="fw-bold text-primary mb-3">
                            <i class="bi bi-bank me-1"></i> Section 2: School / SPED Center Details
                        </h6>
                        <div class="row g-3 mb-3">
                            <div class="col-md-8">
                                <label for="new_school_name" class="form-label fw-semibold">School / SPED Center Name <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="new_school_name" name="new_school_name" required placeholder="e.g. Pasig SPED Center">
                            </div>
                            <div class="col-md-4">
                                <label for="new_school_code" class="form-label fw-semibold">DepEd School ID Code <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" id="new_school_code" name="new_school_code" required placeholder="e.g. 104821">
                                <div class="form-text small">6-digit official DepEd School ID</div>
                            </div>
                        </div>

                        <div class="row g-3 mb-3">
                            <div class="col-md-6">
                                <label for="new_school_region" class="form-label fw-semibold">DepEd Region <span class="text-danger">*</span></label>
                                <select class="form-select" id="new_school_region" name="new_school_region" required onchange="updateDepedDivisions()">
                                    <option value="" disabled selected>-- Select DepEd Region --</option>
                                    <option value="NCR">National Capital Region (NCR)</option>
                                    <option value="CAR">Cordillera Administrative Region (CAR)</option>
                                    <option value="Region I">Region I – Ilocos Region</option>
                                    <option value="Region II">Region II – Cagayan Valley</option>
                                    <option value="Region III">Region III – Central Luzon</option>
                                    <option value="Region IV-A">Region IV-A – CALABARZON</option>
                                    <option value="Region IV-B">Region IV-B – MIMAROPA</option>
                                    <option value="Region V">Region V – Bicol Region</option>
                                    <option value="Region VI">Region VI – Western Visayas</option>
                                    <option value="Region VII">Region VII – Central Visayas</option>
                                    <option value="Region VIII">Region VIII – Eastern Visayas</option>
                                    <option value="Region IX">Region IX – Zamboanga Peninsula</option>
                                    <option value="Region X">Region X – Northern Mindanao</option>
                                    <option value="Region XI">Region XI – Davao Region</option>
                                    <option value="Region XII">Region XII – SOCCSKSARGEN</option>
                                    <option value="Region XIII">Region XIII – Caraga</option>
                                    <option value="BARMM">BARMM – Bangsamoro Region</option>
                                </select>
                            </div>
                            <div class="col-md-6">
                                <label for="new_school_division" class="form-label fw-semibold">School Division <span class="text-danger">*</span></label>
                                <select class="form-select" id="new_school_division" name="new_school_division" required>
                                    <option value="" disabled selected>-- Select Region First --</option>
                                </select>
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="new_school_address" class="form-label fw-semibold">Complete School Location / Address <span class="text-danger">*</span></label>
                            <input type="text" class="form-control" id="new_school_address" name="new_school_address" required placeholder="Street address, Barangay, City/Municipality, Province">
                        </div>

                        <div class="mb-3">
                            <label for="school_logo" class="form-label fw-semibold">Official School Logo / Seal Image <span class="text-danger">*</span></label>
                            <input type="file" class="form-control" id="school_logo" name="school_logo" accept="image/*" required>
                            <div class="form-text small">Please upload your school's official logo or seal (PNG, JPG, WEBP).</div>
                        </div>

                        <div class="row g-3 mt-2">
                            <div class="col-md-6">
                                <label for="government_id" class="form-label fw-semibold">DepEd ID / Valid Govt ID <span class="text-muted fw-normal">(Optional)</span></label>
                                <input type="file" class="form-control" id="government_id" name="government_id" accept="image/*,application/pdf">
                            </div>
                            <div class="col-md-6">
                                <label for="proof_designation" class="form-label fw-semibold">Proof of Appointment Document <span class="text-muted fw-normal">(Optional)</span></label>
                                <input type="file" class="form-control" id="proof_designation" name="proof_designation" accept="image/*,application/pdf">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                        <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-check-circle me-1"></i> Register School & Submit Verification
                        </button>
                    </div>
                </form>
            </div>
        </div>

    <?php else: ?>
        <!-- School Faculty & Staff Application Form -->
        <?php
        require_once __DIR__ . '/../../Models/SchoolModel.php';
        if (!isset($schools) || empty($schools)) {
            $selectSchoolModel = new SchoolModel();
            $schools = $selectSchoolModel->getAllSchools();
        }
        ?>
        <div class="card border-0 shadow-sm">
            <div class="card-body p-4 p-md-5">
                <div class="d-flex align-items-center mb-4 pb-3 border-bottom">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 me-3 text-primary">
                        <i class="bi bi-person-badge-fill fs-3"></i>
                    </div>
                    <div>
                        <h4 class="fw-bold mb-1 text-dark">Apply for School Faculty / Staff Access</h4>
                        <p class="text-muted small mb-0">Apply to join an existing registered SPED Center's faculty roster.</p>
                    </div>
                </div>

                <!-- Error Messages -->
                <?php if (isset($_SESSION['errors'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="bi bi-exclamation-triangle"></i> <strong>Please fix the following errors:</strong>
                        <ul class="mb-0 mt-2">
                            <?php foreach ($_SESSION['errors'] as $error): ?>
                                <li><?php echo htmlspecialchars($error); ?></li>
                            <?php endforeach; ?>
                        </ul>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['errors']); ?>
                <?php endif; ?>

                <?php
                $userModel = new UserModel();
                $loggedInUser = isset($_SESSION['user_id']) ? $userModel->findById($_SESSION['user_id']) : null;
                $applicantName = $loggedInUser['name'] ?? ($_SESSION['user_name'] ?? '');
                $applicantEmail = $loggedInUser['email'] ?? ($_SESSION['user_email'] ?? '');
                ?>

                <form method="POST" action="<?php echo $basePath; ?>/role/submit-staff" enctype="multipart/form-data">
                    <input type="hidden" name="school_mode" value="existing">

                    <!-- STEP 1: CHOOSE SPED SCHOOL -->
                    <div class="mb-4">
                        <label for="school_id" class="form-label fw-bold text-dark mb-2">
                            1. Choose Your Target SPED School <span class="text-danger">*</span>
                        </label>
                        <?php if (empty($schools)): ?>
                            <div class="alert alert-warning border border-warning py-2 mb-0">
                                <i class="bi bi-exclamation-triangle-fill me-2"></i> No registered schools available yet.
                            </div>
                        <?php else: ?>
                            <select class="form-select border-primary fw-semibold text-dark" id="school_id" name="school_id" required onchange="switchExistingSchoolPreview(this.value)">
                                <option value="">-- Select Registered School --</option>
                                <?php foreach ($schools as $sch): ?>
                                    <option value="<?php echo $sch['id']; ?>">
                                        <?php echo htmlspecialchars($sch['school_name']); ?> (DepEd ID: <?php echo htmlspecialchars($sch['school_id']); ?> — <?php echo htmlspecialchars($sch['division'] ?? 'Division'); ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>

                            <!-- Live School Preview -->
                            <?php foreach ($schools as $sch): ?>
                                <?php $schLogoUrl = SchoolModel::getSchoolLogoUrl($sch, $basePath); ?>
                                <div class="existing-school-preview-card mt-3 p-3 bg-light rounded-3 border" id="existing-school-preview-<?php echo $sch['id']; ?>" style="display: none;">
                                    <div class="d-flex align-items-center gap-3">
                                        <div class="p-1 bg-white rounded-circle shadow-sm border" style="width: 55px; height: 55px; flex-shrink: 0;">
                                            <img src="<?php echo htmlspecialchars($schLogoUrl); ?>" alt="Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                                        </div>
                                        <div>
                                            <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($sch['school_name']); ?></h6>
                                            <small class="text-muted d-block">DepEd ID: <?php echo htmlspecialchars($sch['school_id']); ?> | <?php echo htmlspecialchars($sch['division'] ?? 'Division Office'); ?></small>
                                            <small class="text-secondary"><i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars($sch['address'] ?? 'Registered Address'); ?></small>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- STEP 2: ROLE & RANK (SIDE-BY-SIDE) -->
                    <div class="row g-3 mb-4">
                        <div class="col-md-6">
                            <label for="requested_role" class="form-label fw-bold text-dark mb-2">
                                2. Select Applied Position <span class="text-danger">*</span>
                            </label>
                            <select class="form-select border-primary fw-semibold text-dark" id="requested_role" name="requested_role" required onchange="updateRankOptions(this.value)">
                                <option value="">-- Choose Position --</option>
                                <option value="sped_teacher">SPED Teacher</option>
                                <option value="guidance">Guidance Counselor</option>
                                <option value="master_teacher">Master Teacher</option>
                                <option value="general_teacher">General Teacher</option>
                            </select>
                        </div>

                        <div class="col-md-6" id="rank_col">
                            <label for="position_rank" class="form-label fw-bold text-dark mb-2">
                                Rank / Salary Grade <span class="text-danger">*</span>
                            </label>
                            <select class="form-select fw-semibold text-dark" id="position_rank" name="position_rank" disabled>
                                <option value="">-- Select position first --</option>
                            </select>
                        </div>
                    </div>

                    <!-- STEP 3: APPLICANT DETAILS & DOCUMENTS -->
                    <div class="mb-4">
                        <label class="form-label fw-bold text-dark mb-2">
                            3. Applicant Information & Identification
                        </label>
                        <div class="p-3 bg-light rounded-3 border">
                            <div class="row g-3 mb-3">
                                <div class="col-md-6">
                                    <label for="applicant_name" class="form-label fw-semibold">Applicant Name</label>
                                    <input type="text" class="form-control bg-white" id="applicant_name" value="<?php echo htmlspecialchars($applicantName); ?>" readonly>
                                </div>
                                <div class="col-md-6">
                                    <label for="applicant_email" class="form-label fw-semibold">Registered Email</label>
                                    <input type="email" class="form-control bg-white" id="applicant_email" value="<?php echo htmlspecialchars($applicantEmail); ?>" readonly>
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-md-12">
                                    <label for="employee_number" class="form-label fw-semibold">DepEd Employee ID / Plantilla Number <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" id="employee_number" name="employee_number" required placeholder="e.g. Employee ID 1234567" value="<?php echo htmlspecialchars($oldEmployeeNumber); ?>">
                                </div>
                            </div>

                            <div class="row g-3">
                                <div class="col-md-6">
                                    <label for="government_id" class="form-label fw-semibold">DepEd ID / PRC License <span class="text-muted fw-normal">(Optional)</span></label>
                                    <input type="file" class="form-control" id="government_id" name="government_id" accept="image/*,application/pdf">
                                </div>
                                <div class="col-md-6">
                                    <label for="proof_designation" class="form-label fw-semibold">Proof of Appointment <span class="text-muted fw-normal">(Optional)</span></label>
                                    <input type="file" class="form-control" id="proof_designation" name="proof_designation" accept="image/*,application/pdf">
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Submit Buttons -->
                    <div class="d-flex justify-content-end gap-2 mt-4 border-top pt-3">
                        <a href="<?php echo $basePath; ?>/dashboard" class="btn btn-outline-secondary px-4">
                            <i class="bi bi-arrow-left me-1"></i> Back to Dashboard
                        </a>
                        <button type="submit" class="btn btn-primary fw-semibold px-4">
                            <i class="bi bi-send-fill me-1"></i> Submit Access Application
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
        function switchExistingSchoolPreview(schoolId) {
            document.querySelectorAll('.existing-school-preview-card').forEach(function(card) {
                card.style.display = 'none';
            });
            if (schoolId) {
                const previewCard = document.getElementById('existing-school-preview-' + schoolId);
                if (previewCard) {
                    previewCard.style.display = 'block';
                }
            }
        }

        const rankOptions = {
            'sped_teacher': [
                { value: 'sped_teacher_1', label: 'SPED Teacher I' },
                { value: 'sped_teacher_2', label: 'SPED Teacher II' },
                { value: 'sped_teacher_3', label: 'SPED Teacher III' },
            ],
            'guidance': [
                { value: 'guidance_1', label: 'Guidance Counselor I' },
                { value: 'guidance_2', label: 'Guidance Counselor II' },
                { value: 'guidance_3', label: 'Guidance Counselor III' },
            ],
            'master_teacher': [
                { value: 'master_teacher_1', label: 'Master Teacher I' },
                { value: 'master_teacher_2', label: 'Master Teacher II' },
                { value: 'master_teacher_3', label: 'Master Teacher III' },
            ],
            'general_teacher': [
                { value: 'general_teacher_1', label: 'General Teacher I' },
                { value: 'general_teacher_2', label: 'General Teacher II' },
                { value: 'general_teacher_3', label: 'General Teacher III' },
            ],
        };

        function updateRankOptions(role) {
            const rankSelect = document.getElementById('position_rank');
            rankSelect.innerHTML = '<option value="">-- Select Rank --</option>';
            if (role && rankOptions[role]) {
                rankOptions[role].forEach(function(opt) {
                    const option = document.createElement('option');
                    option.value = opt.value;
                    option.textContent = opt.label;
                    rankSelect.appendChild(option);
                });
                rankSelect.disabled = false;
                rankSelect.classList.add('border-primary');
                rankSelect.required = true;
            } else {
                rankSelect.innerHTML = '<option value="">-- Select position first --</option>';
                rankSelect.disabled = true;
                rankSelect.classList.remove('border-primary');
                rankSelect.required = false;
            }
        }
        </script>
    <?php endif; ?>
</div>

<script>
function handleRoleSelectChange(role) {
    const modeNew = document.getElementById('mode_new');
    const modeExisting = document.getElementById('mode_existing');
    if (role === 'principal') {
        modeNew.checked = true;
    } else {
        modeExisting.checked = true;
    }
    handleSchoolModeChange();
}

function handleSchoolModeChange() {
    const isNew = document.getElementById('mode_new').checked;
    const existingBox = document.getElementById('existing_school_box');
    const newBox = document.getElementById('new_school_box');
    if (isNew) {
        existingBox.classList.add('d-none');
        newBox.classList.remove('d-none');
    } else {
        existingBox.classList.remove('d-none');
        newBox.classList.add('d-none');
    }
}

const depedDivisionsMap = {
    'NCR': ['Division of Pasig City', 'Division of Quezon City', 'Division of Manila', 'Division of Makati City', 'Division of Caloocan City', 'Division of Taguig City and Pateros', 'Division of Parañaque City', 'Division of Las Piñas City', 'Division of Muntinlupa City', 'Division of Valenzuela City', 'Division of Marikina City', 'Division of Pasay City', 'Division of Mandaluyong City', 'Division of Malabon City', 'Division of Navotas City', 'Division of San Juan City'],
    'Region XI': ['Division of Davao City', 'Division of Davao del Sur', 'Division of Davao del Norte', 'Division of Davao Oriental', 'Division of Davao de Oro', 'Division of Davao Occidental', 'Division of Tagum City', 'Division of Panabo City', 'Division of Island Garden City of Samal', 'Division of Digos City', 'Division of Mati City'],
    'Region VII': ['Division of Cebu City', 'Division of Cebu Province', 'Division of Mandaue City', 'Division of Lapu-Lapu City', 'Division of Talisay City', 'Division of Bohol', 'Division of Tagbilaran City', 'Division of Negros Oriental', 'Division of Dumaguete City', 'Division of Siquijor', 'Division of Carcar City', 'Division of Danao City', 'Division of Naga City', 'Division of Toledo City', 'Division of Bogo City'],
    'Region III': ['Division of Bulacan', 'Division of Pampanga', 'Division of Angeles City', 'Division of Tarlac', 'Division of Tarlac City', 'Division of Nueva Ecija', 'Division of Cabanatuan City', 'Division of Bataan', 'Division of Olongapo City', 'Division of Zambales', 'Division of Malolos City', 'Division of San Jose del Monte City'],
    'Region IV-A': ['Division of Cavite', 'Division of Cavite City', 'Division of Dasmariñas City', 'Division of Laguna', 'Division of Calamba City', 'Division of Santa Rosa City', 'Division of Batangas', 'Division of Batangas City', 'Division of Lipa City', 'Division of Rizal', 'Division of Antipolo City', 'Division of Quezon', 'Division of Lucena City'],
    'Region VI': ['Division of Iloilo City', 'Division of Iloilo Province', 'Division of Bacolod City', 'Division of Negros Occidental', 'Division of Aklan', 'Division of Capiz', 'Division of Roxas City', 'Division of Antique', 'Division of Guimaras'],
    'Region X': ['Division of Cagayan de Oro City', 'Division of Misamis Oriental', 'Division of Bukidnon', 'Division of Iligan City', 'Division of Gingoog City', 'Division of Malaybalay City', 'Division of Valencia City', 'Division of Misamis Occidental', 'Division of Ozamiz City'],
    'Region XII': ['Division of General Santos City', 'Division of South Cotabato', 'Division of Koronadal City', 'Division of Cotabato', 'Division of Kidapawan City', 'Division of Sultan Kudarat', 'Division of Tacurong City', 'Division of Sarangani'],
    'Region IX': ['Division of Zamboanga City', 'Division of Zamboanga del Sur', 'Division of Pagadian City', 'Division of Zamboanga del Norte', 'Division of Dipolog City', 'Division of Dapitan City', 'Division of Zamboanga Sibugay'],
    'Region VIII': ['Division of Tacloban City', 'Division of Leyte', 'Division of Ormoc City', 'Division of Samar (Western Samar)', 'Division of Catbalogan City', 'Division of Calbayog City', 'Division of Eastern Samar', 'Division of Northern Samar', 'Division of Southern Leyte', 'Division of Biliran'],
    'Region V': ['Division of Albay', 'Division of Legazpi City', 'Division of Ligao City', 'Division of Tabaco City', 'Division of Camarines Sur', 'Division of Naga City', 'Division of Iriga City', 'Division of Camarines Norte', 'Division of Catanduanes', 'Division of Masbate', 'Division of Sorsogon'],
    'Region II': ['Division of Cagayan', 'Division of Tuguegarao City', 'Division of Isabela', 'Division of Cauayan City', 'Division of Santiago City', 'Division of Nueva Vizcaya', 'Division of Quirino', 'Division of Batanes'],
    'Region I': ['Division of Ilocos Norte', 'Division of Laoag City', 'Division of Ilocos Sur', 'Division of Vigan City', 'Division of La Union', 'Division of San Fernando City', 'Division of Pangasinan I', 'Division of Pangasinan II', 'Division of Dagupan City', 'Division of San Carlos City', 'Division of Urdaneta City', 'Division of Alaminos City'],
    'CAR': ['Division of Baguio City', 'Division of Benguet', 'Division of Abra', 'Division of Apayao', 'Division of Ifugao', 'Division of Kalinga', 'Division of Mountain Province', 'Division of Tabuk City'],
    'Region IV-B': ['Division of Palawan', 'Division of Puerto Princesa City', 'Division of Oriental Mindoro', 'Division of Calapan City', 'Division of Occidental Mindoro', 'Division of Marinduque', 'Division of Romblon'],
    'Region XIII': ['Division of Butuan City', 'Division of Agusan del Norte', 'Division of Agusan del Sur', 'Division of Bayugan City', 'Division of Surigao del Norte', 'Division of Surigao City', 'Division of Surigao del Sur', 'Division of Bislig City', 'Division of Tandag City', 'Division of Dinagat Islands'],
    'BARMM': ['Division of Cotabato City', 'Division of Maguindanao del Norte', 'Division of Maguindanao del Sur', 'Division of Lanao del Sur I', 'Division of Lanao del Sur II', 'Division of Marawi City', 'Division of Basilan', 'Division of Lamitan City', 'Division of Sulu', 'Division of Tawi-Tawi']
};

function updateDepedDivisions() {
    const regionSelect = document.getElementById('new_school_region');
    const divisionSelect = document.getElementById('new_school_division');
    if (!regionSelect || !divisionSelect) return;

    const selectedRegion = regionSelect.value;
    divisionSelect.innerHTML = '';

    if (selectedRegion && depedDivisionsMap[selectedRegion]) {
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.disabled = true;
        defaultOpt.selected = true;
        defaultOpt.textContent = '-- Select School Division --';
        divisionSelect.appendChild(defaultOpt);

        depedDivisionsMap[selectedRegion].forEach(div => {
            const opt = document.createElement('option');
            opt.value = div;
            opt.textContent = div;
            divisionSelect.appendChild(opt);
        });
        divisionSelect.removeAttribute('disabled');
    } else {
        const defaultOpt = document.createElement('option');
        defaultOpt.value = '';
        defaultOpt.disabled = true;
        defaultOpt.selected = true;
        defaultOpt.textContent = '-- Select Region First --';
        divisionSelect.appendChild(defaultOpt);
    }
}

document.addEventListener('DOMContentLoaded', function() {
    const regionSelect = document.getElementById('new_school_region');
    if (regionSelect) {
        regionSelect.addEventListener('change', updateDepedDivisions);
        regionSelect.addEventListener('input', updateDepedDivisions);
        if (regionSelect.value) {
            updateDepedDivisions();
        }
    }
});
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

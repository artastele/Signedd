<?php
$pageTitle = 'Principal Dashboard - SignED';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';

// Fetch staff members under Principal's school
require_once __DIR__ . '/../../Models/UserModel.php';
require_once __DIR__ . '/../../Models/RoleRequestModel.php';

$db = Database::getInstance()->getConnection();
$userModel = new UserModel();
$principal = $userModel->findById($_SESSION['user_id']);
$schoolId = $principal['school_id'] ?? null;

$facultyMembers = [];
if ($schoolId) {
    $stmt = $db->prepare("
        SELECT id, name, email, role, created_at 
        FROM users 
        WHERE school_id = :school_id AND role IN ('sped_teacher', 'guidance', 'master_teacher', 'general_teacher')
        ORDER BY name ASC
    ");
    $stmt->execute(['school_id' => $schoolId]);
    $facultyMembers = $stmt->fetchAll(PDO::FETCH_ASSOC);
}

// Count pending staff applications for this school
$pendingStaffCount = 0;
if ($schoolId) {
    $roleReqModel = new RoleRequestModel();
    $pendingStaffRequests = $roleReqModel->getPendingByApproverAndSchool('principal', $schoolId);
    $pendingStaffCount = count($pendingStaffRequests);
}
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">Principal Dashboard</h1>
            <p class="text-muted small mb-0">Manage school faculty, review staff requests, and sign IEPs.</p>
        </div>
        <button class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
            <i class="bi bi-person-plus-fill me-1"></i> Enroll / Register Staff Member
        </button>
    </div>

    <!-- Alert Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo htmlspecialchars($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo htmlspecialchars($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <!-- Official School Profile & Custom Logo Upload Card -->
    <?php
    require_once __DIR__ . '/../../Models/SchoolModel.php';
    $principalSchoolModel = new SchoolModel();
    $mySchool = $schoolId ? $principalSchoolModel->findById($schoolId) : null;
    ?>
    <?php if ($mySchool): ?>
        <?php $myLogoUrl = SchoolModel::getSchoolLogoUrl($mySchool, $basePath); ?>
        <div class="card mb-4 border-0 shadow-sm" style="border-left: 5px solid #a01422 !important; background: #fff;">
            <div class="card-body p-4">
                <div class="row align-items-center g-4">
                    <div class="col-md-2 text-center border-end">
                        <div class="p-2 bg-light rounded-circle d-inline-block shadow-sm mb-2" style="width: 110px; height: 110px;">
                            <img src="<?php echo htmlspecialchars($myLogoUrl); ?>" alt="School Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                        </div>
                        <div class="small fw-bold text-muted">Current School Seal</div>
                    </div>
                    <div class="col-md-10">
                        <h4 class="fw-bold mb-1" style="color: #a01422;"><?php echo htmlspecialchars($mySchool['school_name']); ?></h4>
                        <div class="small text-muted mb-2">
                            <span class="badge bg-secondary me-2">DepEd School ID: <?php echo htmlspecialchars($mySchool['school_id']); ?></span>
                            <span class="badge bg-info text-dark"><?php echo htmlspecialchars($mySchool['division'] ?? 'Division'); ?></span>
                        </div>
                        <p class="small text-secondary mb-0">
                            <i class="bi bi-geo-alt-fill me-1 text-danger"></i> <?php echo htmlspecialchars($mySchool['address'] ?? 'Official Address'); ?>
                        </p>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

<?php if (empty($mySchool['guidelines_published']) && empty($mySchool['enrollment_guidelines'])): ?>
    <div class="alert alert-warning border border-warning shadow-sm mb-4">
        <div class="d-flex align-items-center justify-content-between">
            <div>
                <h5 class="alert-heading fw-bold mb-1 text-dark">
                    <i class="bi bi-exclamation-triangle-fill text-warning me-2"></i> Setup Required: Configure School Profile & Enrollment Details
                </h5>
                <p class="mb-0 text-dark small">
                    Your school profile is registered, but guidelines and contact details have not been published yet. Please set up your official enrollment guidelines, optional contact details (email, phone, Facebook page), and enrollment publicity poster (Pubmat) so parents can view details and enroll.
                </p>
            </div>
            <button type="button" class="btn btn-dark btn-sm fw-bold ms-3 flex-shrink-0" data-bs-toggle="modal" data-bs-target="#manageGuidelinesModal">
                <i class="bi bi-gear-fill me-1"></i> Setup Now
            </button>
        </div>
    </div>
    <?php endif; ?>

    <div class="row mb-4">
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-block mb-3">
                        <i class="bi bi-pen text-primary fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold">IEP Approval</h5>
                    <p class="text-muted small">Review and approve Individualized Education Plans.</p>
                    <a href="<?php echo $basePath; ?>/iep/approval" class="btn btn-outline-primary w-100">Review & Sign</a>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100 <?php echo $pendingStaffCount > 0 ? 'border-warning border' : ''; ?>" style="<?php echo $pendingStaffCount > 0 ? 'border-left: 4px solid #f59e0b !important;' : ''; ?>">
                <div class="card-body text-center p-4 position-relative">
                    <?php if ($pendingStaffCount > 0): ?>
                        <span class="position-absolute top-0 end-0 translate-middle badge rounded-pill bg-danger" style="margin-top: 12px; margin-right: 4px; font-size: 0.8rem; min-width: 24px;">
                            <?php echo $pendingStaffCount; ?>
                        </span>
                    <?php endif; ?>
                    <div class="rounded-circle <?php echo $pendingStaffCount > 0 ? 'bg-warning bg-opacity-15' : 'bg-secondary bg-opacity-10'; ?> p-3 d-inline-block mb-3">
                        <i class="bi bi-person-check <?php echo $pendingStaffCount > 0 ? 'text-warning' : 'text-secondary'; ?> fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold">Pending Staff Requests</h5>
                    <?php if ($pendingStaffCount > 0): ?>
                        <p class="text-warning fw-semibold small mb-3">
                            <i class="bi bi-exclamation-circle-fill me-1"></i>
                            <?php echo $pendingStaffCount; ?> application<?php echo $pendingStaffCount > 1 ? 's' : ''; ?> awaiting approval
                        </p>
                    <?php else: ?>
                        <p class="text-muted small mb-3">No pending applications at this time.</p>
                    <?php endif; ?>
                    <a href="<?php echo $basePath; ?>/principal/staff-requests" class="btn <?php echo $pendingStaffCount > 0 ? 'btn-warning fw-bold' : 'btn-outline-secondary'; ?> w-100">
                        <?php if ($pendingStaffCount > 0): ?>
                            <i class="bi bi-bell-fill me-1"></i> Review Applications
                        <?php else: ?>
                            Staff Applications
                        <?php endif; ?>
                    </a>
                </div>
            </div>
        </div>

        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-block mb-3">
                        <i class="bi bi-people text-success fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold">Register New Staff</h5>
                    <p class="text-muted small">Directly enroll a teacher or counselor to your school.</p>
                    <button type="button" class="btn btn-success w-100" data-bs-toggle="modal" data-bs-target="#addFacultyModal">
                        + Add Staff Member
                    </button>
                </div>
            </div>
        </div>
        
        <div class="col-md-3 mb-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-4">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-3 d-inline-block mb-3">
                        <i class="bi bi-megaphone text-warning fs-1"></i>
                    </div>
                    <h5 class="card-title fw-bold">Enrollment Schedule</h5>
                    <p class="text-muted small">Generate & publish enrollment guidelines & timeline (Process 4).</p>
                    <button type="button" class="btn btn-warning text-dark fw-bold w-100" data-bs-toggle="modal" data-bs-target="#manageGuidelinesModal">
                        <i class="bi bi-gear-fill me-1"></i> Set Guidelines
                    </button>
                </div>
            </div>
        </div>
    </div>

    <!-- School Faculty Roster Table -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
            <h5 class="mb-0 fw-bold text-primary"><i class="bi bi-building me-2"></i>School Faculty & Staff Roster</h5>
            <span class="badge bg-primary rounded-pill"><?php echo count($facultyMembers); ?> Active Staff</span>
        </div>
        <div class="card-body p-0">
            <div class="table-responsive">
                <table class="table table-hover align-middle mb-0">
                    <thead class="table-light">
                        <tr>
                            <th>Staff Name</th>
                            <th>Email Address</th>
                            <th>Designated Role</th>
                            <th>Enrolled Date</th>
                            <th>Status</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (!empty($facultyMembers)): ?>
                            <?php foreach ($facultyMembers as $staff): ?>
                                <tr>
                                    <td class="fw-semibold"><?php echo htmlspecialchars($staff['name']); ?></td>
                                    <td><?php echo htmlspecialchars($staff['email']); ?></td>
                                    <td>
                                        <span class="badge bg-info text-dark">
                                            <?php echo ucwords(str_replace('_', ' ', $staff['role'])); ?>
                                        </span>
                                    </td>
                                    <td><?php echo date('M j, Y', strtotime($staff['created_at'])); ?></td>
                                    <td><span class="badge bg-success">Active</span></td>
                                </tr>
                            <?php endforeach; ?>
                        <?php else: ?>
                            <tr>
                                <td colspan="5" class="text-center text-muted py-4">
                                    No faculty members registered under your school yet. Click <strong>"+ Add Staff Member"</strong> above to enroll staff!
                                </td>
                            </tr>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<!-- Modal: Enroll / Register Staff Member -->
<div class="modal fade" id="addFacultyModal" tabindex="-1" aria-labelledby="addFacultyModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <div class="modal-header bg-primary text-white">
                <h5 class="modal-title" id="addFacultyModalLabel">
                    <i class="bi bi-person-plus-fill me-2"></i> Enroll Staff Member
                </h5>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo $basePath; ?>/principal/register-staff">
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="staff_name" class="form-label fw-semibold">Staff Full Name *</label>
                        <input type="text" class="form-control" id="staff_name" name="name" required placeholder="e.g. Maria Santos">
                    </div>
                    <div class="mb-3">
                        <label for="staff_email" class="form-label fw-semibold">Email Address *</label>
                        <input type="email" class="form-control" id="staff_email" name="email" required placeholder="teacher@school.edu.ph">
                    </div>
                    <div class="mb-3">
                        <label for="staff_role" class="form-label fw-semibold">Designated Staff Role *</label>
                        <select class="form-select" id="staff_role" name="role" required>
                            <option value="sped_teacher">SPED Teacher</option>
                            <option value="guidance">Guidance Counselor</option>
                            <option value="master_teacher">Master Teacher</option>
                            <option value="general_teacher">General Education Teacher</option>
                        </select>
                    </div>
                    <div class="mb-3">
                        <label for="employee_number" class="form-label fw-semibold">Employee / DepEd Number (Optional)</label>
                        <input type="text" class="form-control" id="employee_number" name="employee_number" placeholder="e.g. 104821-T1">
                    </div>
                    <div class="mb-3">
                        <label for="staff_password" class="form-label fw-semibold">Initial Password *</label>
                        <input type="text" class="form-control" id="staff_password" name="password" value="Teacher123!" required>
                        <div class="form-text">Share this password with the staff member for their initial login.</div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-primary">Enroll Faculty Member</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal 2: Set & Publish Enrollment Guidelines & Schedule (Process 4) -->
<?php
require_once __DIR__ . '/../../Models/SystemSettingsModel.php';
$sysModelObj = new SystemSettingsModel();
$currSettings = $sysModelObj->getEnrollmentSettings();
?>
<div class="modal fade" id="manageGuidelinesModal" tabindex="-1" aria-labelledby="manageGuidelinesModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg">
        <div class="modal-content">
            <div class="modal-header bg-warning text-dark">
                <h5 class="modal-title fw-bold" id="manageGuidelinesModalLabel">
                    <i class="bi bi-megaphone-fill me-2"></i> Generate & Publish Enrollment Guidelines & Schedule (Process 4)
                </h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form method="POST" action="<?php echo $basePath; ?>/principal/enrollment-settings" enctype="multipart/form-data">
                <div class="modal-body">
                    <div class="alert alert-info border-0 mb-3" style="background-color: #f0f7ff;">
                        <i class="bi bi-info-circle-fill me-2"></i>
                        <strong>Principal Authority:</strong> Establish your school's enrollment period, guidelines, publicity poster (Pubmat), and contact details for parents.
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="enrollment_sy" class="form-label fw-semibold">Active School Year *</label>
                            <input type="text" class="form-control" id="enrollment_sy" name="enrollment_sy" value="<?php echo htmlspecialchars($currSettings['sy']); ?>" required placeholder="e.g. 2026-2027">
                        </div>
                        <div class="col-md-6">
                            <label for="enrollment_status" class="form-label fw-semibold">Enrollment Status *</label>
                            <select class="form-select" id="enrollment_status" name="enrollment_status" required>
                                <option value="open" <?php echo $currSettings['status'] === 'open' ? 'selected' : ''; ?>>Open</option>
                                <option value="upcoming" <?php echo $currSettings['status'] === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                                <option value="closed" <?php echo $currSettings['status'] === 'closed' ? 'selected' : ''; ?>>Closed</option>
                            </select>
                        </div>
                    </div>

                    <div class="row g-3 mb-3">
                        <div class="col-md-6">
                            <label for="enrollment_start_date" class="form-label fw-semibold">Enrollment Start Date *</label>
                            <input type="date" class="form-control" id="enrollment_start_date" name="enrollment_start_date" value="<?php echo htmlspecialchars($currSettings['start_date']); ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label for="enrollment_end_date" class="form-label fw-semibold">Enrollment End Date (Deadline) *</label>
                            <input type="date" class="form-control" id="enrollment_end_date" name="enrollment_end_date" value="<?php echo htmlspecialchars($currSettings['end_date']); ?>" required>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label class="form-label fw-semibold text-dark mb-1">Requirements Checklist & Policy Guidelines *</label>
                        <div class="form-text small mb-2">Add each required document or policy item one by one. Parents will see this as an official bulleted checklist.</div>
                        
                        <div id="checklist_items_wrapper" class="d-flex flex-column gap-2">
                            <?php 
                            $rawGuidelines = $currSettings['guidelines'] ?? "PSA Birth Certificate\nForm 138/SF10 (Report Card)\nMedical / Diagnostic Evaluation Report\nPWD ID (Optional)";
                            $guidelinesList = array_filter(array_map('trim', explode("\n", $rawGuidelines)));
                            if (empty($guidelinesList)) {
                                $guidelinesList = ["PSA Birth Certificate", "Form 138/SF10 (Report Card)", "Medical / Diagnostic Evaluation Report", "PWD ID (Optional)"];
                            }
                            foreach ($guidelinesList as $index => $gItem):
                                $isOptional = (bool) preg_match('/\((?:Optional|optional)\)$/i', $gItem);
                                $cleanItem = preg_replace('/\s*\((?:Optional|optional)\)$/i', '', preg_replace('/^[\-\*\•\d+\.\s]+/', '', $gItem));
                            ?>
                                <div class="input-group input-group-sm checklist-item-row">
                                    <span class="input-group-text bg-light text-success fw-bold"><i class="bi bi-check2-square"></i></span>
                                    <input type="text" name="checklist_items[]" class="form-control" value="<?php echo htmlspecialchars($cleanItem); ?>" placeholder="e.g. PSA Birth Certificate" required>
                                    <div class="input-group-text bg-white px-2">
                                        <div class="form-check mb-0 d-flex align-items-center gap-1">
                                            <input type="checkbox" class="form-check-input mt-0 optional-checkbox" <?php echo $isOptional ? 'checked' : ''; ?> onchange="syncOptionalHidden(this)">
                                            <input type="hidden" name="checklist_is_optional[]" value="<?php echo $isOptional ? '1' : '0'; ?>" class="optional-hidden">
                                            <label class="form-check-label small text-secondary text-nowrap user-select-none" style="cursor: pointer;">Optional</label>
                                        </div>
                                    </div>
                                    <button type="button" class="btn btn-outline-danger" onclick="removeChecklistItem(this)"><i class="bi bi-trash"></i></button>
                                </div>
                            <?php endforeach; ?>
                        </div>

                        <button type="button" class="btn btn-sm btn-outline-primary mt-2 fw-semibold" onclick="addChecklistItem()">
                            <i class="bi bi-plus-circle-fill me-1"></i> Add Requirement Item
                        </button>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-image me-1 text-primary"></i> Optional: Enrollment Pubmat (Publicity Poster)
                    </h6>
                    <div class="mb-3">
                        <label for="pubmat_image" class="form-label fw-semibold text-dark mb-1">Enrollment Publicity Poster / Pubmat Image <span class="text-muted fw-normal">(Optional)</span></label>
                        <input type="file" class="form-control" id="pubmat_image" name="pubmat_image" accept="image/*">
                        <div class="form-text small">Upload an announcement poster or publicity banner (PNG, JPG, WEBP). This poster will be displayed to parents when enrolling in your school.</div>
                        <?php if (!empty($mySchool['pubmat_path'])): ?>
                            <div class="mt-2 p-2 bg-light rounded border d-flex align-items-center gap-2">
                                <img src="<?php echo $basePath . '/' . ltrim($mySchool['pubmat_path'], '/'); ?>" alt="Current Pubmat Poster" style="height: 60px; object-fit: cover; border-radius: 4px;">
                                <span class="small text-success fw-semibold"><i class="bi bi-check-circle-fill me-1"></i> Current Pubmat Poster Uploaded</span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <hr class="my-3">
                    <h6 class="fw-bold text-dark mb-3">
                        <i class="bi bi-telephone-option me-1 text-primary"></i> Optional: School Contact Details
                    </h6>
                    <div class="row g-3">
                        <div class="col-md-4">
                            <label for="contact_email" class="form-label fw-semibold text-dark text-nowrap mb-1">School Contact Email <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="email" class="form-control" id="contact_email" name="contact_email" value="<?php echo htmlspecialchars($mySchool['contact_email'] ?? ''); ?>" placeholder="e.g. info@pces.edu.ph">
                        </div>
                        <div class="col-md-4">
                            <label for="contact_number" class="form-label fw-semibold text-dark text-nowrap mb-1">Contact / Phone Number <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="text" class="form-control" id="contact_number" name="contact_number" value="<?php echo htmlspecialchars($mySchool['contact_number'] ?? ''); ?>" placeholder="e.g. (082) 298-1234">
                        </div>
                        <div class="col-md-4">
                            <label for="facebook_page" class="form-label fw-semibold text-dark text-nowrap mb-1">Facebook Page Link <span class="text-muted fw-normal">(Optional)</span></label>
                            <input type="url" class="form-control" id="facebook_page" name="facebook_page" value="<?php echo htmlspecialchars($mySchool['facebook_page'] ?? ''); ?>" placeholder="e.g. https://facebook.com/piedad">
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                    <button type="submit" class="btn btn-warning text-dark fw-bold">
                        <i class="bi bi-send-check-fill me-1"></i> Save & Publish Guidelines
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
function syncOptionalHidden(chk) {
    const hidden = chk.closest('.form-check').querySelector('.optional-hidden');
    if (hidden) {
        hidden.value = chk.checked ? '1' : '0';
    }
}

function addChecklistItem() {
    const wrapper = document.getElementById('checklist_items_wrapper');
    const row = document.createElement('div');
    row.className = 'input-group input-group-sm checklist-item-row';
    row.innerHTML = `
        <span class="input-group-text bg-light text-success fw-bold"><i class="bi bi-check2-square"></i></span>
        <input type="text" name="checklist_items[]" class="form-control" placeholder="e.g. Medical Evaluation Report" required>
        <div class="input-group-text bg-white px-2">
            <div class="form-check mb-0 d-flex align-items-center gap-1">
                <input type="checkbox" class="form-check-input mt-0 optional-checkbox" onchange="syncOptionalHidden(this)">
                <input type="hidden" name="checklist_is_optional[]" value="0" class="optional-hidden">
                <label class="form-check-label small text-secondary text-nowrap user-select-none" style="cursor: pointer;">Optional</label>
            </div>
        </div>
        <button type="button" class="btn btn-outline-danger" onclick="removeChecklistItem(this)"><i class="bi bi-trash"></i></button>
    `;
    wrapper.appendChild(row);
    row.querySelector('input[type="text"]').focus();
}

function removeChecklistItem(btn) {
    const wrapper = document.getElementById('checklist_items_wrapper');
    if (wrapper.querySelectorAll('.checklist-item-row').length > 1) {
        btn.closest('.checklist-item-row').remove();
    } else {
        alert('You must have at least one requirement item in your checklist.');
    }
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

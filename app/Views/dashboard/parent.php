<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1 Part E
// Last modified: 2026-05-02
// Part of: SignED — Parent Dashboard with Enrollment Tracking

$pageTitle = 'Parent Dashboard - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">Parent Dashboard</h1>

    <!-- Flash Notifications -->
    <?php if (!empty($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-check-circle-fill me-2 fs-5 text-success"></i>
            <div><?php echo htmlspecialchars($_SESSION['success']); unset($_SESSION['success']); ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <?php if (!empty($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show rounded-3 shadow-sm border-0 d-flex align-items-center mb-4" role="alert">
            <i class="bi bi-exclamation-triangle-fill me-2 fs-5 text-danger"></i>
            <div><?php echo htmlspecialchars($_SESSION['error']); unset($_SESSION['error']); ?></div>
            <button type="button" class="btn-close ms-auto" data-bs-dismiss="alert" aria-label="Close"></button>
        </div>
    <?php endif; ?>

    <!-- Newly Generated Learner Credentials Banner -->
    <?php if (!empty($_SESSION['generated_learner_credentials'])): ?>
        <?php $glc = $_SESSION['generated_learner_credentials']; unset($_SESSION['generated_learner_credentials']); ?>
        <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: linear-gradient(135deg, #1e4072 0%, #0f172a 100%); color: #ffffff;">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3 mb-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-warning text-dark d-flex align-items-center justify-content-center" style="width: 48px; height: 48px; font-size: 1.5rem;">
                            <i class="bi bi-mortarboard-fill"></i>
                        </div>
                        <div>
                            <span class="badge bg-warning text-dark fw-bold px-2 py-1 mb-1" style="font-size: 0.72rem;">BAG-ONG NA-LINK NGA ACCOUNT</span>
                            <h5 class="fw-bold mb-0 text-white">SignED LMS Account ni <?php echo htmlspecialchars($glc['student_name']); ?> kay Andam Na!</h5>
                        </div>
                    </div>
                    <span class="badge bg-success px-3 py-2 fs-6">Active &amp; Ready</span>
                </div>
                <p class="text-white-50 small mb-3">
                    Gamita kining mga credentials aron maka-login ang imong anak sa SignED LMS portal sa iyang tablet o computer:
                </p>
                <div class="row g-3">
                    <div class="col-md-5">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);">
                            <small class="text-white-50 d-block" style="font-size: 0.75rem;">USERNAME / STUDENT ID / LRN</small>
                            <span class="fw-bold text-warning fs-5 font-monospace"><?php echo htmlspecialchars($glc['username']); ?></span>
                        </div>
                    </div>
                    <div class="col-md-5">
                        <div class="p-3 rounded-3" style="background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.15);">
                            <small class="text-white-50 d-block" style="font-size: 0.75rem;">DEFAULT PASSWORD</small>
                            <span class="fw-bold text-white fs-5 font-monospace"><?php echo htmlspecialchars($glc['password']); ?></span>
                        </div>
                    </div>
                    <div class="col-md-2 d-flex align-items-center">
                        <button type="button" class="btn btn-warning w-100 py-3 fw-bold" onclick="navigator.clipboard.writeText('Username: <?php echo addslashes($glc['username']); ?>\nPassword: <?php echo addslashes($glc['password']); ?>'); alert('Kopyado ang Login Credentials sa bata!');" style="border-radius: 8px;">
                            <i class="bi bi-clipboard-check me-1"></i> Kopyahin
                        </button>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Permanent Child Learner LMS Accounts & Credentials Section -->
    <?php
    require_once __DIR__ . '/../../Helpers/CSRFHelper.php';
    if (!isset($parentLearners)) {
        require_once __DIR__ . '/../../Models/MasterlistModel.php';
        $pMasterModel = new MasterlistModel();
        $parentLearners = $pMasterModel->getParentLearnerAccounts((int)$_SESSION['user_id']);
    }
    ?>

    <?php if (!empty($parentLearners)): ?>
    <div class="card border-0 shadow-sm rounded-4 mb-4" style="background: #ffffff; border: 1px solid #e2e8f0 !important;">
        <div class="card-header bg-transparent border-0 pt-4 px-4 pb-2 d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-3">
                <div class="rounded-3 d-flex align-items-center justify-content-center" style="width: 44px; height: 44px; background: #e8f0fe; color: #1e4072;">
                    <i class="bi bi-person-badge-fill fs-4"></i>
                </div>
                <div>
                    <h5 class="fw-bold mb-0" style="color: #1e4072; font-size: 1.15rem;">Mga Account sa Akong mga Anak (Learner LMS Accounts)</h5>
                    <p class="text-muted small mb-0" style="font-size: 0.82rem;">
                        Permanenteng listahan sa mga login credentials sa imong anak para sa SignED LMS portal.
                    </p>
                </div>
            </div>
            <span class="badge rounded-pill px-3 py-2" style="background: #f1f5f9; color: #475569; font-weight: 600; font-size: 0.8rem;">
                <i class="bi bi-mortarboard me-1 text-primary"></i> <?php echo count($parentLearners); ?> <?php echo count($parentLearners) === 1 ? 'Ka Estudyante' : 'Ka mga Estudyante'; ?>
            </span>
        </div>

        <div class="card-body p-4 pt-2">
            <div class="row g-3">
                <?php foreach ($parentLearners as $pl): ?>
                <div class="col-lg-6 col-12">
                    <div class="p-3.5 rounded-3 h-100 d-flex flex-column justify-content-between" style="background: #f8fafc; border: 1px solid #e2e8f0; border-left: 4px solid #1e4072; padding: 18px;">
                        <div>
                            <!-- Header: Name & Track -->
                            <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 mb-2">
                                <div>
                                    <h6 class="fw-bold mb-1" style="color: #0f172a; font-size: 1.05rem;">
                                        <?php echo htmlspecialchars($pl['student_name']); ?>
                                    </h6>
                                    <div class="d-flex align-items-center gap-2 flex-wrap">
                                        <?php if (!empty($pl['section_name'])): ?>
                                            <span class="badge bg-light text-dark border" style="font-size: 0.72rem; font-weight: 500;">
                                                <i class="bi bi-door-closed me-1 text-muted"></i><?php echo htmlspecialchars($pl['section_name']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <?php if (!empty($pl['grade_level'])): ?>
                                            <span class="badge bg-light text-dark border" style="font-size: 0.72rem; font-weight: 500;">
                                                <?php echo htmlspecialchars($pl['grade_level']); ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="badge bg-primary bg-opacity-10 text-primary border border-primary border-opacity-25" style="font-size: 0.72rem; font-weight: 600;">
                                            <?php echo strtoupper($pl['learning_track']); ?> Track
                                        </span>
                                    </div>
                                </div>
                                <span class="badge bg-success bg-opacity-10 text-success border border-success border-opacity-25 px-2.5 py-1.5" style="font-size: 0.75rem;">
                                    <i class="bi bi-check-circle-fill me-1"></i>Active LMS Account
                                </span>
                            </div>

                            <!-- Credentials Box -->
                            <div class="p-3 rounded-3 mb-3 mt-3" style="background: #ffffff; border: 1px solid #e2e8f0;">
                                <div class="row g-2 align-items-center">
                                    <div class="col-sm-7 col-12">
                                        <small class="text-muted d-block" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">
                                            Login Username / Identifier:
                                        </small>
                                        <div class="d-flex align-items-center gap-2 mt-1">
                                            <span class="fw-bold text-dark font-monospace" style="font-size: 1.05rem; letter-spacing: 0.5px;">
                                                <?php echo htmlspecialchars($pl['display_username']); ?>
                                            </span>
                                            <button type="button" class="btn btn-sm btn-outline-secondary p-1 lh-1" title="Kopyahon ang username" onclick="navigator.clipboard.writeText('<?php echo addslashes($pl['display_username']); ?>'); alert('Kopyado ang username: <?php echo addslashes($pl['display_username']); ?>');" style="border-radius: 6px;">
                                                <i class="bi bi-clipboard" style="font-size: 0.8rem;"></i>
                                            </button>
                                        </div>
                                        <?php if ($pl['is_custom_username']): ?>
                                            <small class="text-success d-block mt-0.5" style="font-size: 0.7rem;">
                                                <i class="bi bi-check2 me-1"></i>Customized Username
                                            </small>
                                        <?php else: ?>
                                            <small class="text-muted d-block mt-0.5" style="font-size: 0.7rem;">
                                                (Default Student ID / LRN login)
                                            </small>
                                        <?php endif; ?>
                                    </div>
                                    <div class="col-sm-5 col-12">
                                        <small class="text-muted d-block" style="font-size: 0.72rem; font-weight: 600; text-transform: uppercase;">
                                            Kasamtangang Password:
                                        </small>
                                        <div class="d-flex align-items-center gap-1 mt-1">
                                            <span class="font-monospace text-muted" style="font-size: 0.95rem;">••••••••</span>
                                        </div>
                                        <small class="text-muted d-block mt-0.5" style="font-size: 0.7rem;">
                                            Default: <code class="text-dark">Learner@<?php echo date('Y'); ?></code>
                                        </small>
                                    </div>
                                </div>

                                <!-- Extra IDs Reference -->
                                <div class="mt-2 pt-2 border-top d-flex align-items-center justify-content-between flex-wrap gap-2" style="font-size: 0.75rem; color: #64748b;">
                                    <span>
                                        <strong>Student ID:</strong> <?php echo htmlspecialchars($pl['student_id'] ?: 'N/A'); ?>
                                    </span>
                                    <span>
                                        <strong>LRN:</strong> <?php echo htmlspecialchars($pl['lrn'] ?: 'Wala pa / Pending'); ?>
                                    </span>
                                </div>
                            </div>
                        </div>

                        <!-- Footer Actions -->
                        <div class="d-flex align-items-center justify-content-between flex-wrap gap-2 pt-1">
                            <small class="text-muted" style="font-size: 0.75rem;">
                                <i class="bi bi-info-circle me-1"></i>Pwede ra mag log in gamit ang Username o Student ID.
                            </small>
                            <div class="d-inline-flex gap-2">
                                <?php if (!empty($pl['student_record_id'])): ?>
                                    <button type="button" class="btn btn-outline-success btn-sm px-3 py-1.5" 
                                            data-bs-toggle="modal" 
                                            data-bs-target="#uploadParentChildDocModal_<?php echo (int)$pl['student_record_id']; ?>" 
                                            style="border-radius: 6px; font-weight: 500; font-size: 0.82rem;">
                                        <i class="bi bi-file-earmark-arrow-up-fill me-1"></i> I-upload ang Dokumento
                                    </button>
                                <?php endif; ?>
                                <button type="button" class="btn btn-outline-primary btn-sm px-3 py-1.5" 
                                        data-bs-toggle="modal" 
                                        data-bs-target="#editCredsModal_<?php echo $pl['enrollment_id']; ?>" 
                                        style="border-radius: 6px; font-weight: 500; font-size: 0.82rem;">
                                    <i class="bi bi-key-fill me-1"></i> Ilisi ang Credentials
                                </button>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Edit Credentials Modal for this Learner -->
                <div class="modal fade" id="editCredsModal_<?php echo $pl['enrollment_id']; ?>" tabindex="-1" aria-labelledby="modalLabel_<?php echo $pl['enrollment_id']; ?>" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg rounded-4">
                            <form action="<?php echo BASE_PATH; ?>/parent/update-child-credentials" method="POST">
                                <input type="hidden" name="_csrf_token" value="<?php echo CSRFHelper::getToken(); ?>">
                                <input type="hidden" name="child_record_id" value="<?php echo $pl['enrollment_id']; ?>">
                                <input type="hidden" name="student_record_id" value="<?php echo (int)$pl['student_record_id']; ?>">

                                <div class="modal-header border-0 pb-0 pt-4 px-4">
                                    <div>
                                        <span class="badge bg-primary bg-opacity-10 text-primary px-2 py-1 mb-1" style="font-size: 0.75rem; font-weight: 600;">
                                            SETTINGS SA LOGIN
                                        </span>
                                        <h5 class="modal-title fw-bold text-dark" id="modalLabel_<?php echo $pl['enrollment_id']; ?>" style="font-size: 1.15rem;">
                                            Ilisi ang Credentials ni <?php echo htmlspecialchars($pl['student_name']); ?>
                                        </h5>
                                    </div>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                                </div>

                                <div class="modal-body p-4">
                                    <div class="alert alert-info border-0 rounded-3 small mb-3" style="background: #f0f9ff; color: #0369a1; font-size: 0.8rem;">
                                        <i class="bi bi-lightbulb me-1 fw-bold"></i>
                                        <strong>Opsyonal kini:</strong> Mahimo nimong ilisan ang username ug password aron mas dali matiman-an sa bata kon siya na ang mag-log in sa kanyang tablet o computer. Pwede ra gihapon mag log in ang bata gamit ang iyang Student ID (<code><?php echo htmlspecialchars($pl['student_id']); ?></code>).
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary mb-1">
                                            Bag-ong Username o Email:
                                        </label>
                                        <input type="text" 
                                               name="new_username" 
                                               class="form-control rounded-3" 
                                               value="<?php echo htmlspecialchars($pl['display_username']); ?>" 
                                               placeholder="e.g. <?php echo strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $pl['student_name'])); ?>2026"
                                               minlength="3" 
                                               maxlength="50" 
                                               autocomplete="off"
                                               style="font-size: 0.9rem; padding: 8px 12px;">
                                        <small class="text-muted" style="font-size: 0.72rem;">
                                            Pwedeng username (e.g. <code>juan_sped</code>) o personal email. Minimum 3 ka characters.
                                        </small>
                                    </div>

                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary mb-1">
                                            Bag-ong Password <span class="fw-normal text-muted">(Biyai nga blangko kon dili ilisan)</span>:
                                        </label>
                                        <input type="password" 
                                               name="new_password" 
                                               class="form-control rounded-3" 
                                               placeholder="I-type ang bag-ong password (min. 6 chars)" 
                                               minlength="6"
                                               autocomplete="new-password"
                                               style="font-size: 0.9rem; padding: 8px 12px;">
                                    </div>

                                    <div class="mb-2">
                                        <label class="form-label fw-bold small text-secondary mb-1">
                                            Kumpirmaha ang Bag-ong Password:
                                        </label>
                                        <input type="password" 
                                               name="confirm_password" 
                                               class="form-control rounded-3" 
                                               placeholder="I-type pag-usab ang bag-ong password" 
                                               minlength="6"
                                               autocomplete="new-password"
                                               style="font-size: 0.9rem; padding: 8px 12px;">
                                    </div>
                                </div>

                                <div class="modal-footer border-0 pt-0 px-4 pb-4 gap-2">
                                    <button type="button" class="btn btn-light px-3 py-2 text-secondary" data-bs-dismiss="modal" style="border-radius: 6px; font-size: 0.875rem; font-weight: 500;">
                                        Kanselahon
                                    </button>
                                    <button type="submit" class="btn btn-primary px-4 py-2" style="border-radius: 6px; font-size: 0.875rem; font-weight: 600;">
                                        <i class="bi bi-save me-1"></i> I-save ang Bag-ong Credentials
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>

                <?php if (!empty($pl['student_record_id'])): ?>
                <!-- Upload Documents Modal for Parent's Child -->
                <div class="modal fade text-start" id="uploadParentChildDocModal_<?php echo (int)$pl['student_record_id']; ?>" tabindex="-1">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                            <form method="POST" action="<?php echo BASE_PATH; ?>/students/upload-document" enctype="multipart/form-data">
                                <input type="hidden" name="student_id" value="<?php echo (int)$pl['student_record_id']; ?>">
                                <input type="hidden" name="return_to" value="/dashboard">
                                <div class="modal-header border-bottom py-3">
                                    <h5 class="modal-title fw-bold text-dark fs-6">
                                        <i class="bi bi-file-earmark-arrow-up-fill text-primary me-2"></i>I-upload ang Dokumento ni <?php echo htmlspecialchars($pl['student_name']); ?>
                                    </h5>
                                    <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                </div>
                                <div class="modal-body p-3">
                                    <p class="small text-muted mb-3">
                                        I-upload ang daang IEP, medical certificate, clinical diagnosis, o PSA birth certificate para sa imong anak.
                                    </p>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary">Klase sa Dokumento *</label>
                                        <select name="document_type" class="form-select form-select-sm" required>
                                            <option value="physical_iep">Daang / Traditional nga IEP Dokumento</option>
                                            <option value="medical_record">Medical Certificate / Disability Diagnosis</option>
                                            <option value="assessment_report">Clinical Assessment / Diagnostic Report</option>
                                            <option value="psa_birth_cert">PSA Birth Certificate</option>
                                            <option value="pwd_id">Person with Disability (PWD) ID</option>
                                            <option value="other">DepEd SF10 / Card / Lain nga Dokumento</option>
                                        </select>
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary">Ngalan sa Dokumento (Title)</label>
                                        <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Grade 1 IEP / Hearing Test Report">
                                    </div>
                                    <div class="mb-3">
                                        <label class="form-label fw-bold small text-secondary">Dokumento File * <small class="text-muted">(PDF, JPG, PNG, DOC, DOCX up to 15MB)</small></label>
                                        <input type="file" name="doc_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                    </div>
                                    <div class="mb-2">
                                        <label class="form-label fw-bold small text-secondary">Mensahe / Pahibalo sa Magtutudlo (Notes)</label>
                                        <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Opsyonal nga dugang mensahe para sa SPED Teacher..."></textarea>
                                    </div>
                                </div>
                                <div class="modal-footer border-top py-2">
                                    <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kanselahon</button>
                                    <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 6px; font-weight: 600;">
                                        <i class="bi bi-cloud-arrow-up-fill me-1"></i> I-upload Karon
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
                <?php endif; ?>
                <?php endforeach; ?>
            </div>
        </div>
    <?php endif; ?>

    <!-- Rejected Enrollment Alert (if any) -->
    <?php
    $rejectedEnrollments = array_filter($enrollments, function($e) {
        return $e['status'] === 'rejected' && !empty($e['review_note']);
    });
    ?>
    <?php if (!empty($rejectedEnrollments)): ?>
        <?php foreach ($rejectedEnrollments as $rejected): ?>
        <div class="alert alert-danger alert-dismissible fade show mb-4" role="alert">
            <h5 class="alert-heading">
                <i class="bi bi-exclamation-triangle-fill"></i> 
                Enrollment Rejected - <?php echo htmlspecialchars($rejected['first_name'] . ' ' . $rejected['last_name']); ?>
            </h5>
            <hr>
            <p class="mb-2"><strong>Reason for Rejection:</strong></p>
            <p class="mb-3" style="background: rgba(255,255,255,0.3); padding: 10px; border-radius: 5px;">
                <?php echo nl2br(htmlspecialchars($rejected['review_note'])); ?>
            </p>
            <div class="d-flex gap-2">
                <a href="<?php echo BASE_PATH; ?>/enrollment/view/<?php echo $rejected['id']; ?>" 
                   class="btn btn-sm btn-light">
                    <i class="bi bi-eye"></i> View Details
                </a>
                <a href="<?php echo BASE_PATH; ?>/enrollment?type=<?php echo $rejected['enrollment_type']; ?>" 
                   class="btn btn-sm btn-warning">
                    <i class="bi bi-arrow-repeat"></i> Resubmit Enrollment
                </a>
            </div>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
        <?php endforeach; ?>
    <?php endif; ?>

    <!-- Registered SPED Centers & School Guidelines Dossier Section -->
    <?php
    require_once __DIR__ . '/../../Models/SchoolModel.php';
    require_once __DIR__ . '/../../Models/UserModel.php';
    $parentSchModelObj = new SchoolModel();
    $parentUserModelObj = new UserModel();
    $parentUserRecord = $parentUserModelObj->findById($_SESSION['user_id']);

    // Save school_id into session if passed in URL
    if (!empty($_GET['school_id'])) {
        $_SESSION['selected_school_id'] = (int)$_GET['school_id'];
    }

    // Resolve target school ID for parent
    $targetSchoolId = null;
    if (!empty($enrollments)) {
        foreach ($enrollments as $eRec) {
            if (!empty($eRec['school_id'])) {
                $targetSchoolId = (int)$eRec['school_id'];
                break;
            }
        }
    }

    if (!$targetSchoolId && !empty($_SESSION['selected_school_id'])) {
        $targetSchoolId = (int)$_SESSION['selected_school_id'];
    }

    if (!$targetSchoolId && !empty($parentUserRecord['school_id'])) {
        $targetSchoolId = (int)$parentUserRecord['school_id'];
    }

    if (!isset($allSchools) || empty($allSchools)) {
        $allSchools = $parentSchModelObj->getAllSchools();
    }

    if (!$targetSchoolId && !empty($allSchools)) {
        $targetSchoolId = (int)$allSchools[0]['id'];
    }

    $parentEnrolledSchool = $targetSchoolId ? $parentSchModelObj->findById($targetSchoolId) : null;
    ?>

    <?php if ($parentEnrolledSchool): ?>
        <!-- Display ONLY the selected/enrolled school details card (No dropdown selector menu) -->
        <?php 
        $pts = $parentEnrolledSchool;
        $pSy = $pts['enrollment_sy'] ?? '2026-2027';
        $pStatus = strtoupper($pts['enrollment_status'] ?? 'OPEN');
        $pBadgeClass = ($pStatus === 'OPEN') ? 'bg-success' : (($pStatus === 'UPCOMING') ? 'bg-warning text-dark' : 'bg-secondary');
        $pLogoUrl = SchoolModel::getSchoolLogoUrl($pts, $basePath);
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom flex-wrap gap-2">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i> School Enrollment Guidelines & Profile
                </h5>
                <div class="d-flex align-items-center gap-2">
                    <span class="badge <?php echo $pBadgeClass; ?> px-3 py-1.5">
                        <i class="bi bi-clock-history me-1"></i> Enrollment <?php echo $pStatus; ?> (SY <?php echo htmlspecialchars($pSy); ?>)
                    </span>
                </div>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-start g-4">
                    <!-- School Badge & Address -->
                    <div class="col-md-4 border-end">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-1 bg-white rounded-circle shadow-sm border flex-shrink-0" style="width: 65px; height: 65px;">
                                <img src="<?php echo htmlspecialchars($pLogoUrl); ?>" alt="School Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($pts['school_name']); ?></h6>
                                <small class="text-muted d-block">DepEd ID: <?php echo htmlspecialchars($pts['school_id']); ?></small>
                                <small class="text-muted"><?php echo htmlspecialchars($pts['division'] ?? 'Division Office'); ?></small>
                            </div>
                        </div>

                        <div class="small text-secondary mb-2">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> <strong>Address:</strong> <?php echo htmlspecialchars($pts['address'] ?? 'Official Address'); ?>
                        </div>

                        <?php if (!empty($pts['enrollment_start_date'])): ?>
                            <div class="small text-secondary mb-2">
                                <i class="bi bi-calendar-range text-primary me-1"></i> <strong>Enrollment Timeline:</strong><br>
                                <?php echo date('M j, Y', strtotime($pts['enrollment_start_date'])); ?> <?php echo !empty($pts['enrollment_end_date']) ? ' to ' . date('M j, Y', strtotime($pts['enrollment_end_date'])) : ''; ?>
                            </div>
                        <?php endif; ?>

                        <!-- Contact Details -->
                        <div class="mt-3 pt-3 border-top">
                            <div class="small fw-bold text-dark mb-1">Official School Contacts:</div>
                            <?php if (!empty($pts['contact_email'])): ?>
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-envelope-fill text-primary me-1"></i> <a href="mailto:<?php echo htmlspecialchars($pts['contact_email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($pts['contact_email']); ?></a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($pts['contact_number'])): ?>
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-telephone-fill text-success me-1"></i> <?php echo htmlspecialchars($pts['contact_number']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($pts['facebook_page'])): ?>
                                <div class="small text-muted">
                                    <i class="bi bi-facebook text-primary me-1"></i> <a href="<?php echo htmlspecialchars($pts['facebook_page']); ?>" target="_blank" class="text-decoration-none">Official Facebook Page</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Guidelines & Requirements -->
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-card-checklist text-success me-1"></i> Requirements & Policy Guidelines:</h6>
                        <?php 
                        $pGuidelinesStr = $pts['enrollment_guidelines'] ?? "PSA Birth Certificate\nForm 138/SF10 (Report Card)\nMedical / Diagnostic Evaluation Report\nPWD ID (Optional)";
                        $pGuidelineItems = array_filter(array_map('trim', explode("\n", $pGuidelinesStr)));
                        if (!empty($pGuidelineItems)):
                        ?>
                            <div class="p-3 bg-light rounded-3 border">
                                <ul class="list-unstyled mb-0 small">
                                     <?php foreach ($pGuidelineItems as $pgItem): 
                                         $isOpt = (bool) preg_match('/\((?:Optional|optional)\)$/i', $pgItem);
                                         $cleanText = preg_replace('/\s*\((?:Optional|optional)\)$/i', '', preg_replace('/^[\-\*\•\d+\.\s]+/', '', $pgItem));
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
                            </div>
                        <?php else: ?>
                            <p class="text-muted small mb-0">No official enrollment guidelines have been published yet.</p>
                        <?php endif; ?>
                    </div>

                    <!-- Pubmat Poster -->
                    <div class="col-md-3 text-center">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-image text-info me-1"></i> Publicity Poster (Pubmat):</h6>
                        <?php 
                        $pubmatPath = !empty($pts['pubmat_path']) ? ltrim($pts['pubmat_path'], '/') : null;
                        $pubmatFull = $pubmatPath && function_exists('public_path') ? public_path($pubmatPath) : null;
                        $pubmatUrl = ($pubmatFull && file_exists($pubmatFull)) ? ($basePath . '/' . $pubmatPath) : null;
                        ?>
                        <?php if ($pubmatUrl): ?>
                            <a href="<?php echo htmlspecialchars($pubmatUrl); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($pubmatUrl); ?>" alt="Enrollment Pubmat Poster" class="img-fluid rounded-3 border shadow-sm hover-zoom" style="max-height: 180px; object-fit: cover;">
                            </a>
                            <small class="d-block text-muted mt-1" style="font-size: 0.75rem;">Click to enlarge poster</small>
                        <?php else: ?>
                            <div class="p-4 bg-light rounded-3 border text-muted small">
                                <i class="bi bi-image-fill fs-2 d-block mb-1 text-secondary"></i>
                                No Pubmat poster uploaded yet.
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Child Learning Progress & Tracking Widget (Process 7 Parent Oversight) -->
    <?php
    require_once __DIR__ . '/../../Models/LessonPlanModel.php';
    require_once __DIR__ . '/../../Models/GamificationModel.php';
    $parentLpModel = new LessonPlanModel();
    $parentGamModel = new GamificationModel();
    $parentDb = Database::getInstance()->getConnection();

    $dashChildrenStmt = $parentDb->prepare("
        SELECT sr.id AS student_id,
               sr.student_id AS student_code,
               sr.student_name,
               sr.lrn,
               sr.section_name,
               COALESCE(es.grade_level_to_enroll, 'SPED Program') AS grade_level,
               es.status,
               sch.school_name,
               u.name AS teacher_name
        FROM student_records sr
        JOIN enrollment_submissions es ON sr.enrollment_id = es.id
        LEFT JOIN schools sch ON sr.school_id = sch.id
        LEFT JOIN users u ON sr.assigned_teacher_id = u.id
        WHERE es.parent_id = :pid
          AND es.status = 'verified'
        ORDER BY sr.student_name ASC
    ");
    $dashChildrenStmt->execute(['pid' => $_SESSION['user_id']]);
    $parentDashChildren = $dashChildrenStmt->fetchAll(PDO::FETCH_ASSOC);
    ?>

    <?php if (!empty($parentDashChildren)): ?>
        <div class="card border-0 shadow-sm mb-4" style="border-radius: 12px; overflow: hidden;">
            <div class="card-header bg-white py-3 border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
                <div>
                    <h5 class="mb-0 fw-bold" style="color: #1e4072;">
                        <i class="bi bi-graph-up-arrow text-primary me-2"></i> Learner Progress & Tracking 
                        <span class="d-block d-sm-inline text-muted fst-italic fw-normal" style="font-size: 0.8rem;">(Pagsubaybay sa Pag-uswag ng Mag-aaral)</span>
                    </h5>
                    <small class="text-muted">
                        Monitor story reading, practice activities, and performance scores.
                        <span class="d-block text-muted fst-italic" style="font-size: 0.75rem;">(Subaybayan ang pagbabasa ng kwento, mga pagsasanay, at marka ng iyong anak.)</span>
                    </small>
                </div>
                <a href="<?php echo BASE_PATH; ?>/parent/child-progress" class="btn btn-outline-primary btn-sm px-3 py-1.5 d-inline-flex align-items-center gap-1" style="border-radius: 8px; font-size: 0.85rem; font-weight: 500;">
                    <span>View All <small class="fst-italic text-muted" style="font-size: 0.75rem;">(Tingnan Lahat)</small></span>
                    <i class="bi bi-arrow-right ms-1"></i>
                </a>
            </div>
            <div class="card-body p-4">
                <div class="row g-3">
                    <?php foreach ($parentDashChildren as $child): ?>
                        <?php
                        $cSid = (int)$child['student_id'];
                        $cGam = $parentGamModel->getSummary($cSid);
                        $cLessons = $parentLpModel->getPublishedForStudent($cSid);
                        $cTotalLessons = count($cLessons);
                        $cCompletedLessons = 0;
                        $cTotalTasks = 0;
                        $cCompletedTasks = 0;
                        $cSlidePagesDone = 0;
                        $cTotalSlidePages = 0;

                        foreach ($cLessons as $cLp) {
                            $cLpId = (int)$cLp['id'];
                            $cPages = $parentLpModel->getPagesByLessonPlan($cLpId);
                            $cProg = $parentLpModel->getPageProgress($cSid, $cLpId);
                            $cHasSlides = count($cPages) > 0;
                            $cSlidesDone = !empty($cProg['is_completed']);
                            $cTotalSlidePages += count($cPages);
                            $cSlidePagesDone += (int)($cProg['last_page_number'] ?? 0);

                            $cActProg = $parentLpModel->getLessonProgress($cLpId, $cSid);
                            $cTaskTotal = $cActProg['total'] + ($cHasSlides ? 1 : 0);
                            $cTaskDone  = $cActProg['completed'] + ($cSlidesDone ? 1 : 0);

                            $cTotalTasks += $cTaskTotal;
                            $cCompletedTasks += $cTaskDone;

                            if ($cTaskTotal > 0 && $cTaskDone >= $cTaskTotal) {
                                $cCompletedLessons++;
                            }
                        }

                        $cPct = $cTotalTasks > 0 ? round(($cCompletedTasks / $cTotalTasks) * 100) : 0;
                        ?>
                        <div class="col-12">
                            <div class="p-3 bg-light rounded-3 border d-flex align-items-center justify-content-between flex-wrap gap-3">
                                <div class="d-flex align-items-center gap-3">
                                    <div class="bg-primary text-white rounded-circle d-flex align-items-center justify-content-center fw-bold fs-4 flex-shrink-0" style="width: 52px; height: 52px; background: linear-gradient(135deg, #1e4072 0%, #3a7bd5 100%);">
                                        <?php echo mb_substr($child['student_name'], 0, 1); ?>
                                    </div>
                                    <div>
                                        <div class="d-flex align-items-center gap-2 mb-1">
                                            <h6 class="mb-0 fw-bold text-dark" style="font-size: 1.05rem;"><?php echo htmlspecialchars($child['student_name']); ?></h6>
                                            <span class="badge bg-secondary-subtle text-secondary" style="font-size: 0.72rem;">ID: <?php echo htmlspecialchars($child['student_code']); ?></span>
                                            <?php if (!empty($child['section_name'])): ?>
                                                <span class="badge bg-info-subtle text-info-emphasis" style="font-size: 0.72rem;"><?php echo htmlspecialchars($child['section_name']); ?></span>
                                            <?php endif; ?>
                                        </div>
                                        <small class="text-muted d-block" style="font-size: 0.8rem;">
                                            <i class="bi bi-person-badge text-primary me-1"></i> SPED Teacher: <?php echo htmlspecialchars($child['teacher_name'] ?? 'Assigned Teacher'); ?>
                                            • <i class="bi bi-book text-success ms-1 me-1"></i> <?php echo htmlspecialchars($child['grade_level']); ?>
                                        </small>
                                    </div>
                                </div>

                                <!-- Quick Metric Chips -->
                                <div class="d-flex align-items-center gap-3 flex-wrap">
                                    <div class="px-3 py-1.5 bg-white rounded-2 border text-center shadow-xs">
                                        <span class="text-warning fw-bold fs-6">⭐ <?php echo (int)($cGam['total_stars'] ?? 0); ?></span>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Stars <em class="fst-italic opacity-75">(Bituin)</em></small>
                                    </div>
                                    <div class="px-3 py-1.5 bg-white rounded-2 border text-center shadow-xs">
                                        <span class="text-primary fw-bold fs-6">🏆 <?php echo (int)($cGam['total_xp'] ?? 0); ?></span>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">XP Points <em class="fst-italic opacity-75">(Puntos)</em></small>
                                    </div>
                                    <div class="px-3 py-1.5 bg-white rounded-2 border text-center shadow-xs">
                                        <span class="text-success fw-bold fs-6">✅ <?php echo $cCompletedLessons; ?> / <?php echo $cTotalLessons; ?></span>
                                        <small class="text-muted d-block" style="font-size: 0.72rem;">Completed Lessons <em class="fst-italic opacity-75">(Natapos na)</em></small>
                                    </div>
                                </div>

                                <!-- Progress & Action -->
                                <div class="d-flex align-items-center gap-3" style="min-width: 260px;">
                                    <div class="flex-grow-1">
                                        <div class="d-flex justify-content-between mb-1" style="font-size: 0.75rem;">
                                            <span class="text-muted">Overall Progress <em class="fst-italic" style="font-size: 0.7rem;">(Pag-unlad)</em></span>
                                            <span class="fw-bold text-primary"><?php echo $cPct; ?>%</span>
                                        </div>
                                        <div class="progress" style="height: 7px; border-radius: 4px;">
                                            <div class="progress-bar bg-success" role="progressbar" style="width: <?php echo $cPct; ?>%;" aria-valuenow="<?php echo $cPct; ?>" aria-valuemin="0" aria-valuemax="100"></div>
                                        </div>
                                    </div>
                                    <div class="d-flex align-items-center gap-2">
                                        <button type="button" class="btn btn-outline-success px-2.5 py-1.5 text-nowrap d-flex align-items-center gap-1" data-bs-toggle="modal" data-bs-target="#uploadParentDashChildDocModal_<?php echo $cSid; ?>" style="border-radius: 8px; font-size: 0.82rem; font-weight: 500; height: 36px;">
                                            <i class="bi bi-file-earmark-arrow-up-fill"></i>
                                            <span>Docs</span>
                                        </button>
                                        <a href="<?php echo BASE_PATH; ?>/parent/child-progress/<?php echo $cSid; ?>" class="btn btn-primary px-3 py-1.5 text-nowrap d-flex align-items-center gap-1" style="border-radius: 8px; font-size: 0.85rem; font-weight: 500; height: 36px;">
                                            <i class="bi bi-bar-chart-line"></i>
                                            <span>Track <small class="fst-italic opacity-75" style="font-size: 0.75rem;">(Sundan)</small></span>
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Upload Documents Modal for Learner in Tracker -->
                        <div class="modal fade text-start" id="uploadParentDashChildDocModal_<?php echo $cSid; ?>" tabindex="-1">
                            <div class="modal-dialog modal-dialog-centered">
                                <div class="modal-content border-0 shadow-lg" style="border-radius: 12px;">
                                    <form method="POST" action="<?php echo BASE_PATH; ?>/students/upload-document" enctype="multipart/form-data">
                                        <input type="hidden" name="student_id" value="<?php echo $cSid; ?>">
                                        <input type="hidden" name="return_to" value="/dashboard">
                                        <div class="modal-header border-bottom py-3">
                                            <h5 class="modal-title fw-bold text-dark fs-6">
                                                <i class="bi bi-file-earmark-arrow-up-fill text-primary me-2"></i>I-upload ang Dokumento ni <?php echo htmlspecialchars($child['student_name']); ?>
                                            </h5>
                                            <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                                        </div>
                                        <div class="modal-body p-3">
                                            <p class="small text-muted mb-3">
                                                I-upload ang daang IEP, medical certificate, clinical evaluation, o PSA birth certificate para sa imong anak.
                                            </p>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-secondary">Klase sa Dokumento *</label>
                                                <select name="document_type" class="form-select form-select-sm" required>
                                                    <option value="physical_iep">Daang / Traditional nga IEP Dokumento</option>
                                                    <option value="medical_record">Medical Certificate / Disability Diagnosis</option>
                                                    <option value="assessment_report">Clinical Assessment / Diagnostic Report</option>
                                                    <option value="psa_birth_cert">PSA Birth Certificate</option>
                                                    <option value="pwd_id">Person with Disability (PWD) ID</option>
                                                    <option value="other">DepEd SF10 / Card / Lain nga Dokumento</option>
                                                </select>
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-secondary">Ngalan sa Dokumento (Title)</label>
                                                <input type="text" name="title" class="form-control form-control-sm" placeholder="e.g. Grade 1 IEP / Hearing Test Report">
                                            </div>
                                            <div class="mb-3">
                                                <label class="form-label fw-bold small text-secondary">Dokumento File * <small class="text-muted">(PDF, JPG, PNG, DOC, DOCX up to 15MB)</small></label>
                                                <input type="file" name="doc_file" class="form-control form-control-sm" accept=".pdf,.jpg,.jpeg,.png,.doc,.docx" required>
                                            </div>
                                            <div class="mb-2">
                                                <label class="form-label fw-bold small text-secondary">Mensahe / Pahibalo sa Magtutudlo (Notes)</label>
                                                <textarea name="notes" class="form-control form-control-sm" rows="2" placeholder="Opsyonal nga dugang mensahe para sa SPED Teacher..."></textarea>
                                            </div>
                                        </div>
                                        <div class="modal-footer border-top py-2">
                                            <button type="button" class="btn btn-secondary btn-sm" data-bs-dismiss="modal">Kanselahon</button>
                                            <button type="submit" class="btn btn-primary btn-sm px-3" style="border-radius: 6px; font-weight: 600;">
                                                <i class="bi bi-cloud-arrow-up-fill me-1"></i> I-upload Karon
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- Statistics Cards - Clean & Modern -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                        <i class="bi bi-people-fill text-primary fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo $stats['total'] ?? 0; ?></h3>
                    <small class="text-secondary fw-semibold">Total Enrollments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                        <i class="bi bi-hourglass-split text-warning fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo $stats['pending'] ?? 0; ?></h3>
                    <small class="text-secondary fw-semibold">Pending Review</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo $stats['approved'] ?? 0; ?></h3>
                    <small class="text-secondary fw-semibold">Approved</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle bg-danger bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                        <i class="bi bi-x-circle-fill text-danger fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo $stats['rejected'] ?? 0; ?></h3>
                    <small class="text-secondary fw-semibold">Rejected</small>
                </div>
            </div>
        </div>
    </div>


    <?php
    // Load notifications for dashboard widget
    require_once __DIR__ . '/../../Models/NotificationModel.php';
    $parentNotifModel = new NotificationModel();
    $latestNotifications = $parentNotifModel->getByUserId($_SESSION['user_id'], 5);

    if (!function_exists('timeElapsedString')) {
        function timeElapsedString($datetime, $full = false) {
            $now = new DateTime;
            $ago = new DateTime($datetime);
            $diff = $now->diff($ago);

            $diff->w = floor($diff->d / 7);
            $diff->d -= $diff->w * 7;

            $string = array(
                'y' => 'year',
                'm' => 'month',
                'w' => 'week',
                'd' => 'day',
                'h' => 'hour',
                'i' => 'minute',
                's' => 'second',
            );
            foreach ($string as $k => &$v) {
                if ($diff->$k) {
                    $v = $diff->$k . ' ' . $v . ($diff->$k > 1 ? 's' : '');
                } else {
                    unset($string[$k]);
                }
            }

            if (!$full) $string = array_slice($string, 0, 1);
            return $string ? implode(', ', $string) . ' ago' : 'just now';
        }
    }
    ?>

    <div class="row">
        <!-- Left: Enrollments List -->
        <div class="col-lg-8 mb-4">
            <div class="card h-100 mb-0">
                <div class="card-header">
                    <h5 class="mb-0">My Children's Enrollments</h5>
                </div>
                <div class="card-body">
                    <?php if (empty($enrollments)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-inbox fs-1 d-block mb-2 text-secondary"></i>
                            <p class="mb-0 small">No enrollment applications submitted yet.</p>
                        </div>
                    <?php else: ?>

                        <div class="table-responsive">
                            <table class="table table-hover align-middle">
                                <thead>
                                    <tr>
                                        <th>Child Name</th>
                                        <th>Grade Level</th>
                                        <th>Submitted</th>
                                        <th>Status</th>
                                        <th>Progress</th>
                                        <th>Actions</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($enrollments as $enrollment): ?>
                                        <?php
                                        // Status badge
                                        $statusClass = '';
                                        switch ($enrollment['status']) {
                                            case 'pending':
                                                $statusClass = 'bg-warning bg-opacity-10 text-warning border border-warning';
                                                $statusText = 'Pending';
                                                break;
                                            case 'verified':
                                                $statusClass = 'bg-success bg-opacity-10 text-success border border-success';
                                                $statusText = 'Approved';
                                                break;
                                            case 'rejected':
                                                $statusClass = 'bg-danger bg-opacity-10 text-danger border border-danger';
                                                $statusText = 'Rejected';
                                                break;
                                            default:
                                                $statusClass = 'bg-secondary bg-opacity-10 text-secondary border border-secondary';
                                                $statusText = ucfirst($enrollment['status']);
                                        }

                                        // Progress calculation
                                        $total = $enrollment['total_documents'] ?? 0;
                                        $approved = $enrollment['approved_documents'] ?? 0;
                                        $progress = $total > 0 ? round(($approved / $total) * 100) : 0;
                                        ?>
                                        <tr>
                                            <td>
                                                <strong><?php echo htmlspecialchars($enrollment['first_name'] . ' ' . $enrollment['last_name']); ?></strong>
                                            </td>
                                            <td><?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></td>
                                            <td><?php echo date('M d, Y', strtotime($enrollment['submitted_at'])); ?></td>
                                            <td>
                                                <span class="badge <?php echo $statusClass; ?>">
                                                    <?php echo $statusText; ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php if ($total > 0): ?>
                                                    <div class="d-flex align-items-center justify-content-between mb-1" style="font-size: 0.75rem;">
                                                        <span class="text-muted"><?php echo $approved; ?>/<?php echo $total; ?> docs</span>
                                                        <span class="fw-semibold text-primary"><?php echo $progress; ?>%</span>
                                                    </div>
                                                    <div class="progress" style="height: 6px;">
                                                        <div class="progress-bar bg-primary" role="progressbar" 
                                                             style="width: <?php echo $progress; ?>%"
                                                             aria-valuenow="<?php echo $progress; ?>" 
                                                             aria-valuemin="0" aria-valuemax="100">
                                                        </div>
                                                    </div>
                                                <?php else: ?>
                                                    <small class="text-muted">No documents</small>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <a href="<?php echo BASE_PATH; ?>/enrollment/view/<?php echo $enrollment['id']; ?>" 
                                                   class="btn btn-sm btn-outline-primary">
                                                    <i class="bi bi-eye"></i> View
                                                </a>
                                                <?php if ($enrollment['status'] === 'rejected'): ?>
                                                    <a href="<?php echo BASE_PATH; ?>/enrollment?type=<?php echo $enrollment['enrollment_type']; ?>" 
                                                       class="btn btn-sm btn-outline-warning">
                                                        <i class="bi bi-arrow-repeat"></i> Resubmit
                                                    </a>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>

        <!-- Right: Recent Notifications Widget -->
        <div class="col-lg-4 mb-4">
            <div class="card h-100 mb-0 shadow-sm border-0" style="border-radius: 12px;">
                <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between" style="border-top-left-radius: 12px; border-top-right-radius: 12px;">
                    <h5 class="mb-0 fw-bold" style="color: #1e4072; font-size: 1.05rem;">
                        <i class="bi bi-bell-fill me-2 text-warning"></i>Recent Notifications
                    </h5>
                    <?php if (!empty($latestNotifications)): ?>
                        <span class="badge bg-danger rounded-pill" style="font-size: 0.72rem;">Latest</span>
                    <?php endif; ?>
                </div>
                <div class="card-body p-3">
                    <?php if (empty($latestNotifications)): ?>
                        <div class="text-center py-5 text-muted">
                            <i class="bi bi-bell-slash text-secondary" style="font-size: 2.2rem;"></i>
                            <p class="mb-0 mt-2 small">No recent notifications</p>
                        </div>
                    <?php else: ?>
                        <div class="list-group list-group-flush">
                            <?php foreach ($latestNotifications as $notif): ?>
                                <?php
                                $notifData = $notif['data'] ? json_decode($notif['data'], true) : [];
                                $notifLink = '';
                                if ($notif['type'] === 'role_rejected') {
                                    $notifLink = BASE_PATH . '/role/select';
                                } else if ($notif['type'] === 'enrollment_approved') {
                                    $notifLink = BASE_PATH . '/enrollment/status';
                                } else if ($notif['type'] === 'iep_signature_request' && !empty($notifData['iep_id']) && !empty($notifData['signatory_id'])) {
                                    $notifLink = BASE_PATH . '/iep/sign/' . $notifData['iep_id'] . '/' . $notifData['signatory_id'];
                                } else if ($notif['type'] === 'placement_confirmed' && !empty($notifData['iep_id'])) {
                                    $notifLink = BASE_PATH . '/iep/' . $notifData['iep_id'] . '/placement-notice';
                                } else if (!empty($notifData['iep_id'])) {
                                    $notifLink = BASE_PATH . '/iep';
                                }

                                $bgType = 'info';
                                $iconName = 'info-circle-fill';
                                switch ($notif['type']) {
                                    case 'role_approved':
                                    case 'enrollment_approved':
                                    case 'email_verified':
                                    case 'placement_confirmed':
                                        $bgType = 'success';
                                        $iconName = 'check-circle-fill';
                                        break;
                                    case 'role_rejected':
                                    case 'enrollment_rejected':
                                    case 'document_rejected':
                                        $bgType = 'danger';
                                        $iconName = 'x-circle-fill';
                                        break;
                                    case 'enrollment_submitted':
                                    case 'new_enrollment':
                                        $bgType = 'primary';
                                        $iconName = 'file-earmark-text-fill';
                                        break;
                                    case 'iep_signature_request':
                                        $bgType = 'warning';
                                        $iconName = 'pen-fill';
                                        break;
                                }
                                ?>
                                <div class="list-group-item px-0 py-3 border-0 border-bottom">
                                    <div class="d-flex align-items-start gap-3">
                                        <div class="rounded-circle bg-<?php echo $bgType; ?> bg-opacity-10 text-<?php echo $bgType; ?>" style="width: 34px; height: 34px; display: flex; align-items: center; justify-content: center; flex-shrink: 0;">
                                            <i class="bi bi-<?php echo $iconName; ?>" style="font-size: 1.1rem;"></i>
                                        </div>
                                        <div class="flex-grow-1 min-width-0">
                                            <div class="d-flex align-items-center justify-content-between mb-1">
                                                <h6 class="mb-0 fw-semibold text-dark text-truncate" style="font-size: 0.88rem; max-width: 150px;">
                                                    <?php echo htmlspecialchars($notif['title']); ?>
                                                </h6>
                                                <?php if (!$notif['is_read']): ?>
                                                    <span class="badge bg-primary bg-opacity-10 text-primary rounded-pill" style="font-size: 0.65rem;">New</span>
                                                <?php endif; ?>
                                            </div>
                                            <p class="mb-1 text-muted text-break" style="font-size: 0.8rem; line-height: 1.3;">
                                                <?php echo htmlspecialchars($notif['message']); ?>
                                            </p>
                                            <div class="d-flex align-items-center justify-content-between mt-2">
                                                <small class="text-muted" style="font-size: 0.72rem;">
                                                    <?php echo timeElapsedString($notif['created_at']); ?>
                                                </small>
                                                <?php if ($notifLink): ?>
                                                    <a href="<?php echo $notifLink; ?>" class="btn btn-sm btn-link p-0 text-decoration-none fw-bold" style="font-size: 0.75rem; color: #1e4072;">
                                                        Action <i class="bi bi-arrow-right ms-0"></i>
                                                    </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Cards -->
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-charge-fill text-primary me-1"></i> Quick Actions</h5>
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between text-center p-3">
                    <div>
                        <div class="rounded-circle bg-danger bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                            <i class="bi bi-person-plus-fill text-danger fs-3"></i>
                        </div>
                        <h6 class="card-title fw-bold text-dark mb-1" style="font-size: 0.95rem;">Enroll Child</h6>
                        <p class="text-secondary small mb-3" style="font-size: 0.78rem;">Submit a new SPED enrollment application.</p>
                    </div>
                    <div class="mt-auto pt-1 w-100">
                        <a href="<?php echo BASE_PATH; ?>/enroll" class="btn btn-outline-danger w-100 py-1.5 text-nowrap" style="border-radius: 8px; font-size: 0.85rem; font-weight: 500;">
                            <i class="bi bi-plus-circle me-1"></i> Enroll Child <small class="fst-italic opacity-75">(Mag-enroll)</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100 border-top border-primary border-2">
                <div class="card-body d-flex flex-column justify-content-between text-center p-3">
                    <div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                            <i class="bi bi-bar-chart-line-fill text-primary fs-3"></i>
                        </div>
                        <h6 class="card-title fw-bold text-dark mb-1" style="font-size: 0.95rem;">
                            Learning Progress
                            <small class="text-muted d-block fst-italic fw-normal" style="font-size: 0.72rem;">(Pag-uswag sa Aralin)</small>
                        </h6>
                        <p class="text-secondary small mb-3" style="font-size: 0.78rem;">
                            Track lesson slides, stories, and quiz scores.
                            <span class="d-block text-muted fst-italic" style="font-size: 0.7rem;">(Sundan ang aralin, kwento, at marka.)</span>
                        </p>
                    </div>
                    <div class="mt-auto pt-1 w-100">
                        <a href="<?php echo BASE_PATH; ?>/parent/child-progress" class="btn btn-primary w-100 py-1.5 text-nowrap" style="border-radius: 8px; font-size: 0.85rem; font-weight: 600;">
                            <i class="bi bi-graph-up me-1"></i> Track Progress <small class="fst-italic opacity-75">(Pag-uswag)</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between text-center p-3">
                    <div>
                        <div class="rounded-circle bg-warning bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                            <i class="bi bi-list-check text-warning fs-3"></i>
                        </div>
                        <h6 class="card-title fw-bold text-dark mb-1" style="font-size: 0.95rem;">Enrollment Status</h6>
                        <p class="text-secondary small mb-3" style="font-size: 0.78rem;">Track progress of submitted applications.</p>
                    </div>
                    <div class="mt-auto pt-1 w-100">
                        <a href="<?php echo BASE_PATH; ?>/enrollment/status" class="btn btn-outline-primary w-100 py-1.5 text-nowrap" style="border-radius: 8px; font-size: 0.85rem; font-weight: 500;">
                            <i class="bi bi-eye-fill me-1"></i> View Status <small class="fst-italic opacity-75">(Katayuan)</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between text-center p-3">
                    <div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                            <i class="bi bi-diagram-3-fill text-success fs-3"></i>
                        </div>
                        <h6 class="card-title fw-bold text-dark mb-1" style="font-size: 0.95rem;">IEP & Transition</h6>
                        <p class="text-secondary small mb-3" style="font-size: 0.78rem;">View Individualized Education Plans.</p>
                    </div>
                    <div class="mt-auto pt-1 w-100">
                        <a href="<?php echo BASE_PATH; ?>/iep" class="btn btn-outline-success w-100 py-1.5 text-nowrap" style="border-radius: 8px; font-size: 0.85rem; font-weight: 500;">
                            <i class="bi bi-diagram-3 me-1"></i> View IEP <small class="fst-italic opacity-75">(Tingnan ang IEP)</small>
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>


<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

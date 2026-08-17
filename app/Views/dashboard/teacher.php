<?php
$pageTitle = 'SPED Teacher Dashboard - SignED';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';

require_once __DIR__ . '/../../Models/UserModel.php';
require_once __DIR__ . '/../../Models/SchoolModel.php';
require_once __DIR__ . '/../../Models/TeacherAssignmentModel.php';

$teacherUserModel = new UserModel();
$teacherUser = $teacherUserModel->findById($_SESSION['user_id']);
$teacherSchoolId = $teacherUser['school_id'] ?? null;

$schoolModelObj = new SchoolModel();
$teacherSchool = $teacherSchoolId ? $schoolModelObj->findById($teacherSchoolId) : null;

$teacherAssignModelObj = new TeacherAssignmentModel();
$myAssignmentData = $teacherAssignModelObj->getByTeacherId($_SESSION['user_id']);
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
            <h1 class="h3 mb-0 text-gray-800">SPED Teacher Dashboard</h1>
            <p class="text-muted small mb-0">Manage learner enrollments, educational assessments, and IEP implementations.</p>
        </div>
    </div>

    <!-- 1. Designated Classroom & Section Banner -->
    <?php if (!empty($myAssignmentData)): ?>
        <?php 
        $cleanRoom = preg_replace('/^room\s*/i', '', trim($myAssignmentData['room_number']));
        ?>
        <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #0d6efd !important; background: linear-gradient(135deg, #ffffff 0%, #f0f7ff 100%);">
            <div class="card-body p-4">
                <div class="d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div class="d-flex align-items-center gap-3">
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 text-primary flex-shrink-0">
                            <i class="bi bi-geo-alt-fill fs-2"></i>
                        </div>
                        <div>
                            <h5 class="fw-bold mb-1 text-dark">
                                <i class="bi bi-building me-1 text-primary"></i> Designated Classroom & Section Assignment
                            </h5>
                            <div class="d-flex align-items-center gap-2 flex-wrap mt-1">
                                <span class="badge bg-primary fs-6 px-3 py-1.5 shadow-sm">
                                    <i class="bi bi-bookmark-star-fill me-1"></i> <?php echo htmlspecialchars($myAssignmentData['grade_level']); ?> — <?php echo htmlspecialchars($myAssignmentData['section_name']); ?>
                                </span>
                                <span class="badge bg-secondary fs-6 px-3 py-1.5 shadow-sm">
                                    <i class="bi bi-door-open-fill me-1"></i> <?php echo htmlspecialchars($myAssignmentData['building_name']); ?> | Room <?php echo htmlspecialchars($cleanRoom); ?>
                                </span>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($myAssignmentData['optional_message'])): ?>
                        <div class="bg-white p-3 rounded-3 border shadow-sm" style="max-width: 420px;">
                            <div class="small fw-bold text-primary mb-1"><i class="bi bi-chat-quote-fill me-1"></i> Principal Note / Instructions:</div>
                            <div class="small text-dark fst-italic">"<?php echo htmlspecialchars($myAssignmentData['optional_message']); ?>"</div>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif; ?>

    <!-- 2. Official School Enrollment Details & Guidelines Card -->
    <?php if ($teacherSchool): ?>
        <?php 
        $schLogoUrl = SchoolModel::getSchoolLogoUrl($teacherSchool, $basePath); 
        $schStatus = strtoupper($teacherSchool['enrollment_status'] ?? 'OPEN');
        $schStatusClass = ($schStatus === 'OPEN') ? 'bg-success' : 'bg-secondary';
        ?>
        <div class="card border-0 shadow-sm mb-4">
            <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center border-bottom">
                <h5 class="mb-0 fw-bold text-dark">
                    <i class="bi bi-info-circle-fill text-primary me-2"></i> School Enrollment Guidelines & Profile
                </h5>
                <span class="badge <?php echo $schStatusClass; ?> px-3 py-1.5">
                    <i class="bi bi-clock-history me-1"></i> Enrollment <?php echo $schStatus; ?> (SY <?php echo htmlspecialchars($teacherSchool['enrollment_sy'] ?? '2026-2027'); ?>)
                </span>
            </div>
            <div class="card-body p-4">
                <div class="row align-items-start g-4">
                    <!-- School Badge & Address -->
                    <div class="col-md-4 border-end">
                        <div class="d-flex align-items-center gap-3 mb-3">
                            <div class="p-1 bg-white rounded-circle shadow-sm border flex-shrink-0" style="width: 65px; height: 65px;">
                                <img src="<?php echo htmlspecialchars($schLogoUrl); ?>" alt="School Logo" style="width: 100%; height: 100%; object-fit: contain; border-radius: 50%;">
                            </div>
                            <div>
                                <h6 class="fw-bold mb-0 text-dark"><?php echo htmlspecialchars($teacherSchool['school_name']); ?></h6>
                                <small class="text-muted d-block">DepEd ID: <?php echo htmlspecialchars($teacherSchool['school_id']); ?></small>
                                <small class="text-muted"><?php echo htmlspecialchars($teacherSchool['division'] ?? 'Division Office'); ?></small>
                            </div>
                        </div>

                        <div class="small text-secondary mb-2">
                            <i class="bi bi-geo-alt-fill text-danger me-1"></i> <strong>Address:</strong> <?php echo htmlspecialchars($teacherSchool['address'] ?? 'Official DepEd Registered Address'); ?>
                        </div>

                        <?php if (!empty($teacherSchool['enrollment_start_date'])): ?>
                            <div class="small text-secondary mb-2">
                                <i class="bi bi-calendar-range text-primary me-1"></i> <strong>Enrollment Timeline:</strong><br>
                                <?php echo date('M j, Y', strtotime($teacherSchool['enrollment_start_date'])); ?> to <?php echo date('M j, Y', strtotime($teacherSchool['enrollment_end_date'] ?? $teacherSchool['enrollment_start_date'])); ?>
                            </div>
                        <?php endif; ?>

                        <!-- Contact Details -->
                        <div class="mt-3 pt-3 border-top">
                            <div class="small fw-bold text-dark mb-1">Official School Contacts:</div>
                            <?php if (!empty($teacherSchool['contact_email'])): ?>
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-envelope-fill text-primary me-1"></i> <a href="mailto:<?php echo htmlspecialchars($teacherSchool['contact_email']); ?>" class="text-decoration-none"><?php echo htmlspecialchars($teacherSchool['contact_email']); ?></a>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($teacherSchool['contact_number'])): ?>
                                <div class="small text-muted mb-1">
                                    <i class="bi bi-telephone-fill text-success me-1"></i> <?php echo htmlspecialchars($teacherSchool['contact_number']); ?>
                                </div>
                            <?php endif; ?>
                            <?php if (!empty($teacherSchool['facebook_page'])): ?>
                                <div class="small text-muted">
                                    <i class="bi bi-facebook text-primary me-1"></i> <a href="<?php echo htmlspecialchars($teacherSchool['facebook_page']); ?>" target="_blank" class="text-decoration-none">Official Facebook Page</a>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Guidelines & Requirements -->
                    <div class="col-md-5 border-end">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-card-checklist text-success me-1"></i> Requirements & Policy Guidelines:</h6>
                        <?php if (!empty($teacherSchool['enrollment_guidelines'])): ?>
                            <div class="p-3 bg-light rounded-3 border">
                                <ul class="mb-0 ps-3 small text-secondary">
                                    <?php 
                                    $gLines = explode("\n", $teacherSchool['enrollment_guidelines']);
                                    foreach ($gLines as $gLine):
                                        $gLine = trim($gLine);
                                        if (empty($gLine)) continue;
                                    ?>
                                        <li class="mb-1"><?php echo htmlspecialchars($gLine); ?></li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                        <?php else: ?>
                            <p class="text-muted small">Standard DepEd SPED Enrollment guidelines apply (BEEF Form, PSA Birth Certificate, Medical Assessment Report).</p>
                        <?php endif; ?>

                        <?php if (!empty($teacherSchool['enrollment_announcement'])): ?>
                            <div class="mt-3">
                                <h6 class="fw-bold text-dark mb-1"><i class="bi bi-megaphone-fill text-warning me-1"></i> Announcement:</h6>
                                <p class="small text-secondary mb-0 p-2 bg-warning bg-opacity-10 border border-warning rounded">
                                    <?php echo htmlspecialchars($teacherSchool['enrollment_announcement']); ?>
                                </p>
                            </div>
                        <?php endif; ?>
                    </div>

                    <!-- Pubmat Publicity Poster -->
                    <div class="col-md-3 text-center">
                        <h6 class="fw-bold text-dark mb-2"><i class="bi bi-image text-info me-1"></i> Publicity Poster (Pubmat):</h6>
                        <?php 
                        $pubmatPath = !empty($teacherSchool['pubmat_path']) ? ltrim($teacherSchool['pubmat_path'], '/') : null;
                        $pubmatFull = $pubmatPath && function_exists('public_path') ? public_path($pubmatPath) : null;
                        $pubmatUrl = ($pubmatFull && file_exists($pubmatFull)) ? ($basePath . '/' . $pubmatPath) : null;
                        ?>
                        <?php if ($pubmatUrl): ?>
                            <a href="<?php echo htmlspecialchars($pubmatUrl); ?>" target="_blank">
                                <img src="<?php echo htmlspecialchars($pubmatUrl); ?>" alt="Enrollment Pubmat" class="img-fluid rounded-3 border shadow-sm hover-zoom" style="max-height: 180px; object-fit: cover;">
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

    <!-- Pending Enrollments Alert -->
    <?php if (isset($pendingCount) && $pendingCount > 0): ?>
    <div class="alert alert-warning alert-dismissible fade show mb-4" role="alert">
        <div class="d-flex align-items-center justify-content-between">
            <div class="d-flex align-items-center">
                <div class="me-3" style="font-size: 2rem;">
                    <i class="bi bi-exclamation-triangle-fill text-warning"></i>
                </div>
                <div>
                    <h6 class="alert-heading fw-bold mb-1 text-dark">
                        <i class="bi bi-clipboard-check"></i> Pending Enrollment Applications
                    </h6>
                    <p class="mb-0 text-dark small">
                        You have <strong><?php echo $pendingCount; ?></strong> enrollment application<?php echo $pendingCount > 1 ? 's' : ''; ?> waiting for review under your school.
                    </p>
                </div>
            </div>
            <a href="<?php echo $basePath; ?>/enrollment/review" class="btn btn-warning btn-sm fw-bold px-3 py-1.5 shadow-sm text-nowrap ms-3">
                <i class="bi bi-eye-fill me-1"></i> Review Now
            </a>
        </div>
        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="row g-3 mb-4">
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle bg-warning bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                        <i class="bi bi-hourglass-split text-warning fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo isset($pendingCount) ? $pendingCount : 0; ?></h3>
                    <small class="text-secondary fw-semibold">Pending Enrollments</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle bg-success bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                        <i class="bi bi-check-circle-fill text-success fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo (int)($verifiedStudentsCount ?? 0); ?></h3>
                    <small class="text-secondary fw-semibold">Verified Students</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle bg-info bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                        <i class="bi bi-clipboard-data-fill text-info fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo (int)($assessmentsDoneCount ?? 0); ?></h3>
                    <small class="text-secondary fw-semibold">Assessments Done</small>
                </div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body text-center p-3">
                    <div class="rounded-circle bg-primary bg-opacity-10 p-2.5 d-inline-flex align-items-center justify-content-center mb-2" style="width: 54px; height: 54px;">
                        <i class="bi bi-book-fill text-primary fs-3"></i>
                    </div>
                    <h3 class="fw-bold mb-0 text-dark"><?php echo (int)($activeIepsCount ?? 0); ?></h3>
                    <small class="text-secondary fw-semibold">Active IEPs</small>
                </div>
            </div>
        </div>
    </div>

    <!-- Quick Actions Cards with Consistent Neat Button Design -->
    <h5 class="fw-bold text-dark mb-3"><i class="bi bi-lightning-charge-fill text-primary me-1"></i> Quick Actions</h5>
    <div class="row g-3 mb-4">
        <!-- Action 1: Verify Enrollment -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between text-center p-4">
                    <div>
                        <div class="rounded-circle bg-primary bg-opacity-10 p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-shield-check text-primary fs-2"></i>
                        </div>
                        <h5 class="card-title fw-bold text-dark mb-2" style="font-size: 1.05rem;">Verify Enrollment</h5>
                        <p class="text-secondary small mb-3">Review submitted documents and verify student applications.</p>
                    </div>
                    <div class="mt-auto pt-2 w-100">
                        <a href="<?php echo $basePath; ?>/verification" class="btn btn-outline-primary w-100 fw-semibold py-2 text-nowrap">
                            <i class="bi bi-shield-check me-1"></i> Go to Verification
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action 2: Conduct Assessment -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between text-center p-4">
                    <div>
                        <div class="rounded-circle bg-info bg-opacity-10 p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-clipboard-data-fill text-info fs-2"></i>
                        </div>
                        <h5 class="card-title fw-bold text-dark mb-2" style="font-size: 1.05rem;">Conduct Assessment</h5>
                        <p class="text-secondary small mb-3">Assess student learning abilities, strengths, and special needs.</p>
                    </div>
                    <div class="mt-auto pt-2 w-100">
                        <a href="<?php echo $basePath; ?>/assessment" class="btn btn-outline-info w-100 fw-semibold py-2 text-nowrap">
                            <i class="bi bi-clipboard-data-fill me-1"></i> Go to Assessments
                        </a>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Action 3: Implement IEP -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between text-center p-4">
                    <div>
                        <div class="rounded-circle bg-success bg-opacity-10 p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-book-fill text-success fs-2"></i>
                        </div>
                        <h5 class="card-title fw-bold text-dark mb-2" style="font-size: 1.05rem;">Implement IEP</h5>
                        <p class="text-secondary small mb-3">Create learning materials, activities, and track IEP progress.</p>
                    </div>
                    <div class="mt-auto pt-2 w-100">
                        <a href="<?php echo $basePath; ?>/iep/implementation" class="btn btn-outline-success w-100 fw-semibold py-2 text-nowrap">
                            <i class="bi bi-book-fill me-1"></i> IEP Implementation
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action 4: Grades & Progress -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between text-center p-4">
                    <div>
                        <div class="rounded-circle bg-warning bg-opacity-15 p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-bar-chart-line-fill text-warning fs-2"></i>
                        </div>
                        <h5 class="card-title fw-bold text-dark mb-2" style="font-size: 1.05rem;">Grades & Progress</h5>
                        <p class="text-secondary small mb-3">Track and manage student quarterly grades and SF9 progress reports.</p>
                    </div>
                    <div class="mt-auto pt-2 w-100">
                        <a href="<?php echo $basePath; ?>/progress-reports" class="btn btn-outline-warning text-dark w-100 fw-semibold py-2 text-nowrap">
                            <i class="bi bi-bar-chart-line-fill me-1"></i> Progress & Grades
                        </a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Action 5: IEP Records & Modules -->
        <div class="col-md-4">
            <div class="card border-0 shadow-sm h-100">
                <div class="card-body d-flex flex-column justify-content-between text-center p-4">
                    <div>
                        <div class="rounded-circle bg-secondary bg-opacity-10 p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
                            <i class="bi bi-folder-fill text-secondary fs-2"></i>
                        </div>
                        <h5 class="card-title fw-bold text-dark mb-2" style="font-size: 1.05rem;">IEP Records & Modules</h5>
                        <p class="text-secondary small mb-3">Access Learning Outcomes, Observation, Transition, and Placement modules.</p>
                    </div>
                    <div class="mt-auto pt-2 w-100">
                        <a href="<?php echo $basePath; ?>/iep" class="btn btn-outline-secondary w-100 fw-semibold py-2 text-nowrap">
                            <i class="bi bi-folder-fill me-1"></i> Open IEP Records
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

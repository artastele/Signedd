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

    <!-- Section: Enrollment Review & Section Roster (Dashboard View) -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-person-lines-fill text-primary fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">Enrollment Review &amp; Section Roster</h5>
            </div>
            <div class="d-flex gap-2">
                <ul class="nav nav-pills" id="enrollmentSectionTabs" role="tablist">
                    <li class="nav-item" role="presentation">
                        <button class="nav-link active py-1 px-3 fw-semibold small" id="pending-tab" data-bs-toggle="tab" data-bs-target="#pending-pane" type="button" role="tab">
                            <i class="bi bi-clock-history me-1"></i> Pending Review (<?php echo (int)($pendingCount ?? 0); ?>)
                        </button>
                    </li>
                    <li class="nav-item" role="presentation">
                        <button class="nav-link py-1 px-3 fw-semibold small" id="roster-tab" data-bs-toggle="tab" data-bs-target="#roster-pane" type="button" role="tab">
                            <i class="bi bi-people me-1"></i> Enrolled Section Roster (<?php echo count($myEnrolledStudents ?? []); ?>)
                        </button>
                    </li>
                </ul>
                <a href="<?php echo $basePath; ?>/masterlist" class="btn btn-sm btn-outline-dark fw-semibold py-1 px-3">
                    <i class="bi bi-people-fill me-1"></i> Full Masterlist
                </a>
            </div>
        </div>
        <div class="card-body p-0">
            <div class="tab-content" id="enrollmentSectionTabsContent">
                <!-- Tab 1: Pending Applications -->
                <div class="tab-pane fade show active p-3" id="pending-pane" role="tabpanel">
                    <?php if (empty($pendingEnrollments)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-check2-circle text-success fs-1 d-block mb-1"></i>
                            <h6 class="fw-bold mb-1">No Pending Applications for Review</h6>
                            <p class="small text-muted mb-0">All submitted student applications under your school have been verified and processed.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Applicant Name</th>
                                        <th>Grade Level</th>
                                        <th>Parent / Guardian</th>
                                        <th>Submitted Date</th>
                                        <th>Status</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendingEnrollments as $app): ?>
                                        <tr>
                                            <td class="ps-3 fw-semibold text-dark">
                                                <i class="bi bi-person-fill text-secondary me-1"></i>
                                                <?php echo htmlspecialchars($app['student_name'] ?? ($app['first_name'] . ' ' . $app['last_name'])); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary">
                                                    <?php echo htmlspecialchars($app['grade_level_to_enroll'] ?? $app['grade_level'] ?? 'Kindergarten'); ?>
                                                </span>
                                            </td>
                                            <td class="small text-muted">
                                                <?php echo htmlspecialchars($app['parent_name'] ?? $app['guardian_name'] ?? 'Parent / Guardian'); ?>
                                            </td>
                                            <td class="small text-muted">
                                                <?php echo !empty($app['created_at']) ? date('M d, Y', strtotime($app['created_at'])) : 'Recent'; ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-warning bg-opacity-15 text-dark border border-warning">
                                                    <i class="bi bi-hourglass-split me-1"></i>Pending Review
                                                </span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="<?php echo $basePath; ?>/enrollment/review/<?php echo (int)($app['id'] ?? 0); ?>" class="btn btn-sm btn-warning text-dark fw-semibold py-1 px-2.5 shadow-sm">
                                                    <i class="bi bi-clipboard-check me-1"></i> Review &amp; Verify
                                                </a>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Tab 2: Enrolled Section Roster -->
                <div class="tab-pane fade p-3" id="roster-pane" role="tabpanel">
                    <?php if (empty($myEnrolledStudents)): ?>
                        <div class="text-center py-4 text-muted">
                            <i class="bi bi-person-slash text-secondary fs-1 d-block mb-1"></i>
                            <h6 class="fw-bold mb-1">No Enrolled Students in Section Yet</h6>
                            <p class="small text-muted mb-0">Verified students assigned to your section will automatically appear here.</p>
                        </div>
                    <?php else: ?>
                        <div class="table-responsive">
                            <table class="table table-hover align-middle mb-0">
                                <thead class="table-light">
                                    <tr>
                                        <th class="ps-3">Student Name</th>
                                        <th>Student ID / LRN</th>
                                        <th>Grade &amp; Section</th>
                                        <th>Learning Track</th>
                                        <th>Disability Category</th>
                                        <th class="text-end pe-3">Action</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($myEnrolledStudents as $std): ?>
                                        <?php 
                                        $hasActiveAccount = !empty($std['learner_user_id']) || (($std['learning_track'] ?? '') === 'lms');
                                        $isCandidate = (($std['learning_track'] ?? '') === 'candidate')
                                            || (($std['es_learning_track'] ?? '') === 'candidate')
                                            || (($std['lms_track'] ?? '') === 'candidate')
                                            || (!empty($std['survey_has_internet']) && (!empty($std['survey_willing_online']) || !empty($std['willing_digital'])))
                                            || (!empty($std['has_device']) && !empty($std['willing_digital']))
                                            || !empty($std['modality_online'])
                                            || !empty($std['modality_modular_digital']);
                                        ?>
                                        <tr>
                                            <td class="ps-3 fw-semibold text-dark">
                                                <a href="<?php echo $basePath; ?>/students/view/<?php echo (int)$std['id']; ?>" class="text-primary text-decoration-none">
                                                    <?php echo htmlspecialchars($std['student_name']); ?>
                                                </a>
                                            </td>
                                            <td class="font-monospace small">
                                                <?php echo htmlspecialchars($std['lrn'] ?? $std['student_id'] ?? '—'); ?>
                                            </td>
                                            <td>
                                                <span class="badge bg-secondary"><?php echo htmlspecialchars($std['current_grade_level'] ?? 'SPED'); ?></span>
                                                <small class="text-muted ms-1"><?php echo htmlspecialchars($std['section_name'] ?? 'Section A'); ?></small>
                                            </td>
                                            <td>
                                                <?php if ($hasActiveAccount): ?>
                                                    <span class="badge bg-primary px-2 py-1" title="Active LMS Account">
                                                        <i class="bi bi-check2-circle me-1"></i>SignED LMS (Active)
                                                    </span>
                                                <?php elseif ($isCandidate): ?>
                                                    <span class="badge bg-info bg-opacity-15 text-info-emphasis border border-info px-2 py-1" title="Recommended for SignED LMS via Digital Survey">
                                                        <i class="bi bi-laptop me-1"></i>SignED Candidate
                                                    </span>
                                                <?php else: ?>
                                                    <span class="badge bg-secondary bg-opacity-10 text-secondary border border-secondary px-2 py-1">
                                                        <i class="bi bi-person-workspace me-1"></i>Traditional SEN
                                                    </span>
                                                <?php endif; ?>
                                            </td>
                                            <td>
                                                <span class="small text-secondary"><?php echo htmlspecialchars($std['disability_type'] ?? 'Not Specified'); ?></span>
                                            </td>
                                            <td class="text-end pe-3">
                                                <a href="<?php echo $basePath; ?>/students/view/<?php echo (int)$std['id']; ?>" class="btn btn-sm btn-outline-primary py-1 px-2.5">
                                                    <i class="bi bi-person-badge me-1"></i> Profile
                                                </a>
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
    </div>

    <!-- Section: My Availability & IEP Meeting Schedule Card -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3 border-bottom d-flex align-items-center justify-content-between flex-wrap gap-2">
            <div class="d-flex align-items-center gap-2">
                <i class="bi bi-calendar-check text-primary fs-5"></i>
                <h5 class="mb-0 fw-bold text-dark">My Availability &amp; Meeting Schedule</h5>
            </div>
            <button type="button" class="btn btn-sm btn-outline-primary fw-semibold py-1 px-3" data-bs-toggle="modal" data-bs-target="#availabilityModal">
                <i class="bi bi-pencil-square me-1"></i> Update Weekly Schedule
            </button>
        </div>
        <div class="card-body p-4">
            <div class="row g-3 align-items-center">
                <div class="col-md-7 border-end">
                    <h6 class="fw-bold text-dark mb-2">Weekly Recurring Availability for IEP Meetings:</h6>
                    <p class="text-secondary small mb-3">These are the days parents and team members can schedule IEP meetings with you:</p>
                    <div class="d-flex flex-wrap gap-2">
                        <?php
                        $weekDayNames = [
                            0 => 'Sunday',
                            1 => 'Monday',
                            2 => 'Tuesday',
                            3 => 'Wednesday',
                            4 => 'Thursday',
                            5 => 'Friday',
                            6 => 'Saturday'
                        ];
                        foreach ($weekDayNames as $dNum => $dName):
                            $isAvail = !empty($recurringAvailability[$dNum]);
                        ?>
                            <div class="px-3 py-2 rounded-3 border text-center <?php echo $isAvail ? 'bg-success bg-opacity-10 border-success text-success' : 'bg-light text-muted border-secondary-subtle'; ?>" style="min-width: 95px;">
                                <div class="fw-bold small"><?php echo $dName; ?></div>
                                <div class="small fw-semibold mt-1">
                                    <?php if ($isAvail): ?>
                                        <i class="bi bi-check-circle-fill me-1"></i> Available
                                    <?php else: ?>
                                        <i class="bi bi-dash-circle me-1"></i> Off
                                    <?php endif; ?>
                                </div>
                            </div>
                        <?php endforeach; ?>
                    </div>
                </div>
                <div class="col-md-5 ps-md-4">
                    <h6 class="fw-bold text-dark mb-2"><i class="bi bi-calendar-event text-primary me-1"></i> Current Month (<?php echo date('F Y'); ?>)</h6>
                    <p class="text-secondary small mb-2">Manage meeting availability or add exception dates directly.</p>
                    <div class="p-3 bg-light rounded-3 border">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Active Available Days:</span>
                            <span class="badge bg-success"><?php echo count(array_filter($recurringAvailability ?? [])); ?> days / week</span>
                        </div>
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <span class="small text-muted">Special Date Exceptions:</span>
                            <span class="badge bg-secondary"><?php echo count($currentMonthExceptions ?? []); ?> date(s)</span>
                        </div>
                        <div class="mt-3 pt-2 border-top text-center">
                            <a href="<?php echo $basePath; ?>/iep/availability" class="btn btn-sm btn-primary w-100 fw-semibold py-1.5">
                                <i class="bi bi-calendar3 me-1"></i> Open Full Availability Calendar
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Weekly Availability Edit Modal -->
    <div class="modal fade" id="availabilityModal" tabindex="-1">
        <div class="modal-dialog">
            <div class="modal-content text-dark">
                <form id="dashboardWeeklyScheduleForm">
                    <div class="modal-header bg-light">
                        <h6 class="modal-title fw-bold"><i class="bi bi-calendar-week me-2 text-primary"></i>Update Weekly Availability</h6>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <div class="modal-body">
                        <p class="small text-muted mb-3">Select the days you are regularly available for IEP meetings with parents and specialists:</p>
                        <div class="row g-2">
                            <?php foreach ($weekDayNames as $i => $day): 
                                $checked = !empty($recurringAvailability[$i]);
                            ?>
                                <div class="col-6">
                                    <div class="p-2 border rounded bg-white">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" name="days[]" value="<?php echo $i; ?>" id="dashDay<?php echo $i; ?>" <?php echo $checked ? 'checked' : ''; ?>>
                                            <label class="form-check-label fw-semibold text-dark small" for="dashDay<?php echo $i; ?>">
                                                <?php echo $day; ?>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            <?php endforeach; ?>
                        </div>
                        <div id="availSaveStatus" class="mt-3 small" style="display:none;"></div>
                    </div>
                    <div class="modal-footer bg-light">
                        <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Close</button>
                        <button type="submit" class="btn btn-sm btn-primary fw-semibold" id="saveAvailBtn">Save Schedule</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const form = document.getElementById('dashboardWeeklyScheduleForm');
        if (form) {
            form.addEventListener('submit', function(e) {
                e.preventDefault();
                const btn = document.getElementById('saveAvailBtn');
                const statusDiv = document.getElementById('availSaveStatus');
                const formData = new FormData(form);
                
                btn.disabled = true;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span>Saving...';
                
                fetch('<?php echo $basePath; ?>/iep/availability/save-recurring', {
                    method: 'POST',
                    body: formData
                })
                .then(res => res.json())
                .then(data => {
                    if (data.success) {
                        statusDiv.style.display = 'block';
                        statusDiv.className = 'alert alert-success py-2 mt-3 small';
                        statusDiv.innerHTML = '<i class="bi bi-check-circle-fill me-1"></i> Availability updated successfully!';
                        setTimeout(() => {
                            window.location.reload();
                        }, 800);
                    } else {
                        statusDiv.style.display = 'block';
                        statusDiv.className = 'alert alert-danger py-2 mt-3 small';
                        statusDiv.innerHTML = data.message || 'Failed to update availability.';
                        btn.disabled = false;
                        btn.innerHTML = 'Save Schedule';
                    }
                })
                .catch(err => {
                    statusDiv.style.display = 'block';
                    statusDiv.className = 'alert alert-danger py-2 mt-3 small';
                    statusDiv.innerHTML = 'An unexpected error occurred.';
                    btn.disabled = false;
                    btn.innerHTML = 'Save Schedule';
                });
            });
        }
    });
    </script>

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

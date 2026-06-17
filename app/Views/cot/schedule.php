<?php
$pageTitle = 'Schedule Observation - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>Schedule Classroom Observation</h1>
        <a href="<?= $basePath ?>/cot/observations" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Cancel &amp; Go Back
        </a>
    </div>

    <div class="row justify-content-center">
        <div class="col-md-8">
            <div class="card border-0 shadow-sm">
                <div class="card-header text-white py-3" style="background-color: #1e4072;">
                    <h5 class="card-title mb-0">
                        <i class="bi bi-calendar-event"></i> Observation Details
                    </h5>
                </div>
                <div class="card-body p-4">
                    <form method="post" action="<?= $basePath ?>/cot/observations/schedule">
                        <!-- Observed Teacher -->
                        <div class="mb-3">
                            <label for="observed_teacher_id" class="form-label fw-semibold">Teacher to Observe</label>
                            <select class="form-select" id="observed_teacher_id" name="observed_teacher_id" required>
                                <option value="" disabled selected>-- Select SPED Teacher --</option>
                                <?php foreach ($teachers as $teacher): ?>
                                    <option value="<?= $teacher['id'] ?>">
                                        <?= htmlspecialchars($teacher['name']) ?> (<?= htmlspecialchars($teacher['email']) ?>)
                                    </option>
                                <?php endforeach; ?>
                            </select>
                            <div class="form-text">List of active SPED Teachers in the system.</div>
                        </div>

                        <!-- Subject & Grade Level -->
                        <div class="mb-3">
                            <label for="subject_grade_level" class="form-label fw-semibold">Subject &amp; Grade Level Taught</label>
                            <input type="text" class="form-control" id="subject_grade_level" name="subject_grade_level" 
                                   placeholder="e.g. Mathematics - Grade 3" required>
                        </div>

                        <div class="row">
                            <!-- School Year -->
                            <div class="col-md-4 mb-3">
                                <label for="school_year" class="form-label fw-semibold">School Year</label>
                                <select class="form-select" id="school_year" name="school_year" required>
                                    <?php foreach ($schoolYears as $sy): ?>
                                        <option value="<?= htmlspecialchars($sy) ?>" <?= $sy === 'SY 2025-2026' ? 'selected' : '' ?>>
                                            <?= htmlspecialchars($sy) ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>

                            <!-- Quarter -->
                            <div class="col-md-4 mb-3">
                                <label for="quarter" class="form-label fw-semibold">Quarter</label>
                                <select class="form-select" id="quarter" name="quarter" required>
                                    <option value="1st Quarter">1st Quarter</option>
                                    <option value="2nd Quarter">2nd Quarter</option>
                                    <option value="3rd Quarter">3rd Quarter</option>
                                    <option value="4th Quarter">4th Quarter</option>
                                </select>
                            </div>

                            <!-- Observation Number -->
                            <div class="col-md-4 mb-3">
                                <label class="form-label fw-semibold d-block">Observation Number</label>
                                <div class="pt-2">
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="observation_number" id="obs1" value="1" checked>
                                        <label class="form-check-label" for="obs1">1st Observation</label>
                                    </div>
                                    <div class="form-check form-check-inline">
                                        <input class="form-check-input" type="radio" name="observation_number" id="obs2" value="2">
                                        <label class="form-check-label" for="obs2">2nd Observation</label>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Scheduled Date/Time -->
                        <div class="mb-4">
                            <label for="scheduled_at" class="form-label fw-semibold">Scheduled Date &amp; Time</label>
                            <input type="datetime-local" class="form-control" id="scheduled_at" name="scheduled_at" required>
                        </div>

                        <!-- Submit Button -->
                        <div class="d-grid mt-4">
                            <button type="submit" class="btn text-white py-2 fw-semibold" style="background-color: #a01422;">
                                <i class="bi bi-check-circle"></i> Save Schedule &amp; Send Notification
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

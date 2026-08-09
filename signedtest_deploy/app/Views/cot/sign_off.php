<?php
$pageTitle = 'Sign Off Observation #' . $observation['id'] . ' - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .rating-btn-container {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
    }

    .rating-btn-static {
        min-height: 40px;
        min-width: 48px;
        font-size: 1rem;
        font-weight: 700;
        border: 2px solid #e2e8f0;
        margin: 3px;
        flex: 1;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        background-color: #f8fafc;
        color: #94a3b8;
    }

    /* Highlight class for active static rating */
    .rating-btn-static.active {
        background-color: #a01422 !important;
        border-color: #a01422 !important;
        color: #ffffff !important;
        box-shadow: 0 2px 5px rgba(160, 20, 34, 0.2);
    }

    .indicator-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
    }
</style>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h2 class="text-navy font-bold mb-0">Acknowledge Classroom Observation</h2>
        <a href="<?= $basePath ?>/cot/observations" class="btn btn-outline-secondary">
            <i class="bi bi-x-circle"></i> Cancel
        </a>
    </div>

    <!-- Review Pending Header Card -->
    <div class="card border-0 shadow-sm mb-4" style="border-left: 5px solid #a01422 !important;">
        <div class="card-body p-4">
            <div class="d-flex align-items-center mb-3">
                <i class="bi bi-shield-lock-fill text-crimson fs-2 me-3" style="color: #a01422;"></i>
                <div>
                    <h5 class="text-dark fw-bold mb-1">Awaiting Your Digital Signature</h5>
                    <p class="text-muted mb-0 small">Please review the classroom observation details, ratings, and comments below before signing.</p>
                </div>
            </div>

            <!-- Read Only Fields Info -->
            <div class="row bg-light rounded p-3 text-muted g-3">
                <div class="col-6 col-md-3">
                    <small class="d-block text-uppercase small">Observer Name</small>
                    <strong class="text-dark"><?= htmlspecialchars($observation['observer_name']) ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <small class="d-block text-uppercase small">Date Conducted</small>
                    <strong class="text-dark"><?= date('M j, Y', strtotime($observation['scheduled_at'])) ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <small class="d-block text-uppercase small">Subject &amp; Grade</small>
                    <strong class="text-dark"><?= htmlspecialchars($observation['subject_grade_level']) ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <small class="d-block text-uppercase small">Observation Number</small>
                    <strong class="text-dark"><?= $observation['observation_number'] == 1 ? '1st' : '2nd' ?> Observation</strong>
                </div>
                <div class="col-6 col-md-3 mt-md-2">
                    <small class="d-block text-uppercase small">School Year</small>
                    <strong class="text-dark"><?= htmlspecialchars($observation['school_year']) ?></strong>
                </div>
                <div class="col-6 col-md-3 mt-md-2">
                    <small class="d-block text-uppercase small">Quarter</small>
                    <strong class="text-dark"><?= htmlspecialchars($observation['quarter']) ?></strong>
                </div>
                <div class="col-6 col-md-3 mt-md-2">
                    <small class="d-block text-uppercase small">Status</small>
                    <div>
                        <span class="badge bg-warning text-dark">Pending Sign-off</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rated Indicators -->
    <h4 class="mb-3 text-navy"><i class="bi bi-list-check"></i> Rubric Competency Ratings</h4>
    <div class="mb-4">
        <?php foreach ($indicators as $indicator): 
            $selectedRating = $ratings[$indicator['id']] ?? null;
        ?>
            <div class="card indicator-card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <!-- Text -->
                        <div class="col-lg-7 mb-3 mb-lg-0">
                            <div class="d-flex align-items-start">
                                <span class="badge rounded-pill bg-light text-navy border me-2 mt-1">
                                    <?= $indicator['indicator_number'] ?>
                                </span>
                                <div>
                                    <p class="mb-0 fw-semibold text-dark fs-6"><?= htmlspecialchars($indicator['indicator_text']) ?></p>
                                    <span class="badge bg-secondary-subtle text-secondary-emphasis font-monospace small">Competency Code: <?= htmlspecialchars($indicator['competency_code']) ?></span>
                                </div>
                            </div>
                        </div>

                        <!-- Static Rating Buttons -->
                        <div class="col-lg-5">
                            <div class="rating-btn-container">
                                <?php foreach (['2', '3', '4', '5', '6', 'NO', 'N/A'] as $val): ?>
                                    <div class="rating-btn-static <?= $selectedRating === $val ? 'active' : '' ?>">
                                        <?= $val ?>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Comments -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-chat-left-text"></i> Observer's Comments</h5>
        </div>
        <div class="card-body">
            <p class="text-dark bg-light p-3 rounded mb-0" style="white-space: pre-wrap; min-height: 80px;"><?= !empty($observation['other_comments']) ? htmlspecialchars($observation['other_comments']) : 'No comment provided.' ?></p>
        </div>
    </div>

    <!-- Signature Pad -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header text-white py-3" style="background-color: #a01422;">
            <h5 class="card-title mb-0"><i class="bi bi-vector-pen me-2"></i>Your Digital Signature</h5>
        </div>
        <div class="card-body text-center">
            <p class="text-muted mb-3">Draw your signature below to acknowledge that you have reviewed this observation.</p>
            <canvas id="sigCanvas" width="600" height="200"
                    style="border:2px solid #1e4072;border-radius:8px;max-width:100%;touch-action:none;background:#fff;"></canvas>
            <div class="mt-3 d-flex justify-content-center gap-3 flex-wrap">
                <button type="button" class="btn btn-outline-secondary" onclick="clearSignature()">
                    <i class="bi bi-eraser"></i> Clear
                </button>
            </div>
        </div>
    </div>

    <!-- Sign-Off Form -->
    <form method="post" action="<?= $basePath ?>/cot/observations/<?= $observation['id'] ?>/sign-off" id="signOffForm">
        <input type="hidden" name="signature_data" id="signature_data" value="">
        <button type="button" class="btn text-white w-100 py-3 fw-bold fs-5 mb-5 shadow-sm" 
                style="background-color: #a01422; border-color: #a01422;" onclick="submitSignOff()">
            <i class="bi bi-vector-pen"></i> Sign &amp; Acknowledge Observation
        </button>
    </form>
</div>

<script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
<script>
    const canvas = document.getElementById('sigCanvas');
    let sigPad;

    if (canvas) {
        sigPad = new SignaturePad(canvas, { penColor: '#1e4072' });

        function resizeCanvas() {
            const ratio = Math.max(window.devicePixelRatio || 1, 1);
            const data = sigPad ? sigPad.toData() : [];
            canvas.width = canvas.offsetWidth * ratio;
            canvas.height = canvas.offsetHeight * ratio;
            canvas.getContext('2d').scale(ratio, ratio);
            sigPad.clear();
            if (data.length) {
                sigPad.fromData(data);
            }
        }

        window.addEventListener('resize', resizeCanvas);
        resizeCanvas();
    }

    function clearSignature() {
        sigPad?.clear();
    }

    function submitSignOff() {
        if (!sigPad || sigPad.isEmpty()) {
            Swal.fire({
                icon: 'warning',
                title: 'Signature Required',
                text: 'Please draw your signature in the pad before submitting.',
                confirmButtonColor: '#a01422'
            });
            return;
        }

        document.getElementById('signature_data').value = sigPad.toDataURL('image/png');
        document.getElementById('signOffForm').submit();
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

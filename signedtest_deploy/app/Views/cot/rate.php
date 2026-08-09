<?php
$pageTitle = 'Live Rating — Observation #' . $observation['id'] . ' — SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .rating-btn-container {
        display: flex;
        flex-wrap: wrap;
        width: 100%;
    }

    .rating-btn {
        min-height: 48px;
        min-width: 55px;
        font-size: 1.1rem;
        font-weight: 700;
        border-width: 2px;
        margin: 4px;
        flex: 1;
        transition: all 0.2s ease;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        border-radius: 8px;
        cursor: pointer;
    }

    .rating-btn-2, .rating-btn-3 {
        border-color: #ffd085;
        color: #b56b00;
        background-color: #fff9f0;
    }
    .rating-btn-2:hover, .rating-btn-3:hover {
        background-color: #ffeccf;
    }

    .rating-btn-4, .rating-btn-5 {
        border-color: #b0cbe8;
        color: #1e4072;
        background-color: #f2f7fc;
    }
    .rating-btn-4:hover, .rating-btn-5:hover {
        background-color: #dbe7f5;
    }

    .rating-btn-6 {
        border-color: #a3e2c9;
        color: #0f6e49;
        background-color: #f0fbf6;
    }
    .rating-btn-6:hover {
        background-color: #daf5e9;
    }

    .rating-btn-no {
        border-color: #dcdcdc;
        color: #666;
        background-color: #f8f8f8;
    }
    .rating-btn-no:hover {
        background-color: #eeeeee;
    }

    /* Selected state - highlighted crimson */
    .rating-btn.active {
        background-color: #a01422 !important;
        border-color: #a01422 !important;
        color: #ffffff !important;
        box-shadow: 0 4px 10px rgba(160, 20, 34, 0.3);
        transform: translateY(-2px);
    }

    .indicator-card {
        border-radius: 12px;
        border: 1px solid #e2e8f0;
        transition: border-color 0.3s;
    }
    .indicator-card:hover {
        border-color: #cbd5e1;
    }
</style>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    
    <!-- Top summary bar -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body p-4">
            <div class="d-flex justify-content-between align-items-start align-items-md-center flex-column flex-md-row mb-3">
                <div>
                    <h2 class="text-navy font-bold mb-1">Live Observation Classroom Rating</h2>
                    <span class="badge bg-warning text-dark px-3 py-2 fs-6">Status: <?= ucwords($observation['status']) ?></span>
                </div>
                <a href="<?= $basePath ?>/cot/observations" class="btn btn-outline-secondary mt-3 mt-md-0">
                    <i class="bi bi-x-circle"></i> Save Draft &amp; Exit
                </a>
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
                    <small class="d-block text-uppercase small">Teacher Observed</small>
                    <strong class="text-dark"><?= htmlspecialchars($observation['observed_teacher_name']) ?></strong>
                </div>
                <div class="col-6 col-md-3">
                    <small class="d-block text-uppercase small">Subject &amp; Grade</small>
                    <strong class="text-dark"><?= htmlspecialchars($observation['subject_grade_level']) ?></strong>
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
                    <small class="d-block text-uppercase small">Observation Number</small>
                    <strong class="text-dark"><?= $observation['observation_number'] == 1 ? '1st' : '2nd' ?> Observation</strong>
                </div>
            </div>

            <!-- Real-time Progress Indicator -->
            <div class="mt-4">
                <div class="d-flex justify-content-between mb-2">
                    <span class="fw-semibold">Progress</span>
                    <span id="progress-text" class="fw-bold text-navy">
                        <?= count($ratings) ?> of <?= count($indicators) ?> indicators rated
                    </span>
                </div>
                <div class="progress" style="height: 10px;">
                    <div id="progress-bar" class="progress-bar bg-success" role="progressbar" 
                         style="width: <?= count($indicators) > 0 ? (count($ratings) / count($indicators)) * 100 : 0 ?>%;"></div>
                </div>
            </div>
        </div>
    </div>

    <!-- Rating Sheet Indicators -->
    <h4 class="mb-3 text-navy"><i class="bi bi-list-check"></i> Competency Indicator Items</h4>
    
    <div class="mb-4">
        <?php foreach ($indicators as $indicator): 
            $selectedRating = $ratings[$indicator['id']] ?? null;
        ?>
            <div class="card indicator-card border-0 shadow-sm mb-3">
                <div class="card-body p-3">
                    <div class="row align-items-center">
                        <!-- Text -->
                        <div class="col-lg-6 mb-3 mb-lg-0">
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

                        <!-- Buttons Selector -->
                        <div class="col-lg-6">
                            <div class="rating-btn-container" id="rating-group-<?= $indicator['id'] ?>">
                                <?php foreach (['2', '3', '4', '5', '6', 'NO', 'N/A'] as $val): 
                                    $btnClass = 'rating-btn-no';
                                    if ($val === '2' || $val === '3') {
                                        $btnClass = 'rating-btn-' . $val;
                                    } elseif ($val === '4' || $val === '5') {
                                        $btnClass = 'rating-btn-' . $val;
                                    } elseif ($val === '6') {
                                        $btnClass = 'rating-btn-6';
                                    }
                                ?>
                                    <button type="button" 
                                            class="rating-btn <?= $btnClass ?> <?= $selectedRating === $val ? 'active' : '' ?>" 
                                            data-value="<?= $val ?>"
                                            onclick="selectRating(<?= $indicator['id'] ?>, '<?= $val ?>')">
                                        <?= $val ?>
                                    </button>
                                <?php endforeach; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <!-- Free-text Comments -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white py-3">
            <h5 class="card-title mb-0"><i class="bi bi-chat-left-text"></i> Other Comments</h5>
        </div>
        <div class="card-body">
            <div class="form-group">
                <textarea id="other_comments" class="form-control" rows="4" 
                          placeholder="Type general observation comments and notes here. Saves automatically when clicking outside."><?= htmlspecialchars($observation['other_comments'] ?? '') ?></textarea>
            </div>
            <div class="form-text text-muted mt-2">
                <i class="bi bi-cloud-check-fill text-success"></i> Auto-saves on blur (when you click away).
            </div>
        </div>
    </div>

    <!-- Finalize Button -->
    <form method="post" action="<?= $basePath ?>/cot/observations/<?= $observation['id'] ?>/finalize" id="finalizeForm">
        <button type="button" class="btn text-white w-100 py-3 fw-bold fs-5 mb-5 shadow-sm" 
                style="background-color: #a01422; border-color: #a01422;" onclick="confirmFinalize()">
            <i class="bi bi-clipboard-check-fill"></i> Finalize Observation
        </button>
    </form>
</div>

<script>
    const totalIndicators = <?= count($indicators) ?>;
    let ratedIndicators = <?= count($ratings) ?>;

    function selectRating(indicatorId, value) {
        const group = document.getElementById(`rating-group-${indicatorId}`);
        const buttons = group.getElementsByClassName('rating-btn');
        
        // Optimistic UI update
        for (let btn of buttons) {
            btn.classList.remove('active');
            if (btn.getAttribute('data-value') === value) {
                btn.classList.add('active');
            }
        }

        // AJAX POST to save rating
        const formData = new FormData();
        formData.append('indicator_id', indicatorId);
        formData.append('rating', value);

        fetch('<?= $basePath ?>/cot/observations/<?= $observation['id'] ?>/rate/save', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Network response not ok');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                ratedIndicators = data.rated_count;
                updateProgress(data.rated_count, data.total_indicators);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Auto-save failed',
                    text: data.message || 'Error occurred while saving rating.'
                });
            }
        })
        .catch(err => {
            console.error('Error auto-saving rating:', err);
            Swal.fire({
                icon: 'warning',
                title: 'Offline or Connection Lost',
                text: 'Rating saved locally. We will retry to sync when connection is back.'
            });
        });
    }

    // Auto-save comments on blur
    document.getElementById('other_comments').addEventListener('blur', function() {
        const comments = this.value;
        const formData = new FormData();
        formData.append('other_comments', comments);

        fetch('<?= $basePath ?>/cot/observations/<?= $observation['id'] ?>/comments/save', {
            method: 'POST',
            body: formData
        })
        .then(response => {
            if (!response.ok) throw new Error('Comments save failed');
            return response.json();
        })
        .then(data => {
            if (data.success) {
                console.log('Comments saved successfully.');
            }
        })
        .catch(err => {
            console.error('Error auto-saving comments:', err);
        });
    });

    function updateProgress(rated, total) {
        document.getElementById('progress-text').innerText = `${rated} of ${total} indicators rated`;
        const percentage = total > 0 ? (rated / total) * 100 : 0;
        document.getElementById('progress-bar').style.width = `${percentage}%`;
    }

    function confirmFinalize() {
        let isComplete = ratedIndicators === totalIndicators;
        let warningText = "You won't be able to edit ratings after this.";
        
        if (!isComplete) {
            warningText = "Warning: Some indicators are UNRATED! They will be excluded from the computed average score. You cannot edit ratings after finalization.";
        }

        Swal.fire({
            title: isComplete ? 'Finalize Observation?' : 'Finalize with Unrated Items?',
            text: warningText,
            icon: isComplete ? 'question' : 'warning',
            showCancelButton: true,
            confirmButtonColor: '#a01422',
            cancelButtonColor: '#6c757d',
            confirmButtonText: 'Yes, Finalize!'
        }).then((result) => {
            if (result.isConfirmed) {
                document.getElementById('finalizeForm').submit();
            }
        });
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

<?php
$pageTitle = 'COT Indicator Sets - SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>COT Indicator Sets</h1>
        <a href="<?= $basePath ?>/cot/observations" class="btn btn-outline-secondary">
            <i class="bi bi-arrow-left"></i> Back to Observations
        </a>
    </div>

    <!-- Info box -->
    <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
            <div class="d-flex align-items-center">
                <i class="bi bi-info-circle text-primary me-3" style="font-size: 2rem;"></i>
                <div>
                    <h5 class="mb-1">About Indicator Sets</h5>
                    <p class="text-muted mb-0">
                        Classroom Observation Tool (COT) indicators vary by School Year. You can manage indicator sets for each school year below. Once an observation is scheduled, the indicator set corresponding to that school year will be used for rating.
                    </p>
                </div>
            </div>
        </div>
    </div>

    <div class="row">
        <!-- School Year Selector and Quick Load -->
        <div class="col-lg-4 mb-4">
            <div class="card border-0 shadow-sm mb-4">
                <div class="card-header bg-white py-3">
                    <h5 class="card-title mb-0">Select School Year</h5>
                </div>
                <div class="card-body">
                    <form method="get" action="<?= $basePath ?>/cot/indicators" id="syForm">
                        <div class="mb-3">
                            <label for="school_year_select" class="form-label">Active School Year</label>
                            <select class="form-select" id="school_year_select" name="school_year" onchange="this.form.submit()">
                                <?php 
                                $predefinedYears = ['SY 2024-2025', 'SY 2025-2026', 'SY 2026-2027', 'SY 2027-2028'];
                                $allYears = array_unique(array_merge($schoolYears, $predefinedYears));
                                sort($allYears);
                                $allYears = array_reverse($allYears);
                                foreach ($allYears as $sy):
                                ?>
                                    <option value="<?= htmlspecialchars($sy) ?>" <?= $schoolYear === $sy ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($sy) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </form>

                    <div class="border-top pt-3 mt-3">
                        <label class="form-label text-muted d-block small mb-2">Or define indicators for a new school year:</label>
                        <form method="get" action="<?= $basePath ?>/cot/indicators" class="d-flex gap-2">
                            <input type="text" name="school_year" class="form-control" placeholder="e.g. SY 2028-2029" required>
                            <button type="submit" class="btn btn-outline-primary text-nowrap">Go</button>
                        </form>
                    </div>
                </div>
            </div>

            <!-- Quick Load Card -->
            <?php if ($schoolYear === 'SY 2025-2026' || empty($indicators)): ?>
            <div class="card border-0 shadow-sm" style="border-left: 4px solid #a01422 !important;">
                <div class="card-body">
                    <h5 class="card-title text-danger mb-2">
                        <i class="bi bi-lightning-charge-fill"></i> Load Standard PMES Set
                    </h5>
                    <p class="small text-muted mb-3">
                        Quickly populate this year's indicator set with the 9 standard competency indicators from the PMES rating sheet.
                    </p>
                    <form method="post" action="<?= $basePath ?>/cot/indicators/load-defaults">
                        <input type="hidden" name="school_year" value="<?= htmlspecialchars($schoolYear) ?>">
                        <button type="submit" class="btn btn-danger w-100" style="background-color: #a01422; border-color: #a01422;">
                            Load Default Indicators
                        </button>
                    </form>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Indicator Editor -->
        <div class="col-lg-8 mb-4">
            <div class="card border-0 shadow-sm">
                <div class="card-header bg-white py-3 d-flex justify-content-between align-items-center">
                    <h5 class="card-title mb-0">
                        Indicators for <span class="text-danger"><?= htmlspecialchars($schoolYear) ?></span>
                    </h5>
                    <span class="badge bg-secondary"><?= count($indicators) ?> Indicators Defined</span>
                </div>
                <div class="card-body">
                    <form method="post" action="<?= $basePath ?>/cot/indicators/save">
                        <input type="hidden" name="school_year" value="<?= htmlspecialchars($schoolYear) ?>">
                        
                        <div id="indicatorRowsContainer">
                            <?php if (!empty($indicators)): ?>
                                <?php foreach ($indicators as $index => $indicator): ?>
                                    <div class="row mb-3 indicator-row border-bottom pb-3 align-items-center">
                                        <div class="col-auto">
                                            <span class="fs-5 fw-bold text-muted row-number"><?= $index + 1 ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-1">Competency Code</label>
                                            <input type="text" name="competency_code[]" class="form-control form-control-sm" 
                                                   value="<?= htmlspecialchars($indicator['competency_code']) ?>" placeholder="e.g. 1.1.2" required>
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label small text-muted mb-1">Indicator Text</label>
                                            <textarea name="indicator_text[]" class="form-control form-control-sm" rows="2" 
                                                      placeholder="Enter full indicator text" required><?= htmlspecialchars($indicator['indicator_text']) ?></textarea>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-3 remove-row-btn" onclick="removeRow(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <!-- Pre-fill with a few blank rows if completely empty -->
                                <?php for ($i = 0; $i < 3; $i++): ?>
                                    <div class="row mb-3 indicator-row border-bottom pb-3 align-items-center">
                                        <div class="col-auto">
                                            <span class="fs-5 fw-bold text-muted row-number"><?= $i + 1 ?></span>
                                        </div>
                                        <div class="col-md-3">
                                            <label class="form-label small text-muted mb-1">Competency Code</label>
                                            <input type="text" name="competency_code[]" class="form-control form-control-sm" placeholder="e.g. 1.1.2">
                                        </div>
                                        <div class="col-md-7">
                                            <label class="form-label small text-muted mb-1">Indicator Text</label>
                                            <textarea name="indicator_text[]" class="form-control form-control-sm" rows="2" placeholder="Enter full indicator text"></textarea>
                                        </div>
                                        <div class="col-md-1 text-end">
                                            <button type="button" class="btn btn-sm btn-outline-danger mt-3 remove-row-btn" onclick="removeRow(this)">
                                                <i class="bi bi-trash"></i>
                                            </button>
                                        </div>
                                    </div>
                                <?php endfor; ?>
                            <?php endif; ?>
                        </div>

                        <div class="d-flex justify-content-between align-items-center mt-4">
                            <button type="button" class="btn btn-outline-secondary" onclick="addBlankRow()">
                                <i class="bi bi-plus"></i> Add Indicator Row
                            </button>
                            <button type="submit" class="btn btn-primary" style="background-color: #1e4072; border-color: #1e4072;">
                                <i class="bi bi-save"></i> Save Indicator Set
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    function addBlankRow() {
        const container = document.getElementById('indicatorRowsContainer');
        const rows = container.getElementsByClassName('indicator-row');
        const nextIndex = rows.length + 1;

        const rowDiv = document.createElement('div');
        rowDiv.className = 'row mb-3 indicator-row border-bottom pb-3 align-items-center';
        rowDiv.innerHTML = `
            <div class="col-auto">
                <span class="fs-5 fw-bold text-muted row-number">${nextIndex}</span>
            </div>
            <div class="col-md-3">
                <label class="form-label small text-muted mb-1">Competency Code</label>
                <input type="text" name="competency_code[]" class="form-control form-control-sm" placeholder="e.g. 1.1.2" required>
            </div>
            <div class="col-md-7">
                <label class="form-label small text-muted mb-1">Indicator Text</label>
                <textarea name="indicator_text[]" class="form-control form-control-sm" rows="2" placeholder="Enter full indicator text" required></textarea>
            </div>
            <div class="col-md-1 text-end">
                <button type="button" class="btn btn-sm btn-outline-danger mt-3 remove-row-btn" onclick="removeRow(this)">
                    <i class="bi bi-trash"></i>
                </button>
            </div>
        `;
        container.appendChild(rowDiv);
    }

    function removeRow(button) {
        const container = document.getElementById('indicatorRowsContainer');
        const rows = container.getElementsByClassName('indicator-row');
        
        if (rows.length <= 1) {
            alert('At least one indicator row must remain.');
            return;
        }

        const row = button.closest('.indicator-row');
        row.remove();

        // Re-number rows
        const rowNumbers = container.getElementsByClassName('row-number');
        for (let i = 0; i < rowNumbers.length; i++) {
            rowNumbers[i].innerText = i + 1;
        }
    }
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

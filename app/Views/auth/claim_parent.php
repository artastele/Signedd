<?php
// Part of: SignED — Parent QR Claim & Activation View
// Last modified: 2026-09-22
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($pageTitle ?? 'Parent Activation — SignED') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $basePath ?>/css/custom.css">
    <style>
        body {
            background: #f8fafc;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 1.5rem 1rem;
            font-family: system-ui, -apple-system, sans-serif;
        }
        .claim-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            max-width: 520px;
            width: 100%;
        }
        .claim-header {
            background: linear-gradient(135deg, #1e293b 0%, #0f172a 100%);
            color: #ffffff;
            padding: 1.75rem 1.5rem;
            text-align: center;
        }
        .learner-badge-card {
            background: #f1f5f9;
            border-radius: 8px;
            padding: 1rem 1.25rem;
            border-left: 4px solid #0284c7;
            margin-bottom: 1.25rem;
        }
        .form-control, .form-select {
            border-radius: 6px;
            font-size: 0.875rem;
            padding: 0.6rem 0.85rem;
            border: 1px solid #cbd5e1;
        }
        .form-control:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        .btn-claim {
            border-radius: 6px;
            font-size: 0.9rem;
            font-weight: 600;
            padding: 0.65rem 1.25rem;
            transition: all 0.2s ease;
        }
    </style>
</head>
<body>

    <div class="claim-card">
        <!-- Card Header -->
        <div class="claim-header">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-10 p-2 mb-2" style="width: 48px; height: 48px;">
                <i class="bi bi-qr-code-scan fs-4 text-warning"></i>
            </div>
            <h4 class="fw-bold mb-1">SignED Parent Activation</h4>
            <p class="text-white-50 small mb-0"><?= htmlspecialchars($learner['school_name'] ?? 'SPED Integrated School') ?></p>
        </div>

        <!-- Card Body -->
        <div class="p-4">

            <!-- Alert messages -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm small mb-3">
                    <i class="bi bi-exclamation-circle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm small mb-3">
                    <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Learner Confirmation Card -->
            <div class="learner-badge-card">
                <span class="badge bg-primary px-2 py-1 mb-1" style="font-size: 0.72rem;">Enrolled SPED Learner</span>
                <h5 class="fw-bold text-dark mb-1"><?= htmlspecialchars($learner['student_name']) ?></h5>
                <div class="small text-secondary">
                    <span>Grade: <strong><?= htmlspecialchars($learner['grade_level_to_enroll'] ?? 'Kinder') ?></strong></span>
                    <span class="mx-1">•</span>
                    <span>LRN: <strong><?= htmlspecialchars($learner['lrn'] ?? $learner['student_id'] ?? 'Registered') ?></strong></span>
                </div>
            </div>

            <?php if ($alreadyClaimed): ?>
                <div class="alert alert-warning border-0 shadow-sm small">
                    <i class="bi bi-shield-lock-fill me-1"></i>
                    This learner profile is already connected to a registered parent account. If you believe this is an error, please contact your SPED teacher or school administration.
                </div>
                <div class="text-center mt-3">
                    <a href="<?= $basePath ?>/login" class="btn btn-outline-secondary btn-claim w-100">
                        <i class="bi bi-box-arrow-in-right me-1"></i> Return to Login
                    </a>
                </div>

            <?php elseif (!empty($currentUserId) && $currentUserRole === 'parent'): ?>
                <!-- Already logged in as parent: Quick One-Click Link -->
                <p class="text-muted small mb-3">
                    You are currently signed in as <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Parent') ?></strong>. Click below to connect this learner to your dashboard.
                </p>

                <form method="POST" action="<?= $basePath ?>/invite/activate">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">
                    <button type="submit" class="btn btn-success btn-claim w-100">
                        <i class="bi bi-link-45deg me-1"></i> Connect &amp; Go to Dashboard
                    </button>
                </form>

            <?php else: ?>
                <!-- New Parent Registration Form -->
                <p class="text-muted small mb-3">
                    Set up your parent account to monitor your child's lessons, interactive learning progress, and IEP records.
                </p>

                <form method="POST" action="<?= $basePath ?>/invite/activate">
                    <input type="hidden" name="token" value="<?= htmlspecialchars($token) ?>">

                    <?php 
                    $defaultParentName = '';
                    if (!empty($learner['guardian_first_name'])) {
                        $defaultParentName = trim($learner['guardian_first_name'] . ' ' . ($learner['guardian_last_name'] ?? ''));
                    }
                    ?>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Your Full Name <span class="text-danger">*</span></label>
                        <input type="text" name="name" class="form-control" 
                               value="<?= htmlspecialchars($defaultParentName) ?>" 
                               placeholder="e.g. Maria Dela Cruz" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Email Address <span class="text-danger">*</span></label>
                        <input type="email" name="email" class="form-control" 
                               placeholder="e.g. parent@example.com" required>
                    </div>

                    <div class="mb-3">
                        <label class="form-label small fw-semibold text-secondary">Contact Number</label>
                        <input type="text" name="contact_number" class="form-control" 
                               value="<?= htmlspecialchars($learner['guardian_contact_number'] ?? '') ?>" 
                               placeholder="e.g. 09171234567">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">Create Password <span class="text-danger">*</span></label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 6 characters" required>
                        </div>
                        <div class="col-sm-6">
                            <label class="form-label small fw-semibold text-secondary">Confirm Password <span class="text-danger">*</span></label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-type password" required>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-claim w-100 mb-3">
                        <i class="bi bi-person-check-fill me-1"></i> Activate &amp; Connect to Child
                    </button>

                    <div class="text-center small text-muted">
                        Already have a parent account? 
                        <a href="<?= $basePath ?>/login?return_to=<?= urlencode('/invite/claim/' . $token) ?>" class="text-primary fw-semibold text-decoration-none">
                            Log in here to link
                        </a>
                    </div>
                </form>
            <?php endif; ?>

        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>

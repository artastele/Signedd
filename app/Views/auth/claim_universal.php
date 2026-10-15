<?php
// Part of: SignED — Universal Parent GC Activation View (By Learner Name/Alias)
// Designed specifically for mobile screens, messenger group chat distribution,
// and strict adherence to the Data Privacy Act (RA 10173).
?>
<!DOCTYPE html>
<html lang="ceb">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0, user-scalable=no">
    <title><?= htmlspecialchars($pageTitle ?? 'Parent Portal Activation — SignED') ?></title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
    <link rel="stylesheet" href="<?= $basePath ?>/css/custom.css">
    <style>
        :root {
            --signed-primary: #1e4072;
            --signed-accent: #0284c7;
            --signed-dark: #0f172a;
        }
        * {
            box-sizing: border-box;
        }
        body {
            background: #f1f5f9;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 12px 10px;
            font-family: system-ui, -apple-system, 'Segoe UI', Roboto, Helvetica, Arial, sans-serif;
            color: #1e293b;
        }
        .claim-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            overflow: hidden;
            max-width: 500px;
            width: 100%;
            margin: auto;
        }
        .claim-header {
            background: linear-gradient(135deg, #1e4072 0%, #0f172a 100%);
            color: #ffffff;
            padding: 1.5rem 1.25rem;
            text-align: center;
        }
        .form-control, .form-select {
            border-radius: 7px;
            font-size: 15px;
            padding: 0.65rem 0.85rem;
            border: 1px solid #cbd5e1;
            width: 100%;
        }
        .form-control:focus, .form-select:focus {
            border-color: #0284c7;
            box-shadow: 0 0 0 3px rgba(2, 132, 199, 0.15);
        }
        .btn-claim {
            border-radius: 7px;
            font-size: 0.95rem;
            font-weight: 600;
            padding: 0.75rem 1.25rem;
            transition: all 0.2s ease;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
        }
        .privacy-pill {
            background: #f8fafc;
            border: 1px solid #e2e8f0;
            border-radius: 8px;
            padding: 10px 12px;
            font-size: 0.78rem;
            color: #475569;
            line-height: 1.45;
        }
        .sms-badge {
            background: #e0f2fe;
            color: #0369a1;
            border-radius: 6px;
            padding: 2px 7px;
            font-size: 0.72rem;
            font-weight: 600;
        }
        @media (max-width: 480px) {
            body { padding: 8px; }
            .claim-card { border-radius: 10px; }
            .claim-header { padding: 1.25rem 1rem; }
            .p-4 { padding: 1.15rem !important; }
        }
    </style>
</head>
<body>

    <div class="claim-card">
        <!-- Header -->
        <div class="claim-header">
            <div class="d-inline-flex align-items-center justify-content-center rounded-circle bg-white bg-opacity-10 p-2 mb-2" style="width: 46px; height: 46px;">
                <i class="bi bi-chat-heart-fill fs-4 text-warning"></i>
            </div>
            <h4 class="fw-bold mb-1" style="font-size: 1.25rem; letter-spacing: -0.3px;">SignED Parent Portal</h4>
            <p class="text-white-50 small mb-0" style="font-size: 0.83rem;">
                SPED LMS — Koneksyon sa Ginikanan ug Estudyante
            </p>
        </div>

        <!-- Body -->
        <div class="p-4">

            <!-- Flash Error -->
            <?php if (!empty($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm small mb-3 rounded-3" role="alert">
                    <i class="bi bi-exclamation-circle-fill me-1"></i> <?= htmlspecialchars($error) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <!-- Flash Success -->
            <?php if (!empty($success)): ?>
                <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm small mb-3 rounded-3" role="alert">
                    <i class="bi bi-check-circle-fill me-1"></i> <?= htmlspecialchars($success) ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="privacy-pill mb-3">
                <i class="bi bi-shield-check text-primary me-1 fw-bold"></i>
                <strong>Data Privacy Act (RA 10173):</strong> Aron maprotektahan ang data privacy, mga <strong>ngalan o opisyal nga aliases</strong> sa bata ang gilista dinhi. Pili-a ang ngalan o alias nga gihatag sa inyong SPED Teacher.
            </div>

            <?php if (!empty($currentUserId) && $currentUserRole === 'parent'): ?>
                <!-- Already Logged In Parent Form -->
                <div class="alert alert-info border-0 rounded-3 small mb-3" style="background:#e0f2fe; color:#0369a1;">
                    <i class="bi bi-person-check-fill me-1"></i>
                    Naka-log in ka karon isip: <strong><?= htmlspecialchars($_SESSION['name'] ?? 'Parent') ?></strong>
                </div>

                <form method="POST" action="<?= $basePath ?>/invite/claim-by-alias">
                    <input type="hidden" name="_csrf_token" value="<?= CSRFHelper::getToken() ?>">

                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            Pilia ang Ngalan / Alias sa Imong Anak: <span class="text-danger">*</span>
                        </label>
                        <select name="student_record_id" class="form-select form-select-lg" required>
                            <option value="">-- Pilia ang Estudiyante --</option>
                            <?php foreach ($learners as $l): ?>
                                <option value="<?= (int)$l['student_record_id'] ?>">
                                    <?= htmlspecialchars($l['student_name']) ?>
                                    <?= !empty($l['grade_level_to_enroll']) ? ' — ' . htmlspecialchars($l['grade_level_to_enroll']) : '' ?>
                                    <?= !empty($l['section_name']) ? ' (' . htmlspecialchars($l['section_name']) . ')' : '' ?>
                                    <?= !empty($l['linked_parent_name']) ? ' [Naa nay Parent]' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                    </div>

                    <button type="submit" class="btn btn-primary btn-claim w-100 py-2.5">
                        <i class="bi bi-link-45deg fs-5"></i> I-connect sa Akong Account
                    </button>
                </form>

            <?php else: ?>
                <!-- New Parent Registration Form -->
                <form method="POST" action="<?= $basePath ?>/invite/claim-by-alias">
                    <input type="hidden" name="_csrf_token" value="<?= CSRFHelper::getToken() ?>">

                    <!-- Step 1: Child Selection -->
                    <div class="mb-3">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            1. Pilia ang Ngalan / Alias sa Imong Anak: <span class="text-danger">*</span>
                        </label>
                        <select name="student_record_id" class="form-select form-select-lg" required style="font-size: 0.95rem;">
                            <option value="">-- Pilia ang Estudiyante / Alias --</option>
                            <?php foreach ($learners as $l): ?>
                                <option value="<?= (int)$l['student_record_id'] ?>">
                                    <?= htmlspecialchars($l['student_name']) ?>
                                    <?= !empty($l['grade_level_to_enroll']) ? ' — ' . htmlspecialchars($l['grade_level_to_enroll']) : '' ?>
                                    <?= !empty($l['section_name']) ? ' (' . htmlspecialchars($l['section_name']) . ')' : '' ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text small text-muted" style="font-size:0.75rem;">
                            Walay LRN nga gikinahanglan. Pilia lang ang alias sa imong anak.
                        </div>
                    </div>

                    <hr class="my-3 text-muted opacity-25">

                    <!-- Step 2: Parent Details -->
                    <div class="mb-2.5">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            2. Imong Kompletong Ngalan (Ginikanan/Guardian): <span class="text-danger">*</span>
                        </label>
                        <input type="text" name="name" class="form-control" placeholder="pananglitan: Maria Santos" required autocomplete="name">
                    </div>

                    <div class="mb-2.5">
                        <div class="d-flex align-items-center justify-content-between mb-1">
                            <label class="form-label fw-semibold small text-dark mb-0">
                                Cellphone / Mobile Number: <span class="text-danger">*</span>
                            </label>
                            <span class="sms-badge"><i class="bi bi-chat-text-fill me-1"></i>Para sa SMS Alerts</span>
                        </div>
                        <input type="tel" name="contact_number" class="form-control" placeholder="09171234567" required autocomplete="tel">
                        <div class="form-text small text-muted" style="font-size:0.75rem;">
                            Makadawat ka og text updates inig upload ug inig human sa activities sa bata.
                        </div>
                    </div>

                    <div class="mb-2.5">
                        <label class="form-label fw-semibold small text-dark mb-1">
                            Email Address: <span class="text-danger">*</span>
                        </label>
                        <input type="email" name="email" class="form-control" placeholder="parent@gmail.com" required autocomplete="email">
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-dark mb-1">
                                Password: <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="password" class="form-control" placeholder="Min. 6 chars" required minlength="6" autocomplete="new-password">
                        </div>
                        <div class="col-6">
                            <label class="form-label fw-semibold small text-dark mb-1">
                                Kumpirma: <span class="text-danger">*</span>
                            </label>
                            <input type="password" name="confirm_password" class="form-control" placeholder="Balika" required minlength="6" autocomplete="new-password">
                        </div>
                    </div>

                    <!-- Step 3: Data Privacy Consent -->
                    <div class="form-check mb-3">
                        <input class="form-check-input" type="checkbox" name="privacy_consent" id="privacyConsentCheck" value="1" required>
                        <label class="form-check-label small text-secondary" for="privacyConsentCheck" style="font-size: 0.8rem; line-height: 1.4;">
                            Miuyon ako sa pagkolekta ug pagproseso sa akong impormasyon subay sa 
                            <a href="javascript:void(0)" onclick="openPrivacyModal()" class="text-primary fw-semibold text-decoration-none">
                                Data Privacy Act (RA 10173)
                            </a>.
                        </label>
                    </div>

                    <!-- Submit -->
                    <button type="submit" class="btn btn-primary btn-claim w-100 py-2.5 mb-3" style="background:#1e4072; border-color:#1e4072;">
                        <i class="bi bi-check-circle-fill"></i> I-connect ang Akong Account &amp; Pagsugod
                    </button>
                </form>

                <div class="text-center pt-2 border-top">
                    <span class="small text-muted">Naa na kay account?</span>
                    <a href="<?= $basePath ?>/login" class="small fw-semibold text-primary text-decoration-none ms-1">
                        Mag Log in Dinhi
                    </a>
                </div>
            <?php endif; ?>

        </div>
    </div>

    <!-- Include Data Privacy Modal Component -->
    <?php require_once __DIR__ . '/../components/data_privacy_modal.php'; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        function openPrivacyModal() {
            var modalEl = document.getElementById('dataPrivacyModal');
            if (modalEl) {
                var modal = new bootstrap.Modal(modalEl);
                modal.show();
            }
        }
    </script>
</body>
</html>

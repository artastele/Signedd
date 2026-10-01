<?php
// Part of: SignED — Invalid QR Claim Link View
// Last modified: 2026-09-22
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Invalid Invitation — SignED</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css">
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
        .error-card {
            background: #ffffff;
            border-radius: 12px;
            box-shadow: 0 4px 20px rgba(0,0,0,0.06);
            border: 1px solid #e2e8f0;
            padding: 2.5rem 2rem;
            max-width: 460px;
            width: 100%;
            text-align: center;
        }
    </style>
</head>
<body>
    <div class="error-card">
        <div class="rounded-circle bg-danger bg-opacity-10 text-danger p-3 d-inline-flex align-items-center justify-content-center mb-3" style="width: 64px; height: 64px;">
            <i class="bi bi-exclamation-triangle-fill fs-2"></i>
        </div>
        <h4 class="fw-bold mb-2">Invalid or Expired Link</h4>
        <p class="text-muted small mb-4">
            This parent invitation link could not be found or has already been used. Please reach out to your SPED Teacher to request a new QR code or invitation link.
        </p>
        <a href="<?= $basePath ?>/login" class="btn btn-primary px-4 py-2" style="border-radius:6px; font-weight:600;">
            <i class="bi bi-house-door-fill me-1"></i> Go to SignED Login
        </a>
    </div>
</body>
</html>

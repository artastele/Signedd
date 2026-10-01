<?php
// Part of: SignED — Printable Teacher FSL Quick Reference Guide
// Last modified: 2026-08-27
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Printable FSL Classroom Quick Reference Guide — SignED</title>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/css/bootstrap.min.css">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background: #fff; color: #111; }
        .print-header { border-bottom: 2px solid #1e4072; padding-bottom: 12px; margin-bottom: 20px; }
        .guide-card { border: 1px solid #cbd5e1; border-radius: 8px; padding: 14px; margin-bottom: 16px; page-break-inside: avoid; }
        @media print {
            .no-print { display: none !important; }
            body { padding: 0; }
        }
    </style>
</head>
<body class="p-4">

    <div class="no-print d-flex justify-content-between align-items-center mb-4 bg-light p-3 rounded border">
        <div>
            <h5 class="mb-0 fw-bold">Printable FSL Classroom Reference Guide</h5>
            <small class="text-muted">Print this quick-reference sheet for SPED classroom walls and IEP meeting reference.</small>
        </div>
        <button class="btn btn-primary" onclick="window.print()">
            Print Guide
        </button>
    </div>

    <!-- Printable Sheet Header -->
    <div class="print-header text-center">
        <h3 class="fw-bold mb-0 text-uppercase" style="color: #1e4072;">SignED — SPED Teacher FSL Quick Reference Guide</h3>
        <p class="text-muted mb-0 small">Filipino Sign Language (FSL) Core Classroom Commands, Assessment Phrases &amp; Signing Tips</p>
    </div>

    <!-- Guide Grid -->
    <div class="row g-3">
        <?php foreach ($modules as $m): ?>
            <div class="col-6">
                <div class="guide-card">
                    <div class="d-flex justify-content-between align-items-center mb-1">
                        <span class="badge bg-secondary text-uppercase" style="font-size: 0.7rem;"><?= htmlspecialchars($m['category']) ?></span>
                        <small class="text-muted">#<?= (int)$m['display_order'] ?></small>
                    </div>
                    <h5 class="fw-bold mb-1" style="font-size: 1rem; color: #1e4072;"><?= htmlspecialchars($m['title']) ?></h5>
                    <p class="small text-dark mb-2"><?= htmlspecialchars($m['description']) ?></p>
                    
                    <?php if (!empty($m['tips'])): ?>
                        <div class="p-2 bg-light rounded border text-muted small" style="font-size: 0.78rem;">
                            <strong>Signing Technique:</strong> <?= htmlspecialchars($m['tips']) ?>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        <?php endforeach; ?>
    </div>

    <div class="mt-4 pt-3 border-top text-center text-muted small">
        <em>SignED Inclusive Learning System &bull; Filipino Sign Language (FSL) Guide &bull; RA 11106 Compliant</em>
    </div>

</body>
</html>

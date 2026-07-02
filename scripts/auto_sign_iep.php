<?php
/**
 * Auto-sign pending IEP signatories for local testing.
 * Usage: php scripts/auto_sign_iep.php [iep_id]
 */
require_once __DIR__ . '/../config/env.php';
require_once __DIR__ . '/../config/db.php';
require_once __DIR__ . '/../app/Models/IEPModel.php';

$iepId = isset($argv[1]) ? (int) $argv[1] : 1;
$iepModel = new IEPModel();
$db = Database::getInstance()->getConnection();

$sigDir = __DIR__ . '/../public/uploads/signatures/iep';
if (!is_dir($sigDir)) {
    mkdir($sigDir, 0755, true);
}

$demoSigPath = 'uploads/signatures/iep/demo_auto_sign.png';
$fullPath = __DIR__ . '/../public/' . $demoSigPath;
if (!file_exists($fullPath)) {
    file_put_contents($fullPath, base64_decode(
        'iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mP8z8BQDwAEhQGAhKmMIQAAAABJRU5ErkJggg=='
    ));
}

$signatories = $iepModel->getSignatories($iepId);
if (empty($signatories)) {
    echo "No signatories for IEP #{$iepId}\n";
    exit(1);
}

$signed = 0;
foreach ($signatories as $sig) {
    $hasPath = !empty($sig['signature_image_path']) && trim((string) $sig['signature_image_path']) !== '';
    if ($hasPath) {
        echo "Skip (already signed): {$sig['signatory_role']} — {$sig['signatory_name']}\n";
        continue;
    }

    $path = $demoSigPath;
    if (!$iepModel->saveSignatureImage((int) $sig['id'], $path)) {
        echo "FAIL: could not sign {$sig['signatory_role']}\n";
        exit(1);
    }
    echo "Signed: {$sig['signatory_role']} — {$sig['signatory_name']}\n";
    $signed++;
}

$complete = $iepModel->allSignatoriesSignatureComplete($iepId);
echo "\nIEP #{$iepId}: {$signed} signature(s) added\n";
echo 'All signatures complete: ' . ($complete ? 'yes' : 'no') . "\n";
echo "Refresh http://localhost/Signedd/public/iep/form/{$iepId} and click Finalize IEP\n";

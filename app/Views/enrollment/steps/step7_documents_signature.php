<!-- Step 7: Documents & Signature -->
<div class="form-step" id="step-7">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-file-earmark-arrow-up"></i> Step 7: Documents & Signature
            </h4>
            
            <?php if ($enrollmentType === 'returning'): ?>
            <!-- Returning Student - Documents Auto-Copied -->
            <div class="alert alert-success">
                <h5 class="mb-2"><strong><i class="bi bi-check-circle-fill"></i> Returning Student - Documents Auto-Copied</strong></h5>
                <p class="mb-0">
                    Your documents from last year's enrollment have been automatically copied to this enrollment. 
                    <strong>No need to upload documents again!</strong> Only your signature is required to confirm this enrollment.
                </p>
            </div>
            <?php else: ?>
            <?php
            // Parse active school guidelines for document requirements
            $schoolGuidelinesStr = $selectedSchoolItem['enrollment_guidelines'] ?? '';
            $activeDocs = [];

            $docCatalog = [
                'psa_birth_cert' => [
                    'key'         => 'psa_birth_cert',
                    'title'       => 'PSA Birth Certificate',
                    'icon'        => 'bi-file-earmark-text text-danger',
                    'desc'        => 'PSA certified birth certificate (DepEd Standard)',
                    'default_on'  => true,
                    'default_req' => true
                ],
                'medical_record' => [
                    'key'         => 'medical_record',
                    'title'       => 'Medical Record / Evaluation',
                    'icon'        => 'bi-hospital text-primary',
                    'desc'        => 'Medical certificate or clinical diagnostic report',
                    'default_on'  => true,
                    'default_req' => false
                ],
                'pwd_id' => [
                    'key'         => 'pwd_id',
                    'title'       => 'PWD ID Card',
                    'icon'        => 'bi-person-badge text-warning',
                    'desc'        => 'Person with Disability ID Card (if available)',
                    'default_on'  => true,
                    'default_req' => false
                ],
                'sf10' => [
                    'key'         => 'sf10',
                    'title'       => 'SF10 / Form 138 (Report Card)',
                    'icon'        => 'bi-journal-check text-info',
                    'desc'        => 'Previous school progress report card / Form 137 / SF10',
                    'default_on'  => false,
                    'default_req' => false
                ],
                'brgy_cert' => [
                    'key'         => 'brgy_cert',
                    'title'       => 'Barangay Certificate',
                    'icon'        => 'bi-house-check text-secondary',
                    'desc'        => 'Barangay certificate of residency',
                    'default_on'  => false,
                    'default_req' => false
                ]
            ];

            if (!empty($schoolGuidelinesStr) && strpos($schoolGuidelinesStr, '[DOC:') !== false) {
                preg_match_all('/\[DOC:([a-z0-9_]+):(required|optional)\]/i', $schoolGuidelinesStr, $matches, PREG_SET_ORDER);
                foreach ($matches as $m) {
                    $k = $m[1];
                    $isReq = (strtolower($m[2]) === 'required');
                    if (isset($docCatalog[$k])) {
                        $item = $docCatalog[$k];
                        $item['is_required'] = $isReq;
                        $activeDocs[$k] = $item;
                    }
                }
            } elseif (!empty($schoolGuidelinesStr)) {
                // Legacy heuristic fallback
                foreach ($docCatalog as $k => $item) {
                    $found = false;
                    $req = $item['default_req'];
                    if ($k === 'psa_birth_cert' && stripos($schoolGuidelinesStr, 'PSA') !== false) {
                        $found = true;
                        $req = !preg_match('/PSA[^\n]*\((?:Optional|optional)\)/i', $schoolGuidelinesStr);
                    } elseif ($k === 'pwd_id' && stripos($schoolGuidelinesStr, 'PWD') !== false) {
                        $found = true;
                        $req = !preg_match('/PWD[^\n]*\((?:Optional|optional)\)/i', $schoolGuidelinesStr);
                    } elseif ($k === 'medical_record' && (stripos($schoolGuidelinesStr, 'Medical') !== false || stripos($schoolGuidelinesStr, 'Diagnostic') !== false)) {
                        $found = true;
                        $req = !preg_match('/Medical[^\n]*\((?:Optional|optional)\)/i', $schoolGuidelinesStr);
                    } elseif ($k === 'sf10' && (stripos($schoolGuidelinesStr, 'SF10') !== false || stripos($schoolGuidelinesStr, '138') !== false)) {
                        $found = true;
                        $req = !preg_match('/(?:SF10|138)[^\n]*\((?:Optional|optional)\)/i', $schoolGuidelinesStr);
                    } elseif ($k === 'brgy_cert' && stripos($schoolGuidelinesStr, 'Barangay') !== false) {
                        $found = true;
                        $req = !preg_match('/Barangay[^\n]*\((?:Optional|optional)\)/i', $schoolGuidelinesStr);
                    }
                    if ($found) {
                        $item['is_required'] = $req;
                        $activeDocs[$k] = $item;
                    }
                }
            }

            // Fallback if empty or none matched
            if (empty($activeDocs)) {
                $activeDocs['psa_birth_cert'] = $docCatalog['psa_birth_cert'];
                $activeDocs['psa_birth_cert']['is_required'] = true;
                $activeDocs['medical_record'] = $docCatalog['medical_record'];
                $activeDocs['medical_record']['is_required'] = false;
                $activeDocs['pwd_id'] = $docCatalog['pwd_id'];
                $activeDocs['pwd_id']['is_required'] = false;
            }

            $reqList = [];
            foreach ($activeDocs as $ad) {
                if (!empty($ad['is_required'])) {
                    $reqList[] = $ad['title'];
                }
            }
            ?>
            <!-- New/Transfer Student - Documents Required -->
            <div class="alert alert-info">
                <strong><i class="bi bi-info-circle"></i> Required Documents</strong>
                <p class="mb-0">
                    <?php if (!empty($reqList)): ?>
                        Required: <strong><?php echo implode(', ', $reqList); ?></strong>. Other documents are optional but recommended by the SPED Center.
                    <?php else: ?>
                        Please upload supporting documents as requested by your selected SPED Center.
                    <?php endif; ?>
                </p>
            </div>

            <!-- Dynamic Document Uploads Matching Principal's Checklist -->
            <div class="row g-3">
                <?php foreach ($activeDocs as $docKey => $doc): ?>
                <div class="col-md-4 col-sm-6">
                    <div class="p-3 bg-white rounded-3 border border-secondary border-opacity-25 shadow-sm h-100 d-flex flex-column justify-content-between">
                        <div>
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <label class="form-label fw-bold text-dark mb-0 small">
                                    <i class="<?php echo $doc['icon']; ?> me-1"></i> <?php echo htmlspecialchars($doc['title']); ?>
                                </label>
                                <?php if ($doc['is_required']): ?>
                                    <span class="badge" style="background-color: #fef2f2 !important; color: #dc2626 !important; border: 1px solid #fecaca !important; font-size: 0.72rem; font-weight: 600; padding: 3px 8px;">Required</span>
                                <?php else: ?>
                                    <span class="badge" style="background-color: #f1f5f9 !important; color: #334155 !important; border: 1px solid #cbd5e1 !important; font-size: 0.72rem; font-weight: 600; padding: 3px 8px;">Optional</span>
                                <?php endif; ?>
                            </div>
                            <?php 
                            $fieldName = $docKey;
                            $acceptedTypes = '.pdf,.jpg,.jpeg,.png';
                            $maxSize = 10;
                            $showCamera = true;
                            $isRequiredDoc = $doc['is_required'];
                            $docTitleLabel = $doc['title'];
                            include __DIR__ . '/../../components/upload-zone.php';
                            ?>
                        </div>
                        <div class="form-text text-muted mt-2" style="font-size: 0.75rem;"><?php echo htmlspecialchars($doc['desc']); ?></div>
                    </div>
                </div>
                <?php endforeach; ?>
            </div>
            <?php endif; ?>

            <!-- Signature Section (Compact & Clean) -->
            <div class="p-3 bg-white rounded-3 border border-secondary border-opacity-25 shadow-sm mt-3">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <h6 class="fw-bold text-dark mb-0">
                        <i class="bi bi-pen me-1 text-primary"></i> Parent/Guardian Signature <span class="text-danger">*</span>
                    </h6>
                    <span class="badge" style="background-color: #fef2f2 !important; color: #dc2626 !important; border: 1px solid #fecaca !important; font-size: 0.72rem; font-weight: 600; padding: 3px 8px;">Required</span>
                </div>
                <p class="text-muted small mb-2" style="font-size: 0.8rem;">
                    By signing below, I certify that all information provided in this enrollment form is true and correct.
                </p>
                
                <div class="signature-container mb-2 text-start">
                    <canvas id="signaturePad" width="460" height="120" style="border: 1.5px dashed #cbd5e1; border-radius: 8px; background: #fafafa; width: 100%; max-width: 460px; height: 120px; cursor: crosshair;"></canvas>
                </div>
                
                <div class="d-flex align-items-center gap-2">
                    <button type="button" class="btn btn-outline-secondary btn-sm py-1 px-2" style="font-size: 0.8rem; border-radius: 6px;" onclick="clearSignature()">
                        <i class="bi bi-eraser me-1"></i> Clear Signature
                    </button>
                    <small class="text-muted" style="font-size: 0.75rem;">Draw signature using mouse or touch</small>
                </div>
                
                <!-- Hidden field for signature data -->
                <input type="hidden" id="signature_data" name="signature_data">
            </div>

            <!-- Confirmation Notice -->
            <div class="alert alert-success mt-3 py-2 px-3 small border-success border-opacity-25 mb-0">
                <div class="d-flex align-items-center">
                    <i class="bi bi-check-circle-fill fs-5 text-success me-2"></i>
                    <div>
                        <strong>Ready to Submit:</strong> Once submitted, a SPED teacher will review your application. You will be notified via email & in-app notifications.
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// Make PSA Birth Certificate optional for returning students
document.addEventListener('DOMContentLoaded', function() {
    const enrollmentType = '<?php echo $enrollmentType; ?>';
    
    if (enrollmentType === 'returning') {
        // For returning students, no file uploads are required
        console.log('Returning student - file uploads not required');
    }
});
</script>

<!-- Step 7: Documents & Signature -->
<div class="form-step" id="step-7">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-file-earmark-arrow-up"></i> Step 7: Documents & Signature
            </h4>
            
            <div class="alert alert-info">
                <strong><i class="bi bi-info-circle"></i> Required Documents</strong>
                <p class="mb-0">Only PSA Birth Certificate is required. Other documents are optional but recommended.</p>
            </div>

            <!-- Document Uploads -->
            <div class="row">
                <!-- PSA Birth Certificate -->
                <div class="col-md-6 mb-3">
                    <div class="card border-danger">
                        <div class="card-body">
                            <label for="psa_birth_cert" class="form-label">
                                <i class="bi bi-file-earmark-text text-danger"></i> 
                                <strong>PSA Birth Certificate *</strong>
                            </label>
                            <input type="file" class="form-control" id="psa_birth_cert" name="psa_birth_cert" 
                                   accept=".pdf,.jpg,.jpeg,.png" required>
                            <div class="form-text">Philippine Statistics Authority certified birth certificate (Required)</div>
                        </div>
                    </div>
                </div>

                <!-- BEEF Form (Optional) -->
                <div class="col-md-6 mb-3">
                    <div class="card border-secondary">
                        <div class="card-body">
                            <label for="beef_form" class="form-label">
                                <i class="bi bi-file-earmark-pdf text-secondary"></i> 
                                <strong>BEEF Form</strong> <small class="text-muted">(Optional)</small>
                            </label>
                            <input type="file" class="form-control" id="beef_form" name="beef_form" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">Upload if you have a pre-filled BEEF form, otherwise this form will be auto-generated</div>
                        </div>
                    </div>
                </div>

                <!-- PWD ID -->
                <div class="col-md-6 mb-3">
                    <div class="card border-secondary">
                        <div class="card-body">
                            <label for="pwd_id" class="form-label">
                                <i class="bi bi-card-heading text-secondary"></i> 
                                <strong>PWD ID</strong> <small class="text-muted">(Optional)</small>
                            </label>
                            <input type="file" class="form-control" id="pwd_id" name="pwd_id" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">Person with Disability Identification Card (if available)</div>
                        </div>
                    </div>
                </div>

                <!-- Medical Record -->
                <div class="col-md-6 mb-3">
                    <div class="card border-secondary">
                        <div class="card-body">
                            <label for="medical_record" class="form-label">
                                <i class="bi bi-hospital text-secondary"></i> 
                                <strong>Medical Record</strong> <small class="text-muted">(Optional)</small>
                            </label>
                            <input type="file" class="form-control" id="medical_record" name="medical_record" 
                                   accept=".pdf,.jpg,.jpeg,.png">
                            <div class="form-text">Medical certificate or assessment showing disability (if available)</div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Signature Section -->
            <div class="card border-danger mt-4">
                <div class="card-header bg-danger text-white">
                    <h5 class="mb-0"><i class="bi bi-pen"></i> Parent/Guardian Signature *</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">
                        By signing below, I certify that all information provided in this enrollment form is true and correct to the best of my knowledge.
                    </p>
                    
                    <div class="signature-container mb-3">
                        <canvas id="signaturePad" width="600" height="200" style="border: 2px solid #dee2e6; border-radius: 8px; background: white; width: 100%; max-width: 600px; cursor: crosshair;"></canvas>
                    </div>
                    
                    <div class="d-flex gap-2">
                        <button type="button" class="btn btn-secondary btn-sm" onclick="clearSignature()">
                            <i class="bi bi-eraser"></i> Clear Signature
                        </button>
                        <small class="text-muted align-self-center">Draw your signature using mouse or touch</small>
                    </div>
                    
                    <!-- Hidden field for signature data -->
                    <input type="hidden" id="signature_data" name="signature_data">
                </div>
            </div>

            <!-- Confirmation -->
            <div class="alert alert-success mt-4">
                <h5><i class="bi bi-check-circle"></i> Ready to Submit?</h5>
                <p class="mb-0">
                    Please review all information before submitting. Once submitted, a SPED teacher will review your application. 
                    You will be notified via email and in-app notification about the status of your enrollment.
                </p>
            </div>
        </div>
    </div>
</div>

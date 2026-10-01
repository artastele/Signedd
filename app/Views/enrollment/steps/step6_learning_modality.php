<!-- Step 6: Learning Modality -->
<div class="form-step" id="step-6">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-laptop"></i> Step 6: Learning Modality
            </h4>
            
            <div class="alert alert-info">
                <strong><i class="bi bi-info-circle"></i> Select all learning modalities that apply</strong>
                <p class="mb-0">You can choose multiple options based on your preference and availability.</p>
            </div>

            <!-- Learning Modality Options -->
            <div class="card border-primary mb-3">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0">Available Learning Modalities</h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_modular_print" 
                                       name="modality_modular_print" value="1"
                                       <?php echo isChecked('modality_modular_print') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_modular_print">
                                    <strong>Modular (Print)</strong>
                                    <br><small class="text-muted">Printed learning modules delivered to home</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_modular_digital" 
                                       name="modality_modular_digital" value="1"
                                       <?php echo isChecked('modality_modular_digital') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_modular_digital">
                                    <strong>Modular (Digital)</strong>
                                    <br><small class="text-muted">Digital learning modules via email/USB</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_online" 
                                       name="modality_online" value="1"
                                       <?php echo isChecked('modality_online') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_online">
                                    <strong>Online</strong>
                                    <br><small class="text-muted">Internet-based learning via video conferencing</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_educational_tv" 
                                       name="modality_educational_tv" value="1"
                                       <?php echo isChecked('modality_educational_tv') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_educational_tv">
                                    <strong>Educational TV</strong>
                                    <br><small class="text-muted">TV-based instruction via DepEd TV</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_radio" 
                                       name="modality_radio" value="1"
                                       <?php echo isChecked('modality_radio') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_radio">
                                    <strong>Radio-Based Instruction</strong>
                                    <br><small class="text-muted">Learning via radio broadcasts</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_blended" 
                                       name="modality_blended" value="1"
                                       <?php echo isChecked('modality_blended') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_blended">
                                    <strong>Blended Learning</strong>
                                    <br><small class="text-muted">Combination of online and offline methods</small>
                                </label>
                            </div>
                        </div>

                        <div class="col-md-6 mb-3">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="modality_face_to_face" 
                                       name="modality_face_to_face" value="1"
                                       <?php echo isChecked('modality_face_to_face') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="modality_face_to_face">
                                    <strong>Face-to-Face</strong>
                                    <br><small class="text-muted">In-person classroom instruction</small>
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Preferred Distance Learning Modality -->
            <div class="card bg-light mb-4">
                <div class="card-body">
                    <label for="preferred_distance_modality" class="form-label">
                        <strong>Preferred Distance Learning Modality</strong> (If face-to-face is not available)
                    </label>
                    <select class="form-select" id="preferred_distance_modality" name="preferred_distance_modality">
                        <option value="">-- Select Preferred Modality --</option>
                        <option value="Modular (Print)" <?php echo getFormValue('preferred_distance_modality') === 'Modular (Print)' ? 'selected' : ''; ?>>Modular (Print)</option>
                        <option value="Modular (Digital)" <?php echo getFormValue('preferred_distance_modality') === 'Modular (Digital)' ? 'selected' : ''; ?>>Modular (Digital)</option>
                        <option value="Online" <?php echo getFormValue('preferred_distance_modality') === 'Online' ? 'selected' : ''; ?>>Online</option>
                        <option value="Educational TV" <?php echo getFormValue('preferred_distance_modality') === 'Educational TV' ? 'selected' : ''; ?>>Educational TV</option>
                        <option value="Radio-Based Instruction" <?php echo getFormValue('preferred_distance_modality') === 'Radio-Based Instruction' ? 'selected' : ''; ?>>Radio-Based Instruction</option>
                        <option value="Blended" <?php echo getFormValue('preferred_distance_modality') === 'Blended' ? 'selected' : ''; ?>>Blended</option>
                    </select>
                </div>
            </div>

            <!-- Digital Readiness & SignED LMS Survey (Informational for SPED Teacher Review) -->
            <div class="card border-info shadow-sm mb-3">
                <div class="card-header bg-info-subtle border-info text-dark d-flex align-items-center justify-content-between py-2">
                    <h6 class="mb-0 fw-bold"><i class="bi bi-router-fill me-2 text-info"></i>Digital Readiness & SignED LMS Preparedness Survey</h6>
                    <span class="badge bg-info text-dark" style="font-size: 0.75rem;">Advisory Survey</span>
                </div>
                <div class="card-body">
                    <p class="text-muted small mb-3">
                        <i class="bi bi-info-circle me-1"></i>Tubaga kini nga mubo nga survey aron matabangan ang SPED Teacher sa pag-assess kung pwede ba maka-avail ang bata sa interactive online SignED LMS learning modules o traditional face-to-face instruction.
                    </p>

                    <!-- Question 1: Internet Connection -->
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="form-label fw-semibold">1. Naa ba moy active internet connection sa inyong panimalay? <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="survey_has_internet" id="internet_yes" value="1" <?php echo (getFormValue('survey_has_internet') == '1') ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="internet_yes">
                                    <i class="bi bi-wifi text-success me-1"></i> Oo, naay internet (WiFi / Fiber / Prepaid Data)
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="survey_has_internet" id="internet_no" value="0" <?php echo (getFormValue('survey_has_internet') === '0' || getFormValue('survey_has_internet') === 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="internet_no">
                                    <i class="bi bi-wifi-off text-muted me-1"></i> Wala o hinay kaayo ang signal
                                </label>
                            </div>
                        </div>
                    </div>

                    <!-- Question 2: Available Devices -->
                    <div class="mb-3 pb-3 border-bottom">
                        <label class="form-label fw-semibold">2. Unsa nga mga digital device ang magamit sa bata sa balay? <small class="text-muted">(Pili-a ang tanang magamit)</small></label>
                        <?php 
                            $savedDevices = getFormValue('survey_devices');
                            $devicesArr = is_string($savedDevices) ? explode(',', $savedDevices) : (is_array($savedDevices) ? $savedDevices : []);
                        ?>
                        <div class="row g-2">
                            <div class="col-sm-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="survey_devices[]" value="Smartphone" id="dev_smartphone" <?php echo in_array('Smartphone', $devicesArr) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="dev_smartphone"><i class="bi bi-phone me-1"></i> Smartphone (Android/iPhone)</label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="survey_devices[]" value="Tablet" id="dev_tablet" <?php echo in_array('Tablet', $devicesArr) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="dev_tablet"><i class="bi bi-tablet me-1"></i> Tablet / iPad</label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="survey_devices[]" value="Laptop/PC" id="dev_laptop" <?php echo in_array('Laptop/PC', $devicesArr) ? 'checked' : ''; ?>>
                                    <label class="form-check-label" for="dev_laptop"><i class="bi bi-laptop me-1"></i> Laptop / Desktop Computer</label>
                                </div>
                            </div>
                            <div class="col-sm-6 col-md-4">
                                <div class="form-check">
                                    <input class="form-check-input" type="checkbox" name="survey_devices[]" value="None" id="dev_none" <?php echo in_array('None', $devicesArr) ? 'checked' : ''; ?>>
                                    <label class="form-check-label text-muted" for="dev_none"><i class="bi bi-slash-circle me-1"></i> Walay magamit nga device</label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Question 3: Willingness for Online / Digital LMS -->
                    <div class="mb-2">
                        <label class="form-label fw-semibold">3. Andam ba ug willing ang ginikanan / guardian nga mogamit ang bata og online SignED LMS interactive learning modules? <span class="text-danger">*</span></label>
                        <div class="d-flex gap-4">
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="survey_willing_online" id="willing_yes" value="1" <?php echo (getFormValue('survey_willing_online') == '1') ? 'checked' : ''; ?> required>
                                <label class="form-check-label" for="willing_yes">
                                    <i class="bi bi-check-circle-fill text-success me-1"></i> Oo, andam ug makagiya ang ginikanan
                                </label>
                            </div>
                            <div class="form-check">
                                <input class="form-check-input" type="radio" name="survey_willing_online" id="willing_no" value="0" <?php echo (getFormValue('survey_willing_online') === '0' || getFormValue('survey_willing_online') === 0) ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="willing_no">
                                    <i class="bi bi-x-circle-fill text-secondary me-1"></i> Dili, mas gusto ang purely face-to-face / modular print
                                </label>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
</div>

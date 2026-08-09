<!-- Step 5: Enrollment Details -->
<div class="form-step" id="step-5">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-clipboard-check"></i> Step 5: Enrollment Details
            </h4>
            
            <div class="row">
                <!-- Target School -->
                <div class="col-md-12 mb-3">
                    <label for="target_school_id" class="form-label">Target School / SPED Center <span class="text-danger">*</span></label>
                    <?php
                    require_once __DIR__ . '/../../../Models/SchoolModel.php';
                    $schoolModel = new SchoolModel();
                    $schoolList = $schoolModel->getAllSchools();
                    $selectedSchoolId = getFormValue('target_school_id') ?: ($_GET['school_id'] ?? $_GET['target_school_id'] ?? null);
                    ?>
                    <?php if (empty($schoolList)): ?>
                        <div class="alert alert-warning mb-2 border-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>No Registered SPED Centers Available Yet:</strong> Please wait for a School Principal to register your target SPED Center in the system before submitting an enrollment application.
                        </div>
                        <select class="form-select" id="target_school_id" name="target_school_id" required disabled>
                            <option value="">-- No Schools Registered Yet --</option>
                        </select>
                    <?php else: ?>
                        <select class="form-select" id="target_school_id" name="target_school_id" required onchange="updateSchoolGuidelinesPreview(this.value)">
                            <option value="">-- Select Target School --</option>
                            <?php foreach ($schoolList as $sch): ?>
                                <?php $sel = ($selectedSchoolId == $sch['id']) ? 'selected' : ''; ?>
                                <option value="<?php echo htmlspecialchars($sch['id']); ?>" 
                                        data-guidelines="<?php echo htmlspecialchars($sch['enrollment_guidelines'] ?? 'Official DepEd SPED Enrollment Requirements apply.'); ?>"
                                        data-announcement="<?php echo htmlspecialchars($sch['enrollment_announcement'] ?? 'Enrollment is open for this school.'); ?>"
                                        data-sy="<?php echo htmlspecialchars($sch['enrollment_sy'] ?? '2026-2027'); ?>"
                                        data-status="<?php echo htmlspecialchars(strtoupper($sch['enrollment_status'] ?? 'OPEN')); ?>"
                                        data-address="<?php echo htmlspecialchars($sch['address'] ?? 'DepEd SPED Center'); ?>"
                                        data-division="<?php echo htmlspecialchars($sch['division'] ?? 'Division Office'); ?>"
                                        data-pubmat="<?php echo !empty($sch['pubmat_path']) ? htmlspecialchars($basePath . '/' . ltrim($sch['pubmat_path'], '/')) : ''; ?>"
                                        data-email="<?php echo htmlspecialchars($sch['contact_email'] ?? ''); ?>"
                                        data-number="<?php echo htmlspecialchars($sch['contact_number'] ?? ''); ?>"
                                        data-facebook="<?php echo htmlspecialchars($sch['facebook_page'] ?? ''); ?>"
                                        <?php echo $sel; ?>>
                                    <?php echo htmlspecialchars($sch['school_name']); ?> (DepEd ID: <?php echo htmlspecialchars($sch['school_id']); ?>)
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <div class="form-text">Select the registered SPED school/center where you wish to submit this enrollment.</div>

                        <!-- Selected School Guidelines & Details Box -->
                        <div id="school_guidelines_box" class="mt-3 p-3 bg-light rounded-3 border border-primary border-opacity-25" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-primary mb-0">
                                    <i class="bi bi-info-circle-fill me-1"></i> <span id="preview_school_name">Selected School</span> Guidelines & Details
                                </h6>
                                <span id="preview_school_status" class="badge bg-success px-2 py-1">OPEN</span>
                            </div>
                            <div class="small text-secondary mb-2" id="preview_school_meta">
                                <i class="bi bi-geo-alt me-1"></i> <span id="preview_school_address">Address</span>
                            </div>
                            
                            <!-- Contact Info Badges -->
                            <div id="preview_school_contacts" class="d-flex flex-wrap gap-2 mb-2" style="display: none !important;">
                                <span id="preview_contact_email_badge" class="badge bg-white text-dark border"><i class="bi bi-envelope-fill text-primary me-1"></i><span id="preview_contact_email"></span></span>
                                <span id="preview_contact_number_badge" class="badge bg-white text-dark border"><i class="bi bi-telephone-fill text-success me-1"></i><span id="preview_contact_number"></span></span>
                                <a id="preview_facebook_link" href="#" target="_blank" class="badge bg-primary text-white text-decoration-none"><i class="bi bi-facebook me-1"></i>Facebook Page</a>
                            </div>

                            <!-- Enrollment Pubmat Poster Image -->
                            <div id="preview_pubmat_container" class="mb-3 text-center" style="display: none;">
                                <label class="form-label fw-bold text-dark small d-block text-start mb-1"><i class="bi bi-image me-1 text-primary"></i> Official Enrollment Publicity Poster (Pubmat):</label>
                                <a id="preview_pubmat_link" href="#" target="_blank">
                                    <img id="preview_pubmat_img" src="" alt="School Enrollment Pubmat" class="img-fluid rounded border shadow-sm" style="max-height: 380px; object-fit: contain; width: 100%;">
                                </a>
                            </div>

                            <div id="preview_school_announcement_alert" class="alert alert-info py-2 px-3 small mb-2" style="display: none;">
                                <strong>Notice:</strong> <span id="preview_school_announcement"></span>
                            </div>
                            <div class="border-top pt-2">
                                <strong class="small text-dark">Requirements & Policy Guidelines:</strong>
                                <div id="preview_school_guidelines" class="small text-muted mt-1" style="white-space: pre-line;"></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

                <script>
                function updateSchoolGuidelinesPreview(schoolId) {
                    var box = document.getElementById('school_guidelines_box');
                    var select = document.getElementById('target_school_id');
                    if (!select || !schoolId) {
                        if (box) box.style.display = 'none';
                        return;
                    }
                    var opt = select.options[select.selectedIndex];
                    if (!opt || !opt.value) {
                        if (box) box.style.display = 'none';
                        return;
                    }

                    document.getElementById('preview_school_name').textContent = opt.text;
                    document.getElementById('preview_school_status').textContent = opt.getAttribute('data-status') || 'OPEN';
                    document.getElementById('preview_school_address').textContent = (opt.getAttribute('data-division') ? opt.getAttribute('data-division') + ' | ' : '') + (opt.getAttribute('data-address') || '');
                    
                    // Contact Info
                    var email = opt.getAttribute('data-email');
                    var num = opt.getAttribute('data-number');
                    var fb = opt.getAttribute('data-facebook');
                    var contactsDiv = document.getElementById('preview_school_contacts');
                    var hasContact = false;

                    if (email) {
                        document.getElementById('preview_contact_email').textContent = email;
                        document.getElementById('preview_contact_email_badge').style.display = 'inline-block';
                        hasContact = true;
                    } else {
                        document.getElementById('preview_contact_email_badge').style.display = 'none';
                    }

                    if (num) {
                        document.getElementById('preview_contact_number').textContent = num;
                        document.getElementById('preview_contact_number_badge').style.display = 'inline-block';
                        hasContact = true;
                    } else {
                        document.getElementById('preview_contact_number_badge').style.display = 'none';
                    }

                    if (fb) {
                        document.getElementById('preview_facebook_link').href = fb;
                        document.getElementById('preview_facebook_link').style.display = 'inline-block';
                        hasContact = true;
                    } else {
                        document.getElementById('preview_facebook_link').style.display = 'none';
                    }

                    if (hasContact) {
                        contactsDiv.style.setProperty('display', 'flex', 'important');
                    } else {
                        contactsDiv.style.setProperty('display', 'none', 'important');
                    }

                    // Pubmat Poster
                    var pubmat = opt.getAttribute('data-pubmat');
                    var pubmatContainer = document.getElementById('preview_pubmat_container');
                    if (pubmat) {
                        document.getElementById('preview_pubmat_img').src = pubmat;
                        document.getElementById('preview_pubmat_link').href = pubmat;
                        pubmatContainer.style.display = 'block';
                    } else {
                        pubmatContainer.style.display = 'none';
                    }

                    var ann = opt.getAttribute('data-announcement');
                    var annAlert = document.getElementById('preview_school_announcement_alert');
                    if (ann) {
                        document.getElementById('preview_school_announcement').textContent = ann;
                        annAlert.style.display = 'block';
                    } else {
                        annAlert.style.display = 'none';
                    }

                    var g = opt.getAttribute('data-guidelines');
                    document.getElementById('preview_school_guidelines').textContent = g || 'Standard DepEd SPED Enrollment Requirements apply.';
                    
                    box.style.display = 'block';
                }

                document.addEventListener('DOMContentLoaded', function() {
                    var select = document.getElementById('target_school_id');
                    if (select && select.value) {
                        updateSchoolGuidelinesPreview(select.value);
                    }
                });
                </script>

                <!-- School Year -->
                <div class="col-md-6 mb-3">
                    <label for="school_year" class="form-label">School Year <span class="text-danger">*</span></label>
                    <select class="form-select" id="school_year" name="school_year" required>
                        <?php
                        // Generate school years (current and next 2 years)
                        $currentYear = date('Y');
                        $defaultSY = $currentYear . '-' . ($currentYear + 1);
                        
                        for ($i = 0; $i <= 2; $i++) {
                            $startYear = $currentYear + $i;
                            $endYear = $startYear + 1;
                            $sy = $startYear . '-' . $endYear;
                            $selected = (getFormValue('school_year', $defaultSY) === $sy) ? 'selected' : '';
                            echo "<option value=\"$sy\" $selected>$sy</option>";
                        }
                        ?>
                    </select>
                    <div class="form-text">Select the school year for this enrollment</div>
                </div>
                
                <!-- Grade Level to Enroll -->
                <div class="col-md-6 mb-3">
                    <label for="grade_level_to_enroll" class="form-label">Grade Level to Enroll <span class="text-danger">*</span></label>
                    <select class="form-select" id="grade_level_to_enroll" name="grade_level_to_enroll" required>
                        <option value="">-- Select Grade Level --</option>
                        <option value="Kinder" <?php echo getFormValue('grade_level_to_enroll') === 'Kinder' ? 'selected' : ''; ?>>Kinder</option>
                        <option value="Grade 1" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 1' ? 'selected' : ''; ?>>Grade 1</option>
                        <option value="Grade 2" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 2' ? 'selected' : ''; ?>>Grade 2</option>
                        <option value="Grade 3" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 3' ? 'selected' : ''; ?>>Grade 3</option>
                        <option value="Grade 4" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 4' ? 'selected' : ''; ?>>Grade 4</option>
                        <option value="Grade 5" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 5' ? 'selected' : ''; ?>>Grade 5</option>
                        <option value="Grade 6" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 6' ? 'selected' : ''; ?>>Grade 6</option>
                        <option value="Grade 7" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 7' ? 'selected' : ''; ?>>Grade 7</option>
                        <option value="Grade 8" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 8' ? 'selected' : ''; ?>>Grade 8</option>
                        <option value="Grade 9" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 9' ? 'selected' : ''; ?>>Grade 9</option>
                        <option value="Grade 10" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 10' ? 'selected' : ''; ?>>Grade 10</option>
                        <option value="Grade 11" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 11' ? 'selected' : ''; ?>>Grade 11</option>
                        <option value="Grade 12" <?php echo getFormValue('grade_level_to_enroll') === 'Grade 12' ? 'selected' : ''; ?>>Grade 12</option>
                        <option value="SPED Program" <?php echo getFormValue('grade_level_to_enroll') === 'SPED Program' ? 'selected' : ''; ?>>SPED Program</option>
                    </select>
                </div>
            </div>

            <!-- Additional Options -->
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="row">
                        <div class="col-md-4 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="is_balik_aral" 
                                       name="is_balik_aral" value="1"
                                       <?php echo isChecked('is_balik_aral') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="is_balik_aral">
                                    <strong>Balik-Aral</strong> (Returning to School)
                                </label>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <!-- PEPT Passer -->
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_pept_passer" 
                               name="is_pept_passer" value="1"
                               <?php echo isChecked('is_pept_passer') ? 'checked' : ''; ?>
                               onchange="document.getElementById('pept_rating_field').style.display = this.checked ? 'block' : 'none'">
                        <label class="form-check-label" for="is_pept_passer">
                            <strong>PEPT Passer</strong> (Philippine Educational Placement Test)
                        </label>
                    </div>
                    <div id="pept_rating_field" style="display: <?php echo isChecked('is_pept_passer') ? 'block' : 'none'; ?>">
                        <label for="pept_rating" class="form-label">PEPT Rating</label>
                        <input type="text" class="form-control" id="pept_rating" name="pept_rating" 
                               value="<?php echo htmlspecialchars(getFormValue('pept_rating')); ?>"
                               placeholder="e.g., 85%">
                    </div>
                </div>
            </div>

            <!-- ALS Passer -->
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_als_passer" 
                               name="is_als_passer" value="1"
                               <?php echo isChecked('is_als_passer') ? 'checked' : ''; ?>
                               onchange="document.getElementById('als_rating_field').style.display = this.checked ? 'block' : 'none'">
                        <label class="form-check-label" for="is_als_passer">
                            <strong>ALS A&E Passer</strong> (Alternative Learning System)
                        </label>
                    </div>
                    <div id="als_rating_field" style="display: <?php echo isChecked('is_als_passer') ? 'block' : 'none'; ?>">
                        <label for="als_rating" class="form-label">ALS Rating</label>
                        <input type="text" class="form-control" id="als_rating" name="als_rating" 
                               value="<?php echo htmlspecialchars(getFormValue('als_rating')); ?>"
                               placeholder="e.g., 85%">
                    </div>
                </div>
            </div>

            <!-- Senior High School Details -->
            <div class="card border-secondary">
                <div class="card-header bg-secondary text-white">
                    <h5 class="mb-0">Senior High School Details <small>(If applicable)</small></h5>
                </div>
                <div class="card-body">
                    <div class="row">
                        <!-- SHS Track -->
                        <div class="col-md-4 mb-3">
                            <label for="shs_track" class="form-label">Track</label>
                            <select class="form-select" id="shs_track" name="shs_track">
                                <option value="">-- Select Track --</option>
                                <option value="Academic" <?php echo getFormValue('shs_track') === 'Academic' ? 'selected' : ''; ?>>Academic</option>
                                <option value="TVL" <?php echo getFormValue('shs_track') === 'TVL' ? 'selected' : ''; ?>>Technical-Vocational-Livelihood (TVL)</option>
                                <option value="Sports" <?php echo getFormValue('shs_track') === 'Sports' ? 'selected' : ''; ?>>Sports</option>
                                <option value="Arts & Design" <?php echo getFormValue('shs_track') === 'Arts & Design' ? 'selected' : ''; ?>>Arts & Design</option>
                            </select>
                        </div>

                        <!-- SHS Strand -->
                        <div class="col-md-4 mb-3">
                            <label for="shs_strand" class="form-label">Strand</label>
                            <input type="text" class="form-control" id="shs_strand" name="shs_strand" 
                                   value="<?php echo htmlspecialchars(getFormValue('shs_strand')); ?>"
                                   placeholder="e.g., STEM, ABM, HUMSS">
                        </div>

                        <!-- SHS Semester -->
                        <div class="col-md-4 mb-3">
                            <label for="shs_semester" class="form-label">Semester</label>
                            <select class="form-select" id="shs_semester" name="shs_semester">
                                <option value="">-- Select --</option>
                                <option value="1st" <?php echo getFormValue('shs_semester') === '1st' ? 'selected' : ''; ?>>1st Semester</option>
                                <option value="2nd" <?php echo getFormValue('shs_semester') === '2nd' ? 'selected' : ''; ?>>2nd Semester</option>
                            </select>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

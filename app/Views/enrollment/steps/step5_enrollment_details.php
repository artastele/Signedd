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
                    <label class="form-label fw-bold text-dark"><i class="bi bi-building me-1 text-primary"></i> Target School / SPED Center <span class="badge ms-1" style="background-color: #f1f5f9; color: #475569; border: 1px solid #cbd5e1; font-size: 0.75rem;"><i class="bi bi-lock-fill me-1 text-secondary"></i>Locked</span></label>
                    <?php
                    require_once __DIR__ . '/../../../Models/SchoolModel.php';
                    $schoolModel = new SchoolModel();
                    $schoolList = $schoolModel->getAllSchools();
                    
                    // Auto-resolve school ID from form, URL, session, or user profile
                    $selectedSchoolId = getFormValue('target_school_id') 
                        ?: ($_GET['school_id'] ?? $_GET['target_school_id'] ?? ($_SESSION['school_id'] ?? null));

                    if (empty($selectedSchoolId) && !empty($_SESSION['user_id'])) {
                        $dbUser = Database::getInstance()->getConnection();
                        $stmtUsr = $dbUser->prepare("SELECT school_id FROM users WHERE id = :uid LIMIT 1");
                        $stmtUsr->execute(['uid' => $_SESSION['user_id']]);
                        $selectedSchoolId = $stmtUsr->fetchColumn() ?: null;
                    }

                    // Fallback to first school in list if available
                    $selectedSchoolItem = null;
                    if (!empty($schoolList)) {
                        if (!empty($selectedSchoolId)) {
                            foreach ($schoolList as $sch) {
                                if ((string)$selectedSchoolId === (string)$sch['id']) {
                                    $selectedSchoolItem = $sch;
                                    break;
                                }
                            }
                        }
                        if (!$selectedSchoolItem) {
                            $selectedSchoolItem = $schoolList[0];
                        }
                    }
                    ?>
                    <?php if (empty($schoolList)): ?>
                        <div class="alert alert-warning mb-2 border-warning">
                            <i class="bi bi-exclamation-triangle-fill me-2"></i>
                            <strong>No Registered SPED Centers Available Yet:</strong> Please wait for a School Principal to register your target SPED Center in the system before submitting an enrollment application.
                        </div>
                        <input type="hidden" id="target_school_id" name="target_school_id" value="">
                    <?php else: ?>
                        <!-- Fully Locked / Readonly Non-clickable School Display -->
                        <div class="p-3 bg-light rounded-3 border border-secondary border-opacity-25 shadow-sm" style="user-select: none;">
                            <div class="d-flex align-items-center justify-content-between">
                                <div class="d-flex align-items-center">
                                    <div class="rounded-3 me-3 d-flex align-items-center justify-content-center" style="width: 42px; height: 42px; background-color: #e0e7ff; color: #3730a3;">
                                        <i class="bi bi-building fs-5"></i>
                                    </div>
                                    <div>
                                        <div class="fw-bold text-dark fs-6 mb-0">
                                            <?php echo htmlspecialchars($selectedSchoolItem['school_name']); ?>
                                            <span class="badge ms-1" style="background-color: #f1f5f9; color: #1e293b; border: 1px solid #cbd5e1; font-weight: 600; font-size: 0.75rem;">DepEd ID: <?php echo htmlspecialchars($selectedSchoolItem['school_id']); ?></span>
                                        </div>
                                        <div class="small text-muted">
                                            <i class="bi bi-geo-alt me-1 text-danger"></i>
                                            <?php echo htmlspecialchars(($selectedSchoolItem['division'] ? $selectedSchoolItem['division'] . ' • ' : '') . ($selectedSchoolItem['address'] ?? 'DepEd SPED Center')); ?>
                                        </div>
                                    </div>
                                </div>
                                <span class="badge" style="background-color: #ecfdf5; color: #047857; border: 1px solid #a7f3d0; font-weight: 600; font-size: 0.8rem; padding: 6px 10px;">
                                    <i class="bi bi-check-circle-fill me-1"></i> <?php echo htmlspecialchars(strtoupper($selectedSchoolItem['enrollment_status'] ?? 'OPEN')); ?>
                                </span>
                            </div>

                            <!-- Hidden Field for Form Submission -->
                            <input type="hidden" id="target_school_id" name="target_school_id" value="<?php echo htmlspecialchars($selectedSchoolItem['id']); ?>"
                                   data-guidelines="<?php echo htmlspecialchars($selectedSchoolItem['enrollment_guidelines'] ?? 'Official DepEd SPED Enrollment Requirements apply.'); ?>"
                                   data-announcement="<?php echo htmlspecialchars($selectedSchoolItem['enrollment_announcement'] ?? 'Enrollment is open for this school.'); ?>"
                                   data-sy="<?php echo htmlspecialchars($selectedSchoolItem['enrollment_sy'] ?? '2026-2027'); ?>"
                                   data-status="<?php echo htmlspecialchars(strtoupper($selectedSchoolItem['enrollment_status'] ?? 'OPEN')); ?>"
                                   data-address="<?php echo htmlspecialchars($selectedSchoolItem['address'] ?? 'DepEd SPED Center'); ?>"
                                   data-division="<?php echo htmlspecialchars($selectedSchoolItem['division'] ?? 'Division Office'); ?>"
                                   data-pubmat="<?php echo !empty($selectedSchoolItem['pubmat_path']) ? htmlspecialchars($basePath . '/' . ltrim($selectedSchoolItem['pubmat_path'], '/')) : ''; ?>"
                                   data-email="<?php echo htmlspecialchars($selectedSchoolItem['contact_email'] ?? ''); ?>"
                                   data-number="<?php echo htmlspecialchars($selectedSchoolItem['contact_number'] ?? ''); ?>"
                                   data-facebook="<?php echo htmlspecialchars($selectedSchoolItem['facebook_page'] ?? ''); ?>">
                        </div>
                        <div class="form-text text-muted small mt-1">
                            <i class="bi bi-shield-lock-fill text-secondary me-1"></i> Target SPED Center is locked and associated with your enrollment profile.
                        </div>

                        <!-- Selected School Guidelines & Details Box -->
                        <div id="school_guidelines_box" class="mt-3 p-3 bg-white rounded-3 border border-primary border-opacity-25 shadow-sm">
                            <div class="d-flex justify-content-between align-items-center mb-2">
                                <h6 class="fw-bold text-primary mb-0">
                                    <i class="bi bi-info-circle-fill me-1"></i> <?php echo htmlspecialchars($selectedSchoolItem['school_name']); ?> Guidelines & Details
                                </h6>
                                <span class="badge bg-success px-2 py-1"><?php echo htmlspecialchars(strtoupper($selectedSchoolItem['enrollment_status'] ?? 'OPEN')); ?></span>
                            </div>
                            <div class="small text-secondary mb-2">
                                <i class="bi bi-geo-alt me-1"></i> <?php echo htmlspecialchars(($selectedSchoolItem['division'] ? $selectedSchoolItem['division'] . ' | ' : '') . ($selectedSchoolItem['address'] ?? 'DepEd SPED Center')); ?>
                            </div>
                            
                            <!-- Contact Info Badges -->
                            <?php if (!empty($selectedSchoolItem['contact_email']) || !empty($selectedSchoolItem['contact_number']) || !empty($selectedSchoolItem['facebook_page'])): ?>
                            <div class="d-flex flex-wrap gap-2 mb-2">
                                <?php if (!empty($selectedSchoolItem['contact_email'])): ?>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-envelope-fill text-primary me-1"></i><?php echo htmlspecialchars($selectedSchoolItem['contact_email']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($selectedSchoolItem['contact_number'])): ?>
                                    <span class="badge bg-light text-dark border"><i class="bi bi-telephone-fill text-success me-1"></i><?php echo htmlspecialchars($selectedSchoolItem['contact_number']); ?></span>
                                <?php endif; ?>
                                <?php if (!empty($selectedSchoolItem['facebook_page'])): ?>
                                    <a href="<?php echo htmlspecialchars($selectedSchoolItem['facebook_page']); ?>" target="_blank" class="badge bg-primary text-white text-decoration-none"><i class="bi bi-facebook me-1"></i>Facebook Page</a>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Enrollment Pubmat Poster Image -->
                            <?php if (!empty($selectedSchoolItem['pubmat_path'])): ?>
                            <div class="mb-3 text-center">
                                <label class="form-label fw-bold text-dark small d-block text-start mb-1"><i class="bi bi-image me-1 text-primary"></i> Official Enrollment Publicity Poster (Pubmat):</label>
                                <a href="<?php echo htmlspecialchars($basePath . '/' . ltrim($selectedSchoolItem['pubmat_path'], '/')); ?>" target="_blank">
                                    <img src="<?php echo htmlspecialchars($basePath . '/' . ltrim($selectedSchoolItem['pubmat_path'], '/')); ?>" alt="School Enrollment Pubmat" class="img-fluid rounded border shadow-sm" style="max-height: 380px; object-fit: contain; width: 100%;">
                                </a>
                            </div>
                            <?php endif; ?>

                            <?php if (!empty($selectedSchoolItem['enrollment_announcement'])): ?>
                            <div class="alert alert-info py-2 px-3 small mb-2">
                                <strong>Notice:</strong> <?php echo htmlspecialchars($selectedSchoolItem['enrollment_announcement']); ?>
                            </div>
                            <?php endif; ?>

                            <div class="border-top pt-2">
                                <strong class="small text-dark">Requirements & Policy Guidelines:</strong>
                                <div class="small text-muted mt-1" style="white-space: pre-line;"><?php echo htmlspecialchars($selectedSchoolItem['enrollment_guidelines'] ?? 'Standard DepEd SPED Enrollment Requirements apply.'); ?></div>
                            </div>
                        </div>
                    <?php endif; ?>
                </div>

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

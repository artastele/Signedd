<!-- Step 1: Learner Information -->
<div class="form-step" id="step-1">
    <div class="card">
        <div class="card-body">
            <h4 class="card-title text-primary mb-4">
                <i class="bi bi-person-fill"></i> Step 1: Learner Information
            </h4>
            
            <div class="row">
                <!-- LRN -->
                <div class="col-md-6 mb-3">
                    <label for="lrn" class="form-label">Learner Reference Number (LRN)</label>
                    <input type="text" class="form-control" id="lrn" name="lrn" 
                           value="<?php echo htmlspecialchars(getFormValue('lrn')); ?>"
                           placeholder="12-digit LRN" maxlength="12">
                    <div class="form-text">Optional - Leave blank if not yet assigned</div>
                </div>
            </div>

            <div class="row">
                <!-- Last Name -->
                <div class="col-md-3 mb-3">
                    <label for="last_name" class="form-label">Last Name *</label>
                    <input type="text" class="form-control" id="last_name" name="last_name" 
                           value="<?php echo htmlspecialchars(getFormValue('last_name')); ?>"
                           required>
                </div>

                <!-- First Name -->
                <div class="col-md-3 mb-3">
                    <label for="first_name" class="form-label">First Name *</label>
                    <input type="text" class="form-control" id="first_name" name="first_name" 
                           value="<?php echo htmlspecialchars(getFormValue('first_name')); ?>"
                           required>
                </div>

                <!-- Middle Name -->
                <div class="col-md-3 mb-3">
                    <label for="middle_name" class="form-label">Middle Name</label>
                    <input type="text" class="form-control" id="middle_name" name="middle_name" 
                           value="<?php echo htmlspecialchars(getFormValue('middle_name')); ?>">
                </div>

                <!-- Extension Name -->
                <div class="col-md-3 mb-3">
                    <label for="extension_name" class="form-label">Extension Name</label>
                    <select class="form-select" id="extension_name" name="extension_name">
                        <option value="">None</option>
                        <option value="Jr." <?php echo getFormValue('extension_name') === 'Jr.' ? 'selected' : ''; ?>>Jr.</option>
                        <option value="Sr." <?php echo getFormValue('extension_name') === 'Sr.' ? 'selected' : ''; ?>>Sr.</option>
                        <option value="II" <?php echo getFormValue('extension_name') === 'II' ? 'selected' : ''; ?>>II</option>
                        <option value="III" <?php echo getFormValue('extension_name') === 'III' ? 'selected' : ''; ?>>III</option>
                        <option value="IV" <?php echo getFormValue('extension_name') === 'IV' ? 'selected' : ''; ?>>IV</option>
                    </select>
                </div>
            </div>

            <div class="row">
                <!-- Birth Date -->
                <div class="col-md-4 mb-3">
                    <label for="birth_date" class="form-label">Birth Date *</label>
                    <input type="date" class="form-control" id="birth_date" name="birth_date" 
                           value="<?php echo htmlspecialchars(getFormValue('birth_date')); ?>"
                           onchange="calculateAge()"
                           required>
                </div>

                <!-- Sex -->
                <div class="col-md-4 mb-3">
                    <label for="sex" class="form-label">Sex *</label>
                    <select class="form-select" id="sex" name="sex" required>
                        <option value="">-- Select --</option>
                        <option value="Male" <?php echo getFormValue('sex') === 'Male' ? 'selected' : ''; ?>>Male</option>
                        <option value="Female" <?php echo getFormValue('sex') === 'Female' ? 'selected' : ''; ?>>Female</option>
                    </select>
                </div>

                <!-- Age (Auto-calculated) -->
                <div class="col-md-4 mb-3">
                    <label for="age" class="form-label">Age</label>
                    <input type="number" class="form-control" id="age" name="age" 
                           value="<?php echo htmlspecialchars(getFormValue('age')); ?>"
                           min="0" max="100" readonly>
                    <div class="form-text">Auto-calculated from birth date</div>
                </div>
            </div>

            <div class="row">
                <!-- Birth Place -->
                <div class="col-md-12 mb-3">
                    <label for="birth_place" class="form-label">Birth Place *</label>
                    <input type="text" class="form-control" id="birth_place" name="birth_place" 
                           value="<?php echo htmlspecialchars(getFormValue('birth_place')); ?>"
                           placeholder="e.g., Cebu City, Cebu"
                           required>
                    <div class="form-text">City/Municipality and Province</div>
                </div>
            </div>

            <div class="row">
                <!-- Mother Tongue -->
                <div class="col-md-6 mb-3">
                    <label for="mother_tongue" class="form-label">Mother Tongue</label>
                    <input type="text" class="form-control" id="mother_tongue" name="mother_tongue" 
                           value="<?php echo htmlspecialchars(getFormValue('mother_tongue')); ?>"
                           placeholder="e.g., Cebuano, Tagalog, Ilocano">
                </div>
            </div>

            <!-- Indigenous Peoples -->
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_indigenous_people" 
                               name="is_indigenous_people" value="1"
                               <?php echo isChecked('is_indigenous_people') ? 'checked' : ''; ?>
                               onchange="document.getElementById('indigenous_group_field').style.display = this.checked ? 'block' : 'none'">
                        <label class="form-check-label" for="is_indigenous_people">
                            <strong>Belongs to Indigenous Peoples (IP)</strong>
                        </label>
                    </div>
                    <div id="indigenous_group_field" style="display: <?php echo isChecked('is_indigenous_people') ? 'block' : 'none'; ?>">
                        <label for="indigenous_group" class="form-label">Specify Ethnic Group</label>
                        <input type="text" class="form-control" id="indigenous_group" name="indigenous_group" 
                               value="<?php echo htmlspecialchars(getFormValue('indigenous_group')); ?>"
                               placeholder="e.g., Lumad, Aeta, Igorot">
                    </div>
                </div>
            </div>

            <!-- 4Ps Beneficiary -->
            <div class="card bg-light mb-3">
                <div class="card-body">
                    <div class="form-check mb-2">
                        <input class="form-check-input" type="checkbox" id="is_4ps_beneficiary" 
                               name="is_4ps_beneficiary" value="1"
                               <?php echo isChecked('is_4ps_beneficiary') ? 'checked' : ''; ?>
                               onchange="document.getElementById('fourps_field').style.display = this.checked ? 'block' : 'none'">
                        <label class="form-check-label" for="is_4ps_beneficiary">
                            <strong>4Ps Beneficiary (Pantawid Pamilyang Pilipino Program)</strong>
                        </label>
                    </div>
                    <div id="fourps_field" style="display: <?php echo isChecked('is_4ps_beneficiary') ? 'block' : 'none'; ?>">
                        <label for="fourps_household_id" class="form-label">4Ps Household ID Number</label>
                        <input type="text" class="form-control" id="fourps_household_id" name="fourps_household_id" 
                               value="<?php echo htmlspecialchars(getFormValue('fourps_household_id')); ?>">
                    </div>
                </div>
            </div>

            <!-- Disability Information -->
            <div class="card border-primary">
                <div class="card-header bg-primary text-white">
                    <h5 class="mb-0"><i class="bi bi-heart-pulse"></i> Disability Information</h5>
                </div>
                <div class="card-body">
                    <p class="text-muted">Check all that apply:</p>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disability_visual" 
                                       name="disability_visual" value="1"
                                       <?php echo isChecked('disability_visual') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="disability_visual">
                                    Visual Impairment
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disability_hearing" 
                                       name="disability_hearing" value="1"
                                       <?php echo isChecked('disability_hearing') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="disability_hearing">
                                    Hearing Impairment
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disability_learning" 
                                       name="disability_learning" value="1"
                                       <?php echo isChecked('disability_learning') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="disability_learning">
                                    Learning Disability
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disability_speech" 
                                       name="disability_speech" value="1"
                                       <?php echo isChecked('disability_speech') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="disability_speech">
                                    Speech/Language Impairment
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disability_intellectual" 
                                       name="disability_intellectual" value="1"
                                       <?php echo isChecked('disability_intellectual') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="disability_intellectual">
                                    Intellectual Disability
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disability_physical" 
                                       name="disability_physical" value="1"
                                       <?php echo isChecked('disability_physical') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="disability_physical">
                                    Physical Disability
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disability_emotional" 
                                       name="disability_emotional" value="1"
                                       <?php echo isChecked('disability_emotional') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="disability_emotional">
                                    Emotional-Behavioral Disorder
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disability_chronic_illness" 
                                       name="disability_chronic_illness" value="1"
                                       <?php echo isChecked('disability_chronic_illness') ? 'checked' : ''; ?>>
                                <label class="form-check-label" for="disability_chronic_illness">
                                    Chronic Illness
                                </label>
                            </div>
                        </div>
                        <div class="col-md-6 mb-2">
                            <div class="form-check">
                                <input class="form-check-input" type="checkbox" id="disability_others" 
                                       name="disability_others" value="1"
                                       <?php echo isChecked('disability_others') ? 'checked' : ''; ?>
                                       onchange="document.getElementById('disability_others_field').style.display = this.checked ? 'block' : 'none'">
                                <label class="form-check-label" for="disability_others">
                                    Others (Please Specify)
                                </label>
                            </div>
                        </div>
                    </div>
                    <div id="disability_others_field" class="mt-3" style="display: <?php echo isChecked('disability_others') ? 'block' : 'none'; ?>">
                        <label for="disability_others_specify" class="form-label">Specify Other Disability</label>
                        <input type="text" class="form-control" id="disability_others_specify" 
                               name="disability_others_specify" 
                               value="<?php echo htmlspecialchars(getFormValue('disability_others_specify')); ?>">
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

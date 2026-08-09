<?php
$pageTitle = 'System Settings - SignED';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <h1 class="mb-4">
        <i class="bi bi-gear"></i> System Settings
    </h1>

    <!-- Success/Error Messages -->
    <?php if (isset($_SESSION['success'])): ?>
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            <i class="bi bi-check-circle"></i> <?php echo $_SESSION['success']; unset($_SESSION['success']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <?php if (isset($_SESSION['error'])): ?>
        <div class="alert alert-danger alert-dismissible fade show" role="alert">
            <i class="bi bi-exclamation-triangle"></i> <?php echo $_SESSION['error']; unset($_SESSION['error']); ?>
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        </div>
    <?php endif; ?>

    <!-- Settings Form -->
    <div class="card">
        <div class="card-header" style="background-color: #1e4072; color: white;">
            <h5 class="mb-0">
                <i class="bi bi-shield-lock"></i> Security & Session Settings
            </h5>
        </div>
        <div class="card-body">
            <form method="POST" action="<?php echo $basePath; ?>/admin/settings/update">
                
                <!-- Session Timeout -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-clock-history"></i> Session Timeout
                    </label>
                    <select name="session_timeout" class="form-select" required>
                        <option value="5" <?php echo ($settings['session_timeout']['value'] ?? 15) == 5 ? 'selected' : ''; ?>>5 minutes</option>
                        <option value="10" <?php echo ($settings['session_timeout']['value'] ?? 15) == 10 ? 'selected' : ''; ?>>10 minutes</option>
                        <option value="15" <?php echo ($settings['session_timeout']['value'] ?? 15) == 15 ? 'selected' : ''; ?>>15 minutes (Default)</option>
                        <option value="20" <?php echo ($settings['session_timeout']['value'] ?? 15) == 20 ? 'selected' : ''; ?>>20 minutes</option>
                        <option value="30" <?php echo ($settings['session_timeout']['value'] ?? 15) == 30 ? 'selected' : ''; ?>>30 minutes</option>
                        <option value="45" <?php echo ($settings['session_timeout']['value'] ?? 15) == 45 ? 'selected' : ''; ?>>45 minutes</option>
                        <option value="60" <?php echo ($settings['session_timeout']['value'] ?? 15) == 60 ? 'selected' : ''; ?>>60 minutes</option>
                    </select>
                    <small class="text-muted">How long before inactive users are automatically logged out</small>
                </div>

                <!-- Max Login Attempts -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-shield-x"></i> Maximum Login Attempts
                    </label>
                    <input type="number" name="max_login_attempts" class="form-control" 
                           min="3" max="10" 
                           value="<?php echo $settings['max_login_attempts']['value'] ?? 5; ?>" 
                           required>
                    <small class="text-muted">Number of failed login attempts before account lockout (3-10)</small>
                </div>

                <!-- Account Lockout Duration -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-lock"></i> Account Lockout Duration
                    </label>
                    <select name="lockout_duration" class="form-select" required>
                        <option value="5" <?php echo ($settings['lockout_duration']['value'] ?? 15) == 5 ? 'selected' : ''; ?>>5 minutes</option>
                        <option value="10" <?php echo ($settings['lockout_duration']['value'] ?? 15) == 10 ? 'selected' : ''; ?>>10 minutes</option>
                        <option value="15" <?php echo ($settings['lockout_duration']['value'] ?? 15) == 15 ? 'selected' : ''; ?>>15 minutes (Default)</option>
                        <option value="30" <?php echo ($settings['lockout_duration']['value'] ?? 15) == 30 ? 'selected' : ''; ?>>30 minutes</option>
                        <option value="60" <?php echo ($settings['lockout_duration']['value'] ?? 15) == 60 ? 'selected' : ''; ?>>60 minutes</option>
                    </select>
                    <small class="text-muted">How long to lock account after max failed attempts</small>
                </div>

                <!-- OTP Expiration -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-key"></i> OTP Expiration Time
                    </label>
                    <select name="otp_expiration" class="form-select" required>
                        <option value="5" <?php echo ($settings['otp_expiration']['value'] ?? 10) == 5 ? 'selected' : ''; ?>>5 minutes</option>
                        <option value="10" <?php echo ($settings['otp_expiration']['value'] ?? 10) == 10 ? 'selected' : ''; ?>>10 minutes (Default)</option>
                        <option value="15" <?php echo ($settings['otp_expiration']['value'] ?? 10) == 15 ? 'selected' : ''; ?>>15 minutes</option>
                        <option value="20" <?php echo ($settings['otp_expiration']['value'] ?? 10) == 20 ? 'selected' : ''; ?>>20 minutes</option>
                    </select>
                    <small class="text-muted">How long email verification codes remain valid</small>
                </div>

                <!-- Logout Warning -->
                <div class="mb-4">
                    <label class="form-label fw-bold">
                        <i class="bi bi-bell"></i> Logout Warning Time
                    </label>
                    <select name="logout_warning" class="form-select" required>
                        <option value="1" <?php echo ($settings['logout_warning']['value'] ?? 2) == 1 ? 'selected' : ''; ?>>1 minute before</option>
                        <option value="2" <?php echo ($settings['logout_warning']['value'] ?? 2) == 2 ? 'selected' : ''; ?>>2 minutes before (Default)</option>
                        <option value="3" <?php echo ($settings['logout_warning']['value'] ?? 2) == 3 ? 'selected' : ''; ?>>3 minutes before</option>
                        <option value="5" <?php echo ($settings['logout_warning']['value'] ?? 2) == 5 ? 'selected' : ''; ?>>5 minutes before</option>
                    </select>
                    <small class="text-muted">When to show warning before automatic logout</small>
                </div>

                <hr class="my-4">

                <!-- Process 4: Enrollment Guidelines & Schedule Settings -->
                <h5 class="mb-3 text-primary">
                    <i class="bi bi-megaphone-fill text-warning me-1"></i> Enrollment Guidelines & Schedule (Process 4)
                </h5>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Active School Year</label>
                        <input type="text" name="enrollment_sy" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['enrollment_sy']['value'] ?? '2026-2027'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Enrollment Status</label>
                        <select name="enrollment_status" class="form-select" required>
                            <option value="open" <?php echo ($settings['enrollment_status']['value'] ?? 'open') === 'open' ? 'selected' : ''; ?>>Open</option>
                            <option value="upcoming" <?php echo ($settings['enrollment_status']['value'] ?? '') === 'upcoming' ? 'selected' : ''; ?>>Upcoming</option>
                            <option value="closed" <?php echo ($settings['enrollment_status']['value'] ?? '') === 'closed' ? 'selected' : ''; ?>>Closed</option>
                        </select>
                    </div>
                </div>

                <div class="row g-3 mb-3">
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Enrollment Start Date</label>
                        <input type="date" name="enrollment_start_date" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['enrollment_start_date']['value'] ?? '2026-06-01'); ?>" required>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label fw-bold">Enrollment End Date (Deadline)</label>
                        <input type="date" name="enrollment_end_date" class="form-control" 
                               value="<?php echo htmlspecialchars($settings['enrollment_end_date']['value'] ?? '2026-08-15'); ?>" required>
                    </div>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Public Announcement Banner Message</label>
                    <input type="text" name="enrollment_announcement" class="form-control" 
                           value="<?php echo htmlspecialchars($settings['enrollment_announcement']['value'] ?? 'Official Enrollment for SY 2026-2027 is now OPEN across all registered SPED Centers.'); ?>" required>
                    <small class="text-muted">Displayed to users before role selection and on dashboard</small>
                </div>

                <div class="mb-4">
                    <label class="form-label fw-bold">Official Requirements & Policy Guidelines</label>
                    <textarea name="enrollment_guidelines" class="form-control" rows="4" required><?php echo htmlspecialchars($settings['enrollment_guidelines']['value'] ?? "1. PSA Birth Certificate\n2. Form 138/SF10 (Progress Report Card)\n3. Medical / Diagnostic Evaluation Report\n4. PWD ID Card (if available)"); ?></textarea>
                    <small class="text-muted">Official requirements checklist and enrollment policy</small>
                </div>

                <!-- Save Button -->
                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-lg" style="background-color: #a01422; color: white;">
                        <i class="bi bi-save"></i> Save Settings
                    </button>
                </div>

            </form>
        </div>
    </div>

    <!-- Info Card -->
    <div class="card mt-4" style="border-left: 4px solid #1e4072;">
        <div class="card-body">
            <h6 class="fw-bold"><i class="bi bi-info-circle"></i> Important Notes</h6>
            <ul class="mb-0">
                <li>Session timeout changes will apply to new sessions only</li>
                <li>Existing logged-in users will not be affected until they log in again</li>
                <li>Login attempt limits help prevent brute force attacks</li>
                <li>All setting changes are logged in the activity log</li>
            </ul>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

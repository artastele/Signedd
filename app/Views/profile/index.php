<?php
// Part of: SignED — Universal Profile & Account Settings View
// Last modified: 2026-08-21

$pageTitle = 'Account Settings & Profile — SignED';
require_once __DIR__ . '/../layouts/header.php';
?>

<style>
    .page-header-card {
        background: var(--gradient-hero, linear-gradient(135deg, #1e4072 0%, #a01422 100%)) !important;
        color: #ffffff;
        border-radius: 12px;
        padding: 1.5rem 1.75rem;
        box-shadow: 0 4px 12px rgba(0,0,0,0.08);
        margin-bottom: 1.5rem;
    }
    .btn-modern {
        height: 38px;
        font-size: 0.875rem;
        font-weight: 500;
        border-radius: 6px;
        padding: 0.375rem 1rem;
        display: inline-flex;
        align-items: center;
        justify-content: center;
        gap: 0.5rem;
    }
    .profile-avatar-box {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        object-fit: cover;
        border: 4px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .profile-avatar-placeholder {
        width: 110px;
        height: 110px;
        border-radius: 50%;
        background: #e2e8f0;
        color: #64748b;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 3rem;
        border: 4px solid #ffffff;
        box-shadow: 0 4px 10px rgba(0,0,0,0.1);
    }
    .settings-card {
        border-radius: 10px;
        border: 1px solid #e2e8f0;
        background: #ffffff;
        box-shadow: 0 2px 8px rgba(0,0,0,0.04);
        margin-bottom: 1.5rem;
    }
    .settings-card .card-header {
        background: #f8fafc;
        border-bottom: 1px solid #e2e8f0;
        font-weight: 600;
        padding: 1rem 1.25rem;
    }
</style>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <div class="container-fluid p-0">

                <!-- Page Header -->
                <div class="page-header-card d-flex align-items-center justify-content-between flex-wrap gap-3">
                    <div>
                        <h4 class="fw-bold mb-1"><i class="bi bi-person-gear text-warning me-2"></i>Account Profile &amp; Settings</h4>
                        <p class="text-white-50 mb-0 small">Manage your personal information, profile photo, and account security credentials.</p>
                    </div>
                    <div>
                        <span class="badge bg-primary-subtle text-primary border border-primary-subtle px-3 py-2 fs-6">
                            <i class="bi bi-shield-check me-1"></i> Role: <?= ucwords(str_replace('_', ' ', $user['role'])) ?>
                        </span>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-check-circle-fill me-2"></i><?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show border-0 shadow-sm" role="alert">
                        <i class="bi bi-exclamation-triangle-fill me-2"></i><?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <div class="row g-4">
                    <!-- Left Column: User Summary & Avatar -->
                    <div class="col-lg-4">
                        <div class="card settings-card text-center p-4">
                            <div class="d-flex justify-content-center mb-3">
                                <?php if (!empty($user['profile_photo'])): ?>
                                    <img src="<?= $basePath . '/' . ltrim($user['profile_photo'], '/') ?>" alt="Avatar" class="profile-avatar-box">
                                <?php else: ?>
                                    <div class="profile-avatar-placeholder">
                                        <i class="bi bi-person-fill"></i>
                                    </div>
                                <?php endif; ?>
                            </div>

                            <h5 class="fw-bold mb-1 text-dark"><?= htmlspecialchars($user['name']) ?></h5>
                            <p class="text-muted small mb-2"><?= htmlspecialchars($user['email']) ?></p>
                            <div class="mb-3">
                                <span class="badge bg-secondary"><?= ucwords(str_replace('_', ' ', $user['role'])) ?></span>
                                <?php if (!empty($user['school_name'])): ?>
                                    <span class="badge bg-info-subtle text-info-emphasis"><i class="bi bi-building me-1"></i><?= htmlspecialchars($user['school_name']) ?></span>
                                <?php endif; ?>
                            </div>

                            <?php if ($role === 'learner' && !empty($studentRecord)): ?>
                                <div class="p-3 bg-light rounded text-start small mb-3 border">
                                    <div><strong>LRN / ID:</strong> <?= htmlspecialchars($studentRecord['lrn'] ?? $studentRecord['student_id']) ?></div>
                                    <div><strong>Section:</strong> <?= htmlspecialchars($studentRecord['assigned_section'] ?? $studentRecord['section_name'] ?? 'SPED Section') ?></div>
                                    <div><strong>Track:</strong> <span class="badge bg-primary"><?= strtoupper($studentRecord['learning_track']) ?></span></div>
                                </div>
                            <?php endif; ?>

                            <!-- Upload Avatar Form -->
                            <form method="POST" action="<?= $basePath ?>/profile/upload-avatar" enctype="multipart/form-data" class="mt-2">
                                <label class="form-label small fw-semibold text-muted mb-1">Change Profile Photo</label>
                                <div class="input-group input-group-sm mb-2">
                                    <input type="file" name="avatar" class="form-control" accept="image/*" required>
                                    <button type="submit" class="btn btn-dark"><i class="bi bi-upload"></i></button>
                                </div>
                                <small class="text-muted" style="font-size: 0.72rem;">Max 5MB (JPG, PNG, WEBP)</small>
                            </form>
                        </div>
                    </div>

                    <!-- Right Column: Edit Profile & Change Password -->
                    <div class="col-lg-8">
                        
                        <!-- 1. Personal Information Card -->
                        <div class="card settings-card">
                            <div class="card-header">
                                <i class="bi bi-person-lines-fill me-2 text-primary"></i>Personal Information
                            </div>
                            <div class="card-body p-3">
                                <form method="POST" action="<?= $basePath ?>/profile/update">
                                    <div class="row g-3">
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Display / Full Name <span class="text-danger">*</span></label>
                                            <input type="text" name="name" class="form-control" value="<?= htmlspecialchars($user['name']) ?>" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Email / Username <small class="text-muted">(Login ID)</small></label>
                                            <input type="text" class="form-control bg-light" value="<?= htmlspecialchars($user['email']) ?>" readonly>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Contact / Phone Number</label>
                                            <input type="text" name="phone_number" class="form-control" placeholder="e.g. 0912 345 6789" value="<?= htmlspecialchars($user['phone_number'] ?? '') ?>">
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Account Role</label>
                                            <input type="text" class="form-control bg-light" value="<?= ucwords(str_replace('_', ' ', $user['role'])) ?>" readonly>
                                        </div>
                                        <div class="col-12">
                                            <label class="form-label fw-semibold">Bio / Notes</label>
                                            <textarea name="bio" class="form-control" rows="2" placeholder="Tell us about yourself or your educational role..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                                        </div>

                                        <!-- Stage 3 Accessibility & Inclusion Preferences -->
                                        <div class="col-12 border-top pt-3">
                                            <h6 class="fw-bold text-dark mb-2" style="font-size: 0.88rem;">
                                                <i class="bi bi-universal-access me-1 text-primary"></i>Accessibility &amp; Inclusion Preferences
                                            </h6>
                                            <div class="row g-2">
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch p-2 bg-light rounded border">
                                                        <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="highContrastSwitch" name="high_contrast" value="1" <?= !empty($user['high_contrast']) ? 'checked' : '' ?>>
                                                        <label class="form-check-label fw-semibold small" for="highContrastSwitch">
                                                            High-Contrast Theme Mode
                                                        </label>
                                                        <div class="text-muted" style="font-size: 0.72rem;">Enhances readability with dark background and bright high-visibility elements.</div>
                                                    </div>
                                                </div>
                                                <div class="col-md-6">
                                                    <div class="form-check form-switch p-2 bg-light rounded border">
                                                        <input class="form-check-input ms-0 me-2" type="checkbox" role="switch" id="fslPopupsSwitch" name="fsl_popups_enabled" value="1" <?= (!isset($user['fsl_popups_enabled']) || $user['fsl_popups_enabled'] == 1) ? 'checked' : '' ?>>
                                                        <label class="form-check-label fw-semibold small" for="fslPopupsSwitch">
                                                            Interactive FSL Sign Pop-ups
                                                        </label>
                                                        <div class="text-muted" style="font-size: 0.72rem;">Allows clicking highlighted words in lessons to view sign language clips.</div>
                                                    </div>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="col-12 text-end mt-3">
                                            <button type="submit" class="btn btn-primary btn-modern shadow-sm">
                                                <i class="bi bi-check2-circle"></i> Save Profile Details
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

                        <!-- 2. Security & Password Change Card -->
                        <div class="card settings-card">
                            <div class="card-header">
                                <i class="bi bi-shield-lock-fill me-2 text-primary"></i>Security &amp; Change Password
                            </div>
                            <div class="card-body p-3">
                                <form method="POST" action="<?= $basePath ?>/profile/change-password">
                                    <div class="row g-3">
                                        <?php if (!empty($user['password_hash'])): ?>
                                            <div class="col-md-12">
                                                <label class="form-label fw-semibold">Current Password</label>
                                                <input type="password" name="current_password" class="form-control" placeholder="Enter current password" required>
                                            </div>
                                        <?php endif; ?>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">New Password <span class="text-danger">*</span></label>
                                            <input type="password" name="new_password" class="form-control" placeholder="Min. 6 characters" minlength="6" required>
                                        </div>
                                        <div class="col-md-6">
                                            <label class="form-label fw-semibold">Confirm New Password <span class="text-danger">*</span></label>
                                            <input type="password" name="confirm_password" class="form-control" placeholder="Re-type new password" minlength="6" required>
                                        </div>
                                        <div class="col-12 text-end">
                                            <button type="submit" class="btn btn-dark btn-modern shadow-sm">
                                                <i class="bi bi-key-fill"></i> Update Password
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>

    </div> <!-- End container-fluid -->
</div> <!-- End .main-content -->

    <?php require_once __DIR__ . '/../layouts/footer.php'; ?>

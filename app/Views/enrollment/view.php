<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 1
// Last modified: 2026-05-01
// Part of: SPED LMS — View Enrollment Details (Parent/Teacher)

$pageTitle = 'View Enrollment - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';

// Document type labels
$docLabels = [
    'psa_birth_cert' => 'PSA Birth Certificate',
    'pwd_id' => 'PWD ID',
    'medical_record' => 'Medical Record',
    'beef_form' => 'BEEF Form'
];

// Status colors
$statusColors = [
    'draft' => 'secondary',
    'pending' => 'warning',
    'verified' => 'success',
    'rejected' => 'danger'
];

$isParent = $_SESSION['role'] === 'parent';
?>

<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content">
    <!-- Header -->
    <div class="d-flex justify-content-between align-items-center mb-4">
        <h1>
            <i class="bi bi-file-text text-primary"></i> Enrollment Details
        </h1>
        <a href="<?php echo $basePath; ?><?php echo $isParent ? '/enrollment/status' : '/enrollment/review'; ?>" 
           class="btn btn-secondary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Status Banner -->
    <div class="alert alert-<?php echo $statusColors[$enrollment['status']]; ?> mb-4">
        <div class="d-flex justify-content-between align-items-center">
            <div>
                <h5 class="mb-1">
                    <i class="bi bi-info-circle"></i> 
                    Status: <strong><?php echo strtoupper($enrollment['status']); ?></strong>
                </h5>
                <p class="mb-0">
                    Enrollment ID: #<?php echo $enrollment['id']; ?> | 
                    Submitted: <?php echo date('F j, Y g:i A', strtotime($enrollment['submitted_at'])); ?>
                </p>
            </div>
            <?php if ($isParent && $enrollment['status'] === 'rejected'): ?>
                <a href="<?php echo $basePath; ?>/enrollment/create?type=<?php echo $enrollment['enrollment_type']; ?>&resubmit=<?php echo $enrollment['id']; ?>" 
                   class="btn btn-warning">
                    <i class="bi bi-arrow-repeat"></i> Resubmit
                </a>
            <?php endif; ?>
        </div>
    </div>

    <!-- Student Information Summary -->
    <div class="card mb-4 border-primary">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-person-badge"></i> Student Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-8">
                    <h4 class="text-primary mb-3">
                        <?php 
                        echo htmlspecialchars($enrollment['first_name'] . ' ' . 
                             ($enrollment['middle_name'] ? $enrollment['middle_name'] . ' ' : '') . 
                             $enrollment['last_name'] . 
                             ($enrollment['extension_name'] ? ' ' . $enrollment['extension_name'] : ''));
                        ?>
                    </h4>
                    <div class="row">
                        <div class="col-md-6 mb-2">
                            <strong>LRN:</strong> <?php echo htmlspecialchars($enrollment['lrn'] ?? 'Not assigned'); ?>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Birth Date:</strong> <?php echo date('F j, Y', strtotime($enrollment['birth_date'])); ?>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Sex:</strong> <?php echo htmlspecialchars($enrollment['sex']); ?>
                        </div>
                        <div class="col-md-6 mb-2">
                            <strong>Age:</strong> <?php echo htmlspecialchars($enrollment['age'] ?? 'N/A'); ?>
                        </div>
                    </div>
                </div>
                <div class="col-md-4">
                    <div class="card bg-light">
                        <div class="card-body">
                            <h6 class="text-primary">Enrollment Details</h6>
                            <p class="mb-1"><strong>Type:</strong> <?php echo ucfirst($enrollment['enrollment_type']); ?></p>
                            <p class="mb-1"><strong>Grade Level:</strong> <?php echo htmlspecialchars($enrollment['grade_level_to_enroll']); ?></p>
                            <p class="mb-0"><strong>School Year:</strong> <?php echo htmlspecialchars($enrollment['school_year']); ?></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Documents Status -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-file-earmark-check"></i> Document Status</h5>
        </div>
        <div class="card-body">
            <?php if (empty($documents)): ?>
                <div class="alert alert-warning">
                    <i class="bi bi-exclamation-triangle"></i> No documents uploaded.
                </div>
            <?php else: ?>
                <div class="table-responsive">
                    <table class="table table-hover">
                        <thead class="table-dark">
                            <tr>
                                <th>Document Type</th>
                                <th>Status</th>
                                <th>Uploaded</th>
                                <th>Reviewed</th>
                                <th>Notes</th>
                                <th>Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($documents as $doc): ?>
                            <tr>
                                <td><strong><?php echo $docLabels[$doc['document_type']]; ?></strong></td>
                                <td>
                                    <span class="badge bg-<?php echo $statusColors[$doc['status']]; ?>">
                                        <?php echo ucfirst($doc['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('M j, Y', strtotime($doc['uploaded_at'])); ?></td>
                                <td>
                                    <?php if ($doc['reviewed_at']): ?>
                                        <?php echo date('M j, Y', strtotime($doc['reviewed_at'])); ?><br>
                                        <small class="text-muted">by <?php echo htmlspecialchars($doc['reviewer_name'] ?? 'Unknown'); ?></small>
                                    <?php else: ?>
                                        <span class="text-muted">Not yet reviewed</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php if ($doc['review_note']): ?>
                                        <span class="text-danger"><?php echo htmlspecialchars($doc['review_note']); ?></span>
                                    <?php else: ?>
                                        <span class="text-muted">—</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <a href="<?php echo $basePath; ?>/<?php echo htmlspecialchars($doc['file_path']); ?>" 
                                       target="_blank" class="btn btn-sm btn-outline-primary">
                                        <i class="bi bi-download"></i> View
                                    </a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Contact Information -->
    <?php if (!$isParent): ?>
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-telephone"></i> Contact Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4">
                    <h6 class="text-primary">Parent/Guardian</h6>
                    <p>
                        <strong>Name:</strong> <?php echo htmlspecialchars($enrollment['parent_name']); ?><br>
                        <strong>Email:</strong> <?php echo htmlspecialchars($enrollment['parent_email']); ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary">Father</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['father_first_name'] ?? '') . ' ' . ($enrollment['father_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['father_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
                <div class="col-md-4">
                    <h6 class="text-primary">Mother</h6>
                    <p>
                        <strong>Name:</strong> 
                        <?php echo htmlspecialchars(($enrollment['mother_first_name'] ?? '') . ' ' . ($enrollment['mother_maiden_last_name'] ?? '')); ?><br>
                        <strong>Contact:</strong> <?php echo htmlspecialchars($enrollment['mother_contact_number'] ?? 'N/A'); ?>
                    </p>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Address Information -->
    <div class="card mb-4">
        <div class="card-header bg-primary text-white">
            <h5 class="mb-0"><i class="bi bi-geo-alt"></i> Address Information</h5>
        </div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-6">
                    <h6 class="text-primary">Current Address</h6>
                    <p>
                        <?php echo htmlspecialchars($enrollment['current_house_no'] ?? ''); ?><br>
                        Barangay <?php echo htmlspecialchars($enrollment['current_barangay'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($enrollment['current_city'] ?? ''); ?>, 
                        <?php echo htmlspecialchars($enrollment['current_province'] ?? ''); ?><br>
                        <?php echo htmlspecialchars($enrollment['current_region'] ?? ''); ?> 
                        <?php echo htmlspecialchars($enrollment['current_zip_code'] ?? ''); ?>
                    </p>
                </div>
                <div class="col-md-6">
                    <h6 class="text-primary">Permanent Address</h6>
                    <?php if ($enrollment['same_as_current_address']): ?>
                        <p class="text-muted"><em>Same as current address</em></p>
                    <?php else: ?>
                        <p>
                            <?php echo htmlspecialchars($enrollment['permanent_house_no'] ?? ''); ?><br>
                            Barangay <?php echo htmlspecialchars($enrollment['permanent_barangay'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($enrollment['permanent_city'] ?? ''); ?>, 
                            <?php echo htmlspecialchars($enrollment['permanent_province'] ?? ''); ?><br>
                            <?php echo htmlspecialchars($enrollment['permanent_region'] ?? ''); ?> 
                            <?php echo htmlspecialchars($enrollment['permanent_zip_code'] ?? ''); ?>
                        </p>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Action Buttons -->
    <div class="text-center mb-4">
        <?php if (!$isParent): ?>
            <a href="<?php echo $basePath; ?>/enrollment/review/<?php echo $enrollment['id']; ?>" 
               class="btn btn-primary btn-lg">
                <i class="bi bi-clipboard-check"></i> Review Enrollment
            </a>
        <?php endif; ?>
        
        <a href="<?php echo $basePath; ?><?php echo $isParent ? '/enrollment/status' : '/enrollment/review'; ?>" 
           class="btn btn-secondary btn-lg">
            <i class="bi bi-arrow-left"></i> Back to List
        </a>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

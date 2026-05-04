<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 4
// Last modified: 2026-05-04
// Part of: SPED LMS — IEP Meetings List

require __DIR__ . '/../layouts/header.php';
require __DIR__ . '/../layouts/sidebar.php';
require __DIR__ . '/../layouts/topbar.php';

$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$userRole = $_SESSION['role'] ?? 'user';
?>

<div class="main-content">
    <div class="container-fluid py-4">
        <!-- Page Header -->
        <div class="row mb-4">
            <div class="col-12">
                <h1 class="h3 mb-0" style="color: #a01422;">
                    <i class="fas fa-calendar-check"></i> IEP Meetings
                </h1>
                <p class="text-muted mt-2">Scheduled IEP meetings and calendar management</p>
            </div>
        </div>

        <!-- Alert Messages -->
        <div id="alertContainer"></div>

        <!-- Calendar Upload Section (for Guidance & Principal) -->
        <?php if (in_array($userRole, ['guidance', 'principal'])): ?>
        <div class="row mb-4">
            <div class="col-lg-12">
                <div class="card" style="border-left: 4px solid #a01422;">
                    <div class="card-header" style="background-color: #f8f9fa; border-bottom: 1px solid #dee2e6;">
                        <h6 class="mb-0" style="color: #a01422;">
                            <i class="fas fa-calendar-upload"></i> Upload Your Calendar Availability
                        </h6>
                    </div>
                    <div class="card-body">
                        <form id="calendarUploadForm" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label for="calendar_file" class="form-label">Calendar File (ICS, PDF, or TXT)</label>
                                    <input type="file" class="form-control" id="calendar_file" name="calendar_file" 
                                           accept=".ics,.pdf,.txt" required>
                                    <small class="form-text text-muted">
                                        Export from Google Calendar, Outlook, or Apple Calendar
                                    </small>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="valid_from" class="form-label">Valid From</label>
                                    <input type="date" class="form-control" id="valid_from" name="valid_from" 
                                           value="<?php echo date('Y-m-d'); ?>" required>
                                </div>
                                <div class="col-md-3 mb-3">
                                    <label for="valid_until" class="form-label">Valid Until</label>
                                    <input type="date" class="form-control" id="valid_until" name="valid_until" 
                                           value="<?php echo date('Y-m-d', strtotime('+1 month')); ?>" required>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-sm" style="background-color: #a01422; color: white;">
                                <i class="fas fa-upload"></i> Upload Calendar
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
        <?php endif; ?>

        <!-- Statistics Cards -->
        <div class="row mb-4">
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #ffc107;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Scheduled</h6>
                        <h3 style="color: #ffc107;"><?php echo count(array_filter($meetings ?? [], fn($m) => $m['status'] === 'scheduled')); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #3b6d11;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Completed</h6>
                        <h3 style="color: #3b6d11;"><?php echo count(array_filter($meetings ?? [], fn($m) => $m['status'] === 'completed')); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #dc3545;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Cancelled</h6>
                        <h3 style="color: #dc3545;"><?php echo count(array_filter($meetings ?? [], fn($m) => $m['status'] === 'cancelled')); ?></h3>
                    </div>
                </div>
            </div>
            <div class="col-md-3">
                <div class="card text-center" style="border-top: 3px solid #1e4072;">
                    <div class="card-body">
                        <h6 class="text-muted mb-2">Total</h6>
                        <h3 style="color: #1e4072;"><?php echo count($meetings ?? []); ?></h3>
                    </div>
                </div>
            </div>
        </div>

        <!-- Meetings Table -->
        <div class="card">
            <div class="table-responsive">
                <table class="table table-hover mb-0">
                    <thead style="background-color: #1e4072; color: white;">
                        <tr>
                            <th>Student Name</th>
                            <th>LRN</th>
                            <th>Meeting Date</th>
                            <th>Location</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($meetings)): ?>
                            <tr>
                                <td colspan="6" class="text-center py-4 text-muted">
                                    <i class="fas fa-inbox"></i> No meetings scheduled
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($meetings as $meeting): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($meeting['student_name']); ?></td>
                                    <td><?php echo htmlspecialchars($meeting['lrn'] ?? 'N/A'); ?></td>
                                    <td><?php echo date('M d, Y H:i', strtotime($meeting['meeting_date'])); ?></td>
                                    <td><?php echo htmlspecialchars($meeting['meeting_location'] ?? 'TBD'); ?></td>
                                    <td>
                                        <?php 
                                        $statusColor = match($meeting['status']) {
                                            'scheduled' => '#ffc107',
                                            'completed' => '#3b6d11',
                                            'cancelled' => '#dc3545',
                                            default => '#6c757d'
                                        };
                                        ?>
                                        <span class="badge" style="background-color: <?php echo $statusColor; ?>;">
                                            <?php echo ucfirst($meeting['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="<?php echo $basePath; ?>/iep/meetings/<?php echo $meeting['id']; ?>" 
                                           class="btn btn-sm btn-outline-primary">
                                            <i class="bi bi-eye"></i> View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

<script>
document.getElementById('calendarUploadForm')?.addEventListener('submit', async function(e) {
    e.preventDefault();
    
    const formData = new FormData(this);
    const basePath = '<?php echo $basePath; ?>';
    
    try {
        const response = await fetch(basePath + '/iep/meetings/upload-calendar', {
            method: 'POST',
            body: formData
        });
        
        const data = await response.json();
        
        if (data.success) {
            showAlert('Calendar uploaded successfully!', 'success');
            setTimeout(() => {
                window.location.reload();
            }, 1500);
        } else {
            showAlert(data.message || 'Failed to upload calendar', 'danger');
        }
    } catch (error) {
        console.error('Error:', error);
        showAlert('An error occurred while uploading the calendar', 'danger');
    }
});

function showAlert(message, type) {
    const alertContainer = document.getElementById('alertContainer');
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert alert-${type} alert-dismissible fade show`;
    alertDiv.innerHTML = `
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    alertContainer.appendChild(alertDiv);
    
    // Auto-dismiss after 5 seconds
    setTimeout(() => {
        alertDiv.remove();
    }, 5000);
}
</script>

<?php require __DIR__ . '/../layouts/footer.php'; ?>

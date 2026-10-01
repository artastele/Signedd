<?php
/**
 * Reusable Upload Zone Component
 * Mobile-responsive with camera upload support
 * 
 * Parameters:
 * - $fieldName: Name attribute for the file input
 * - $acceptedTypes: Comma-separated accepted file types (default: '.jpg,.jpeg,.png,.pdf')
 * - $maxSize: Maximum file size in MB (default: 10)
 * - $showCamera: Whether to show camera button (default: true)
 * - $uploadUrl: AJAX upload endpoint (optional)
 * - $additionalData: Additional form data for AJAX (optional array)
 */

$fieldName = $fieldName ?? 'document';
$acceptedTypes = $acceptedTypes ?? '.jpg,.jpeg,.png,.pdf';
$maxSize = $maxSize ?? 10;
$showCamera = $showCamera ?? true;
$uploadUrl = $uploadUrl ?? null;
$additionalData = $additionalData ?? [];
$uniqueId = uniqid('upload_');
?>

<div class="upload-zone-container">
    <!-- Upload Zone (shown when no file uploaded) -->
    <div class="upload-zone py-2 px-2" id="<?= $uniqueId ?>_zone" style="<?= isset($existingFile) ? 'display: none;' : '' ?>">
        <div class="upload-zone-content text-center">
            <i class="bi bi-cloud-arrow-up fs-4 text-muted mb-0"></i>
            <p class="upload-hint mb-0 fw-medium" style="font-size: 0.8rem; color: #475569;">Drag & drop or choose file</p>
            <p class="upload-formats text-muted mb-1" style="font-size: 0.72rem;">
                <?= str_replace(['.', ','], ['', ', '], strtoupper($acceptedTypes)) ?> (Max <?= $maxSize ?>MB)
            </p>
            
            <div class="upload-buttons d-flex gap-1 justify-content-center mt-1">
                <button type="button" class="btn btn-sm btn-outline-primary py-1 px-2" style="font-size: 0.78rem; border-radius: 6px;" id="<?= $uniqueId ?>_file_btn">
                    <i class="bi bi-folder2-open me-1"></i> Choose File
                </button>
                <?php if ($showCamera): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary btn-upload-camera py-1 px-2" style="font-size: 0.78rem; border-radius: 6px;" id="<?= $uniqueId ?>_camera_btn">
                        <i class="bi bi-camera me-1"></i> Camera
                    </button>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Hidden file inputs -->
        <input type="file" 
               id="<?= $uniqueId ?>_file_input" 
               name="<?= $fieldName ?>" 
               accept="<?= $acceptedTypes ?>" 
               <?= !empty($isRequiredDoc) ? 'data-required="true"' : 'data-required="false"' ?>
               data-doc-title="<?= htmlspecialchars($docTitleLabel ?? $fieldName) ?>"
               style="display: none;">
        
        <?php if ($showCamera): ?>
            <input type="file" 
                   id="<?= $uniqueId ?>_camera_input" 
                   accept="image/*" 
                   capture="environment" 
                   style="display: none;">
        <?php endif; ?>
    </div>

    <!-- Upload Preview (shown after file selected) -->
    <div id="<?= $uniqueId ?>_preview" class="upload-preview" style="<?= isset($existingFile) ? '' : 'display: none;' ?>">
        <div class="d-flex align-items-center justify-content-between p-3 border rounded">
            <div class="d-flex align-items-center">
                <i class="bi bi-file-earmark-text fs-4 text-success me-2" id="<?= $uniqueId ?>_icon"></i>
                <div>
                    <div class="fw-medium" id="<?= $uniqueId ?>_filename">
                        <?= isset($existingFile) ? htmlspecialchars(basename($existingFile)) : '' ?>
                    </div>
                    <small class="text-muted" id="<?= $uniqueId ?>_fileinfo">
                        <?php if (isset($existingFile)): ?>
                            <?= strtoupper(pathinfo($existingFile, PATHINFO_EXTENSION)) ?>
                        <?php endif; ?>
                    </small>
                </div>
            </div>
            <div>
                <?php if (isset($existingFile) && isset($viewUrl)): ?>
                    <button type="button" class="btn btn-sm btn-outline-secondary me-2" 
                            onclick="viewDocument('<?= $viewUrl ?>', '<?= pathinfo($existingFile, PATHINFO_EXTENSION) ?>')">
                        <i class="bi bi-eye"></i> View
                    </button>
                <?php endif; ?>
                <?php if (isset($existingFile) && isset($downloadUrl)): ?>
                    <a href="<?= $downloadUrl ?>" class="btn btn-sm btn-outline-primary me-2">
                        <i class="bi bi-download"></i> Download
                    </a>
                <?php endif; ?>
                <button type="button" class="btn btn-sm btn-outline-danger" id="<?= $uniqueId ?>_remove_btn">
                    <i class="bi bi-x"></i>
                </button>
            </div>
        </div>
    </div>

    <!-- Upload Error -->
    <div id="<?= $uniqueId ?>_error" class="alert alert-danger mt-2" style="display: none;"></div>

    <!-- Upload Progress (for AJAX uploads) -->
    <?php if ($uploadUrl): ?>
        <div id="<?= $uniqueId ?>_progress" class="progress mt-2" style="display: none;">
            <div class="progress-bar bg-primary" role="progressbar" style="width: 0%"></div>
        </div>
    <?php endif; ?>
</div>

<script>
document.addEventListener('DOMContentLoaded', function() {
    const uploadZone = document.getElementById('<?= $uniqueId ?>_zone');
    const fileInput = document.getElementById('<?= $uniqueId ?>_file_input');
    const cameraInput = document.getElementById('<?= $uniqueId ?>_camera_input');
    const fileBtnElement = document.getElementById('<?= $uniqueId ?>_file_btn');
    const cameraBtnElement = document.getElementById('<?= $uniqueId ?>_camera_btn');
    const previewElement = document.getElementById('<?= $uniqueId ?>_preview');
    const errorElement = document.getElementById('<?= $uniqueId ?>_error');
    const removeBtnElement = document.getElementById('<?= $uniqueId ?>_remove_btn');
    const progressElement = document.getElementById('<?= $uniqueId ?>_progress');

    // File upload handlers
    if (fileBtnElement) {
        fileBtnElement.addEventListener('click', (e) => {
            e.stopPropagation();
            fileInput.click();
        });
    }
    
    if (cameraBtnElement) {
        cameraBtnElement.addEventListener('click', (e) => {
            e.stopPropagation();
            cameraInput.click();
        });
    }

    // Handle file selection (both file picker and camera)
    function handleFileSelect(file) {
        if (!file) return;

        // Validate file type
        const allowedTypes = '<?= $acceptedTypes ?>'.split(',');
        const fileExt = '.' + file.name.split('.').pop().toLowerCase();
        if (!allowedTypes.includes(fileExt)) {
            showError('Only <?= str_replace(['.', ','], ['', ', '], $acceptedTypes) ?> files are allowed.');
            return;
        }

        // Validate file size
        const maxSizeBytes = <?= $maxSize ?> * 1024 * 1024;
        if (file.size > maxSizeBytes) {
            showError('File size must be less than <?= $maxSize ?>MB.');
            return;
        }

        <?php if ($uploadUrl): ?>
            // AJAX upload
            uploadFile(file);
        <?php else: ?>
            // Show preview only
            showPreview(file.name, fileExt.substring(1), (file.size / 1024).toFixed(1) + ' KB');
        <?php endif; ?>
    }

    if (fileInput) {
        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                handleFileSelect(e.target.files[0]);
            }
        });
    }

    if (cameraInput) {
        cameraInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                if (fileInput && window.DataTransfer) {
                    try {
                        const dt = new DataTransfer();
                        dt.items.add(e.target.files[0]);
                        fileInput.files = dt.files;
                    } catch(err) {}
                }
                handleFileSelect(e.target.files[0]);
            }
        });
    }

    // Drag and drop
    if (uploadZone) {
        uploadZone.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadZone.classList.add('dragover');
        });

        uploadZone.addEventListener('dragleave', () => {
            uploadZone.classList.remove('dragover');
        });

        uploadZone.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadZone.classList.remove('dragover');
            
            if (e.dataTransfer.files.length > 0) {
                if (fileInput && window.DataTransfer) {
                    try {
                        fileInput.files = e.dataTransfer.files;
                    } catch(err) {}
                }
                handleFileSelect(e.dataTransfer.files[0]);
            }
        });

        uploadZone.addEventListener('click', () => {
            fileInput.click();
        });
    }

    <?php if ($uploadUrl): ?>
    // AJAX upload function
    function uploadFile(file) {
        const formData = new FormData();
        formData.append('<?= $fieldName ?>', file);
        
        <?php foreach ($additionalData as $key => $value): ?>
            formData.append('<?= $key ?>', '<?= $value ?>');
        <?php endforeach; ?>

        // Show progress
        if (uploadZone) uploadZone.style.display = 'none';
        if (progressElement) progressElement.style.display = 'block';
        hideError();

        fetch('<?= $uploadUrl ?>', {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (progressElement) progressElement.style.display = 'none';
            
            if (data.success) {
                showPreview(data.filename || file.name, data.fileType || 'file', data.fileSize || '');
                // Fire custom event so parent pages can react (e.g. reload)
                document.dispatchEvent(new CustomEvent('uploadSuccess', { detail: data }));
            } else {
                showError(data.message || 'Upload failed');
                if (uploadZone) uploadZone.style.display = 'block';
            }
        })
        .catch(error => {
            if (progressElement) progressElement.style.display = 'none';
            showError('Upload failed: ' + error.message);
            if (uploadZone) uploadZone.style.display = 'block';
        });
    }
    <?php endif; ?>

    // Show upload preview
    function showPreview(filename, fileType, fileSize) {
        if (previewElement) {
            document.getElementById('<?= $uniqueId ?>_filename').textContent = filename;
            document.getElementById('<?= $uniqueId ?>_fileinfo').textContent = fileType.toUpperCase() + (fileSize ? ' • ' + fileSize : '');
            
            // Update icon based on file type
            const iconElement = document.getElementById('<?= $uniqueId ?>_icon');
            if (iconElement) {
                iconElement.className = 'fs-4 me-2 ';
                if (['pdf'].includes(fileType.toLowerCase())) {
                    iconElement.className += 'bi bi-file-earmark-pdf text-danger';
                } else if (['jpg', 'jpeg', 'png', 'gif'].includes(fileType.toLowerCase())) {
                    iconElement.className += 'bi bi-image text-primary';
                } else {
                    iconElement.className += 'bi bi-file-earmark-text text-success';
                }
            }
            
            previewElement.style.display = 'block';
        }
    }

    // Remove file
    if (removeBtnElement) {
        removeBtnElement.addEventListener('click', () => {
            if (previewElement) previewElement.style.display = 'none';
            if (uploadZone) uploadZone.style.display = 'block';
            if (fileInput) fileInput.value = '';
            if (cameraInput) cameraInput.value = '';
            hideError();
        });
    }

    // Show/hide error
    function showError(message) {
        if (errorElement) {
            errorElement.textContent = message;
            errorElement.style.display = 'block';
        }
    }

    function hideError() {
        if (errorElement) {
            errorElement.style.display = 'none';
        }
    }
});
</script>
<?php
// Test file URL generation in browser context
session_start();

// Simulate logged-in SPED teacher
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'sped_teacher';

require_once __DIR__ . '/../config/db.php';

$db = Database::getInstance()->getConnection();

// Get a document
$stmt = $db->query("SELECT * FROM enrollment_documents LIMIT 1");
$doc = $stmt->fetch();

// Get BASE_PATH
$basePath = defined('BASE_PATH') ? BASE_PATH : '';

?>
<!DOCTYPE html>
<html>
<head>
    <title>Test File URL</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .info { background: #f5f5f5; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>🧪 File URL Test</h1>
    
    <div class="info">
        <h3>BASE_PATH Info:</h3>
        <p><strong>BASE_PATH constant:</strong> <?php echo defined('BASE_PATH') ? BASE_PATH : 'NOT DEFINED'; ?></p>
        <p><strong>$basePath variable:</strong> <?php echo $basePath; ?></p>
        <p><strong>$_SERVER['SCRIPT_NAME']:</strong> <?php echo $_SERVER['SCRIPT_NAME']; ?></p>
        <p><strong>dirname(SCRIPT_NAME):</strong> <?php echo dirname($_SERVER['SCRIPT_NAME']); ?></p>
    </div>

    <?php if ($doc): ?>
        <div class="info">
            <h3>Document Info:</h3>
            <p><strong>ID:</strong> <?php echo $doc['id']; ?></p>
            <p><strong>Type:</strong> <?php echo $doc['document_type']; ?></p>
            <p><strong>File Path (from DB):</strong> <?php echo $doc['file_path']; ?></p>
        </div>

        <?php
        // Generate URL the same way as review_detail.php
        $fileUrl = $basePath . '/' . $doc['file_path'];
        $fullServerPath = __DIR__ . '/' . $doc['file_path'];
        ?>

        <div class="info">
            <h3>Generated URL:</h3>
            <p><strong>URL:</strong> <code><?php echo $fileUrl; ?></code></p>
            <p><strong>Full server path:</strong> <code><?php echo $fullServerPath; ?></code></p>
            <p><strong>File exists:</strong> 
                <?php if (file_exists($fullServerPath)): ?>
                    <span class="success">✅ YES</span>
                <?php else: ?>
                    <span class="error">❌ NO</span>
                <?php endif; ?>
            </p>
        </div>

        <div class="info">
            <h3>Test Links:</h3>
            <p><a href="<?php echo $fileUrl; ?>" target="_blank">Click to View File</a></p>
            <p><a href="<?php echo $fileUrl; ?>" download>Click to Download File</a></p>
        </div>

        <div class="info">
            <h3>Image Preview (if image):</h3>
            <?php if (in_array(strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION)), ['jpg', 'jpeg', 'png', 'gif'])): ?>
                <img src="<?php echo $fileUrl; ?>" style="max-width: 400px; border: 1px solid #ccc;">
            <?php else: ?>
                <p>Not an image file</p>
            <?php endif; ?>
        </div>

        <div class="info">
            <h3>PDF Embed (if PDF):</h3>
            <?php if (strtolower(pathinfo($doc['file_path'], PATHINFO_EXTENSION)) === 'pdf'): ?>
                <iframe src="<?php echo $fileUrl; ?>" width="100%" height="600px" style="border: 1px solid #ccc;"></iframe>
            <?php else: ?>
                <p>Not a PDF file</p>
            <?php endif; ?>
        </div>

    <?php else: ?>
        <div class="info">
            <p class="error">No documents found in database</p>
        </div>
    <?php endif; ?>

</body>
</html>

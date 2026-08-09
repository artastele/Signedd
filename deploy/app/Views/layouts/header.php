<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle ?? 'SignED'; ?></title>
    
    <!-- Favicon -->
    <link rel="icon" type="image/x-icon" href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/images/favicon.ico">
    
    <!-- Bootstrap 5 CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    
    <!-- Custom CSS -->
    <link href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/css/custom.css" rel="stylesheet">

    <!-- Print CSS (Process 5 — IEP print layout) -->
    <link href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/css/print.css" rel="stylesheet" media="print">
    
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.0/font/bootstrap-icons.css">
    
    <!-- Tabler Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@tabler/icons@latest/tabler-icons.min.css">
    
    <!-- Fredoka Font for Cartoon Style -->
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Fredoka:wght@300..700&display=swap" rel="stylesheet">
</head>
<?php
$bodyClass = 'bg-light-surface';
$bodyAttrs = '';
if (isset($_SESSION['role']) && $_SESSION['role'] === 'learner') {
    $bodyClass .= ' learner-layout-active';
}
if (isset($_SESSION['user_id'])) {
    $bodyAttrs .= ' data-logged-in="true"';
}
?>
<body class="<?php echo $bodyClass; ?>"<?php echo $bodyAttrs; ?>>

<!-- Sidebar Overlay (Mobile) -->
<div class="sidebar-overlay"></div>

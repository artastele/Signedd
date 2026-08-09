    <!-- All Rights Reserved Footer Bar -->
    <footer class="py-3 mt-auto bg-light border-top text-center text-muted small">
        <div class="container-fluid">
            <span>&copy; <?php echo date('Y'); ?> <strong>SignED</strong> — Special Education Learning Management System (DepEd SPED LMS). All Rights Reserved.</span>
        </div>
    </footer>

    <!-- Bootstrap 5 JS Bundle -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- SweetAlert2 for Beautiful Alerts -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    
    <!-- Flash Messages as Alerts -->
    <?php if (isset($_SESSION['success'])): ?>
    <script>
        Swal.fire({
            icon: 'success',
            title: 'Success!',
            text: '<?php echo addslashes($_SESSION['success']); ?>',
            confirmButtonColor: '#3b6d11',
            timer: 3000,
            timerProgressBar: true
        });
    </script>
    <?php unset($_SESSION['success']); endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
    <script>
        Swal.fire({
            icon: 'error',
            title: 'Error!',
            text: '<?php echo addslashes($_SESSION['error']); ?>',
            confirmButtonColor: '#a01422'
        });
    </script>
    <?php unset($_SESSION['error']); endif; ?>
    
    <?php if (isset($_SESSION['warning'])): ?>
    <script>
        Swal.fire({
            icon: 'warning',
            title: 'Warning!',
            text: '<?php echo addslashes($_SESSION['warning']); ?>',
            confirmButtonColor: '#ffc107'
        });
    </script>
    <?php unset($_SESSION['warning']); endif; ?>
    
    <?php if (isset($_SESSION['info'])): ?>
    <script>
        Swal.fire({
            icon: 'info',
            title: 'Information',
            text: '<?php echo addslashes($_SESSION['info']); ?>',
            confirmButtonColor: '#1e4072'
        });
    </script>
    <?php unset($_SESSION['info']); endif; ?>
    
    <!-- Custom JS -->
    <script src="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/js/app.js"></script>
    
    <!-- Sidebar Mobile Navigation -->
    <script src="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/js/sidebar.js"></script>

    <?php if (isset($_SESSION['role']) && $_SESSION['role'] === 'learner'): 
        $reqUri = (string)($_SERVER['REQUEST_URI'] ?? '');
    ?>
    <!-- Mobile Bottom Navigation Bar for Learner -->
    <nav class="learner-bottom-nav">
        <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/learning/dashboard" class="learner-bottom-tab <?php echo ($reqUri !== '' && strpos($reqUri, '/learning/dashboard') !== false && strpos($reqUri, 'tab=badges') === false) ? 'active' : ''; ?>">
            <i class="bi bi-house-heart-fill"></i>
            <span>Home</span>
        </a>
        <a href="<?php echo defined('BASE_PATH') ? BASE_PATH : ''; ?>/learning/dashboard?tab=badges" class="learner-bottom-tab <?php echo ($reqUri !== '' && strpos($reqUri, 'tab=badges') !== false) ? 'active' : ''; ?>">
            <i class="bi bi-trophy-fill"></i>
            <span>My Badges</span>
        </a>
    </nav>
    <?php endif; ?>
</body>
</html>

<?php
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
$userName = $_SESSION['user_name'] ?? 'User';
$userEmail = $_SESSION['user_email'] ?? '';
$role = $_SESSION['role'] ?? 'user';
?>

<!-- Top Navigation Bar -->
<div class="topbar">
    <div class="topbar-left">
        <?php if ($role !== 'learner'): ?>
        <!-- Hamburger — visible on mobile only -->
        <button class="hamburger-btn" id="sidebarHamburger" type="button" aria-label="Toggle sidebar">
            <i class="bi bi-list"></i>
        </button>
        <?php else: ?>
        <!-- Top Pill Navigation for Learner -->
        <nav class="learner-top-nav" aria-label="Pangunahing Navigasyon ng Mag-aaral">
            <a href="<?php echo $basePath; ?>/learning/dashboard" class="learner-nav-pill <?php echo isActive('/learning/dashboard') && !isset($_GET['tab']) ? 'active' : ''; ?>">
                <i class="ph-bold ph-book-open" aria-hidden="true"></i>
                <span>Aking Aralin</span>
            </a>
            <a href="<?php echo $basePath; ?>/learning/dashboard?tab=badges" class="learner-nav-pill <?php echo isset($_GET['tab']) && $_GET['tab'] === 'badges' ? 'active' : ''; ?>">
                <i class="ph-bold ph-star" aria-hidden="true"></i>
                <span>Mga Bituin at Badge</span>
            </a>
        </nav>
        <?php endif; ?>
    </div>
    
    <div class="topbar-right">
        <!-- Notification Bell -->
        <div class="topbar-notification">
            <button class="notification-bell-btn" id="notificationBell" type="button">
                <i class="bi bi-bell-fill"></i>
                <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
            </button>
            
            <!-- Notification Dropdown Panel -->
            <div class="notification-panel" id="notificationPanel" style="display: none;">
                <div class="notification-header d-flex justify-content-between align-items-center">
                    <h6 class="mb-0">Notifications</h6>
                    <button class="btn btn-sm btn-link text-white p-0" id="markAllRead" style="display: none;">
                        Mark all as read
                    </button>
                </div>
                <!-- Push Notification Permission Banner -->
                <div id="pushNotifyBanner" class="p-2 border-bottom bg-light text-center small" style="display: none;">
                    <div class="text-muted mb-1" style="font-size: 0.78rem;">Gusto ba nimong makadawat og instant alerts sa cellphone?</div>
                    <button type="button" class="btn btn-primary btn-sm py-1 px-3 fw-semibold" onclick="requestBrowserPush()" style="font-size: 0.75rem; border-radius: 6px;">
                        <i class="bi bi-bell-fill me-1"></i> Allow Notifications
                    </button>
                </div>
                <div class="notification-body" id="notificationBody">
                    <div class="text-center py-4 text-muted">
                        <i class="bi bi-bell-slash" style="font-size: 2rem;"></i>
                        <p class="mb-0 mt-2">No new notifications</p>
                    </div>
                </div>
            </div>
        </div>

        <script>
        function requestBrowserPush() {
            if (!('Notification' in window)) {
                alert('Dili pa mosuporta ang imong browser sa Web Push Notifications.');
                return;
            }
            Notification.requestPermission().then(permission => {
                if (permission === 'granted') {
                    new Notification('SignED SPED LMS', {
                        body: 'Malampusong na-on ang notifications! Makadawat ka na karon og instant alerts.',
                        icon: '<?= $basePath ?>/favicon.ico'
                    });
                    const b = document.getElementById('pushNotifyBanner');
                    if (b) b.style.display = 'none';
                    const alertCard = document.getElementById('parentPushAlert');
                    if (alertCard) alertCard.style.display = 'none';
                }
            });
        }
        document.addEventListener('DOMContentLoaded', () => {
            if ('Notification' in window && Notification.permission === 'default') {
                const b = document.getElementById('pushNotifyBanner');
                if (b) b.style.display = 'block';
                const alertCard = document.getElementById('parentPushAlert');
                if (alertCard) alertCard.style.display = 'flex';
            }
        });
        </script>

        <!-- User Profile Dropdown -->
        <div class="topbar-user dropdown">
            <button class="topbar-user-btn dropdown-toggle" type="button" id="userDropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <div class="user-avatar">
                    <i class="bi bi-person-circle"></i>
                </div>
                <div class="user-info">
                    <div class="user-name"><?php echo htmlspecialchars($userName); ?></div>
                    <div class="user-role"><?php echo ucwords(str_replace('_', ' ', $role)); ?></div>
                </div>
            </button>
            <ul class="dropdown-menu dropdown-menu-end" aria-labelledby="userDropdown">
                <li>
                    <div class="dropdown-header">
                        <strong><?php echo htmlspecialchars($userName); ?></strong><br>
                        <small class="text-muted"><?php echo htmlspecialchars($userEmail); ?></small>
                    </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item" href="<?php echo $role === 'learner' ? $basePath . '/learning/dashboard' : $basePath . '/dashboard'; ?>"><i class="bi bi-house-door me-2"></i> Dashboard</a></li>
                <li><a class="dropdown-item" href="<?php echo $basePath; ?>/profile"><i class="bi bi-person-gear me-2 text-primary"></i> Profile &amp; Settings</a></li>
                <li><hr class="dropdown-divider"></li>
                <li><a class="dropdown-item text-danger" href="<?php echo $basePath; ?>/logout"><i class="bi bi-box-arrow-right me-2"></i> Logout</a></li>
            </ul>
        </div>
    </div>
</div>

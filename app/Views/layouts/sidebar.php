<?php
$currentPath = (string)($_SERVER['REQUEST_URI'] ?? '');
$basePath = (string)(defined('BASE_PATH') ? BASE_PATH : '');
$role = $_SESSION['role'] ?? 'user';
$userName = $_SESSION['user_name'] ?? 'User';

function isActive($path) {
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
    $base = (string)(defined('BASE_PATH') ? BASE_PATH : '');
    $full = $base . (string)($path ?? '');
    if ($uri === '' || $full === '') return '';
    return strpos($uri, $full) === 0 ? 'active' : '';
}

// Check if any IEP Procedure link is active
function isIEPProcedureActive() {
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
    $base = (string)(defined('BASE_PATH') ? BASE_PATH : '');
    $iepPaths = [
        '/assessment/conduct', 
        '/iep/meetings', 
        '/iep/p2/', 
        '/iep/p3/', 
        '/iep/documents', 
        '/iep/',
    ];
    // Exclude transition paths to prevent both menus from opening
    foreach (['/transition-readiness', '/individual-transition-plan', '/inclusive-iep-itgp', '/placement-notice'] as $exclude) {
        if ($uri !== '' && strpos($uri, $base . $exclude) !== false) {
            return false;
        }
    }
    foreach ($iepPaths as $path) {
        if ($uri !== '' && strpos($uri, $base . $path) !== false) {
            return true;
        }
    }
    return false;
}

// Check if any ITP Procedure link is active
function isITPProcedureActive() {
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
    $base = (string)(defined('BASE_PATH') ? BASE_PATH : '');
    $itpPaths = [
        '/transition-readiness',
        '/individual-transition-plan',
        '/inclusive-iep-itgp',
        '/placement-notice'
    ];
    foreach ($itpPaths as $path) {
        if ($uri !== '' && strpos($uri, $base . $path) !== false) {
            return true;
        }
    }
    return false;
}

function isEnrollmentAvailabilityMenuActive() {
    $uri = (string)($_SERVER['REQUEST_URI'] ?? '');
    $base = (string)(defined('BASE_PATH') ? BASE_PATH : '');
    foreach (['/verification', '/enrollment/review', '/iep/availability'] as $p) {
        if ($uri !== '' && strpos($uri, $base . $p) === 0) {
            return true;
        }
    }
    return false;
}
if ($role === 'learner') {
    return;
}
?>

<!-- Collapsible Sidebar Arrow Toggle Button -->
<button type="button" id="sidebarCollapseArrow" class="sidebar-collapse-arrow" onclick="toggleSidebarCollapse()" aria-label="Toggle Navigation Sidebar" title="Itago ang Sidebar">
    <i class="ph-bold ph-caret-left" id="sidebarArrowIcon"></i>
</button>

<div class="sidebar" id="sidebar">
    <!-- Logo -->
    <div class="sidebar-logo">
        <?php if (file_exists(__DIR__ . '/../../../public/images/logo.png') || file_exists(__DIR__ . '/../../../images/logo.png')): ?>
            <img src="<?php echo $basePath; ?>/images/logo.png" alt="SignED Logo">
        <?php else: ?>

            <i class="bi bi-mortarboard-fill" style="font-size: 3rem; color: #ffffff;"></i>
        <?php endif; ?>
        <h4>SignED</h4>
    </div>

    <!-- Navigation Menu (Scrollable) -->
    <div class="sidebar-menu">
        <a href="<?php echo $basePath; ?>/dashboard" class="<?php echo isActive('/dashboard'); ?>">
            <i class="bi bi-house-door-fill"></i>
            <span>Dashboard</span>
        </a>

        <?php if ($role === 'user'): ?>
            <a href="<?php echo $basePath; ?>/services" class="<?php echo isActive('/services'); ?>">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Services</span>
            </a>

        <?php elseif ($role === 'learner'): ?>
            <!-- LMS Learner nav links -->
            <a href="<?php echo $basePath; ?>/learning/dashboard" class="<?php echo isActive('/learning/dashboard'); ?>">
                <i class="bi bi-book-open"></i>
                <span>My Lessons</span>
            </a>
            <a href="<?php echo $basePath; ?>/learning/progress" class="<?php echo isActive('/learning/progress'); ?>">
                <i class="bi bi-bar-chart-line"></i>
                <span>My Progress</span>
            </a>

        <?php elseif ($role === 'parent'): ?>
            <a href="<?php echo $basePath; ?>/enrollment/status" class="<?php echo isActive('/enrollment/status'); ?>">
                <i class="bi bi-list-check"></i>
                <span>My Enrollments</span>
            </a>
            <!-- Step 16 — Child LMS Progress -->
            <a href="<?php echo $basePath; ?>/parent/child-progress" class="<?php echo isActive('/parent/child-progress'); ?>">
                <i class="bi bi-bar-chart-line"></i>
                <span>My Child's Progress</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep/meetings" class="<?php echo isActive('/iep/meetings'); ?>">
                <i class="bi bi-calendar-check"></i>
                <span>IEP Meetings</span>
            </a>
            <a href="<?php echo $basePath; ?>/progress-reports" class="<?php echo isActive('/progress-reports'); ?>">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Progress Reports</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep" class="<?php echo isActive('/iep') && !isActive('/iep/meetings') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-text"></i>
                <span>My Child's IEP</span>
            </a>
            <a href="<?php echo $basePath; ?>/services" class="<?php echo isActive('/services'); ?>">
                <i class="bi bi-grid-3x3-gap"></i>
                <span>Services</span>
            </a>

        <?php elseif ($role === 'sped_teacher'): ?>
            <!-- IEP Procedure (Collapsible) -->
            <div class="sidebar-section">
                <a href="#" class="sidebar-section-toggle <?php echo isIEPProcedureActive() ? 'active' : ''; ?>" data-bs-toggle="collapse" data-bs-target="#iepProcedureMenu" aria-expanded="<?php echo isIEPProcedureActive() ? 'true' : 'false'; ?>">
                    <i class="bi bi-file-earmark-medical"></i>
                    <span>IEP Procedure</span>
                    <i class="bi bi-chevron-down toggle-icon"></i>
                </a>
                <div class="collapse <?php echo isIEPProcedureActive() ? 'show' : ''; ?>" id="iepProcedureMenu">
                    <a href="<?php echo $basePath; ?>/assessment" class="sidebar-submenu-item <?php echo isActive('/assessment') && !isActive('/assessment/conduct'); ?>">
                        <i class="bi bi-clock-history"></i>
                        <span>Assessment History</span>
                    </a>
                    <a href="<?php echo $basePath; ?>/assessment/conduct" class="sidebar-submenu-item <?php echo isActive('/assessment/conduct'); ?>">
                        <span class="step-num-badge badge-step-1">1</span>
                        <span>Part 1: Assessment</span>
                    </a>
                    <a href="<?php echo $basePath; ?>/iep/meetings" class="sidebar-submenu-item <?php echo isActive('/iep/meetings'); ?>">
                        <span class="step-num-badge badge-step-2">2</span>
                        <span>Part 2: Meeting &amp; PDSP</span>
                    </a>
                    <a href="<?php echo $basePath; ?>/iep" class="sidebar-submenu-item <?php echo isActive('/iep') && !isActive('/iep/meetings') && !isActive('/iep/availability') && !preg_match('#/iep/\\d+/#', $currentPath) ? 'active' : ''; ?>">
                        <span class="step-num-badge badge-step-3">3</span>
                        <span>Part 3: Generate IEP</span>
                    </a>
                </div>
            </div>

            <a href="<?php echo $basePath; ?>/iep/implementation" class="<?php echo isActive('/iep/implementation'); ?>">
                <i class="bi bi-book"></i>
                <span>IEP Workspace</span>
            </a>
            <a href="<?php echo $basePath; ?>/progress-reports" class="<?php echo isActive('/progress-reports') || isActive('/iep/implementation/progress-tracker'); ?>">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Progress Reports &amp; Tracker</span>
            </a>
            <a href="<?php echo $basePath; ?>/attendance-log" class="<?php echo isActive('/attendance-log'); ?>">
                <i class="bi bi-calendar-check"></i>
                <span>Attendance List</span>
            </a>
            <a href="<?php echo $basePath; ?>/cot/observations" class="<?php echo isActive('/cot/observations'); ?>">
                <i class="bi bi-eye"></i>
                <span>My COT</span>
            </a>

        <?php elseif ($role === 'general_teacher'): ?>
            <a href="<?php echo $basePath; ?>/iep" class="<?php echo isActive('/iep') && !preg_match('#/iep/\\d+/#', $currentPath) ? 'active' : ''; ?>">
                <i class="bi bi-folder2-open"></i>
                <span>Assigned IEPs</span>
            </a>
            <?php
            $currentIepId = null;
            if (preg_match('#/iep/(\d+)#', $currentPath, $matches)) {
                $currentIepId = (int)$matches[1];
            }
            if ($currentIepId):
            ?>
                <div class="border-top border-secondary opacity-25 my-2"></div>
                <a href="<?php echo $basePath; ?>/iep/<?php echo $currentIepId; ?>/inclusive-iep-itgp" class="sidebar-submenu-item <?php echo isActive('/iep/' . $currentIepId . '/inclusive-iep-itgp'); ?>">
                    <i class="bi bi-file-text"></i>
                    <span>Inclusive IEP (ITGP)</span>
                </a>
                <a href="<?php echo $basePath; ?>/iep/<?php echo $currentIepId; ?>/placement-notice" class="sidebar-submenu-item <?php echo isActive('/iep/' . $currentIepId . '/placement-notice'); ?>">
                    <i class="bi bi-envelope"></i>
                    <span>Placement Notice</span>
                </a>
            <?php endif; ?>

        <?php elseif ($role === 'guidance'): ?>
            <a href="<?php echo $basePath; ?>/iep/availability" class="<?php echo isActive('/iep/availability'); ?>">
                <i class="bi bi-calendar3"></i>
                <span>My Availability</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep/meetings" class="<?php echo isActive('/iep/meetings'); ?>">
                <i class="bi bi-calendar-event"></i>
                <span>IEP Meetings</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep" class="<?php echo isActive('/iep') && !isActive('/iep/meetings') && !isActive('/iep/availability') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-medical"></i>
                <span>IEP Documents</span>
            </a>
            <a href="<?php echo $basePath; ?>/progress-reports" class="<?php echo isActive('/progress-reports'); ?>">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Progress Reports</span>
            </a>

        <?php elseif ($role === 'principal'): ?>
            <a href="<?php echo $basePath; ?>/principal/staff-requests" class="<?php echo isActive('/principal/staff-requests'); ?>">
                <i class="bi bi-person-check"></i>
                <span>Staff Requests</span>
            </a>
            <a href="<?php echo $basePath; ?>/sections" class="<?php echo isActive('/sections'); ?>">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                <span>Section Management</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep/availability" class="<?php echo isActive('/iep/availability'); ?>">
                <i class="bi bi-calendar3"></i>
                <span>My Availability</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep/meetings" class="<?php echo isActive('/iep/meetings'); ?>">
                <i class="bi bi-calendar-event"></i>
                <span>IEP Meetings</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep" class="<?php echo isActive('/iep') && !isActive('/iep/meetings') && !isActive('/iep/availability') ? 'active' : ''; ?>">
                <i class="bi bi-file-earmark-medical"></i>
                <span>IEP Documents</span>
            </a>
            <a href="<?php echo $basePath; ?>/progress-reports" class="<?php echo isActive('/progress-reports'); ?>">
                <i class="bi bi-file-earmark-bar-graph"></i>
                <span>Progress Reports</span>
            </a>
            <a href="<?php echo $basePath; ?>/cot/observations" class="<?php echo isActive('/cot/observations'); ?>">
                <i class="bi bi-eye"></i>
                <span>Observations</span>
            </a>

        <?php elseif ($role === 'master_teacher'): ?>
            <a href="<?php echo $basePath; ?>/sections" class="<?php echo isActive('/sections'); ?>">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                <span>Section Management</span>
            </a>
            <a href="<?php echo $basePath; ?>/iep" class="<?php echo isActive('/iep') && !isActive('/iep/implementation') ? 'active' : ''; ?>">
                <i class="bi bi-folder2-open"></i>
                <span>IEP Records</span>
            </a>
            <a href="<?php echo $basePath; ?>/cot/indicators" class="<?php echo isActive('/cot/indicators'); ?>">
                <i class="bi bi-list-task"></i>
                <span>COT Indicator Sets</span>
            </a>
            <a href="<?php echo $basePath; ?>/cot/observations" class="<?php echo isActive('/cot/observations'); ?>">
                <i class="bi bi-eye"></i>
                <span>Observations</span>
            </a>
            <a href="<?php echo $basePath; ?>/itgp/inspection-queue" class="<?php echo isActive('/itgp/inspection-queue'); ?>">
                <i class="bi bi-journal-check"></i>
                <span>ITGP Inspection</span>
            </a>

        <?php elseif ($role === 'admin'): ?>
            <a href="<?php echo $basePath; ?>/admin/role-requests" class="<?php echo isActive('/admin/role-requests'); ?>">
                <i class="bi bi-person-check"></i>
                <span>Principal &amp; School Requests</span>
            </a>
            <a href="<?php echo $basePath; ?>/sections" class="<?php echo isActive('/sections'); ?>">
                <i class="bi bi-grid-3x3-gap-fill"></i>
                <span>Section Management</span>
            </a>
            <a href="<?php echo $basePath; ?>/admin/manage-users" class="<?php echo isActive('/admin/manage-users'); ?>">
                <i class="bi bi-people"></i>
                <span>Manage Users</span>
            </a>
            <a href="<?php echo $basePath; ?>/admin/settings" class="<?php echo isActive('/admin/settings'); ?>">
                <i class="bi bi-gear"></i>
                <span>System Settings</span>
            </a>
            <a href="<?php echo $basePath; ?>/admin/login-logs" class="<?php echo isActive('/admin/login-logs'); ?>">
                <i class="bi bi-shield-lock"></i>
                <span>Login Logs</span>
            </a>
            <a href="<?php echo $basePath; ?>/admin/activity-logs" class="<?php echo isActive('/admin/activity-logs'); ?>">
                <i class="bi bi-activity"></i>
                <span>Activity Logs</span>
            </a>
        <?php endif; ?>

        <?php if ($role !== 'parent' && $role !== 'user' && $role !== 'learner'): ?>
            <!-- Student Records & Masterlist - Available for all staff roles -->
            <div class="sidebar-divider"></div>
            <a href="<?php echo $basePath; ?>/masterlist" class="<?php echo isActive('/masterlist'); ?>">
                <i class="bi bi-people-fill"></i>
                <span>Learner Masterlist &amp; Registry</span>
            </a>
        <?php endif; ?>

    </div>

    <!-- User Info & Logout (Natural Flow at bottom of flex column) -->
    <div class="sidebar-user">
        <a href="<?php echo $basePath; ?>/profile" class="sidebar-user-info text-decoration-none" title="View Profile & Settings">
            <div class="name text-white"><?php echo htmlspecialchars($userName); ?> <i class="bi bi-gear-fill ms-1 opacity-50" style="font-size: 0.75rem;"></i></div>
            <div class="role">
                <span class="badge bg-secondary"><?php echo ucwords(str_replace('_', ' ', $role)); ?></span>
            </div>
        </a>
        <a href="<?php echo $basePath; ?>/logout" class="btn btn-logout">
            <i class="bi bi-box-arrow-right me-1"></i> Logout
        </a>
    </div>
</div>

<style>
/* ============================================
   SIDEBAR — Layout & High-Contrast Badges
   ============================================ */
.sidebar {
    display: flex !important;
    flex-direction: column !important;
    height: 100vh !important;
    position: fixed !important;
    top: 0 !important;
    left: 0 !important;
    width: var(--sidebar-width) !important;
    background: linear-gradient(180deg, #a01422 0%, #1e4072 100%) !important;
    overflow: hidden !important;
    z-index: 1000 !important;
}

.sidebar-logo {
    flex-shrink: 0 !important;
    padding: 1.25rem 1rem !important;
    text-align: center;
    border-bottom: 1px solid rgba(255, 255, 255, 0.1);
}

.sidebar-menu {
    flex: 1 1 auto !important;
    overflow-y: auto !important;
    overflow-x: hidden !important;
    padding: 0.75rem 0 1.5rem 0 !important;
}

.sidebar-user {
    position: relative !important;
    flex-shrink: 0 !important;
    width: 100% !important;
    padding: 0.85rem 1.25rem !important;
    background: rgba(10, 15, 25, 0.45) !important;
    backdrop-filter: blur(8px);
    border-top: 1px solid rgba(255, 255, 255, 0.15) !important;
    margin-top: auto !important;
    z-index: 20 !important;
}

/* High-Contrast Step Badges for Numbers */
.step-num-badge {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 22px;
    height: 22px;
    border-radius: 50%;
    font-size: 0.75rem;
    font-weight: 700;
    line-height: 1;
    margin-right: 10px;
    flex-shrink: 0;
    box-shadow: 0 1px 3px rgba(0,0,0,0.3);
}

.badge-step-1 {
    background-color: #ff4757 !important;
    color: #ffffff !important;
}

.badge-step-2 {
    background-color: #ffa502 !important;
    color: #212529 !important;
}

.badge-step-3 {
    background-color: #2ed573 !important;
    color: #ffffff !important;
}

/* Collapsible section toggle */
.sidebar-section { margin: 3px 0; }

.sidebar-section-toggle {
    display: flex;
    align-items: center;
    padding: 10px 20px;
    color: rgba(255,255,255,0.9);
    text-decoration: none;
    transition: all 0.25s ease;
    cursor: pointer;
}
.sidebar-section-toggle:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding-left: 24px;
}
.sidebar-section-toggle.active {
    background: rgba(160,20,34,0.35);
    border-left: 4px solid #ff4757;
    color: #fff;
}
.sidebar-section-toggle i:first-child { margin-right: 12px; font-size: 1.1rem; }
.sidebar-section-toggle .toggle-icon { margin-left: auto; transition: transform 0.3s; font-size: 0.85rem; }
.sidebar-section-toggle[aria-expanded="true"] .toggle-icon { transform: rotate(180deg); }

/* Submenu items */
.sidebar-submenu-item {
    display: flex;
    align-items: center;
    padding: 8px 20px 8px 42px;
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    transition: all 0.25s ease;
    font-size: 0.875rem;
    border-left: 3px solid transparent;
}
.sidebar-submenu-item:hover {
    background: rgba(255,255,255,0.1);
    color: #fff;
    padding-left: 46px;
    border-left-color: rgba(255,255,255,0.3);
}
.sidebar-submenu-item.active {
    background: rgba(160,20,34,0.4);
    border-left-color: #ff4757;
    color: #fff;
    font-weight: 600;
}
.sidebar-submenu-item i { margin-right: 10px; font-size: 0.95rem; }

/* Scrollbar styling */
.sidebar-menu::-webkit-scrollbar { width: 5px; }
.sidebar-menu::-webkit-scrollbar-track { background: rgba(255,255,255,0.05); }
.sidebar-menu::-webkit-scrollbar-thumb { background: rgba(255,255,255,0.25); border-radius: 3px; }
.sidebar-menu::-webkit-scrollbar-thumb:hover { background: rgba(255,255,255,0.45); }
</style>
<script>
function toggleSidebarCollapse() {
    document.body.classList.toggle('sidebar-collapsed');
    const isCollapsed = document.body.classList.contains('sidebar-collapsed');
    const icon = document.getElementById('sidebarArrowIcon');
    if (icon) {
        icon.className = isCollapsed ? 'ph-bold ph-caret-right' : 'ph-bold ph-caret-left';
    }
    const btn = document.getElementById('sidebarCollapseArrow');
    if (btn) {
        btn.setAttribute('title', isCollapsed ? 'Ipakita ang Sidebar' : 'Itago ang Sidebar');
    }
    try {
        localStorage.setItem('signed_sidebar_collapsed', isCollapsed ? '1' : '0');
    } catch(e){}
}

(function() {
    try {
        const saved = localStorage.getItem('signed_sidebar_collapsed');
        if (saved === '1') {
            document.body.classList.add('sidebar-collapsed');
            const icon = document.getElementById('sidebarArrowIcon');
            if (icon) icon.className = 'ph-bold ph-caret-right';
            const btn = document.getElementById('sidebarCollapseArrow');
            if (btn) btn.setAttribute('title', 'Ipakita ang Sidebar');
        }
    } catch(e){}
})();
</script>

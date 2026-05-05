<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Learner Dashboard (Cartoon Style)

$pageTitle = 'My Learning - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo $basePath; ?>/css/learner.css">

<body data-logged-in="true" class="learner-page">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-content">
    <!-- Welcome Banner -->
    <div class="welcome-banner">
        <div class="row align-items-center">
            <div class="col-md-8">
                <h1 class="welcome-title">
                    👋 Hi <?php echo htmlspecialchars($_SESSION['first_name'] ?? 'Learner'); ?>!
                </h1>
                <p class="welcome-subtitle">Ready to learn something awesome today?</p>
            </div>
            <div class="col-md-4 text-end">
                <div class="stars-display">
                    <i class="bi bi-star-fill"></i>
                    <span class="stars-count"><?php echo $totalStars; ?></span>
                    <span class="stars-label">Stars</span>
                </div>
            </div>
        </div>
    </div>

    <!-- Progress Card -->
    <div class="card cartoon-card mb-4">
        <div class="card-body text-center">
            <h5 class="card-title">📊 My Progress</h5>
            <div class="progress-circle">
                <svg width="120" height="120">
                    <circle cx="60" cy="60" r="50" fill="none" stroke="#e0e0e0" stroke-width="10"/>
                    <circle cx="60" cy="60" r="50" fill="none" stroke="#FFD93D" stroke-width="10"
                            stroke-dasharray="<?php echo $progressPercentage * 3.14; ?> 314"
                            transform="rotate(-90 60 60)"/>
                </svg>
                <div class="progress-text"><?php echo round($progressPercentage); ?>%</div>
            </div>
            <p class="mt-3 mb-0">Keep going! You're doing great! 🎉</p>
        </div>
    </div>

    <!-- Quick Stats -->
    <div class="row mb-4">
        <div class="col-md-4">
            <div class="stat-card stat-modules">
                <div class="stat-icon">📚</div>
                <div class="stat-number"><?php echo count($modules); ?></div>
                <div class="stat-label">Modules</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-assignments">
                <div class="stat-icon">📝</div>
                <div class="stat-number"><?php echo count($assignments); ?></div>
                <div class="stat-label">Assignments</div>
            </div>
        </div>
        <div class="col-md-4">
            <div class="stat-card stat-stars">
                <div class="stat-icon">⭐</div>
                <div class="stat-number"><?php echo $totalStars; ?></div>
                <div class="stat-label">Stars Earned</div>
            </div>
        </div>
    </div>

    <!-- My Modules -->
    <div class="section-header">
        <h3>📚 My Modules</h3>
        <a href="<?php echo $basePath; ?>/learning/modules" class="btn-cartoon btn-primary">
            See All <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <?php if (empty($modules)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p>No modules yet. Check back soon!</p>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <?php foreach (array_slice($modules, 0, 3) as $module): ?>
                <div class="col-md-4 mb-3">
                    <div class="module-card">
                        <div class="module-icon">
                            <?php 
                            $icons = ['📖', '📕', '📗', '📘', '📙'];
                            echo $icons[array_rand($icons)];
                            ?>
                        </div>
                        <h5 class="module-title"><?php echo htmlspecialchars($module['material_name']); ?></h5>
                        <?php if ($module['progress_status'] === 'completed'): ?>
                            <div class="module-badge badge-completed">✓ Completed</div>
                        <?php elseif ($module['progress_status'] === 'in_progress'): ?>
                            <div class="module-badge badge-progress">⏳ In Progress</div>
                        <?php else: ?>
                            <div class="module-badge badge-new">✨ New!</div>
                        <?php endif; ?>
                        <a href="<?php echo $basePath; ?>/learning/module/<?php echo $module['id']; ?>" 
                           class="btn-cartoon btn-start">
                            Start Learning! 🚀
                        </a>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- My Assignments -->
    <div class="section-header">
        <h3>📝 My Assignments</h3>
        <a href="<?php echo $basePath; ?>/learning/assignments" class="btn-cartoon btn-primary">
            See All <i class="bi bi-arrow-right"></i>
        </a>
    </div>

    <?php if (empty($assignments)): ?>
        <div class="empty-state">
            <div class="empty-icon">📭</div>
            <p>No assignments yet. Enjoy your free time!</p>
        </div>
    <?php else: ?>
        <div class="row mb-4">
            <?php foreach (array_slice($assignments, 0, 3) as $assignment): ?>
                <div class="col-md-4 mb-3">
                    <div class="assignment-card">
                        <div class="assignment-icon">✏️</div>
                        <h5 class="assignment-title"><?php echo htmlspecialchars($assignment['material_name']); ?></h5>
                        <?php if ($assignment['due_date']): ?>
                            <?php
                            $daysLeft = ceil((strtotime($assignment['due_date']) - time()) / 86400);
                            $urgentClass = $daysLeft <= 3 ? 'urgent' : '';
                            ?>
                            <div class="assignment-due <?php echo $urgentClass; ?>">
                                📅 Due in <?php echo $daysLeft; ?> day<?php echo $daysLeft != 1 ? 's' : ''; ?>
                            </div>
                        <?php endif; ?>
                        <?php if ($assignment['submission_id']): ?>
                            <div class="assignment-badge badge-submitted">✓ Submitted</div>
                        <?php else: ?>
                            <a href="<?php echo $basePath; ?>/learning/assignment/<?php echo $assignment['id']; ?>" 
                               class="btn-cartoon btn-do">
                                Do Assignment! ✍️
                            </a>
                        <?php endif; ?>
                    </div>
                </div>
            <?php endforeach; ?>
        </div>
    <?php endif; ?>

    <!-- Quick Links -->
    <div class="row">
        <div class="col-md-6 mb-3">
            <a href="<?php echo $basePath; ?>/learning/modules" class="quick-link-card">
                <div class="quick-link-icon">📚</div>
                <div class="quick-link-text">
                    <h5>Browse Modules</h5>
                    <p>Explore all learning materials</p>
                </div>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
        <div class="col-md-6 mb-3">
            <a href="<?php echo $basePath; ?>/learning/progress" class="quick-link-card">
                <div class="quick-link-icon">⭐</div>
                <div class="quick-link-text">
                    <h5>My Progress</h5>
                    <p>See your stars and achievements</p>
                </div>
                <i class="bi bi-arrow-right"></i>
            </a>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

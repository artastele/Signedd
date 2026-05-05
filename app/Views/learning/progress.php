<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-05
// Part of: SPED LMS — Progress Page

$pageTitle = 'My Progress - SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
?>

<link rel="stylesheet" href="<?php echo $basePath; ?>/css/learner.css">

<body data-logged-in="true" class="learner-page">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-content">
    <div class="section-header">
        <h1>⭐ My Progress</h1>
        <a href="<?php echo $basePath; ?>/learning/dashboard" class="btn-cartoon btn-primary">
            <i class="bi bi-arrow-left"></i> Back
        </a>
    </div>

    <!-- Stats Overview -->
    <div class="row mb-4">
        <div class="col-md-3">
            <div class="progress-stat-card stat-stars">
                <div class="stat-icon">⭐</div>
                <div class="stat-number"><?php echo $totalStars; ?></div>
                <div class="stat-label">Total Stars</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="progress-stat-card stat-completed">
                <div class="stat-icon">✅</div>
                <div class="stat-number"><?php echo $completedCount; ?></div>
                <div class="stat-label">Completed</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="progress-stat-card stat-total">
                <div class="stat-icon">📚</div>
                <div class="stat-number"><?php echo $totalMaterials; ?></div>
                <div class="stat-label">Total Materials</div>
            </div>
        </div>
        <div class="col-md-3">
            <div class="progress-stat-card stat-submissions">
                <div class="stat-icon">📝</div>
                <div class="stat-number"><?php echo $submissionsCount; ?></div>
                <div class="stat-label">Submissions</div>
            </div>
        </div>
    </div>

    <!-- Progress Circle -->
    <div class="card cartoon-card mb-4">
        <div class="card-body text-center">
            <h3>📊 Overall Progress</h3>
            <div class="large-progress-circle">
                <svg width="200" height="200">
                    <circle cx="100" cy="100" r="80" fill="none" stroke="#e0e0e0" stroke-width="15"/>
                    <circle cx="100" cy="100" r="80" fill="none" stroke="url(#gradient)" stroke-width="15"
                            stroke-dasharray="<?php echo $progressPercentage * 5.03; ?> 503"
                            stroke-linecap="round"
                            transform="rotate(-90 100 100)"/>
                    <defs>
                        <linearGradient id="gradient" x1="0%" y1="0%" x2="100%" y2="100%">
                            <stop offset="0%" style="stop-color:var(--kid-yellow);stop-opacity:1" />
                            <stop offset="100%" style="stop-color:var(--kid-orange);stop-opacity:1" />
                        </linearGradient>
                    </defs>
                </svg>
                <div class="large-progress-text"><?php echo round($progressPercentage); ?>%</div>
            </div>
            <p class="mt-3 mb-0" style="font-size: 1.2rem;">
                <?php if ($progressPercentage >= 80): ?>
                    🎉 Amazing! You're almost done!
                <?php elseif ($progressPercentage >= 50): ?>
                    💪 Great progress! Keep it up!
                <?php elseif ($progressPercentage >= 20): ?>
                    🚀 You're doing well! Keep going!
                <?php else: ?>
                    ✨ Let's start learning!
                <?php endif; ?>
            </p>
        </div>
    </div>

    <!-- Achievement Badges -->
    <div class="card cartoon-card mb-4">
        <div class="card-body">
            <h3 class="text-center mb-4">🏆 Achievements</h3>
            <div class="achievements-grid">
                <!-- Star Collector Badges -->
                <div class="achievement-badge <?php echo $totalStars >= 1 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">⭐</div>
                    <div class="badge-name">First Star</div>
                    <div class="badge-desc">Earn your first star</div>
                </div>
                
                <div class="achievement-badge <?php echo $totalStars >= 10 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">🌟</div>
                    <div class="badge-name">Star Collector</div>
                    <div class="badge-desc">Earn 10 stars</div>
                </div>
                
                <div class="achievement-badge <?php echo $totalStars >= 50 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">✨</div>
                    <div class="badge-name">Super Star</div>
                    <div class="badge-desc">Earn 50 stars</div>
                </div>
                
                <!-- Completion Badges -->
                <div class="achievement-badge <?php echo $completedCount >= 1 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">📖</div>
                    <div class="badge-name">First Steps</div>
                    <div class="badge-desc">Complete 1 module</div>
                </div>
                
                <div class="achievement-badge <?php echo $completedCount >= 5 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">📚</div>
                    <div class="badge-name">Bookworm</div>
                    <div class="badge-desc">Complete 5 modules</div>
                </div>
                
                <div class="achievement-badge <?php echo $completedCount >= 20 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">🎓</div>
                    <div class="badge-name">Scholar</div>
                    <div class="badge-desc">Complete 20 modules</div>
                </div>
                
                <!-- Assignment Badges -->
                <div class="achievement-badge <?php echo $submissionsCount >= 1 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">✏️</div>
                    <div class="badge-name">First Submit</div>
                    <div class="badge-desc">Submit 1 assignment</div>
                </div>
                
                <div class="achievement-badge <?php echo $submissionsCount >= 10 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">📝</div>
                    <div class="badge-name">Hard Worker</div>
                    <div class="badge-desc">Submit 10 assignments</div>
                </div>
                
                <!-- Progress Badges -->
                <div class="achievement-badge <?php echo $progressPercentage >= 50 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">🚀</div>
                    <div class="badge-name">Halfway There</div>
                    <div class="badge-desc">Reach 50% progress</div>
                </div>
                
                <div class="achievement-badge <?php echo $progressPercentage >= 100 ? 'unlocked' : 'locked'; ?>">
                    <div class="badge-icon">🏅</div>
                    <div class="badge-name">Champion</div>
                    <div class="badge-desc">Complete everything!</div>
                </div>
            </div>
        </div>
    </div>

    <!-- Recent Activity -->
    <div class="card cartoon-card mb-4">
        <div class="card-body">
            <h3 class="mb-4">📅 Recent Activity</h3>
            
            <?php if (empty($recentProgress) && empty($recentAttempts)): ?>
                <div class="empty-state">
                    <div class="empty-icon">📭</div>
                    <p>No activity yet. Start learning to see your progress here!</p>
                </div>
            <?php else: ?>
                <div class="activity-timeline">
                    <?php
                    // Combine and sort recent activities
                    $activities = [];
                    
                    foreach ($recentProgress as $progress) {
                        $activities[] = [
                            'type' => 'progress',
                            'date' => $progress['updated_at'],
                            'data' => $progress
                        ];
                    }
                    
                    foreach ($recentAttempts as $attempt) {
                        $activities[] = [
                            'type' => 'attempt',
                            'date' => $attempt['attempted_at'],
                            'data' => $attempt
                        ];
                    }
                    
                    usort($activities, function($a, $b) {
                        return strtotime($b['date']) - strtotime($a['date']);
                    });
                    
                    $activities = array_slice($activities, 0, 10);
                    ?>
                    
                    <?php foreach ($activities as $activity): ?>
                        <div class="activity-item">
                            <?php if ($activity['type'] === 'progress'): ?>
                                <?php $prog = $activity['data']; ?>
                                <div class="activity-icon" style="background: var(--kid-green);">
                                    <?php echo $prog['status'] === 'completed' ? '✓' : '⏳'; ?>
                                </div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        <?php echo $prog['status'] === 'completed' ? 'Completed' : 'Started'; ?>: 
                                        <?php echo htmlspecialchars($prog['material_name']); ?>
                                    </div>
                                    <?php if ($prog['stars_earned'] > 0): ?>
                                        <div class="activity-stars">
                                            <?php for ($i = 0; $i < $prog['stars_earned']; $i++): ?>
                                                ⭐
                                            <?php endfor; ?>
                                        </div>
                                    <?php endif; ?>
                                    <div class="activity-date">
                                        <?php echo date('F j, Y g:i A', strtotime($prog['updated_at'])); ?>
                                    </div>
                                </div>
                            <?php else: ?>
                                <?php $attempt = $activity['data']; ?>
                                <div class="activity-icon" style="background: var(--kid-blue);">🎯</div>
                                <div class="activity-content">
                                    <div class="activity-title">
                                        Attempted Activity: <?php echo htmlspecialchars($attempt['activity_name']); ?>
                                    </div>
                                    <div class="activity-score">
                                        Score: <?php echo $attempt['score']; ?>/<?php echo $attempt['total_points']; ?>
                                        (<?php echo round(($attempt['score'] / $attempt['total_points']) * 100); ?>%)
                                    </div>
                                    <div class="activity-date">
                                        <?php echo date('F j, Y g:i A', strtotime($attempt['attempted_at'])); ?>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<style>
.progress-stat-card {
    background: #fff;
    border-radius: 20px;
    padding: 20px;
    text-align: center;
    border: 4px solid;
    box-shadow: 6px 6px 0 rgba(0,0,0,0.1);
    transition: transform 0.3s ease;
}

.progress-stat-card:hover {
    transform: scale(1.05);
}

.stat-completed {
    border-color: var(--kid-green);
}

.stat-total {
    border-color: var(--kid-blue);
}

.stat-submissions {
    border-color: var(--kid-orange);
}

.large-progress-circle {
    position: relative;
    display: inline-block;
    margin: 20px 0;
}

.large-progress-text {
    position: absolute;
    top: 50%;
    left: 50%;
    transform: translate(-50%, -50%);
    font-size: 3rem;
    font-weight: bold;
    color: var(--kid-orange);
}

.achievements-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
    gap: 20px;
}

.achievement-badge {
    background: #f5f5f5;
    border-radius: 15px;
    padding: 20px;
    text-align: center;
    border: 3px solid #ddd;
    transition: all 0.3s ease;
}

.achievement-badge.unlocked {
    background: linear-gradient(135deg, var(--kid-yellow) 0%, var(--kid-orange) 100%);
    border-color: #333;
    box-shadow: 4px 4px 0 rgba(0,0,0,0.1);
    animation: pulse 2s infinite;
}

.achievement-badge.locked {
    opacity: 0.5;
    filter: grayscale(100%);
}

.badge-icon {
    font-size: 3rem;
    margin-bottom: 10px;
}

.badge-name {
    font-weight: bold;
    font-size: 1rem;
    margin-bottom: 5px;
}

.badge-desc {
    font-size: 0.8rem;
    color: #666;
}

.achievement-badge.unlocked .badge-desc {
    color: #333;
}

.activity-timeline {
    display: flex;
    flex-direction: column;
    gap: 15px;
}

.activity-item {
    display: flex;
    align-items: flex-start;
    background: #f9f9f9;
    border-radius: 15px;
    padding: 15px;
    border: 2px solid #e0e0e0;
    transition: transform 0.3s ease;
}

.activity-item:hover {
    transform: translateX(5px);
    border-color: var(--kid-blue);
}

.activity-icon {
    width: 50px;
    height: 50px;
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    color: #fff;
    margin-right: 15px;
    flex-shrink: 0;
}

.activity-content {
    flex: 1;
}

.activity-title {
    font-weight: bold;
    font-size: 1.1rem;
    margin-bottom: 5px;
}

.activity-stars {
    font-size: 1.2rem;
    margin-bottom: 5px;
}

.activity-score {
    color: var(--kid-blue);
    font-weight: bold;
    margin-bottom: 5px;
}

.activity-date {
    font-size: 0.9rem;
    color: #666;
}

@media (max-width: 768px) {
    .achievements-grid {
        grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
    }
    
    .achievement-badge {
        padding: 15px;
    }
    
    .badge-icon {
        font-size: 2rem;
    }
}
</style>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

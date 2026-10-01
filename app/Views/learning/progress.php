<?php
// Part of: SignED - Learner Progress View

$pageTitle = 'My Progress - SignED';
$basePath = defined('BASE_PATH') ? BASE_PATH : '';
require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . $basePath . '/css/learner.css">';

$pct = ($overallTotal > 0) ? round(($overallComplete / $overallTotal) * 100) : 0;
if ($pct === 0) {
    $progressMsg = 'Start a mission and your progress will appear here.';
} elseif ($pct < 50) {
    $progressMsg = 'You are building momentum. Keep going.';
} elseif ($pct < 100) {
    $progressMsg = 'Almost there. Finish the remaining missions.';
} else {
    $progressMsg = 'All assigned missions are complete.';
}

$ringRadius = 44;
$ringCircumference = round(2 * M_PI * $ringRadius, 2);
$ringDashOffset = round($ringCircumference * (1 - $pct / 100), 2);
$totalSubs = !empty($recentSubmissions) ? count($recentSubmissions) : count($recentGrades ?? []);
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-quest-page progress-journey-page compact-progress-page">
    <div class="progress-journey-shell">
        <div class="progress-page-head mb-4 d-flex justify-content-between align-items-center flex-wrap gap-2">
            <div>
                <div class="quest-eyebrow text-primary fw-semibold small text-uppercase">Achievement Journey</div>
                <h1 class="fw-semibold text-dark mb-0 fs-3">Your Learning Journey</h1>
            </div>
            <a href="<?php echo htmlspecialchars($basePath); ?>/learning/dashboard" class="btn btn-outline-secondary rounded-2 px-3 fw-semibold d-inline-flex align-items-center gap-1">
                <i class="ph-bold ph-arrow-left"></i> <span>Back to My Lessons</span>
            </a>
        </div>

        <section class="compact-stat-grid mb-4">
            <article class="compact-stat-card card border rounded-3 p-3 text-center bg-white shadow-sm">
                <span class="stat-icon mb-2 text-warning fs-3"><i class="ph-bold ph-star"></i></span>
                <strong class="fs-4 d-block text-dark"><?php echo number_format($totalStars ?? 0); ?></strong>
                <small class="text-muted">Total Stars</small>
            </article>
            <article class="compact-stat-card card border rounded-3 p-3 text-center bg-white shadow-sm">
                <span class="stat-icon mb-2 text-success fs-3"><i class="ph-bold ph-check-circle"></i></span>
                <strong class="fs-4 d-block text-dark"><?php echo (int)$overallComplete; ?></strong>
                <small class="text-muted">Completed Missions</small>
            </article>
            <article class="compact-stat-card card border rounded-3 p-3 text-center bg-white shadow-sm">
                <span class="stat-icon mb-2 text-primary fs-3"><i class="ph-bold ph-game-controller"></i></span>
                <strong class="fs-4 d-block text-dark"><?php echo (int)$overallTotal; ?></strong>
                <small class="text-muted">Total Challenges</small>
            </article>
            <article class="compact-stat-card card border rounded-3 p-3 text-center bg-white shadow-sm">
                <span class="stat-icon mb-2 text-secondary fs-3"><i class="ph-bold ph-book-open"></i></span>
                <strong class="fs-4 d-block text-dark"><?php echo (int)$totalSubs; ?></strong>
                <small class="text-muted">Mission Logs</small>
            </article>
        </section>

        <section class="journey-banner compact-journey-banner mb-4 p-4 rounded-3 text-white" style="background: var(--gradient-hero);">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                <div>
                    <span class="badge bg-white bg-opacity-25 rounded-pill px-2.5 py-1 mb-1" style="font-size: 0.75rem; font-weight: 500;">Keep Going</span>
                    <h2 class="fs-4 fw-semibold text-white mb-1"><?php echo $pct >= 100 ? 'All missions complete' : 'Your next mission is waiting'; ?></h2>
                    <p class="text-white-50 mb-0"><?php echo htmlspecialchars($progressMsg); ?></p>
                </div>
                <a href="<?php echo htmlspecialchars($basePath); ?>/learning/dashboard" class="btn btn-light text-dark fw-semibold rounded-2 px-3 py-2 d-inline-flex align-items-center gap-1">
                    <i class="ph-bold ph-play-circle text-primary"></i> <span>Next Activity</span>
                </a>
            </div>
        </section>

        <section class="overall-progress-card card border rounded-3 p-4 shadow-sm bg-white mb-4">
            <div class="d-flex align-items-center gap-3">
                <div class="overall-progress-icon text-primary p-3 bg-light rounded-3">
                    <i class="ph-bold ph-trend-up" style="font-size: 2.2rem;" aria-hidden="true"></i>
                </div>
                <div class="overall-progress-copy">
                    <div class="quest-eyebrow text-primary fw-semibold small text-uppercase">Mission Progress</div>
                    <h2 class="fw-semibold text-dark mb-1 fs-5">You have completed <?php echo (int)$overallComplete; ?> of <?php echo (int)$overallTotal; ?> learning missions!</h2>
                    <p class="congrats-text text-secondary mb-0"><?php echo htmlspecialchars($progressMsg); ?></p>
                </div>
            </div>
        </section>

        <?php if (!empty($domainProgress)): ?>
            <section class="compact-panel card border rounded-3 p-4 shadow-sm bg-white mb-4">
                <div class="compact-panel-head mb-3">
                    <div>
                        <div class="quest-eyebrow text-primary fw-semibold small text-uppercase">Skill Areas</div>
                        <h2 class="fw-semibold text-dark fs-5">Progress by Domain</h2>
                    </div>
                </div>
                <div class="domain-progress-list">
                    <?php foreach ($domainProgress as $domain):
                        $domainPct = ($domain['total'] > 0) ? round(($domain['completed'] / $domain['total']) * 100) : 0;
                        $avgScore = $domain['avg_score'] !== null ? round((float)$domain['avg_score'], 1) : null;
                    ?>
                        <article class="domain-progress-row d-flex align-items-center justify-content-between p-2 mb-2 border-bottom">
                            <div>
                                <strong class="fw-medium text-dark"><?php echo htmlspecialchars($domain['domain']); ?></strong>
                                <small class="text-muted d-block"><?php echo (int)$domain['completed']; ?> of <?php echo (int)$domain['total']; ?> missions done</small>
                            </div>
                            <div class="domain-progress-meter mx-3 flex-grow-1" style="max-width: 300px;">
                                <div class="progress" style="height: 6px; border-radius: 4px;">
                                    <div class="progress-bar bg-success" style="width:<?php echo $domainPct; ?>%"></div>
                                </div>
                            </div>
                            <b class="domain-status-label small">
                                <?php if ($domain['completed'] >= $domain['total'] && $domain['total'] > 0): ?>
                                    <span class="badge bg-success"><i class="ph-bold ph-check-circle me-1"></i> All Done!</span>
                                <?php else: ?>
                                    <span class="badge bg-light text-dark border"><i class="ph-bold ph-hourglass me-1"></i> Practicing</span>
                                <?php endif; ?>
                            </b>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="compact-panel card border rounded-3 p-4 shadow-sm bg-white">
            <div class="compact-panel-head mb-3">
                <div>
                    <div class="quest-eyebrow text-primary fw-semibold small text-uppercase">Completed Missions</div>
                    <h2 class="fw-semibold text-dark fs-5">Recent Activity</h2>
                </div>
            </div>

            <?php if (!empty($recentSubmissions)): ?>
                <div class="mission-log-list">
                    <?php foreach ($recentSubmissions as $log):
                        $score = $log['score'] !== null ? (int)$log['score'] : ($log['auto_score'] !== null ? (int)$log['auto_score'] : null);
                        $max = (int)($log['grade_max_score'] ?? $log['activity_max_score'] ?? 0);
                        $scorePct = ($score !== null && $max > 0) ? round(($score / $max) * 100) : null;
                        $stars = $scorePct === null ? 0 : ($scorePct >= 90 ? 3 : ($scorePct >= 70 ? 2 : 1));
                        $status = !empty($log['graded_at']) ? 'Graded' : 'Submitted';
                    ?>
                        <article class="mission-log-card d-flex align-items-center justify-content-between p-3 mb-2 rounded-2 border bg-light">
                            <div class="mission-log-main">
                                <strong class="text-dark d-block fw-semibold"><?php echo htmlspecialchars($log['activity_title']); ?></strong>
                                <small class="text-muted"><?php echo htmlspecialchars($log['lesson_plan_title']); ?></small>
                            </div>
                            <div class="mission-log-score text-center">
                                <?php if ($score !== null && $max > 0): ?>
                                    <b class="d-block text-dark"><?php echo $score; ?>/<?php echo $max; ?></b>
                                    <span class="mini-stars text-warning fs-6" aria-label="<?php echo $stars; ?> stars">
                                        <?php for ($star = 1; $star <= 3; $star++): ?>
                                            <i class="ph-bold ph-star <?php echo $star <= $stars ? 'text-warning' : 'text-muted opacity-25'; ?>"></i>
                                        <?php endfor; ?>
                                    </span>
                                <?php else: ?>
                                    <small class="text-muted">For review</small>
                                <?php endif; ?>
                            </div>
                            <span class="badge <?php echo $status === 'Graded' ? 'bg-success' : 'bg-primary'; ?>"><?php echo $status; ?></span>
                            <time class="text-muted small"><?php echo !empty($log['submitted_at']) ? date('M j, Y', strtotime($log['submitted_at'])) : 'No date'; ?></time>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="compact-empty-state text-center py-5">
                    <i class="ph-bold ph-tray text-muted" style="font-size: 3rem; display: block; margin-bottom: 12px;"></i>
                    <p class="text-muted">No mission logs yet. Complete an assigned activity and your progress will appear here.</p>
                    <a href="<?php echo htmlspecialchars($basePath); ?>/learning/dashboard" class="btn btn-primary rounded-2 px-3 fw-semibold">
                        <i class="ph-bold ph-play-circle me-1"></i> Go to My Lessons
                    </a>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

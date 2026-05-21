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
<body data-logged-in="true">

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-quest-page progress-journey-page compact-progress-page">
    <div class="progress-journey-shell">
        <div class="progress-page-head">
            <div>
                <div class="quest-eyebrow">Achievement Journey</div>
                <h1>Your Learning Journey</h1>
            </div>
            <a href="<?php echo htmlspecialchars($basePath); ?>/learning/dashboard" class="mission-back-link">
                <i class="bi bi-arrow-left"></i> Back to My Lessons
            </a>
        </div>

        <section class="compact-stat-grid">
            <article class="compact-stat-card">
                <span class="stat-icon"><i class="bi bi-star-fill"></i></span>
                <strong><?php echo number_format($totalStars ?? 0); ?></strong>
                <small>Total Stars</small>
            </article>
            <article class="compact-stat-card">
                <span class="stat-icon success"><i class="bi bi-check-circle-fill"></i></span>
                <strong><?php echo (int)$overallComplete; ?></strong>
                <small>Completed Missions</small>
            </article>
            <article class="compact-stat-card">
                <span class="stat-icon blue"><i class="bi bi-controller"></i></span>
                <strong><?php echo (int)$overallTotal; ?></strong>
                <small>Total Challenges</small>
            </article>
            <article class="compact-stat-card">
                <span class="stat-icon warm"><i class="bi bi-journal-check"></i></span>
                <strong><?php echo (int)$totalSubs; ?></strong>
                <small>Mission Logs</small>
            </article>
        </section>

        <section class="journey-banner compact-journey-banner">
            <div>
                <span class="quest-eyebrow">Keep Going</span>
                <h2><?php echo $pct >= 100 ? 'All missions complete' : 'Your next mission is waiting'; ?></h2>
                <p><?php echo htmlspecialchars($progressMsg); ?></p>
            </div>
            <a href="<?php echo htmlspecialchars($basePath); ?>/learning/dashboard" class="quest-primary-btn">
                <i class="bi bi-play-circle-fill"></i> Next Activity
            </a>
        </section>

        <section class="overall-progress-card">
            <div class="progress-ring-small">
                <svg width="118" height="118" viewBox="0 0 112 112" role="img" aria-label="<?php echo $pct; ?> percent complete">
                    <circle cx="56" cy="56" r="<?php echo $ringRadius; ?>" fill="none" stroke="#e6edf5" stroke-width="10"/>
                    <circle cx="56" cy="56" r="<?php echo $ringRadius; ?>" fill="none" stroke="#ef9f27" stroke-width="10"
                            stroke-dasharray="<?php echo $ringCircumference; ?>" stroke-dashoffset="<?php echo $ringDashOffset; ?>"
                            stroke-linecap="round" transform="rotate(-90 56 56)"/>
                    <text x="56" y="61" text-anchor="middle" font-size="20" font-weight="900" fill="#173765"><?php echo $pct; ?>%</text>
                </svg>
            </div>
            <div class="overall-progress-copy">
                <div class="quest-eyebrow">Overall Progress</div>
                <h2><?php echo (int)$overallComplete; ?> of <?php echo (int)$overallTotal; ?> missions completed</h2>
                <p><?php echo htmlspecialchars($progressMsg); ?></p>
                <div class="progress-mini-track" aria-hidden="true">
                    <span style="width:<?php echo $pct; ?>%"></span>
                </div>
            </div>
        </section>

        <?php if (!empty($domainProgress)): ?>
            <section class="compact-panel">
                <div class="compact-panel-head">
                    <div>
                        <div class="quest-eyebrow">Skill Areas</div>
                        <h2>Progress by Domain</h2>
                    </div>
                </div>
                <div class="domain-progress-list">
                    <?php foreach ($domainProgress as $domain):
                        $domainPct = ($domain['total'] > 0) ? round(($domain['completed'] / $domain['total']) * 100) : 0;
                        $avgScore = $domain['avg_score'] !== null ? round((float)$domain['avg_score'], 1) : null;
                    ?>
                        <article class="domain-progress-row">
                            <div>
                                <strong><?php echo htmlspecialchars($domain['domain']); ?></strong>
                                <small><?php echo (int)$domain['completed']; ?> / <?php echo (int)$domain['total']; ?> complete<?php echo $avgScore !== null ? ' - Avg ' . $avgScore . '%' : ''; ?></small>
                            </div>
                            <div class="domain-progress-meter">
                                <span style="width:<?php echo $domainPct; ?>%"></span>
                            </div>
                            <b><?php echo $domainPct; ?>%</b>
                        </article>
                    <?php endforeach; ?>
                </div>
            </section>
        <?php endif; ?>

        <section class="compact-panel">
            <div class="compact-panel-head">
                <div>
                    <div class="quest-eyebrow">Completed Missions</div>
                    <h2>Recent Activity</h2>
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
                        <article class="mission-log-card">
                            <div class="mission-log-main">
                                <strong><?php echo htmlspecialchars($log['activity_title']); ?></strong>
                                <small><?php echo htmlspecialchars($log['lesson_plan_title']); ?></small>
                            </div>
                            <div class="mission-log-score">
                                <?php if ($score !== null && $max > 0): ?>
                                    <b><?php echo $score; ?>/<?php echo $max; ?></b>
                                    <span class="mini-stars" aria-label="<?php echo $stars; ?> stars">
                                        <?php for ($star = 1; $star <= 3; $star++): ?><i class="bi bi-star-fill <?php echo $star <= $stars ? 'earned' : ''; ?>"></i><?php endfor; ?>
                                    </span>
                                <?php else: ?>
                                    <b>For review</b>
                                <?php endif; ?>
                            </div>
                            <span class="status-pill <?php echo $status === 'Graded' ? 'success' : ''; ?>"><?php echo $status; ?></span>
                            <time><?php echo !empty($log['submitted_at']) ? date('M j, Y', strtotime($log['submitted_at'])) : 'No date'; ?></time>
                        </article>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="compact-empty-state">
                    <i class="bi bi-journal-check"></i>
                    <p>No mission logs yet. Complete an assigned activity and your progress will appear here.</p>
                    <a href="<?php echo htmlspecialchars($basePath); ?>/learning/dashboard" class="quest-primary-btn">
                        <i class="bi bi-play-circle-fill"></i> Go to My Lessons
                    </a>
                </div>
            <?php endif; ?>
        </section>
    </div>
</div>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

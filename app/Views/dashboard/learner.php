<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-05-13
// Part of: SignED — Learner Dashboard (matching reference design)

$pageTitle = 'My Learning — SignED';
require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . (defined('BASE_PATH') ? BASE_PATH : '') . '/css/learner.css">';

$basePath   = defined('BASE_PATH') ? BASE_PATH : '';
$firstName  = explode(' ', trim($studentName ?? 'Learner'))[0];
$pct        = ($overallTotal > 0) ? round(($overallComplete / $overallTotal) * 100) : 0;
$missionTotal = 0;
$missionDone = 0;
$nextLesson = null;
foreach (($lessonPlans ?? []) as $lpForQuest) {
    $activityTotal = (int)($lpForQuest['activity_count'] ?? 0);
    $activityDone = (int)($lpForQuest['completed_count'] ?? 0);
    $missionTotal += $activityTotal;
    $missionDone += $activityDone;
    if ($nextLesson === null && ($activityTotal === 0 || $activityDone < $activityTotal)) {
        $nextLesson = $lpForQuest;
    }
}
if ($nextLesson === null && !empty($lessonPlans)) {
    $nextLesson = $lessonPlans[0];
}
$missionRemaining = max(0, $missionTotal - $missionDone);
$latestScoreLabel = isset($avgScore) && (float)$avgScore > 0 ? round((float)$avgScore, 1) . '%' : 'No score yet';

if ($pct === 0)     $progressMsg = "Keep going! You're doing great! 🎉";
elseif ($pct < 50)  $progressMsg = "You are doing great! Keep going! 💪";
elseif ($pct < 100) $progressMsg = "Almost there! You are doing amazing! 🌟";
else                $progressMsg = "You finished everything! You are a superstar! 🏆";

if (!isset($badges))             $badges             = [];
if (!isset($totalStarsPossible)) $totalStarsPossible = 0;
if (!isset($avgScore))           $avgScore           = 0;

// SVG ring
$r = 54; $circ = round(2 * M_PI * $r, 2);
$dashOff = round($circ * (1 - $pct / 100), 2);
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-quest-page">

<style>
/* ── Learner-specific additions — Process 7 ── */
.lrn-banner{background:linear-gradient(90deg,#ef9f27 0%,#f5bc50 100%);border-radius:16px;padding:20px 24px;display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;gap:16px}
.lrn-banner__left{display:flex;align-items:center;gap:14px}
.lrn-banner__emoji{font-size:2rem;flex-shrink:0}
.lrn-banner__text{font-size:1.2rem;font-weight:700;color:#fff;line-height:1.3}
.lrn-banner__stars{background:rgba(255,255,255,.25);border-radius:12px;padding:10px 18px;display:flex;align-items:center;gap:8px;flex-shrink:0;color:#fff;font-size:1rem;font-weight:700}
.lrn-banner__stars span{font-size:1.4rem}

.lrn-ring-card{background:#fff;border-radius:16px;border:2px solid #222;padding:24px 20px;margin-bottom:22px;text-align:center;box-shadow:0 2px 8px rgba(0,0,0,.06)}
.lrn-ring-card h3{font-size:1rem;font-weight:700;color:#2c2c2c;margin-bottom:16px;display:flex;align-items:center;justify-content:center;gap:6px}
.lrn-ring-msg{font-size:.93rem;color:#6c757d;margin-top:12px}

.lrn-stats{display:grid;grid-template-columns:repeat(3,1fr);gap:14px;margin-bottom:24px}
.lrn-stat{background:#fff;border-radius:16px;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:20px 14px;text-align:center;border:2px solid #ddd}
.lrn-stat--mod {border-color:#4a90d9}
.lrn-stat--done{border-color:#3b6d11}
.lrn-stat--star{border-color:#ef9f27}
.lrn-stat__ico{font-size:2rem;margin-bottom:8px;display:block}
.lrn-stat__num{font-size:2.2rem;font-weight:700;color:#222;line-height:1}
.lrn-stat__lbl{font-size:.85rem;color:#6c757d;margin-top:5px}

.lrn-tabs{display:flex;border-bottom:2px solid #e8e8e8;margin-bottom:20px}
.lrn-tab{background:none;border:none;cursor:pointer;padding:11px 20px;font-size:.92rem;font-weight:600;color:#999;border-bottom:3px solid transparent;margin-bottom:-2px;transition:color .15s,border-color .15s}
.lrn-tab.active{color:#a01422;border-bottom-color:#a01422}
.lrn-tab:hover:not(.active){color:#555}
.lrn-panel{display:none}
.lrn-panel.active{display:block}

.lrn-grid{display:grid;grid-template-columns:1fr 1fr;gap:14px}
.lrn-lcard{background:#fff;border-radius:16px;border-top:4px solid #bbb;box-shadow:0 2px 8px rgba(0,0,0,.07);padding:18px;display:flex;flex-direction:column}
.lrn-lcard--done{border-top-color:#3b6d11}
.lrn-lcard--prog{border-top-color:#ef9f27}
.lrn-domain{display:inline-block;padding:3px 10px;border-radius:20px;background:#1e4072;color:#fff;font-size:.7rem;font-weight:700;margin-bottom:8px}
.lrn-ltitle{font-size:.95rem;font-weight:600;color:#2c2c2c;margin-bottom:8px;flex:1}
.lrn-mini{height:8px;border-radius:4px;background:#e9e9e9;overflow:hidden;margin-bottom:4px}
.lrn-mini__f{height:100%;background:#a01422;border-radius:4px}
.lrn-status{display:inline-flex;align-items:center;gap:4px;padding:3px 10px;border-radius:12px;font-size:.72rem;font-weight:700;margin-bottom:10px}
.lrn-status--done{background:#e8f0e0;color:#3b6d11}
.lrn-status--prog{background:#faeeda;color:#854f0b}
.lrn-status--none{background:#f0f0f0;color:#666}
.lrn-btn{display:flex;align-items:center;justify-content:center;gap:8px;width:100%;min-height:52px;border-radius:20px;font-size:.92rem;font-weight:700;text-decoration:none;border:none;cursor:pointer;margin-top:auto}
.lrn-btn:hover{opacity:.88;text-decoration:none}
.lrn-btn--start,.lrn-btn--cont{background:#a01422;color:#fff}
.lrn-btn--done{background:#3b6d11;color:#fff}

.lrn-prog-grid{display:grid;grid-template-columns:1fr 1fr;gap:12px;margin:16px 0}
.lrn-prog-stat{background:#fff;border-radius:14px;padding:16px;box-shadow:0 2px 8px rgba(0,0,0,.07);text-align:center;border:2px solid #ddd}
.lrn-prog-stat--star{border-color:#222}
.lrn-prog-stat--done{border-color:#3b6d11}
.lrn-prog-stat--mat {border-color:#4a90d9}
.lrn-prog-stat--sub {border-color:#ef9f27}
.lrn-prog-stat .ico{font-size:1.6rem;display:block;margin-bottom:6px}
.lrn-prog-stat .val{font-size:1.8rem;font-weight:700;color:#222;line-height:1}
.lrn-prog-stat .lbl{font-size:.8rem;color:#6c757d;margin-top:4px}
.lrn-prog-msg{background:#e8f0e0;border-radius:14px;padding:16px;color:#3b6d11;font-size:.95rem;font-weight:600;text-align:center}

.lrn-shelf{display:flex;flex-wrap:wrap;gap:16px;padding:4px 0}
.lrn-badge{display:flex;flex-direction:column;align-items:center;width:88px;text-align:center}
.lrn-badge__circle{width:64px;height:64px;border-radius:50%;display:flex;align-items:center;justify-content:center;margin-bottom:6px}
.lrn-badge__circle i{font-size:1.6rem}
.lrn-badge--earned .lrn-badge__circle{background:#faeeda}
.lrn-badge--earned .lrn-badge__circle i{color:#854f0b}
.lrn-badge--locked{opacity:.38}
.lrn-badge--locked .lrn-badge__circle{background:#f0f0f0}
.lrn-badge--locked .lrn-badge__circle i{color:#bbb}
.lrn-badge__name{font-size:.75rem;font-weight:600;color:#2c2c2c;line-height:1.3}

@media(max-width:768px){
  .lrn-grid{grid-template-columns:1fr}
  .lrn-stats{grid-template-columns:repeat(3,1fr)}
  .lrn-banner__text{font-size:1rem}
  .lrn-banner__stars{padding:8px 12px}
}
@media(max-width:480px){
  .lrn-stats{grid-template-columns:1fr 1fr}
  .lrn-tab{padding:10px 12px;font-size:.84rem}
}
</style>

<!-- Welcome banner -->
<div class="lrn-banner">
  <div class="lrn-banner__left">
    <div class="lrn-banner__mascot">
      <?php 
      $mascotState = 'waving';
      require __DIR__ . '/../components/mascot.php'; 
      ?>
    </div>
    <div class="lrn-banner__text">
      <?php if (!empty($studentIdCode)): ?>
        <div class="small opacity-90 mb-1">Student ID: <?php echo htmlspecialchars(StudentDisplayHelper::formatStudentId($studentIdCode)); ?></div>
      <?php else: ?>
        <?php /* $studentIdCode from controller when available */ ?>
      <?php endif; ?>
      <?php if ($pct === 100): ?>
        <h2>Amazing, <?php echo htmlspecialchars($firstName); ?>!</h2>
        <p>You finished everything! You are a superstar! 🏆</p>
      <?php else: ?>
        <h2>Hi <?php echo htmlspecialchars($firstName); ?>!</h2>
        <p>Your learning adventure is waiting! Let's learn something awesome! 🌟</p>
      <?php endif; ?>
    </div>
  </div>
  <div class="lrn-banner__stars">
    <span class="star-animation">⭐</span>
    <div class="star-details">
      <strong><?php echo number_format($totalStars ?? 0); ?></strong>
      <span>Stars Earned</span>
    </div>
  </div>
</div>

<?php if (!empty($nextLesson)): ?>
<section class="quest-continue hero-quest-card" aria-labelledby="continueQuestTitle">
  <div class="quest-continue__content">
    <span class="quest-eyebrow">🚀 Your Next Adventure</span>
    <h2 id="continueQuestTitle"><?php echo htmlspecialchars($nextLesson['title'] ?? 'Next mission'); ?></h2>
    <p class="quest-desc">
      <?php 
      if ($missionRemaining > 0) {
          echo 'You have <strong>' . $missionRemaining . ' challenge' . ($missionRemaining === 1 ? '' : 's') . '</strong> left in this mission. Let\'s do it! 💪';
      } else {
          echo 'You\'ve finished this mission! Tap below to review your achievement and keep practicing. 🌟';
      }
      ?>
    </p>
    <a href="<?php echo $basePath; ?>/learning/lesson/<?php echo (int)$nextLesson['id']; ?>" class="quest-primary-btn btn-hero-start">
      <i class="bi bi-rocket-takeoff-fill"></i>
      <span><?php echo $pct > 0 ? 'Continue Mission' : 'Start Mission'; ?></span>
    </a>
  </div>
  <div class="quest-continue__mascot">
    <?php 
    $mascotState = 'pointing';
    require __DIR__ . '/../components/mascot.php'; 
    ?>
  </div>
</section>
<?php endif; ?>

<!-- Stat cards -->
<div class="lrn-stats">
  <div class="lrn-stat lrn-stat--mod card-cartoonish-stat">
    <div class="stat-icon-wrapper mod-theme">📚</div>
    <div class="stat-content">
      <div class="lrn-stat__num"><?php echo count($lessonPlans ?? []); ?></div>
      <div class="lrn-stat__lbl">Lessons Assigned</div>
    </div>
  </div>
  <div class="lrn-stat lrn-stat--done card-cartoonish-stat">
    <div class="stat-icon-wrapper done-theme">🏆</div>
    <div class="stat-content">
      <div class="lrn-stat__num"><?php echo (int)$overallComplete; ?></div>
      <div class="lrn-stat__lbl">Missions Completed</div>
    </div>
  </div>
  <div class="lrn-stat lrn-stat--star card-cartoonish-stat">
    <div class="stat-icon-wrapper star-theme">🌟</div>
    <div class="stat-content">
      <div class="lrn-stat__num" style="font-size:1.3rem;"><?php echo htmlspecialchars($latestScoreLabel); ?></div>
      <div class="lrn-stat__lbl">Latest Power</div>
    </div>
  </div>
</div>

<?php
$activeTab = isset($_GET['tab']) && $_GET['tab'] === 'badges' ? 'badges' : 'lessons';
?>
<!-- Tabs -->
<div class="lrn-tabs" role="tablist">
  <button id="tab-lessons"  class="lrn-tab <?php echo $activeTab === 'lessons' ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'lessons' ? 'true' : 'false'; ?>"  onclick="lTab('lessons')"><i class="bi bi-book-open-fill"></i> My Lessons</button>
  <button id="tab-badges"   class="lrn-tab <?php echo $activeTab === 'badges' ? 'active' : ''; ?>" role="tab" aria-selected="<?php echo $activeTab === 'badges' ? 'true' : 'false'; ?>" onclick="lTab('badges')"><i class="bi bi-patch-check-fill"></i> My Badges</button>
</div>

<!-- TAB 1: MY LESSONS -->
<div id="panel-lessons" class="lrn-panel <?php echo $activeTab === 'lessons' ? 'active' : ''; ?>" role="tabpanel">
<?php if (empty($lessonPlans)): ?>
  <div class="text-center py-5 text-muted empty-state-friendly">
    <span style="font-size:4rem;display:block;margin-bottom:12px;">🌟</span>
    <h3>No lessons assigned yet!</h3>
    <p>Your awesome learning quests will show up here as soon as your teacher adds them. Stay tuned! 😊</p>
  </div>
<?php else: ?>
<div class="lrn-grid quest-grid">
<?php foreach ($lessonPlans as $lp):
  $at=(int)($lp['activity_count']??0); $ad=(int)($lp['completed_count']??0);
  $lp_pct=($at>0)?round(($ad/$at)*100):0;
  if($ad>=$at&&$at>0){$cm='done';$sm='done';$si='bi-check-circle-fill';$st='Completed!';   $bm='done';$bt='View lesson';}
  elseif($ad>0)       {$cm='prog';$sm='prog';$si='bi-clock-fill';      $st='In Progress';$bm='cont';$bt='Continue';}
  else                {$cm='';   $sm='none';$si='bi-circle';           $st='Not Started';$bm='start';$bt='Start';}
?>
<div class="lrn-lcard quest-mission-card<?php echo $cm?' lrn-lcard--'.$cm:''; ?>">
  <span class="lrn-domain"><?php echo htmlspecialchars($lp['pdsp_domain']); ?></span>
  <div class="lrn-ltitle"><?php echo htmlspecialchars($lp['title']); ?></div>
  
  <!-- Friendly star/step progress representation -->
  <div class="stars-progress-bar">
    <?php
    $maxStarsToShow = 5;
    $completedStars = $at > 0 ? round(($ad / $at) * $maxStarsToShow) : 0;
    for ($starIdx = 1; $starIdx <= $maxStarsToShow; $starIdx++) {
        if ($starIdx <= $completedStars) {
            echo '<i class="bi bi-star-fill star-earned"></i>';
        } else {
            echo '<i class="bi bi-star star-unearned"></i>';
        }
    }
    ?>
    <span class="stars-text"><?php echo $ad; ?> / <?php echo $at; ?> Done</span>
  </div>

  <div class="quest-mission-meta">
    <span><i class="bi bi-controller"></i> <?php echo $at; ?> Challenge<?php echo $at === 1 ? '' : 's'; ?></span>
    <span><i class="bi bi-flag-fill"></i> <?php echo $lp_pct; ?>% Complete</span>
  </div>

  <span class="lrn-status lrn-status--<?php echo $sm; ?>">
    <i class="bi <?php echo $si; ?>"></i><?php echo $st; ?>
  </span>
  <a id="btn-lesson-<?php echo (int)$lp['id']; ?>"
     href="<?php echo $basePath; ?>/learning/lesson/<?php echo (int)$lp['id']; ?>"
     class="lrn-btn lrn-btn--<?php echo $bm; ?>">
    <i class="bi bi-rocket-takeoff-fill"></i><?php echo htmlspecialchars($bt === 'View lesson' ? 'Review Mission' : $bt . ' Mission'); ?>
  </a>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- TAB 3: MY BADGES -->
<div id="panel-badges" class="lrn-panel <?php echo $activeTab === 'badges' ? 'active' : ''; ?>" role="tabpanel">
  <?php $ec = count(array_filter($badges, fn($b) => $b['earned'])); ?>
  <div class="badge-status-banner">
    🏅 You have earned <strong style="color:var(--kid-orange); font-size: 1.3rem;"><?php echo $ec; ?></strong> of
    <strong><?php echo count($badges); ?></strong> collectible badges! Keep going superstar!
  </div>
  <?php if (empty($badges)): ?>
    <div class="text-center py-4 text-muted empty-state-friendly">
      <span style="font-size:4rem;display:block;margin-bottom:10px;">🏅</span>
      <h3>No badges yet!</h3>
      <p>Complete your first activity mission to unlock a shiny badge! 🌟</p>
    </div>
  <?php else: ?>
  <div class="lrn-shelf">
  <?php foreach ($badges as $b): ?>
    <div class="lrn-badge lrn-badge--<?php echo $b['earned'] ? 'earned' : 'locked'; ?>"
         title="<?php echo $b['earned'] ? htmlspecialchars($b['name']) : 'Locked badge - complete missions to unlock!'; ?>">
      <div class="lrn-badge__circle"><i class="ti <?php echo htmlspecialchars($b['icon']); ?>"></i></div>
      <div class="lrn-badge__name"><?php echo htmlspecialchars($b['name']); ?></div>
    </div>
  <?php endforeach; ?>
  </div>
  <?php endif; ?>
</div>

</div><!-- /.main-content -->

<?php if (isset($_SESSION['success'])): ?>
<script>Swal?.fire({icon:'success',title:'',text:'<?php echo addslashes($_SESSION['success']); ?>',confirmButtonColor:'#3b6d11',timer:3000,timerProgressBar:true});</script>
<?php unset($_SESSION['success']); endif; ?>

<script>
function lTab(name) {
  document.querySelectorAll('.lrn-tab').forEach(function(t) {
    var on = t.id === 'tab-' + name;
    t.classList.toggle('active', on);
    t.setAttribute('aria-selected', on);
  });
  document.querySelectorAll('.lrn-panel').forEach(function(p) {
    p.classList.toggle('active', p.id === 'panel-' + name);
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

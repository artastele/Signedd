<?php
// DO NOT ALTER WITHOUT APPROVAL — Process 7
// Last modified: 2026-09-11
// Part of: SignED — Kid & SPED-Friendly Learner Dashboard

$pageTitle = 'Aking Aralin — SignED SPED LMS';
require_once __DIR__ . '/../layouts/header.php';
echo '<link rel="stylesheet" href="' . (defined('BASE_PATH') ? BASE_PATH : '') . '/css/learner.css">';

$basePath   = defined('BASE_PATH') ? BASE_PATH : '';
$firstName  = explode(' ', trim($studentName ?? 'Mag-aaral'))[0];
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
$latestScoreLabel = isset($avgScore) && (float)$avgScore > 0 ? round((float)$avgScore, 1) . '%' : 'Wala pa';

if ($pct === 0)     $progressMsg = "Simulan natin ang pag-aaral! Kaya mo 'yan!";
elseif ($pct < 50)  $progressMsg = "Napakagaling! Ipagpatuloy ang pag-aaral!";
elseif ($pct < 100) $progressMsg = "Kaunti na lang! Malapit mo nang matapos ang lahat!";
else                $progressMsg = "Binabati kita! Natapos mo ang lahat ng aralin!";

if (!isset($badges))             $badges             = [];
if (!isset($totalStarsPossible)) $totalStarsPossible = 0;
if (!isset($avgScore))           $avgScore           = 0;
?>

<?php require_once __DIR__ . '/../layouts/sidebar.php'; ?>
<?php require_once __DIR__ . '/../layouts/topbar.php'; ?>

<div class="main-content learner-quest-page p-3 p-md-4" style="background-color: #f8fafc; min-height: 100vh;">

<style>
/* Page-Specific Dashboard Enhancements */
.kid-stat-icon-blue { background: var(--kid-blue-light); color: var(--kid-blue); }
.kid-stat-icon-green { background: var(--kid-green-light); color: var(--kid-green); }
.kid-stat-icon-gold { background: var(--kid-amber-light); color: var(--kid-amber); }
</style>

<!-- 1. Welcome Banner -->
<div class="kid-banner mb-4">
  <div class="kid-banner__left">
    <div class="kid-banner__icon" aria-hidden="true">
      <i class="ph-bold ph-hand-waving"></i>
    </div>
    <div class="kid-banner__text">
      <h2 class="mb-1">Magandang araw, <?php echo htmlspecialchars($firstName); ?>!</h2>
      <p class="mb-0"><?php echo $progressMsg; ?></p>
    </div>
  </div>
  <div class="kid-banner__stars">
    <i class="ph-bold ph-star star-icon text-warning" aria-hidden="true"></i>
    <div>
      <div class="star-count"><?php echo number_format($totalStars ?? 0); ?></div>
      <div class="star-lbl">Aking mga Bituin</div>
    </div>
  </div>
</div>

<?php
$activeTab = isset($_GET['tab']) && $_GET['tab'] === 'badges' ? 'badges' : 'lessons';
?>

<!-- TAB 1: ARALIN AT KWENTO (Direktang pinapakita) -->
<div id="panel-lessons" class="kid-panel <?php echo $activeTab === 'lessons' ? 'active' : ''; ?>" role="tabpanel">

  <?php if (empty($lessonPlans)): ?>
    <div class="text-center py-5 bg-white rounded-4 border p-4 shadow-sm">
      <i class="ph-bold ph-books text-muted" style="font-size: 3.5rem; display: block; margin-bottom: 12px;" aria-hidden="true"></i>
      <h4 class="fw-bold text-dark">Wala pang nakatalagang aralin</h4>
      <p class="text-muted small mb-0">Makikita mo rito ang iyong mga aralin at kwento kapag inihanda na ng iyong guro.</p>
    </div>
  <?php else: ?>
    <div class="kid-course-grid" id="kidLessonContainer">
      <?php foreach ($lessonPlans as $lp):
        $domainKey = strtolower(trim($lp['pdsp_domain'] ?? 'default'));
        
        $domainFriendlyNames = [
          'daily_living_skills'    => 'Pang-araw-araw na Gawain',
          'perceptuo_cognitive'    => 'Isip at Dunong',
          'socio_emotional'        => 'Pakikipagkaibigan',
          'psychosocial'           => 'Pangkaisipan at Lipunan',
          'communication_language' => 'Senyas at Wika (FSL)',
          'psychomotor'            => 'Galaw at Liksi',
          'default'                => 'Aralin sa SPED'
        ];
        $domainLabel = $domainFriendlyNames[$domainKey] ?? ucwords(str_replace('_', ' ', $domainKey));

        $at = (int)($lp['activity_count'] ?? 0);
        $ad = (int)($lp['completed_count'] ?? 0);
        $matCount = (int)($lp['material_count'] ?? 0);
        $slideCount = (int)($lp['page_count'] ?? 0);
        $progressPct = (int)($lp['progress_pct'] ?? 0);
        
        $barColor = ($progressPct === 100) ? 'var(--signed-green)' : (($progressPct > 0) ? 'var(--signed-navy)' : '#cbd5e1');
        $statusText = ($progressPct === 100) ? '100% Natapos Na' : ($progressPct > 0 ? $progressPct . '% Nasimulan Na' : 'Bago! Simulan Na');

        $headerBgClass = 'bg-domain-' . $domainKey;
      ?>
      <div class="kid-card kid-lesson-card" 
           data-title="<?php echo htmlspecialchars(strtolower($lp['title'])); ?>" 
           data-domain="<?php echo htmlspecialchars($domainKey); ?>">
        
        <!-- Header Banner with Domain Pill -->
        <div class="kid-card-header <?php echo $headerBgClass; ?>">
          <div class="d-flex justify-content-between align-items-center">
            <span class="kid-card-tag"><?php echo htmlspecialchars($domainLabel); ?></span>
            <?php if ($progressPct === 100): ?>
              <span class="badge bg-success rounded-pill px-2.5 py-1" style="font-size: 0.72rem;"><i class="ph-bold ph-check-circle me-1" aria-hidden="true"></i> Natapos</span>
            <?php elseif ($progressPct > 0): ?>
              <span class="badge bg-warning text-dark rounded-pill px-2.5 py-1" style="font-size: 0.72rem;"><i class="ph-bold ph-hourglass me-1" aria-hidden="true"></i> Ipinagpapatuloy</span>
            <?php else: ?>
              <span class="badge bg-white text-dark rounded-pill px-2.5 py-1" style="font-size: 0.72rem;"><i class="ph-bold ph-sparkle me-1" aria-hidden="true"></i> Bagong Aralin</span>
            <?php endif; ?>
          </div>
          <div class="text-white opacity-75" style="font-size: 1.4rem;">
            <i class="ph-bold ph-book-open" aria-hidden="true"></i>
          </div>
        </div>

        <!-- Body -->
        <div class="kid-card-body">
          <h4 class="kid-card-title" title="<?php echo htmlspecialchars($lp['title']); ?>">
            <?php echo htmlspecialchars($lp['title']); ?>
          </h4>

          <div class="kid-card-badges">
            <?php if ($slideCount > 0): ?>
              <span class="kid-card-badge"><i class="ph-bold ph-book-open me-1" aria-hidden="true"></i> <?php echo $slideCount; ?> Pahina</span>
            <?php endif; ?>
            <?php if ($at > 0): ?>
              <span class="kid-card-badge"><i class="ph-bold ph-game-controller me-1" aria-hidden="true"></i> <?php echo $at; ?> Laro</span>
            <?php endif; ?>
            <span class="kid-card-badge fsl-badge"><i class="ph-bold ph-hand-waving me-1" aria-hidden="true"></i> May FSL Video</span>
          </div>

          <div class="mt-auto">
            <div class="kid-progress-bar">
              <div class="kid-progress-fill" style="width: <?php echo $progressPct; ?>%; background: <?php echo $barColor; ?>;"></div>
            </div>
            <div class="kid-progress-text mb-3">
              <span><?php echo $statusText; ?></span>
              <span class="text-muted small"><?php echo $ad; ?>/<?php echo $at; ?> gawain</span>
            </div>

            <a href="<?php echo $basePath; ?>/learning/lesson/<?php echo (int)$lp['id']; ?>" class="btn btn-bubble w-100">
              <span><?php echo $progressPct === 100 ? 'Balikan ang Kwento' : ($progressPct > 0 ? 'Ipagpatuloy ang Aralin' : 'Buksan ang Kwento'); ?></span>
              <i class="ph-bold <?php echo $progressPct === 100 ? 'ph-arrow-counter-clockwise' : 'ph-arrow-right'; ?> ms-1" aria-hidden="true"></i>
            </a>
          </div>
        </div>
      </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

<!-- TAB 2: MGA BADGE AT BITUIN -->
<div id="panel-badges" class="kid-panel <?php echo $activeTab === 'badges' ? 'active' : ''; ?>" role="tabpanel">
  <?php $ec = count(array_filter($badges ?? [], fn($b) => $b['earned'])); ?>
  <div class="p-4 bg-white border rounded-4 shadow-sm mb-4 text-center">
    <i class="ph-bold ph-trophy text-warning" style="font-size: 3rem; display: block; margin-bottom: 8px;" aria-hidden="true"></i>
    <h4 class="fw-bold text-dark mt-2">Aking mga Nakuhang Bituin at Badge</h4>
    <p class="text-muted mb-0">
      Nakahakot ka na ng <strong class="text-warning fs-5"><?php echo $ec; ?></strong> sa kabuuang 
      <strong><?php echo count($badges ?? []); ?></strong> collectible badges!
    </p>
  </div>

  <?php if (empty($badges)): ?>
    <div class="text-center py-5 bg-white border rounded-4 p-4 shadow-sm">
      <i class="ph-bold ph-star text-muted" style="font-size: 3.5rem; display: block; margin-bottom: 10px;" aria-hidden="true"></i>
      <h5 class="fw-bold text-dark">Wala pang nakabukas na badge</h5>
      <p class="small text-muted mb-0">Tapusin ang mga pahina ng kwento at laro upang manalo ng makikinang na badge!</p>
    </div>
  <?php else: ?>
    <div class="kid-badge-shelf bg-white p-4 border rounded-4 shadow-sm justify-content-center">
      <?php foreach ($badges as $b): ?>
        <div class="kid-badge-item kid-badge-<?php echo $b['earned'] ? 'earned' : 'locked'; ?>"
             title="<?php echo $b['earned'] ? htmlspecialchars($b['name']) : 'Naka-lock pa — tapusin ang aralin upang mabuksan!'; ?>">
          <div class="kid-badge-circle" aria-label="<?php echo $b['earned'] ? 'Bituin na Natamo: ' . htmlspecialchars($b['name']) : 'Naka-lock na Bituin'; ?>">
            <?php if ($b['earned']): ?>
              <i class="ph-bold ph-star" aria-hidden="true"></i>
            <?php else: ?>
              <i class="ph-bold ph-lock-simple" aria-hidden="true"></i>
            <?php endif; ?>
          </div>
          <div class="kid-badge-name"><?php echo htmlspecialchars($b['name']); ?></div>
        </div>
      <?php endforeach; ?>
    </div>
  <?php endif; ?>
</div>

</div><!-- /.main-content -->

<script>
function kTab(name) {
  document.querySelectorAll('.kid-tab').forEach(function(t) {
    var on = t.id === 'tab-' + name;
    t.classList.toggle('active', on);
    t.setAttribute('aria-selected', on);
  });
  document.querySelectorAll('.kid-panel').forEach(function(p) {
    p.classList.toggle('active', p.id === 'panel-' + name);
  });
}

let activeDomain = 'all';

function filterDomain(domain, el) {
  activeDomain = domain.toLowerCase();
  document.querySelectorAll('.kid-chip').forEach(c => c.classList.remove('active'));
  if (el) el.classList.add('active');
  applyFilters();
}

function filterLessonsSearch() {
  applyFilters();
}

function applyFilters() {
  const searchInput = document.getElementById('kidSearchInput');
  const search = searchInput ? searchInput.value.toLowerCase().trim() : '';
  const cards = document.querySelectorAll('.kid-lesson-card');

  cards.forEach(card => {
    const cardDomain = (card.getAttribute('data-domain') || '').toLowerCase();
    const cardTitle = (card.getAttribute('data-title') || '').toLowerCase();

    const matchesDomain = (activeDomain === 'all' || cardDomain === activeDomain);
    const matchesSearch = (!search || cardTitle.includes(search) || cardDomain.includes(search));

    if (matchesDomain && matchesSearch) {
      card.style.display = 'flex';
    } else {
      card.style.display = 'none';
    }
  });
}
</script>

<?php require_once __DIR__ . '/../layouts/footer.php'; ?>

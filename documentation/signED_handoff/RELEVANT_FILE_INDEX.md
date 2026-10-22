# SignED — Relevant File Index

**Document Version:** 1.0.0  
**Audit Date:** October 10, 2026  
**Auditor:** DeepMind Antigravity AI  
**Scope:** Complete inventory of source files powering the Resource Hub and Offline Learning subsystems.

---

## 1. Resource Hub Files

### Controllers & Business Logic
- [`app/Controllers/IEPImplementationController.php`](file:///c:/laragon/www/Signedd/app/Controllers/IEPImplementationController.php)
  - `resourceHub()` (lines 2112–2141): Hub home/browse action.
  - `previewResourcePack($id)` (lines 2146–2185): Slide preview action.
  - `shareToResourceHub($id)` (lines 2191–2249): Lesson publishing action.
  - `cloneFromResourceHub($id)` (lines 2255–2313): Pack adaptation/cloning action.
  - `unshareFromResourceHub($id)` (lines 2319–2334): Pack archiving action.
  - `recordMasteryResult($id)` (lines 2344–2400): Manual mainstream mastery logging.
  - `getMasteryResult($id, $studentId)` (lines 2402–2420): Mainstream mastery retrieval.

### Data Models
- [`app/Models/ResourceHubModel.php`](file:///c:/laragon/www/Signedd/app/Models/ResourceHubModel.php)
  - Core model managing `resource_hub_packs`, `resource_hub_pages`, and `resource_hub_activities`.
  - Methods: `getAllPacks()`, `getPackById()`, `getPackPages()`, `getPackActivities()`, `countFslSignsInHtml()`, `shareLessonToHub()`, `clonePackToIep()`, `unsharePack()`, `getManageableIepsForTeacher()`.
- [`app/Models/LessonPlanModel.php`](file:///c:/laragon/www/Signedd/app/Models/LessonPlanModel.php)
  - Interfaced by `ResourceHubModel` to read source lesson plans and create adapted lesson instances.
- [`app/Models/ExternalMasteryModel.php`](file:///c:/laragon/www/Signedd/app/Models/ExternalMasteryModel.php)
  - Manages `external_mastery_results` table for linking mainstream practice evidence to adapted lessons.

### Helpers & Security
- [`app/Helpers/HtmlSanitizer.php`](file:///c:/laragon/www/Signedd/app/Helpers/HtmlSanitizer.php)
  - Method `cleanLessonHtml()`: Sanitizes HTML slide content while safely preserving `.fsl-word` spans, data attributes, tables, and instructional media tags.

### Views & Templates
- [`app/Views/iep_implementation/resource_hub.php`](file:///c:/laragon/www/Signedd/app/Views/iep_implementation/resource_hub.php)
  - Main teacher interface: Browse Resources tab, My Shared Packs tab, search/filter form, template cards, Use-as-Template modal.
- [`app/Views/iep_implementation/resource_hub_preview.php`](file:///c:/laragon/www/Signedd/app/Views/iep_implementation/resource_hub_preview.php)
  - Read-only slide deck canvas, slide navigator list, interactive FSL sign popups, formative activity templates view, Use-as-Template modal.
- [`app/Views/iep_implementation/workspace.php`](file:///c:/laragon/www/Signedd/app/Views/iep_implementation/workspace.php)
  - Lines 353–358: "Share to Hub" button on lesson plan cards.
  - Lines 4778–4850: JavaScript function `openShareToHubFromWorkspace()` and `submitWorkspaceShareToHub()`.
  - Lines 4854–4950: Bootstrap modal `#shareToHubWorkspaceModal`.
- [`app/Views/iep_implementation/lesson_slide_builder.php`](file:///c:/laragon/www/Signedd/app/Views/iep_implementation/lesson_slide_builder.php)
  - Lines 318–320: "Share to Hub" topbar button.
  - Lines 3100–3149: Bootstrap modal `#shareToHubModal`.
  - Lines 3152–3205: JavaScript handler `submitShareToHub()`.

---

## 2. Offline Learning Files

### Service Worker & Client Managers
- [`public/sw.js`](file:///c:/laragon/www/Signedd/public/sw.js)
  - Service worker script; handles `CACHE_OFFLINE_LESSON`, `DELETE_OFFLINE_LESSON`, fetch event cache interceptor, and navigation fallback to `offline-reader.html`.
- [`public/js/offline-manager.js`](file:///c:/laragon/www/Signedd/public/js/offline-manager.js)
  - Client-side manager; registers `sw.js`, monitors online/offline events, triggers manifest downloads, caches metadata in `localStorage`, and updates UI badges.

### Standalone Offline Reader
- [`public/offline-reader.html`](file:///c:/laragon/www/Signedd/public/offline-reader.html)
  - Self-contained offline HTML5 reader application; renders slides, images, guide questions, and FSL sign demo videos from local browser storage.

### Controllers & Endpoints
- [`app/Controllers/LearningController.php`](file:///c:/laragon/www/Signedd/app/Controllers/LearningController.php)
  - `offlineLessonManifest(string $lessonPlanId)` (lines 247–494): Scans lesson pages, images, and FSL vocabulary to generate JSON asset manifest.
  - `lessonView(string $lessonPlanId)` (lines 200–242): Main online lesson viewer.

### Views & Integration
- [`app/Views/learning/lesson_view.php`](file:///c:/laragon/www/Signedd/app/Views/learning/lesson_view.php)
  - Line 94: `.signed-network-indicator` network status badge.
  - Line 98: `#offlineLessonWidget` container for download/remove button.
  - Line 447 & 1248: External mastery offline warning indicator.
- [`app/Views/dashboard/learner.php`](file:///c:/laragon/www/Signedd/app/Views/dashboard/learner.php)
  - Line 153: `.offline-badge-slot` on assigned lesson cards to show "Available Offline" badge.
- [`app/Views/layouts/header.php`](file:///c:/laragon/www/Signedd/app/Views/layouts/header.php)
  - Lines 38–40: Conditionally loads [`public/js/offline-manager.js`](file:///c:/laragon/www/Signedd/public/js/offline-manager.js) for authenticated sessions.

---

## 3. Shared Dependencies & Media Handling

- [`public/js/fsl-modal.js`](file:///c:/laragon/www/Signedd/public/js/fsl-modal.js)
  - Shared modal video player used for interactive sign demonstration across online viewer, offline reader, and Resource Hub preview.
- [`app/Models/FSLModel.php`](file:///c:/laragon/www/Signedd/app/Models/FSLModel.php)
  - Provides sign vocabulary queries used by `offlineLessonManifest()` to match sign videos with lesson narrative text.
- [`public/css/custom.css`](file:///c:/laragon/www/Signedd/public/css/custom.css)
  - Core design styles cached for offline use and applied across all views.
- [`public/css/learner.css`](file:///c:/laragon/www/Signedd/public/css/learner.css)
  - Dedicated student interface styling.
- [`public/css/phosphor/style.css`](file:///c:/laragon/www/Signedd/public/css/phosphor/style.css) & fonts
  - Self-hosted Phosphor Icons (zero-CDN dependency; cached offline).
- [`public/css/poppins.css`](file:///c:/laragon/www/Signedd/public/css/poppins.css) & fonts
  - Self-hosted Poppins font files (zero-CDN dependency; cached offline).

---

## 4. Database & Migration Files

- [`config/v67_resource_hub_packs.sql`](file:///c:/laragon/www/Signedd/config/v67_resource_hub_packs.sql)
  - Creates `resource_hub_packs`, `resource_hub_pages`, `resource_hub_activities`, and adds `lesson_plans.cloned_from_pack_id`.
- [`config/v68_external_mastery.sql`](file:///c:/laragon/www/Signedd/config/v68_external_mastery.sql)
  - Creates `external_mastery_results` and adds `lesson_plans.mainstream_mastery_url` and `lesson_plans.mainstream_mastery_title`.
- [`config/db.php`](file:///c:/laragon/www/Signedd/config/db.php)
  - Database connection singleton (`Database::getInstance()->getConnection()`).
- [`config/SchemaManager.php`](file:///c:/laragon/www/Signedd/config/SchemaManager.php)
  - Automated schema migration runner.

---

## 5. Routes

- [`routes/web.php`](file:///c:/laragon/www/Signedd/routes/web.php)
  - Lines 211–217: Resource Hub routes (`GET /iep/implementation/resource-hub`, `GET /teacher/resource-hub`, `GET /iep/implementation/resource-hub/preview/{id}`, `GET /teacher/resource-hub/preview/{id}`, `POST /iep/implementation/resource-hub/share/{id}`, `POST /iep/implementation/resource-hub/clone/{id}`, `POST /iep/implementation/resource-hub/unshare/{id}`).
  - Lines 220–222: Mainstream mastery routes (`POST .../mastery-result`, `GET .../mastery-result/{student_id}`, `POST .../mastery-resource`).
  - Line 233: Offline manifest route (`GET /learning/lesson/{id}/offline-manifest`).
  - Line 234: Progress saving route (`POST /learning/lesson/{id}/progress`).
  - Line 236: Activity submission route (`POST /learning/activity/{id}/submit`).

---

## 6. Frontend / UI Assets

- [`public/index.php`](file:///c:/laragon/www/Signedd/public/index.php): Front controller, path resolution, route dispatcher.
- [`public/offline-reader.html`](file:///c:/laragon/www/Signedd/public/offline-reader.html): Standalone offline reader.
- [`public/images/logo-large.png`](file:///c:/laragon/www/Signedd/public/images/logo-large.png): Brand logo cached for offline mode.
- [`public/images/fsl/default_sign.svg`](file:///c:/laragon/www/Signedd/public/images/fsl/default_sign.svg): Fallback sign icon cached for offline mode.

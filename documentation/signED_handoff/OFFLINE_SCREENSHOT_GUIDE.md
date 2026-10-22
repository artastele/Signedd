# SignED — Offline Learning Screenshot Capture Guide

**Document Version:** 1.0.0  
**Audit Date:** October 10, 2026  
**Auditor:** DeepMind Antigravity AI  
**Purpose:** Step-by-step walkthrough for manual or automated capture of all Offline Learning interfaces and evidence of current implementation vs gaps for capstone documentation and project handoff.

---

## Prerequisites & Test Credentials

- **Local Base URL:** `http://localhost/Signedd/public`
- **Learner Account (Enrolled Student):**
  - **Student ID / Username:** `onetest_kid` (or `learner_a@signed.ph`)
  - **Password:** `password`
  - **Role:** `learner`
- **Assigned Lesson ID:** Any published lesson plan assigned to this learner (e.g., Lesson ID `1` or `2`).

---

## Screenshot 1: "Download for Offline" Button / Action Widget

- **Exact URL:** `http://localhost/Signedd/public/learning/lesson/{lesson_id}`
- **Required User Role:** `learner`
- **Login Steps:**
  1. Open `http://localhost/Signedd/public/login`.
  2. Log in using `onetest_kid` / `password`.
  3. On the Learner Dashboard (`/learning/dashboard`), click on an assigned lesson card to open the lesson viewer.
- **What Should Be Visible in the Screenshot:**
  - The top white header bar of the lesson viewer.
  - The green network status pill: **`<span class="signed-network-indicator">● Online</span>`**.
  - The container `#offlineLessonWidget` displaying the primary outline button:
    ```html
    <button id="btnDownloadOffline" class="btn btn-sm btn-outline-primary ...">
        <i class="ph-bold ph-download-simple"></i> Download for Offline
    </button>
    ```
  - The lesson progress bar and title.

---

## Screenshot 2: Active Download Progress & "Available Offline" Badge

- **Exact URL:** `http://localhost/Signedd/public/learning/lesson/{lesson_id}`
- **Required User Role:** `learner`
- **What to Click:**
  1. Click the **`[Download for Offline]`** button.
  2. Observe the button transition through:
     - Spinner: *"Kinukuha ang listahan..."*
     - Percentage counter: *"`XX% (X/total)`"* as the Service Worker caches assets.
  3. Wait for the SweetAlert2 popup: **`✓ Handa na Nang Offline!`**.
  4. Click **OK** on the alert.
- **What Should Be Visible in the Screenshot:**
  - The `#offlineLessonWidget` updated to the post-download state:
    - Green badge: **`✓ Available Offline`**
    - Outline button: **`[Remove Download]`** (`window.removeOfflineLesson()`).
  - Open DevTools (F12) $\rightarrow$ **Application** tab $\rightarrow$ **Cache Storage**:
    - Show the cache entry named `signed-offline-lesson-{learnerId}-{lessonId}-v1` containing cached styles, fonts, slide images, and FSL video files.
  - Open DevTools $\rightarrow$ **Local Storage**:
    - Show `signed_offline_lesson_{learnerId}_{lessonId}` and `signed_offline_catalog_{learnerId}` keys populated.

---

## Screenshot 3: Learner Dashboard Offline Indicators

- **Exact URL:** `http://localhost/Signedd/public/learning/dashboard`
- **Required User Role:** `learner`
- **Login Steps:**
  1. Return to the Learner Dashboard after downloading one or more lessons.
- **What Should Be Visible in the Screenshot:**
  - The learner dashboard card grid of assigned learning modules.
  - The `.offline-badge-slot` on the downloaded lesson card showing the green badge:
    ```html
    <span class="badge bg-success-subtle text-success border border-success-subtle ...">
        <i class="ph-bold ph-download-simple me-0.5"></i> Available Offline
    </span>
    ```
  - Clear visual contrast between downloaded lessons and non-downloaded lessons.

---

## Screenshot 4: Standalone Offline Reader in Disconnected State

- **Exact URL:** `http://localhost/Signedd/public/offline-reader.html?lesson_id={lesson_id}&learner_id={learner_id}` (or by disconnecting internet and navigating to `/learning/lesson/{lesson_id}`)
- **Simulation Steps:**
  1. In Chrome/Edge DevTools (F12), go to the **Network** tab.
  2. Change throttling dropdown from **"No throttling"** to **"Offline"** (or turn off Wi-Fi).
  3. Refresh the page or navigate to `http://localhost/Signedd/public/learning/lesson/{lesson_id}`.
  4. The Service Worker intercepts the request and loads `offline-reader.html`.
- **What Should Be Visible in the Screenshot:**
  - Top navy banner with the back arrow, lesson title, and the orange pill:
    ```html
    <span class="offline-pill"><i class="ph-bold ph-wifi-slash"></i> Offline Mode</span>
    ```
  - Slide header with slide pagination pill (e.g. *Pahina 1 ng 4*), domain badge, and green progress bar.
  - Slide content area showing slide text, guide questions callout, and navigation buttons (*Nakaraang Pahina* / *Susunod na Pahina*).
  - Highlighted `.fsl-word` spans embedded in the text.

---

## Screenshot 5: Offline Interactive FSL Video Modal Demonstration

- **Exact URL:** Inside `offline-reader.html` in offline mode.
- **What to Click:**
  1. Click on any highlighted `.fsl-word` span inside the slide narrative.
- **What Should Be Visible in the Screenshot:**
  - The modal dialog triggered by `fsl-modal.js` floating over the offline reader.
  - The HTML5 video player successfully streaming and playing the local FSL video file (served directly from the Service Worker Cache API).
  - Confirmation in the DevTools Network panel that the video request was served `(from ServiceWorker)`.

---

## Screenshot 6: Formative Activities Blocked Notice (Documenting the Implementation Gap)

- **Exact URL:** Inside `offline-reader.html` in offline mode.
- **What to Click:**
  1. In the offline reader header, click the second tab: **`"Mga Pagsasanay (Online Required)"`** (`#tabBtnActivities`).
- **What Should Be Visible in the Screenshot:**
  - The blocked activities container (`#panelActivities`).
  - The large orange offline icon (`ph-wifi-slash`).
  - The explicit DepEd SPED requirement notice:
    > **"Kailangan ng Koneksyon sa Internet"**  
    > *"Ang mga pagsasanay at pagsusulit ay nangangailangan ng aktibong internet upang maitala ang iyong mga sagot, makakuha ng bituin, at madagdagan ang iyong XP."*
  - The return button: *`[Bumalik sa Pagbabasa ng Aralin]`*.

---

## Screenshot 7: Reconnect Indicator & Absence of Sync Endpoint (Documenting the Gap)

- **Simulation Steps:**
  1. While in the offline reader or on the lesson view, switch DevTools Network back from **"Offline"** to **"Online"** (or reconnect Wi-Fi).
  2. Observe the `.signed-network-indicator` pill update immediately to green **"Online"** via `window.addEventListener('online')`.
- **What Should Be Visible in the Screenshot:**
  - The DevTools Network panel open showing that upon reconnection, **zero HTTP POST requests** are dispatched to any `/sync` or progress endpoint.
  - This provides clear, objective evidence for capstone evaluation that while **connectivity detection** is implemented, the **data reconciliation engine** remains a planned capstone enhancement.

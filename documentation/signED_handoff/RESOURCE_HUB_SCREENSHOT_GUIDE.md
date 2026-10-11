# SignED — Resource Hub Screenshot Capture Guide

**Document Version:** 1.0.0  
**Audit Date:** October 10, 2026  
**Auditor:** DeepMind Antigravity AI  
**Purpose:** Step-by-step walkthrough for manual or automated capture of all Resource Hub interfaces for capstone documentation and project handoff.

---

## Prerequisites & Test Credentials

- **Local Base URL:** `http://localhost/Signedd/public` (or `http://signedd.test/public` depending on local host configuration)
- **Primary Teacher Account (SPED Teacher):**
  - **Email:** `teacher_a@signed.ph` (or `asteleackerman@gmail.com`)
  - **Password:** `password`
  - **Role:** `sped_teacher`
- **Secondary Teacher Account (Recipient for Cloning):**
  - **Email:** `masterteacher@spedlms.local`
  - **Password:** `password`
  - **Role:** `master_teacher`
- **System Administrator Account:**
  - **Email:** `admin@spedlms.local`
  - **Password:** `password`
  - **Role:** `admin`

---

## Screenshot 1: Teacher Resource Hub Home / Browse Page

- **Exact URL:** `http://localhost/Signedd/public/teacher/resource-hub` (or `http://localhost/Signedd/public/iep/implementation/resource-hub`)
- **Required User Role:** `sped_teacher`, `teacher`, `master_teacher`, or `admin`
- **Login Steps:**
  1. Open `http://localhost/Signedd/public/login`.
  2. Log in with `teacher_a@signed.ph` / `password`.
  3. In the sidebar, click **Resource Hub** (or navigate directly to the URL above).
- **What to Click / Filter:**
  - Ensure the **"Browse Resources"** tab is active (`#tab-browse-btn`).
  - Optional: Type a keyword (e.g. `Addition`) in the search box or check the **"FSL-Only"** checkbox and click **"Filter"**.
- **What Should Be Visible in the Screenshot:**
  - The navy hero banner with title **"SignED Accessibility Pack Resource Hub"** and badges *(Teacher Resource Hub · Deaf-Accessible · FSL Packs)*.
  - The search and filter toolbar (search bar, PDSP domain dropdown, learning level dropdown, FSL-Only checkbox).
  - The grid of pack cards showing domain badges (e.g., *Perceptuo-Cognitive*), level, FSL sign count badge *(e.g., "14 FSL Signs")*, slide count, activity count, author name, adaptation count, **Preview** button, and **Use as Template** button.

---

## Screenshot 2: Upload / Share Resource Modal ("Create Resource")

*Note: In the SignED architecture, resources in the Hub are created by sharing an existing completed lesson plan rather than through a disconnected blank form.*

- **Exact URL:** `http://localhost/Signedd/public/iep/implementation/workspace/{iep_id}` (or inside the Lesson Studio at `http://localhost/Signedd/public/iep/implementation/lesson/{lp_id}/builder`)
- **Required User Role:** `sped_teacher` (the author of the lesson)
- **Login Steps:**
  1. Log in as `teacher_a@signed.ph` / `password`.
  2. Navigate to an IEP Workspace managing a completed lesson plan (e.g. `http://localhost/Signedd/public/iep/implementation/workspace/1`).
- **What to Click:**
  - On any lesson plan card in the lesson list, click the yellow button: **`[Share to Hub]`** (or if inside the Lesson Studio topbar, click **`[Share to Hub]`**).
- **What Should Be Visible in the Screenshot:**
  - The modal dialog **"Share to Resource Hub"** (`#shareToHubWorkspaceModal`).
  - The privacy callout: *"Privacy Guarded: Educational content and FSL media only. Learner names, IEP IDs, and evaluations are never shared."*
  - The form fields: **Title**, **Short Description**, **Subject / Domain** dropdown, **Grade / Learning Level**, and the collapsed/expanded **Advanced Options** (Learning Competency, Tags, External Resource Title/URL).
  - The **`[Share Resource]`** submit button.

---

## Screenshot 3: Resource Details / Inspection (Read-Only Preview Mode)

- **Exact URL:** `http://localhost/Signedd/public/iep/implementation/resource-hub/preview/{pack_id}` (e.g. `/preview/3` or `/preview/7`)
- **Required User Role:** `sped_teacher`, `teacher`, `master_teacher`, or `admin`
- **Login Steps:**
  1. From the Resource Hub browse page, click the **`[Preview]`** button on any pack card.
- **What to Click:**
  - Click on any slide in the left sidebar list (e.g., `#btnSlideNav_0` or `#btnSlideNav_1`).
  - Click on an embedded yellow **`FSL Word`** span inside the slide narrative to trigger the popup FSL video player.
- **What Should Be Visible in the Screenshot:**
  - Top header displaying the pack title, domain badge, author, slide count, and the **"Preview Mode"** badge.
  - Left navigation pane showing the numbered slide buttons and the practice activities count button.
  - Right canvas rendering the slide narrative, instructional image, highlighted FSL vocabulary, and the green/light guide questions callout box.
  - The interactive sign language modal player displaying the FSL video and replay/speed controls.

---

## Screenshot 4: "My Resources" Page ("My Shared Packs" Tab)

- **Exact URL:** `http://localhost/Signedd/public/iep/implementation/resource-hub?tab=my_packs`
- **Required User Role:** `sped_teacher` (who has shared at least one pack) or `admin`
- **Login Steps:**
  1. Log in as `teacher_a@signed.ph` / `password`.
  2. Open `http://localhost/Signedd/public/iep/implementation/resource-hub`.
  3. Click the **"My Shared Packs"** tab (`#tab-mypacks-btn`).
- **What to Click:**
  - Ensure the "My Shared Packs" tab is active.
- **What Should Be Visible in the Screenshot:**
  - The tab pill showing the active count of packs authored by the logged-in teacher.
  - The cards displaying the green badge **"✓ My Shared Pack"**, date shared, total teachers adapted count, **Preview** button, and the red outline **`[Unshare]`** button.

---

## Screenshot 5: Clone / Reuse / Adapt Interface ("Use as Template" Modal)

- **Exact URL:** `http://localhost/Signedd/public/iep/implementation/resource-hub` (or on the preview page)
- **Required User Role:** `master_teacher` or another `sped_teacher` who has at least one active signed IEP assigned to them
- **Login Steps:**
  1. Log in as a teacher with signed IEPs.
  2. Open the Resource Hub.
  3. On any pack card (or on the preview header), click the blue button: **`[Use as Template]`**.
- **What to Click:**
  - Click **`[Use as Template]`** to open the modal `#useAsTemplateModal`.
  - Click the **"Choose Your Student / Signed IEP Context"** dropdown (`#targetIepSelect`) to show the available assigned learners.
- **What Should Be Visible in the Screenshot:**
  - The modal titled **"Use as Template"** with subtitle *"Create an independent draft for your learner"*.
  - The selected pack title display box.
  - The dropdown list showing the teacher's manageable signed IEPs *(Student Name, LRN, School Year)*.
  - The blue safety callout: *"Privacy & Safety Protected: Only educational slides, instructional imagery, and approved FSL signs are copied..."*
  - The primary button: **`[Clone & Open in Builder]`**.

---

## Screenshot 6: Cloned & Adapted Lesson in Lesson Studio (Post-Clone State)

- **Exact URL:** `http://localhost/Signedd/public/iep/implementation/lesson/{cloned_lp_id}/builder`
- **Required User Role:** The teacher who performed the clone
- **Login Steps:**
  1. Immediately follows submitting the clone modal (auto-redirected).
- **What Should Be Visible in the Screenshot:**
  - Top studio header showing the lesson title with the automatic suffix: **`[Title] (Adapted)`**.
  - Status badge indicating **`Draft`** (confirming that cloning creates an independent draft, not an auto-published lesson).
  - Left slide thumbnails populated with the cloned slides.
  - WYSIWYG editor canvas displaying the cloned narrative and FSL vocabulary ready for teacher customization.

---

## Screenshot 7: Review / Approval Interface (Documenting the Implementation Gap)

- **Status in Code:** **NOT YET IMPLEMENTED**
- **Explanation for Handoff / Capstone:**
  - In the current codebase, there is no administrator review screen or approval button because the model method `shareLessonToHub()` in [`ResourceHubModel.php:169`](file:///c:/laragon/www/Signedd/app/Models/ResourceHubModel.php#L169) sets:
    ```php
    'status' => 'published'
    ```
  - Therefore, all submitted packs become active and visible immediately.
  - **What to Capture as Evidence for Capstone Documentation:**
    - Capture the database table `resource_hub_packs` in phpMyAdmin (`http://localhost/phpmyadmin`) or MySQL Workbench showing that the `status` column only contains `'published'` or `'archived'`, with no `'pending_review'` state.

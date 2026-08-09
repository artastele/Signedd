# SignED — System & Future Features Overview
### A Comprehensive Review Document

---

## 🧭 What is SignED?

**SignED** is a specialized, inclusive Learning Management System (LMS) built for Philippine SPED (Special Education) schools, specifically targeting **hearing-impaired (Deaf and Hard of Hearing / DHH)** learners.

It is built on:
- **Backend:** PHP (OOP / MVC pattern)
- **Database:** MySQL 8
- **Frontend:** Bootstrap 5 (Custom Theme)
- **Email:** PHPMailer
- **Auth:** Google OAuth 2.0
- **Server:** Apache on Laragon (local dev)

> **Aligned with:** SDG 4 — Quality Education | DepEd Inclusive Education Policy | RA 10173 Data Privacy Act

---

## 👥 Who Uses SignED?

| Role | What They Do |
|---|---|
| **Admin** | Full system access, manage users |
| **SPED Teacher** | Upload lessons, verify enrollment, conduct assessments, implement IEPs |
| **Guidance** | Facilitate IEP meetings, provide insights |
| **Principal** | Sign and approve IEPs |
| **Parent / Guardian** | Submit enrollment, monitor child's progress |
| **Student (DHH)** | View lessons, take quizzes, track IEP progress |

---

## 📦 Current System Features (Already Built)

```
Process 1: Parent Enrollment Submission
Process 2: SPED Teacher Document Verification
Process 3: Initial Assessment Conduct
Process 4: IEP Meeting Facilitation
Process 5: IEP Generation and Signing
Process 6: IEP Implementation
Process 7: Learning Activity Tracking
```

**Security already in place:**
- Password hashing (bcrypt)
- Role-based access control (RBAC)
- Session timeout (15 minutes)
- Login attempt logging
- Admin activity logging
- Secure file uploads

---

## 🚀 The Big Idea — Role-Based Feature Differentiation

### The Core Concept

> The IEP system, enrollment, and dashboards are **shared for all users.**
> But if a student is tagged as **hearing-impaired**, their account **automatically unlocks additional features** designed specifically for DHH learners.

This is done by checking the student's `disability_type` field (set during enrollment/IEP) and enabling or disabling features accordingly.

```
All Students:
  ✅ IEP Management
  ✅ Lesson Viewing
  ✅ Quiz Taking
  ✅ Grade Tracking
  ✅ Parent Monitoring
  ✅ Enrollment

Hearing-Impaired Students (Additional):
  ➕ FSL Highlighted Word Pop-Ups
  ➕ AI-Generated Video Captions
  ➕ Smart Remedial Lesson Tracks
  ➕ High-Contrast Visual-First Interface
  ➕ FSL Word of the Day
```

### How It Works in the Database

```sql
-- Student profile stores disability type (set during enrollment)
students
  ├── student_id
  ├── name
  ├── disability_type   ← "hearing_impaired" / "visual" / "intellectual"
  └── ...

-- Feature flags per disability type
disability_features
  ├── disability_type = "hearing_impaired"
  ├── fsl_popups      = true
  ├── ai_captions     = true
  ├── smart_tracks    = true
  └── high_contrast   = true
```

### How It Works in PHP

```php
// Middleware checks disability type on every page load
if ($student->disability_type === 'hearing_impaired') {
    $features = [
        'fsl_popups'    => true,
        'ai_captions'   => true,
        'smart_tracks'  => true,
        'high_contrast' => true,
    ];
}
// Features are passed to the view and toggle HTML elements on/off
```

---

## 🤟 Feature 1 — FSL Highlighted Vocabulary Pop-Ups

### What It Is
Selected **educational/vocabulary words** inside lesson text are highlighted. When a DHH student clicks or hovers on them, a **short FSL sign video/GIF pops up** showing how to sign that word in Filipino Sign Language.

### Scope
- **NOT every word** — only teacher-curated vocabulary words per lesson
- Estimated: **10–20 highlighted words per lesson**
- Total FSL clip library: **300–500 clips** (~1.5–5 GB storage)

### How It Works
```
Student reads lesson text
        ↓
Sees highlighted word: "enrollment"
        ↓
Clicks on it
        ↓
Small popup appears showing FSL sign video/GIF for "enrollment"
        ↓
Student learns both the word AND its FSL sign
```

### Technical Implementation
```html
<!-- Teacher marks words in lesson content -->
<span class="fsl-word" data-word="enrollment">enrollment</span>

<!-- JavaScript shows popup on click -->
<div id="fsl-popup">
  <video src="/fsl/enrollment.mp4" autoplay loop></video>
  <p>FSL Sign: Enrollment</p>
</div>
```

```sql
-- FSL vocabulary table
fsl_vocabulary
  ├── word         = "enrollment"
  ├── video_path   = "/fsl/enrollment.mp4"
  └── gif_path     = "/fsl/enrollment.gif"
```

### Resource Impact
| Resource | Impact |
|---|---|
| Storage | +1.5–5 GB (300–500 clips) |
| Network | +10–20 Mbps (on-click fetch) |
| CPU | None (static file serving) |
| RAM | Minimal |

---

## 🎙️ Feature 2 — AI-Generated Captions on Video Lessons

### What It Is
When SPED teachers upload video lessons, the system automatically sends the audio to an AI speech-to-text API (**OpenAI Whisper** or **Google Speech-to-Text**). The API returns a transcript with timestamps, which is saved as a caption file (`.vtt`) and displayed as subtitles when DHH students watch the video.

### How It Works
```
Teacher uploads video lesson
        ↓
Server sends audio to Whisper API / Google STT
        ↓
API returns text with timestamps:
  [00:01] "Today we will learn about enrollment..."
  [00:05] "An IEP is an Individualized Education Plan..."
        ↓
System saves as .vtt caption file
        ↓
DHH student watches video — captions appear automatically
```

### Why This Matters
> Hearing-impaired students cannot hear the teacher's voice in video lessons. Automated captions make every video lesson immediately accessible without the teacher needing to manually type subtitles.

### Technical Implementation
```php
// After teacher uploads video
$audioFile = extractAudio($videoPath); // Extract audio from video

// Send to OpenAI Whisper API
$response = $whisperAPI->transcribe([
    'file'     => $audioFile,
    'model'    => 'whisper-1',
    'language' => 'fil', // Filipino
]);

// Save caption file
saveCaptionFile($response->vtt, $lessonId);
```

```html
<!-- Video player with captions -->
<video controls>
  <source src="/lessons/lesson1.mp4">
  <track src="/captions/lesson1.vtt" kind="subtitles" label="Filipino">
</video>
```

### Resource Impact
| Resource | Impact |
|---|---|
| CPU | Spike during API call (external processing, not local) |
| Storage | +500 MB (caption .vtt files — very small) |
| Network | +10–50 Mbps (video file sent to API during upload) |
| RAM | +1–2 GB (video buffering during upload) |

---

## 🔁 Feature 3 — Smart Remedial Lesson Tracks

### What It Is
A **rule-based (not AI)** system that tracks quiz scores. If a DHH student scores below **70%** on a lesson quiz, the system automatically redirects them to a **simpler remedial version** of the lesson before allowing them to move forward. This simulates an IEP-aligned personalized learning pace.

### How It Works
```
Student completes Lesson 3 Quiz
        ↓
Score = 55% (below 70% threshold)
        ↓
System detects: needs remediation
        ↓
Auto-redirects to: "Lesson 3 — Remedial Review"
  (simpler language, more FSL pop-ups, shorter content)
        ↓
Student retakes quiz → Score = 80% → Moves to Lesson 4 ✅
```

### Why Rule-Based (Not AI)?
- **Simpler to build** for capstone scope
- **Transparent and auditable** — teachers can see exactly why a student was rerouted
- **Aligns with IEP approach** — personalized pacing without complex ML models
- **Phase 2 future plan:** Replace with true AI recommendation engine

### Technical Implementation
```php
// After quiz submission
if ($quizScore < 70) {
    // Log the remediation trigger
    $this->trackModel->logRemediation($studentId, $lessonId, $quizScore);

    // Redirect to remedial lesson
    $remedialLesson = $this->lessonModel->getRemedial($lessonId);
    redirect("/lessons/{$remedialLesson->id}");
} else {
    // Unlock next lesson
    $this->trackModel->unlockNext($studentId, $lessonId);
    redirect("/lessons/next");
}
```

```sql
-- Lessons table with remedial link
lessons
  ├── lesson_id
  ├── title
  ├── content
  ├── remedial_lesson_id  ← links to the simpler version
  └── disability_type     ← "hearing_impaired" / "all"
```

### Resource Impact
| Resource | Impact |
|---|---|
| CPU | Minimal (simple IF/ELSE logic) |
| Storage | +5–10 GB (remedial lesson content) |
| Network | None additional |
| RAM | None additional |

---

## 📚 Feature 4 — Teacher FSL Training Module

### What It Is
A **dedicated resource tab inside the teacher portal** that helps SPED teachers learn FSL so they can better support their DHH students. This is crucial because many SPED teachers are not FSL-fluent.

### Contents
```
Teacher FSL Training Module
  ├── 📖 FSL Digital Dictionary
  │     └── Browse FSL signs alphabetically
  │         Click a word → see FSL sign video
  │
  ├── 🎓 Instructional Guide Videos
  │     └── How to sign common classroom phrases
  │         How to conduct IEP meetings with DHH students
  │
  ├── ✋ Basic Signing Tips
  │     └── Handshape guides
  │         Common mistakes to avoid
  │
  └── 📝 FSL Quick Reference Cards
        └── Printable PDF guides per topic
```

### Resource Impact
| Resource | Impact |
|---|---|
| Storage | +20–80 GB (training videos, dictionary clips) |
| Network | +10–30 Mbps (teachers streaming training content) |
| CPU | Minimal (static video serving) |
| RAM | Minimal |

---

## 🎨 Feature 5 — High-Contrast Visual-First Interface for DHH

### What It Is
When a student with `disability_type = hearing_impaired` logs in, the UI automatically switches to a **high-contrast, visual-first layout** optimized for DHH learners:

| Standard UI | DHH UI |
|---|---|
| Normal contrast | High contrast (dark bg, bright text) |
| Text-heavy | More icons and visuals |
| Audio indicators | Visual indicators (flash, color change) |
| Standard font size | Larger, clearer fonts |
| Normal button style | Larger tap targets |

### Technical Implementation
```php
// In the session after login
if ($user->disability_type === 'hearing_impaired') {
    $_SESSION['theme'] = 'high-contrast';
    $_SESSION['features'] = ['fsl', 'captions', 'smart_tracks'];
}
```

```css
/* High contrast theme */
body.high-contrast {
    background: #0a0a0a;
    color: #ffffff;
    font-size: 1.1rem;
}

body.high-contrast .fsl-word {
    background: #FFD700;
    color: #000;
    border-bottom: 3px solid #FF6B00;
    cursor: pointer;
}
```

---

## 📊 Combined Resource Requirements Summary

### What the Combined System Needs

| Resource | Base LMS Only | Combined System (All Features) | Change |
|---|---|---|---|
| **CPU** | 2–4 cores | 4–6 cores | +2 cores |
| **RAM** | 4–8 GB | 8–16 GB | 2× increase |
| **Storage** | 50–200 GB | 200–500 GB | 2.5× increase |
| **Network** | 50–100 Mbps | 200–400 Mbps peak | 4× increase |

### What Drives the Increase
- 🎥 Video lesson streaming to DHH students → **Network ↑↑**
- 📀 FSL clips + training videos + lesson videos → **Storage ↑↑**
- 🎙️ AI caption API uploads → **CPU spike events + Network ↑**
- 👥 More concurrent users (DHH + teachers using FSL module) → **RAM ↑**

---

## 🏗️ Recommended Deployment Architecture

```
Internet
    ↓
[Domain: signed.sped.edu.ph]
    ↓
[Cloudflare CDN]  ← caches FSL clips, training videos, static assets
    ↓
[Type 1 Hypervisor — Proxmox VE / KVM]
    ├── VM 1: SignED App Server
    │     └── Ubuntu 22.04 + Apache + PHP 8.1
    │         (IEP system, dashboards, FSL pop-ups, smart tracks)
    │
    ├── VM 2: Database Server
    │     └── MySQL 8 (isolated — student PII protected)
    │
    └── VM 3: Media Server
          └── Nginx file server (FSL clips, lesson videos,
              training module content, caption files)
```

### Why Type 1 Hypervisor?
1. **Snapshots** — safely deploy AI caption API integrations, rollback if broken
2. **VM Isolation** — student data in DB VM, separate from app server
3. **SAN/SSD backend** — handles concurrent FSL video + DB I/O without contention
4. **10 Gbps vNIC** — handles peak video streaming from all DHH students
5. **Live scaling** — add vCPU/RAM for new features without downtime
6. **RA 10173 compliance** — encrypted VM storage for DHH student PII

---

## 📅 Development Phases

### Phase 1 — Capstone (Build Now)
- [ ] Disability type tagging from enrollment/IEP
- [ ] FSL highlighted vocabulary pop-ups (GIFs/short clips)
- [ ] Smart remedial lesson tracks (rule-based, score < 70%)
- [ ] Teacher FSL training module (resource tab)
- [ ] High-contrast visual-first dashboard for DHH students
- [ ] Student quiz system with score tracking
- [ ] Video lesson viewer with caption file support

### Phase 2 — Future Extension
- [ ] AI captions via OpenAI Whisper / Google Speech-to-Text API
- [ ] True AI recommendation engine (replace rule-based smart tracks)
- [ ] Real-time FSL animation generation
- [ ] Multi-school / multi-tenant support
- [ ] Mobile app (PWA or native)

---

## 📋 Problem Statement (Why This Matters)

| Problem | SignED's Solution |
|---|---|
| DHH students excluded from digital learning | Role-based DHH features unlock accessible content |
| Teachers lack FSL skills for inclusive classes | Teacher FSL Training Module |
| Video lessons inaccessible to DHH learners | AI-generated captions on all video lessons |
| No personalized pacing for DHH learners | Smart remedial lesson tracks |
| DepEd inclusive content policy non-compliance | FSL-integrated curriculum-aligned materials |

---

## 🎯 Objectives

| Objective | Target |
|---|---|
| Inclusion of SEN learners in distance learning | ≥ 85% participation |
| Inclusive content policy compliance across DepEd schools | ≥ 80% compliance |
| FSL integration in teacher education programs | ≥ 75% program adoption |

---

*Document prepared for: SignED Capstone Project — SPED LMS for Hearing-Impaired Learners*
*Technology Stack: PHP (OOP/MVC) · MySQL 8 · Bootstrap 5 · PHPMailer · Google OAuth · OpenAI Whisper (planned)*
*Compliance: RA 10173 Data Privacy Act · DepEd Inclusive Education Policy · SDG 4 Quality Education*

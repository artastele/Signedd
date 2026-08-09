# SignED — Future Features Overview
### Planned Additions to the SPED LMS
**Design Principle:** Universal Design for Learning (UDL) — All features available to ALL users.

---

## 🤟 Feature 1: FSL Highlighted Vocabulary Pop-Ups

### What It Is
Key vocabulary words inside lesson text are **highlighted**. Any student who clicks a highlighted word sees a **short Filipino Sign Language (FSL) sign video or GIF** pop up — teaching the word's FSL sign alongside the lesson.

### Who Benefits
- **DHH Students** — Primary beneficiary, learns FSL signs for vocabulary
- **All Students** — Visual reinforcement of key terms
- **Parents** — Awareness of FSL vocabulary used in lessons

### Scope
- Only **teacher-curated vocabulary words** per lesson (NOT every word)
- Estimated **10–20 highlighted words per lesson**
- Total FSL clip library: **300–500 clips** (~1.5–5 GB)
- Format: Short `.mp4` clips or `.gif` animations

### User Flow
```
Teacher creates lesson → marks "enrollment" as FSL word
          ↓
Student reads lesson → sees "enrollment" highlighted in gold
          ↓
Student clicks the word
          ↓
Popup appears → plays short FSL sign video for "enrollment"
          ↓
Student learns word meaning + FSL sign at the same time ✅
```

### How Teacher Marks FSL Words
```
Lesson Editor (Teacher Portal)
  ├── Type or paste lesson content
  ├── Select a word → click "Mark as FSL Word"
  └── System wraps it: <span class="fsl-word" data-word="enrollment">enrollment</span>
```

### Technical Implementation
```html
<!-- Lesson content -->
<p>The <span class="fsl-word" data-word="enrollment">enrollment</span>
process begins every June.</p>

<!-- FSL popup modal -->
<div id="fsl-popup" class="fsl-modal hidden">
  <video src="" id="fsl-video" autoplay loop muted></video>
  <p id="fsl-label">FSL Sign: </p>
  <button onclick="closeFSLPopup()">✕</button>
</div>
```

```javascript
// JS — triggered on FSL word click
document.querySelectorAll('.fsl-word').forEach(word => {
  word.addEventListener('click', function () {
    const w = this.dataset.word;
    fetch(`/api/fsl?word=${w}`)
      .then(res => res.json())
      .then(data => {
        document.getElementById('fsl-video').src = data.video_path;
        document.getElementById('fsl-label').textContent = `FSL Sign: ${w}`;
        document.getElementById('fsl-popup').classList.remove('hidden');
      });
  });
});
```

```sql
-- Database table
fsl_vocabulary
  ├── id
  ├── word         = "enrollment"
  ├── video_path   = "/fsl/clips/enrollment.mp4"
  ├── gif_path     = "/fsl/gifs/enrollment.gif"
  └── created_at
```

### Resource Impact
| Resource | Change |
|---|---|
| Storage | +1.5–5 GB |
| Network | +10–20 Mbps (on-click fetch) |
| CPU | No change |
| RAM | No change |

---

## 🎙️ Feature 2: AI-Generated Captions on Video Lessons

### What It Is
When teachers upload video lessons, the system **automatically sends the audio to an AI speech-to-text API** (OpenAI Whisper or Google Speech-to-Text). The result is a timestamped caption file (`.vtt`) that displays as subtitles for all students watching the video — no manual typing required from the teacher.

### Who Benefits
- **DHH Students** — Can fully understand video lessons without hearing the audio
- **All Students** — Captions improve reading comprehension and retention
- **Teachers** — Zero extra work; captions are auto-generated on video upload

### User Flow
```
Teacher uploads "Lesson 3: Understanding IEPs" video
          ↓
System extracts audio from the video
          ↓
Audio sent to Whisper API / Google STT
          ↓
API returns timestamped transcript:
  [00:01] "Ngayon, pag-usapan natin ang IEP..."
  [00:06] "Ang IEP ay nangangahulugang..."
          ↓
System saves as lesson_3.vtt caption file
          ↓
All students watch video → captions display automatically ✅
```

### Technical Implementation
```php
// CaptionController.php — triggered after video upload
public function generateCaptions($lessonId, $videoPath) {

    // Step 1: Extract audio from video
    $audioPath = $this->extractAudio($videoPath);

    // Step 2: Send to Whisper API
    $caption = $this->whisperAPI->transcribe([
        'file'            => $audioPath,
        'model'           => 'whisper-1',
        'language'        => 'fil',       // Filipino
        'response_format' => 'vtt',       // WebVTT subtitle format
    ]);

    // Step 3: Save caption file
    $captionPath = "/captions/lesson_{$lessonId}.vtt";
    file_put_contents(PUBLIC_PATH . $captionPath, $caption);

    // Step 4: Link caption to lesson in DB
    $this->lessonModel->update($lessonId, [
        'caption_path' => $captionPath
    ]);
}
```

```html
<!-- Video player with auto-captions (all students) -->
<video controls class="lesson-player">
  <source src="/lessons/lesson3.mp4" type="video/mp4">
  <track src="/captions/lesson_3.vtt"
         kind="subtitles"
         srclang="fil"
         label="Filipino"
         default>
</video>
```

### Resource Impact
| Resource | Change |
|---|---|
| Storage | +500 MB (caption .vtt files — very small) |
| Network | +10–50 Mbps (video file uploaded to API) |
| CPU | Spike during API call (external processing — not on your server) |
| RAM | +1–2 GB during video buffering on upload |

---

## 🔁 Feature 3: Smart Remedial Lesson Tracks

### What It Is
A **rule-based personalized learning system** for all students. After every quiz, the system checks the score. If the student scores below **70%**, they are **automatically redirected to a simpler remedial version** of the lesson before being allowed to advance. No teacher intervention needed.

### Who Benefits
- **All Students** — No student is pushed forward before they're ready
- **SPED Teachers** — Automatic intervention without manually checking each student
- **Parents** — Child's progress adapts to their actual learning pace
- **Guidance Counselors** — Remediation data serves as IEP evidence

### User Flow
```
Student finishes Lesson 3 quiz
          ↓
Score = 55% → below 70% threshold
          ↓
System logs: remediation triggered for this student + lesson
          ↓
Auto-redirects to: "Lesson 3 — Remedial Review"
  (simpler language, shorter content, more FSL pop-ups,
   more visual aids, slower pace)
          ↓
Student completes remedial content → retakes quiz
          ↓
Score = 83% → Lesson 4 unlocked ✅
```

### Why Rule-Based (Not Full AI) for Now
| | Rule-Based (Now) | AI-Based (Future) |
|---|---|---|
| Build complexity | Low — simple IF/ELSE | High — needs ML model + training data |
| Transparency | Teachers can audit every decision | Black-box decisions |
| Reliability | Always consistent | Depends on model quality |
| Capstone scope | ✅ Achievable | 🔄 Phase 2 |

### Technical Implementation
```php
// QuizController.php — after quiz submission
public function submitQuiz($studentId, $lessonId, $answers) {

    // Calculate score
    $score = $this->calculateScore($answers, $lessonId);

    // Get threshold (default 70, configurable per lesson)
    $threshold = $this->lessonModel->getThreshold($lessonId);

    // Log result
    $this->quizModel->save([
        'student_id'   => $studentId,
        'lesson_id'    => $lessonId,
        'score'        => $score,
        'passed'       => $score >= $threshold,
        'attempted_at' => now(),
    ]);

    if ($score < $threshold) {
        // Get linked remedial lesson
        $remedial = $this->lessonModel->getRemedial($lessonId);
        return redirect("/lessons/{$remedial->id}");
    } else {
        // Unlock the next lesson
        $this->trackModel->unlockNext($studentId, $lessonId);
        return redirect("/lessons/next");
    }
}
```

```sql
-- Lessons table with remedial support
lessons
  ├── lesson_id
  ├── title
  ├── content
  ├── video_path
  ├── caption_path
  ├── score_threshold     ← default 70, teacher can adjust
  ├── remedial_lesson_id  ← points to the simpler version
  └── is_remedial         ← true = this is a remedial lesson
```

### Resource Impact
| Resource | Change |
|---|---|
| Storage | +5–10 GB (remedial lesson content + videos) |
| Network | No additional impact |
| CPU | Minimal (simple IF/ELSE logic in PHP) |
| RAM | No additional impact |

---

## 📚 Feature 4: Teacher FSL Training Module

### What It Is
A **dedicated resource tab inside the teacher portal** that helps all SPED teachers learn Filipino Sign Language. Many SPED teachers are not FSL-fluent — this module gives them the tools to improve their skills and better serve DHH learners in their classes.

### Who Benefits
- **All SPED Teachers** — Learn FSL at their own pace inside the platform
- **New Teachers** — Onboarding resource before handling DHH learners
- **Guidance Counselors** — Reference FSL signs during IEP meetings
- **Parents** — Access basic FSL guides to support home learning

### Module Contents
```
📖 FSL Digital Dictionary
   ├── Search any word → see its FSL sign video
   ├── Browse alphabetically (A–Z)
   └── Categories: Classroom | IEP Terms | Daily Living | Emotions

🎓 Instructional Guide Videos
   ├── "How to Sign Common Classroom Commands"
   ├── "How to Conduct IEP Meetings with DHH Students"
   ├── "Basic FSL Handshapes for Beginners"
   └── "FSL for SPED Assessment Terms"

✋ Basic Signing Tips
   ├── Common handshape mistakes to avoid
   ├── Facial expressions in FSL (they are grammar, not just emotion)
   └── Fingerspelling guide (A–Z Filipino alphabet)

📝 Quick Reference Cards
   ├── Printable PDF: Classroom Commands
   ├── Printable PDF: IEP Vocabulary
   └── Printable PDF: Daily Living Signs
```

### Resource Impact
| Resource | Change |
|---|---|
| Storage | +20–80 GB (training videos + dictionary clips) |
| Network | +10–30 Mbps (teachers streaming training content) |
| CPU | Minimal (static video file serving) |
| RAM | Minimal |

---

## 🎨 Feature 5: High-Contrast Visual-First UI (User Preference Toggle)

### What It Is
Any user — student, teacher, or parent — can **toggle high-contrast mode** from their profile settings. It activates a visual-first layout with dark background, bright text, larger fonts, and larger tap targets. The preference is saved to the database and persists across all sessions and devices.

### Who Benefits
- **DHH Students** — Visual-first layout reduces cognitive load
- **Any User** — Preferred by users with visual sensitivity or low-light environments
- **All Students** — Larger text and clearer buttons improve usability for all learners

### Standard vs High-Contrast
| UI Element | Standard Mode | High-Contrast Mode |
|---|---|---|
| Background | White / light gray | Deep dark `#0a0a0a` |
| Text color | Dark gray | Bright white `#ffffff` |
| FSL highlighted words | Subtle gold | Bold gold + thick orange border |
| Button size | Normal | Larger tap targets |
| Font size | 1rem (16px) | 1.15rem (18.4px) |
| Caption display | Optional | ON by default |
| Icons | Small | Larger with text labels |

### User Flow
```
User goes to Profile Settings
          ↓
Clicks toggle: "Enable High-Contrast Mode ○/●"
          ↓
UI instantly switches — dark bg, bright text, larger elements
          ↓
Preference saved to DB: user_preferences.high_contrast = true
          ↓
Every page load checks preference → applies theme automatically
          ↓
Works on any device, any browser ✅
```

### Technical Implementation
```php
// user_preferences table
user_preferences
  ├── user_id
  ├── high_contrast      = true / false
  ├── caption_default    = true / false   ← captions ON by default?
  └── fsl_popup_enabled  = true / false   ← FSL popups ON by default?
```

```php
// Applied in the base layout (header.php)
$prefs = $this->prefModel->get(Auth::id());
$bodyClass = $prefs->high_contrast ? 'high-contrast' : '';
```

```html
<!-- Base layout body tag -->
<body class="<?= $bodyClass ?>">
```

```css
/* High contrast theme */
body.high-contrast {
    background-color: #0a0a0a;
    color: #ffffff;
    font-size: 1.15rem;
}
body.high-contrast .fsl-word {
    background: #FFD700;
    color: #000000;
    border-bottom: 3px solid #FF6B00;
    font-weight: bold;
}
body.high-contrast .btn {
    padding: 0.75rem 1.5rem;
    font-size: 1.1rem;
    min-width: 120px;
}
body.high-contrast video track {
    display: block; /* captions always visible */
}
```

### Resource Impact
| Resource | Change |
|---|---|
| Storage | +Minimal (1 extra DB column per user) |
| Network | No additional impact |
| CPU | No additional impact |
| RAM | No additional impact |

---

## 📊 Combined Future Features — Resource Summary

| Resource | Base LMS Only | After All 5 Future Features | Biggest Driver |
|---|---|---|---|
| **CPU** | 2–4 cores | **4–6 cores** | AI caption API calls + more concurrent sessions |
| **RAM** | 4–8 GB | **10–20 GB** | Video buffering + more active user sessions |
| **Storage** | 50–200 GB | **200–500 GB** | Lesson videos + FSL clips + training module |
| **Network** | 50–100 Mbps | **300–600 Mbps peak** | All students streaming video lessons concurrently |

---

## 📅 Build Order (Recommended)

| Priority | Feature | Reason |
|---|---|---|
| 1st | Smart Remedial Tracks | Core learning logic — foundational |
| 2nd | FSL Pop-Up Vocabulary | High-impact, low-complexity |
| 3rd | Teacher FSL Training Module | Enables teachers to support DHH learners |
| 4th | High-Contrast UI Toggle | UI polish, low effort |
| 5th | AI-Generated Captions | Requires API key + cost planning |

---

*SignED Future Features — Universal Design for Learning (UDL)*
*All 5 features available to ALL users — no disability tag required.*

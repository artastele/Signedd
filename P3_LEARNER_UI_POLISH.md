# P3 Learner UI Polish

## Learner UI Concept

### Learner design goal
Make the learner experience feel like a simple learning quest: colorful, friendly, easy to scan, and motivating without changing the underlying lesson, activity, submission, or progress logic.

### Main learner screens
- Learner dashboard as the Learning Quest Hub.
- Lesson and activity pages as missions and challenges.
- Progress page as the learner achievement journey.

### Gamification elements
- Mission cards for lessons and activities.
- Completed mission counters from real completion data.
- Progress bars and completion percentages from existing lesson/activity data.
- Score and star-style labels derived from existing submissions where available.
- Encouraging mission language for start, continue, complete, and achievement states.

### Accessibility considerations
- Larger touch targets for mission actions.
- Text labels beside icon-style visual elements.
- Focus outlines for keyboard navigation.
- Reduced visual clutter and short learner-facing labels.
- Motion kept lightweight, with reduced-motion support in CSS.

### Demo limitations
- No new database fields were added.
- Points and star wording are visual/computed from existing submissions and completion status.
- FSL/sign recognition is not included in this phase.
- The learner flow still depends on existing demo activities, lesson plans, and learner accounts.

## Learner POV Demo Flow

1. Log in using an existing verified learner demo account.
2. Open the learner dashboard.
3. Show the Learning Quest Hub, mission stats, continue learning card, and mission cards.
4. Open a lesson from My Lessons.
5. Show the Learning Mission Hub, learning path, material cards, and activity mission cards.
6. Open a material in the maximized viewer, then close it.
7. Click Start Mission or Continue Mission.
8. Complete the displayed activity using the game-style activity player.
9. Submit using Submit Mission.
10. Open View Progress.
11. Show the compact achievement journey, mission logs, progress indicators, and score/star values.

## What To Click First
- From the learner dashboard, click the primary Continue Learning button if a next lesson is available.
- If no continue card is available, choose any visible lesson mission card and click Start Mission.
- Inside the lesson page, open the first material card before starting the first activity mission.

## What Evaluators Should Notice
- The learner pages now feel more like a game-like quest flow than a plain LMS list.
- The interface uses existing real lesson, activity, progress, and submission data.
- The activity completion flow is still the original backend submission flow.
- The progress page explains achievement using available learner records.
- The lesson page separates learning guides from activity missions and uses the same mission language as the activity player.
- The material viewer opens in a near-fullscreen modal with an Open in New Tab fallback.

## How Gamification Works
- Completed activities come from existing lesson progress and submission data.
- Progress percentages come from completed activities compared with available activities.
- Latest score comes from existing progress/submission values when present.
- Mission labels, stars, and achievement wording are visual polish layered on top of existing data.
- Lesson mission cards use existing `lms_activities` records and learner submission status.
- Progress logs use `lms_submissions` first and `lms_grades` when a teacher grade is available.

## Real Data vs Visual/Computed
- Real: learner name, lesson titles, activity titles, activity counts, completion status, scores/submissions where available.
- Computed: completion percentage, remaining mission count, dashboard summary labels.
- Visual only: quest wording, mission styling, achievement labels, hover effects.
- Computed visual values: stars based on score percentage when a score and max score exist.

## Known Limitations
- If no learner activities are assigned, the dashboard shows the existing empty state.
- If no submissions exist, the progress page shows the existing no-progress guidance.
- Browser behavior still needs final manual confirmation with Laragon Apache/MySQL running.
- The progress page does not create new progress records; it displays existing submission and grade data only.

## Lesson Page Changes
- Replaced the tabbed list layout with a compact Learning Mission Hub.
- Added lesson hero stats for materials, activities, and completed missions.
- Added a simple learning path: Read Material, Start Activity, Complete Mission, View Progress.
- Converted materials into learning guide cards.
- Converted activities into mission cards with status, type, points/score, due date, and Start/Review actions.

## Material Viewer Changes
- File, YouTube, and Google Drive preview materials can open inside a near-fullscreen modal.
- Viewer uses about 95vw by 90vh on desktop and remains responsive on mobile.
- Viewer includes Close and Open in New Tab controls.
- Fallback text tells the learner to open the file in a new tab if it does not load.

## Progress Page Alignment Fixes
- Rebuilt the progress page as a compact Achievement Journey.
- Header and Back button are aligned in one row on desktop and stack cleanly on mobile.
- Stat cards use equal compact sizing and responsive columns.
- Overall progress is smaller and aligned with progress text instead of a large empty card.
- Recent submissions are displayed as compact mission log cards.

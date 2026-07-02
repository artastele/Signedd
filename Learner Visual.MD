# SignED Learner Dashboard — UI/UX Redesign Prompt

> Paste this prompt into your AI coding tool (e.g. Claude Code) or hand it to your designer/dev. It describes **visual and layout changes only**. No backend logic, routes, API calls, state, or data bindings should change.

---

## 1. Context

This is the **learner-facing dashboard** of SignED, a Filipino Sign Language (FSL) learning platform. Learners are **PWDs (persons with disabilities)**. The current dashboard reuses the same layout pattern as the admin/instructor account (red sidebar, plain white cards, dense stat boxes), which makes it feel clinical and indistinguishable from a back-office tool.

The goal is to redesign the learner experience to feel **distinct, alive, interactive, and welcoming** — built specifically for PWD and child learners — without touching any underlying functionality.

## 2. Hard Constraints (do not break these)

- **Do not change any function, route, API call, data binding, or business logic.** Every button must still trigger the exact same action it does today (Start Mission, Submit Mission, label inputs, sequence answers, star scoring, badges, notifications, logout, etc.).
- Treat this strictly as a **presentation/UX layer redesign** — component structure can be reshuffled, but the data it displays and the actions it triggers must remain identical.
- All existing pages must keep working: Dashboard, My Lessons, My Badges, Activity/Mission pages (Image Label Mission, Sequence Question, Mission Review/Submit).

## 3. Structural Changes (explicit removals)

- **Remove the left sidebar entirely.** No persistent red vertical nav. Free up the full width of the screen for the learner.
- **Remove the "My Progress" widget/tab** (the circular % chart and its tab). Do not replace it with another progress chart — the redesign should de-emphasize raw percentage tracking entirely in favor of friendlier, achievement-based feedback (see Section 6).
- Navigation that used to live in the sidebar (Dashboard, My Lessons, My Badges, Logout) needs a new, non-sidebar home — see Section 5.

## 4. Visual Direction

- **Cartoonish, colorful, full of life** — rounded shapes, soft drop shadows, bouncy/playful illustrations, no sharp corporate edges.
- A friendly **mascot character** (e.g. a cheerful sign-language buddy) should anchor the experience — greeting the learner, pointing at the next mission, celebrating wins.
- Warm, energetic but **not overstimulating** color palette — bright accent colors against calm, high-contrast backgrounds (avoid pure white glare and avoid harsh neon combinations).
- Rounded, legible typography (think Baloo, Quicksand, Fredoka, or similar friendly-but-readable type) — large default sizes.
- Generous spacing and big illustrated icons rather than small line icons.
- Encouraging, plain-language copy everywhere (e.g. reframe "0 Missions Done" as something like "Your first mission is waiting! 🌟" instead of a bare zero).

## 5. Navigation Without a Sidebar

Replace the sidebar with one of (pick whichever fits the stack best):

- A **bottom tab bar** (mobile-app style): large icon + label buttons for Home, My Lessons, My Badges. Thumb-friendly, familiar pattern, minimal cognitive load.
- OR a **simple top pill-nav**: 2–3 big rounded buttons with icon + text label, high contrast, clearly highlighting the active tab.

Either way:
- Each nav target needs a big tap target (48px+ height minimum), icon **and** text label (never icon-only).
- Logout and account info can move into a simple profile menu in the top-right corner (keep current function, just restyle).

## 6. General PWD Accessibility Requirements

- **Contrast & color:** Text/background contrast ratio of at least 4.5:1. Never use color as the *only* signal (e.g. pair "complete" with a checkmark icon + label, not just a green tint).
- **Touch targets:** All interactive elements (buttons, inputs, nav items) at least 44–48px in height/width, with clear spacing between them to avoid mis-taps.
- **Motion safety:** No strobing, rapid flashing, or harsh auto-playing animations (seizure-safety). Celebratory animations (confetti, stars, mascot bounce) should be smooth and brief, and ideally respect "reduce motion" settings.
- **Text scaling:** Layout should not break if text size is increased; avoid fixed-height containers that clip enlarged text.
- **Keyboard & focus:** Every interactive element needs a visible focus state and must be reachable/operable via keyboard, not just touch/mouse.
- **Simple, predictable layout:** Consistent placement of "next action" buttons across pages (e.g. primary CTA always bottom-center or bottom-right). Reduce the number of choices/options visible at once.
- **Plain language:** Short sentences, no jargon, friendly tone, instructions stated positively ("Tap to start!" not "Click here to initiate session").
- **No timed pressure:** Avoid countdown timers or auto-advancing screens unless the learner explicitly starts them.

## 7. Specialized for Deaf / Hard-of-Hearing Learners

Since the platform teaches FSL, design **visual-first, with no dependency on sound**:

- **Never rely on audio alone for feedback or alerts.** Every notification (e.g. the bell icon, "Saved", "Mission complete!") must have a strong **visual** signal — animated badges, color pulses, on-screen banners, mascot reactions — not a sound cue as the primary indicator.
- If any video or animated sign demonstration is used, always include **captions or text labels**; never assume audio narration will be heard.
- Use **clear iconography + short text together** (icon-only meanings can be ambiguous without sound cues to reinforce them).
- Celebrate achievements **visually and energetically** — confetti bursts, mascot cheering, stars filling up, color bursts — since these learners can't rely on cheerful sound effects to feel rewarded.
- Favor **visual sequencing/progress indicators** (filled stars, progress paths, badge unlocks) over numeric percentages, which feel sterile and abstract.

## 8. Child-Friendly Elements

- Mascot/character present throughout, with simple personality (waves hello, cheers on completion, gently encourages on a "not started" lesson).
- Badges and stars should look like **collectible rewards** (shiny, glowing, slightly animated on unlock) rather than flat icons.
- Avoid any shaming language for incomplete progress — always frame as an invitation, not a deficiency.
- Sound-free celebratory micro-interactions (bounce, sparkle, confetti) on key moments: starting a mission, completing a question, finishing a mission.

## 9. Component-by-Component Guidance (map to existing pieces)

| Current Element | Redesign Direction |
|---|---|
| "Hi Ana! Ready to learn something awesome today?" banner | Keep the greeting function, but anchor it with the mascot illustration, a friendly wave animation, and bigger, warmer typography. |
| "Continue Learning" / Start Mission card | Make this the clear visual hero of the page — large illustrated card, mascot pointing at the CTA, big rounded "Start Mission" button. |
| Stat cards (Lesson Quests / Missions Done / Latest Power) | Turn into playful trophy/badge-style cards with icons and small illustrations instead of plain numbers in boxes — same data, more visual personality. |
| "My Progress" circular % chart | **Remove entirely** per Section 3 — do not reintroduce a percentage-based widget. |
| Lesson cards (FSL, etc.) with "Start Mission" | Illustrated cards per lesson topic, large readable title, friendly progress shown as filled stars/steps rather than a thin numeric bar, big CTA button. |
| My Lessons / My Progress / My Badges tabs | Becomes the new bottom/top nav (Section 5) — drop "My Progress" tab, keep My Lessons and My Badges. |
| Mission/Activity pages (Image Label Mission, label inputs, Sequence Question, star rating, Submit Mission) | Keep all logic and inputs as-is; restyle with larger input fields, friendlier card framing, and a clear celebratory state on "Mission complete!" (confetti + mascot cheer + visual "Saved" confirmation) instead of the current plain text confirmation. |

## 10. Deliverable Expectations

- Apply this redesign **only** to the learner-facing dashboard and mission/activity pages shown in the reference screenshots.
- Preserve every existing route, API call, data field, and button action exactly as-is.
- This is a **visual/UX layer redesign only** — component markup may be restructured, but no functional behavior should change.
---
inclusion: auto
description: Process 12 — Individual Transition Goal Plan (ITGP). Fully digital, authored by General Teacher with SPED Teacher reference + comment thread. Use when working on ITGP goals, activities table, or SPED Teacher consultation features.
---

# Process 12 — Individual Transition Goal Plan (ITGP)

> DO NOT ALTER WITHOUT APPROVAL — Process 12
> One feature at a time. Describe → ask → build → test → approve.
> Processes 1–11 are LOCKED. Never touch their files during Process 12 work.

---

## IMPORTANT — author is General Teacher, not SPED Teacher

Unlike every prior process, the PRIMARY AUTHOR of the ITGP is the
**General Teacher** (the receiving regular-class teacher), not the SPED
Teacher. SPED Teacher's role here is reference + consultation only — their
IEP/PDSP/ITP records are shown as read-only panels, plus a comment thread
for the General Teacher to ask questions.

> ⚠️ Confirm before building: `general_teacher` role does not yet exist in
> RBAC (`/config/permissions.php`, `users.role`). It needs the same
> verification flow as Guidance/Principal/Master Teacher (upload ID +
> designation document, admin approval) before this process can function.
> Flag this and get explicit approval before creating the role.

---

## Trigger
Process 12 unlocks for a student right after Process 11 (ITP) is finalized.
General Teacher must be assigned to this student to access it (assignment
mechanism — confirm during build: does SPED Teacher/Principal assign a
General Teacher to a student, or does General Teacher self-select from a
pending list? Ask user before building Feature 1).

This runs IN PARALLEL with the rest of the transition process — General
Teacher works on ITGP while ITP is still being acted on, ahead of the final
Process 13 placement decision.

---

## Reference form
Individual Transition Goal Plan — fully digital, no upload, no print, no
physical signature (same pattern as COT and ITP digital sections).

---

## Feature 1 — Reference panels (SPED Teacher data, read-only)

Before/alongside the ITGP form, General Teacher sees collapsible reference
panels (same UX pattern as the PDSP/IEP reference panels used in earlier
processes):
- **IEP panel** — signed IEP document + goals/steps table (read-only)
- **PDSP panel** — signed PDSP domains and ratings (read-only)
- **ITP panel** — finalized ITP: Point of Entry, Recommendations (Beginning
  and End of SY), Strengths/Interests/Talents/Skills/Needs (read-only)

Panels collapsible, open by default on screens wider than 1200px, "View"
buttons open modals on mobile — consistent with existing reference panel pattern.

---

## Feature 2 — ITGP form

Header (auto-filled from `student_records`, read-only display):
- Learner name
- Disability

Core fields (General Teacher fills):
- Goal (textarea) — pre-filled as a suggestion from ITP's Recommendations,
  General Teacher edits/confirms
- Entry Point (text or dropdown) — pre-filled from ITP's Point of Entry,
  editable
- Learning Package/s (text or multi-select)

Activities table (dynamic rows — same "Add row" pattern as IEP steps table):
- Competency/Skill
- Activities
- Time Frame
- Person Responsible
- Remarks

Recommendations box (textarea, free text).

Save as draft anytime — editable until finalized.

---

## Feature 3 — Comment/question thread

Simple threaded comments attached to this ITGP record:
- General Teacher posts a question ("Can you advise on pacing for the
  fine motor goal?")
- SPED Teacher (still has access to view + comment on this specific
  student's ITGP, even though they're not the primary author) replies
- Comments shown in chronological order, simple text + timestamp + author
  name/role badge
- In-system notification sent to the other party when a new comment is posted
- No file attachments needed in comments — text only, keep it simple
- Comment thread remains visible/active even after ITGP is finalized
  (for historical reference, just no longer affects the document content)

---

## Feature 4 — Finalize

"Finalize ITGP" button (crimson, full width) — General Teacher only.

Validation on click:
- [ ] Goal filled
- [ ] Entry Point filled
- [ ] At least one activity row complete (Competency/Skill + Activities +
      Time Frame + Person Responsible all filled)
- [ ] Recommendations filled

Confirmation modal: "Finalize this ITGP? This marks the transition goal
plan as ready and prepares this student for class placement review."

On confirm:
- `itgp_records.status` → finalized
- `itgp_records.finalized_at` → timestamp
- All fields read-only (comment thread remains active)
- In-system notification to SPED Teacher, Principal: ITGP finalized
- Process 13 (Class Placement) becomes accessible for this student — General
  Teacher's finalized ITGP + the rest of the transition chain feeds into the
  Process 13 summary view

---

## DB Tables (schema.sql only — show migration diff first)

```sql
itgp_records      — id, student_id, itp_id, general_teacher_id (user_id),
                    goal, entry_point, learning_packages, recommendations,
                    status (draft/finalized), finalized_at (nullable),
                    created_at, updated_at

itgp_activities   — id, itgp_id, competency_skill, activities, time_frame,
                    person_responsible, remarks, display_order

itgp_comments     — id, itgp_id, posted_by (user_id), comment_text,
                    created_at

general_teacher_assignments — id, student_id, general_teacher_id,
                              assigned_by (user_id), assigned_at
                              (table needed regardless of which assignment
                              mechanism is chosen in Feature 1 — confirm
                              direction with user before building)
```

---

## RBAC

- `general_teacher`: full edit on ITGP form, post comments, finalize
- `sped_teacher`: view ITGP (read-only), post comments — no edit on form fields
- `principal`: view finalized ITGP
- `parent`: NOT shown this process — internal teacher-to-teacher document

Add to `/config/permissions.php` (after general_teacher role is approved):
```php
'general_teacher' => [..., 'itgp.manage', 'itgp.finalize', 'itgp.comment'],
'sped_teacher'    => [..., 'itgp.view', 'itgp.comment'],
'principal'       => [..., 'itgp.view'],
```

---

## UI rules

- Color scheme #a01422 crimson, #1e4072 navy
- Reference panels: same collapsible style as IEP/PDSP panels elsewhere
- Activities table: navy header row, dynamic add/remove rows
- Comment thread: simple chat-style list, navy bubble for SPED Teacher,
  crimson-accented bubble for General Teacher, role badge on each comment
- Finalize button only visible/enabled for general_teacher role

---

## Build order (one feature at a time — describe and ask before building)

1. **Flag and resolve `general_teacher` role creation** — confirm RBAC
   addition + verification flow before any other step
2. schema.sql migration — itgp_records, itgp_activities, itgp_comments, general_teacher_assignments
3. General Teacher assignment mechanism (confirm direction with user first)
4. Reference panels — IEP, PDSP, ITP read-only views
5. ITGP form — header, Goal/Entry Point/Learning Package (pre-filled from ITP), activities table
6. Comment/question thread
7. Finalize flow — validation, lock, notify, unlock Process 13

---

## Self-check before presenting any code

- [ ] Processes 1–11 files not touched
- [ ] general_teacher role creation explicitly flagged and approved before use
- [ ] Process 12 unlocks right after Process 11 finalized — not gated on Process 13
- [ ] General Teacher is the primary author — SPED Teacher has view + comment only
- [ ] Reference panels show IEP, PDSP, and ITP data correctly, read-only
- [ ] Goal and Entry Point pre-fill from ITP, fully editable
- [ ] Activities table dynamic rows work correctly
- [ ] Comment thread works both directions, notifies on new comment
- [ ] Comment thread stays visible/active after finalize
- [ ] Finalize locks the form but not the comment thread
- [ ] Process 13 becomes accessible only after ITGP finalized
- [ ] Parent NOT shown this process
- [ ] schema.sql is the only place for DB changes
- [ ] Prepared statements for all DB operations
- [ ] Color scheme #a01422 and #1e4072 — no default Bootstrap blue or gray
- [ ] No blank pages or raw errors shown to user

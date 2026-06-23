---
inclusion: auto
description: Process 13 — Generating Regular Class Placement. Notification-only confirmation action by General Teacher. Use when working on placement summary view, confirm/hold actions, or student archiving.
---

# Process 13 — Generating Regular Class Placement

> DO NOT ALTER WITHOUT APPROVAL — Process 13
> One feature at a time. Describe → ask → build → test → approve.
> Processes 1–12 are LOCKED. Never touch their files during Process 13 work.
> This is the simplest process — a summary review + confirm/hold action,
> not a form. No new document is created beyond a notification record.

---

## Trigger
Process 13 becomes accessible for a student once Process 12 (ITGP) is
finalized by the General Teacher.

---

## Feature 1 — Summary snapshot view

Before the confirm/hold action, General Teacher (or whoever has access —
confirm this is General Teacher only, same as the one assigned in Process 12)
sees a read-only summary pulling from the full transition chain:

- **Student header** — name, LRN, current grade level, photo if available
- **IEP summary** — key goals from the signed IEP (Process 5)
- **Transition Readiness result** — overall Ready/Partial/Not Ready (Process 10)
- **ITP summary** — Point of Entry selected, Recommendations (Beginning and
  End of SY) (Process 11)
- **ITGP summary** — Goal, Entry Point, Learning Package, key activities
  (Process 12)

All shown as compact read-only cards/sections — this is a review screen,
not editable. Single scroll-through view so General Teacher can make an
informed decision before confirming.

---

## Feature 2 — Confirm or Hold action

Two buttons at the bottom of the summary view:

**"Confirm Placement"** (crimson, primary):
- Confirmation modal: "Confirm regular class placement for [student name]?
  This will archive their record from active SPED tracking and notify the
  parent."
- On confirm:
  - `class_placements.status` → confirmed
  - `class_placements.confirmed_at` → timestamp
  - `student_records.status` (or equivalent flag) → 'mainstreamed' / archived
    from active SPED LMS tracking (per earlier decision — archive only, no
    certificate)
  - In-system notification + PHPMailer to Parent: "Your child has been
    recommended for regular class placement"
  - In-system notification to SPED Teacher, Principal: placement confirmed
  - Student no longer appears in active SPED Teacher dashboards/learner
    lists — moved to an "Archived / Mainstreamed" filtered view instead

**"Not Ready / Hold"** (navy outline, secondary):
- Opens a reason field (textarea, required): "Why is this student not yet
  ready for placement?"
- On submit:
  - `class_placements.status` → on_hold
  - `class_placements.hold_reason` → entered text
  - In-system notification to SPED Teacher and General Teacher's supervisor
    (confirm: Principal?) explaining the hold reason
  - Student remains in active tracking — NOT archived
  - General Teacher (or SPED Teacher) can revisit and re-attempt placement
    later — the summary view + confirm/hold action remains accessible again

---

## Feature 3 — Placement history / record

Simple log accessible to SPED Teacher, Principal, General Teacher:
- Shows all placement attempts for a student (confirm and hold entries)
- Each entry: date, who acted, decision, hold reason if applicable
- Useful if a student goes through hold → re-evaluation → confirm later

---

## DB Tables (schema.sql only — show migration diff first)

```sql
class_placements — id, student_id, itgp_id, reviewed_by (user_id, General Teacher),
                   status (confirmed/on_hold), hold_reason (nullable),
                   confirmed_at (nullable), created_at, updated_at
```

Also requires one column addition to the existing `student_records`/`users`
table (confirm exact table/column name with user before building) to flag
a student as archived/mainstreamed — e.g. `student_status` ENUM
('active','mainstreamed') or similar. Show this as part of the schema diff,
do not silently alter the existing locked table without explicit approval
since it touches a Process 1–2 table.

---

## RBAC

- `general_teacher`: view summary, confirm or hold
- `sped_teacher`: view placement history, view summary (read-only)
- `principal`: view summary, view placement history, receives hold notifications
- `parent`: receives confirm notification only — no direct page access

Add to `/config/permissions.php`:
```php
'general_teacher' => [..., 'class_placement.review', 'class_placement.confirm'],
'sped_teacher'    => [..., 'class_placement.view'],
'principal'       => [..., 'class_placement.view'],
```

---

## UI rules

- Color scheme #a01422 crimson, #1e4072 navy
- Summary cards: white bg, navy left-border accent per section (IEP/Readiness/ITP/ITGP)
- Confirm button: crimson, full width
- Hold button: navy outline, full width, secondary position
- Archived/mainstreamed students: shown with a distinct gray "Mainstreamed"
  badge wherever they might still appear in lists (e.g. historical records)

---

## Build order (one feature at a time — describe and ask before building)

1. schema.sql migration — class_placements table + student archive flag
   (show diff against existing student table clearly, get explicit approval
   since this touches a locked Process 1–2 table)
2. Summary snapshot view — pulling IEP/Readiness/ITP/ITGP data read-only
3. Confirm Placement action — modal, status update, archive flag, notifications
4. Not Ready/Hold action — reason field, status update, notifications
5. Placement history log view

---

## Self-check before presenting any code

- [ ] Processes 1–12 files not touched
- [ ] Process 13 only accessible after ITGP (Process 12) finalized
- [ ] Summary view is read-only — no editing of IEP/ITP/ITGP data here
- [ ] Confirm action archives student correctly without deleting any data
- [ ] Confirm sends notification to Parent (in-system + PHPMailer), SPED Teacher, Principal
- [ ] Hold action requires a reason, does NOT archive the student
- [ ] Hold notifies the appropriate supervisor role
- [ ] Held students remain accessible for re-attempt later
- [ ] No certificate or document generated — notification only, as specified
- [ ] Student table modification explicitly approved before applying (touches locked table)
- [ ] schema.sql is the only place for DB changes
- [ ] Prepared statements for all DB operations
- [ ] Color scheme #a01422 and #1e4072 — no default Bootstrap blue or gray
- [ ] No blank pages or raw errors shown to user

# SPED LMS — Process Status & Pending Work
> Last updated: 2026-05-08
> DO NOT DELETE — reference document for development progress

---

## ⛔ LOCKED — Processes 1, 2, 3, 4 (Complete)

### Process 1 — Parent Enrollment ✅
- Multi-step form (7 steps), draft save, document upload
- Returning student lookup
- Parent dashboard with LRN confirmation card (shows when learner_account_created = 1)
- Enrollment status tracking, rejection feedback

### Process 2 — Enrollment Verification ✅
- SPED Teacher reviews, approves/rejects per document
- Student record + LRN auto-generated
- Learner account created, credentials emailed to parent

### Process 3 — Initial Assessment ✅
- Part I form with auto-fill from student records
- Services checklist + dynamic MDT table
- File upload per service (stored in public/uploads/assessments/)
- Assessment versioning — grouped by student in index view
- Assessment history per student

### Process 4 — IEP Meeting ✅
- Availability calendar (recurring + exceptions + task notes)
- IEP meeting dates auto-marked on calendar
- Meeting scheduling with suggested dates
- PHPMailer notifications to all participants
- PDSP form (6 DepEd domains, handwritten upload)
- PDSP viewable by Guidance/Principal once uploaded
- Meeting edit (SPED Teacher only)
- Meeting cancellation (SPED Teacher, Guidance, Principal — parent cannot cancel)
- Upcoming vs Past meetings based on status
- AI extraction removed (redundant)

---

## ⚠️ IN PROGRESS — Process 5 (IEP Generation)

### What's built:
- Unified IEP Documents dashboard (`/iep/documents`) per role
- IEP document creation (P3 form — basic fields)
- Signature page with separate pad per role (sped_teacher, parent, guidance, principal)
- Auto-marks `signed_approved` when all 4 sign
- Principal approval queue
- Sidebar consolidated

### ⏳ PENDING — waiting for IEP form design from user:
- **Actual IEP form fields/layout** — user will provide the DepEd IEP form design
- **Auto-fill from Process 3 & 4** — pull assessment data + PDSP recommendations into IEP
- **Document locking** after all 4 signatures (schema supports, logic not wired)
- **Rename all "P3" labels** → "Individualized Education Plan" throughout

---

## ⏳ NOT STARTED — Process 6 & 7

### Process 6 — Implementing IEP
- Assign IEP to learner
- Upload learning materials per domain
- Track implementation progress

### Process 7 — Learner Learning Activities
- Learner dashboard with modules
- Activity completion tracking
- Progress reports

---

## 🔧 Known Issues / Fixes Applied This Session

| Issue | Fix | Status |
|-------|-----|--------|
| `meeting_time` column not found | Combined date+time into DATETIME | ✅ Fixed |
| `review_note` missing from enrollment_submissions | Schema migration v1.36 | ✅ Fixed |
| `ADD COLUMN IF NOT EXISTS` MariaDB error | Replaced with INFORMATION_SCHEMA checks | ✅ Fixed |
| `CREATE INDEX IF NOT EXISTS` MariaDB error | Replaced with INFORMATION_SCHEMA checks | ✅ Fixed |
| Assessment documents not saving | Fixed file path (public/uploads/) + DataTransfer re-attach | ✅ Fixed |
| Student list empty info | Fixed JOIN to use enrollment_id not lrn | ✅ Fixed |
| Meeting location showing "Online" | Fixed to read meeting_location column | ✅ Fixed |
| Meeting edit not saving | Fixed allowedFields in IEPMeetingModel::update() | ✅ Fixed |
| Meeting edit sets status to rescheduled but enum missing it | Schema migration v1.38 | ✅ Fixed |
| iep_meetings status enum missing 'rescheduled' | Fixed in schema.sql base definition | ✅ Fixed |
| Assessment index showing all versions flat | Grouped by student, History button per student | ✅ Fixed |
| AI extraction button in PDSP | Removed (redundant) | ✅ Fixed |
| Meeting cancellation not built | Added cancelMeeting() + route + UI | ✅ Fixed |

---

## 📋 Schema Migrations Applied

| Version | Description |
|---------|-------------|
| v20-21 | Base schema |
| v22 | User security columns |
| v23-24 | Assessment Section A columns |
| v25-27 | Process 4 tables |
| v28-30 | Assessment conducted_by, updated_at |
| v31-33 | PDSP updates |
| v34-36 | review_note columns |
| v37 | user_availability.note column |
| v38 | iep_meetings status enum + rescheduled |

---

## 🗄️ Schema Import Notes (for PC transfer)

The `config/schema.sql` file is self-contained. To import on a new machine:

```bash
mysql -u root -p sped_lms < config/schema.sql
```

**Known compatibility issues fixed:**
- All `ADD COLUMN IF NOT EXISTS` replaced with INFORMATION_SCHEMA prepared statements (MariaDB < 10.3 compatible)
- All `CREATE INDEX IF NOT EXISTS` replaced with INFORMATION_SCHEMA checks
- `SET FOREIGN_KEY_CHECKS = 0` at top prevents FK order issues
- All tables use `CREATE TABLE IF NOT EXISTS`

**If you get errors on import:**
1. Make sure the database exists first: `CREATE DATABASE sped_lms CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;`
2. Run with `--force` flag to skip individual statement errors: `mysql -u root -p --force sped_lms < config/schema.sql`
3. The SchemaManager will apply any remaining migrations on first app boot

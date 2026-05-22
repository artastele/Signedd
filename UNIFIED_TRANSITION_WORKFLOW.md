# SignED Unified Transition + IEP Workflow

## Purpose

This workflow connects the six new transition processes to the existing SignED IEP, implementation, learner activity, progress, and notification flow.

Existing IEP -> IEP Implementation -> Learner Activities / Progress -> Progress Report Card -> COT Observation -> Transition Readiness Evaluation -> Individual Transition Plan -> Inclusive IEP + ITGP -> Regular Class Placement / Transfer Notice -> Notification to selected receiving teacher.

## Data Reused

- `student_records`: learner profile, LRN, enrollment link.
- `enrollment_submissions`: parent/guardian, school year, grade level when available.
- `iep_records`: current IEP record and school year.
- `iep_steps`: existing IEP goals/objectives when available.
- `lesson_plans`, `lms_activities`, `lms_submissions`: learner implementation and activity progress.
- `notifications`: exact user notification delivery.

## New Tables

- `progress_reports`: Progress Report Card summary linked to `student_id` and `iep_record_id`.
- `cot_observations`: COT Rating Sheet linked to IEP, learner, observed teacher, and creator.
- `transition_readiness`: readiness decision linked to IEP, progress report, and COT if available.
- `individual_transition_plans`: ITP linked to readiness and IEP.
- `inclusive_iep_records`: inclusive IEP record generated from the original IEP plus transition records.
- `itgp_records`: ITGP header linked to inclusive IEP, readiness, and ITP.
- `itgp_items`: ITGP goal rows.
- `placement_notices`: placement/transfer notice linked to inclusive IEP, ITGP, readiness, and exact `receiving_teacher_id`.

Runtime table creation is handled by `TransitionWorkflowModel::ensureTables()` for demo stability. `config/schema.sql` records v47 and the table list.

## Routes

- `GET /iep/{id}/transition-workflow`
- `GET|POST /iep/{id}/progress-report`
- `GET|POST /iep/{id}/cot-observation`
- `GET|POST /iep/{id}/transition-readiness`
- `GET|POST /iep/{id}/individual-transition-plan`
- `GET|POST /iep/{id}/inclusive-iep-itgp`
- `GET|POST /iep/{id}/placement-notice`

## Status Flow

1. Progress Report: `draft` -> `finalized`
2. COT Observation: `draft` -> `finalized`
3. Transition Readiness: `draft` -> `finalized`
4. ITP: `draft` -> `completed`
5. Inclusive IEP + ITGP: `draft` -> `for_signature` -> `signed`
6. Placement Notice: `Draft` -> `For Approval` -> `Approved` -> `Notice Sent` -> `Placed`

## Notification Flow

- COT finalization notifies the selected observed teacher only.
- Placement notice with status `Notice Sent` or `Placed` notifies only the selected `receiving_teacher_id`.
- Parent notification is sent only if a parent account is linked through the learner enrollment.

## Demo Flow

1. Open an existing signed IEP.
2. Open `Transition workflow` from the IEP repository or IEP implementation workspace.
3. Save/finalize a Progress Report Card using existing activity submission evidence.
4. Save/finalize COT and select the exact observed teacher.
5. Review Transition Readiness; the page shows a suggested result from learner submission evidence.
6. Create the ITP from learner and readiness data.
7. Generate Inclusive IEP + ITGP from existing IEP plus readiness/ITP.
8. Generate Placement Notice and select the exact receiving teacher account.
9. Set placement status to `Notice Sent` to send notification to that selected account only.

## Known Limitations

- The current `users.role` enum has no `general_teacher` role. The implementation uses active `sped_teacher` and `master_teacher` accounts as selectable teacher accounts until a general teacher role is formally added.
- Liveform layouts are represented as connected data-entry sections, not exact pixel-perfect replicas.
- Full browser verification requires authenticated demo accounts.

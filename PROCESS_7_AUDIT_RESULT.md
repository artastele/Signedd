# PROCESS 7 AUDIT RESULT

Date: 2026-06-15
Scope: Learning Outcomes / Progress Report audit only. No implementation changes made.

## Files inspected
- app/Controllers/LearningController.php
- app/Controllers/TransitionWorkflowController.php
- app/Models/LessonPlanModel.php
- app/Models/LearnerProgressModel.php
- app/Models/TransitionWorkflowModel.php
- app/Models/IEPModel.php
- app/Views/transition/workflow.php
- config/schema.sql
- UNIFIED_TRANSITION_WORKFLOW.md
- routes/web.php

## Existing grading sources
- Primary grading source: lms_submissions + lms_grades
  - lms_submissions stores learner answers, auto_score, submitted_at
  - lms_grades stores teacher/manual score override, max_score, is_complete, remarks
- Supporting data: lesson_plans, lesson_assignments, lms_activities
  - These tables already tie activities to IEPs/learners and can supply assigned/completed counts, score percentages, and submission history
- Existing summary views already compute activity counts and average score from these tables in LessonPlanModel::getStudentOverallProgress()
- No dedicated teacher-gradebook page exists yet; the current data is spread across LMS and transition workflow code paths

## Existing attendance sources
- Current attendance evidence is not a dedicated attendance table
- The only existing event-based attendance-like source is lms_logs
  - action values are 'opened', 'submitted', 'graded'
- The transition workflow currently stores manual attendance notes in progress_reports.attendance_summary
- There is no existing table for daily attendance, per-day status (Present/Absent/Late/Excused), or F2F attendance records
- Conclusion: online attendance can be approximated from lms_logs and lms_submissions, but it is not yet a real attendance system and cannot fully support the required day-by-day F2F attendance model

## Existing IEP goal structure
- Existing IEP goals/objectives are stored in iep_steps
  - columns include step_number, step_domain, step_objective, instructional_evaluation, observation, observation_unlocked
- IEP step-to-lesson-plan links exist via iep_step_lesson_plans and iep_step_materials
- There is no separate goal-progress table for automatic IEP-goal completion tracking at this time
- Therefore, the current IEP goal structure is a step/objective repository, not yet an activity-to-goal progress mapping layer

## F2F support feasibility
- Feasibility is limited in the current LMS architecture
- The lms_activities schema only supports LMS activity types such as multiple_choice, true_false, fill_in_blanks, matching, drag_drop_sort, image_label, flashcards, sequencing
- There is no delivery_mode, modality, attendance_type, venue, or F2F activity field in lms_activities or lms_submissions
- Enrollment data does contain modality_face_to_face in enrollment_submissions, but that is enrollment/modality context, not LMS activity delivery
- Conclusion: F2F activity support is not currently built into the LMS activity architecture. It would require a new schema/logic decision if the team wants actual F2F activity records or attendance tracking

## Attendance feasibility
- Online attendance ("Activity Submitted = Present (Online)") can be derived from existing submissions and logs, but only as an approximation
- The current LMS model can tell whether a learner opened or submitted an activity, but it does not store a daily attendance status or a reliable 'not login / not activity opened' record for every school day
- F2F attendance support is not feasible with current tables alone because there is no daily attendance table or status field for Present / Absent / Late / Excused
- Safe conclusion: attendance summaries can be generated from existing data, but true attendance reporting would require explicit schema additions

## Progress Report architecture
- A progress report workflow already exists in the transition workflow layer
- progress_reports is an existing table created by TransitionWorkflowModel
- It stores:
  - school_year
  - quarter
  - attendance_summary
  - progress_summary
  - teacher_remarks
  - ratings (JSON)
  - status (draft / finalized)
- The current transition rendering in app/Views/transition/workflow.php shows that progress reporting is already wired into the IEP workflow
- However, the current structure is manual and summary-only; it does not yet compute:
  - activities assigned
  - activities completed
  - missing activities
  - completion rate
  - average score
  - IEP goal progress
  - strengths / areas for improvement / behavioral observation / recommendations as structured fields

## Transition Readiness dependency
- The transition_readiness table links to progress_reports via progress_report_id
- The current transition workflow does not enforce the rule that a finalized progress report must exist before creating readiness, nor does it block readiness creation when the linked progress report is finalized
- The current controller saves readiness without checking whether the linked progress report status is finalized or whether it should be non-finalized
- Safe enforcement path: validate this in the controller/model before save, using the existing progress_reports.status field; no new schema is required for the dependency rule itself

## Required database changes
No schema changes are required for the audit itself.
For the full Process 7 implementation described in the request, the likely schema additions would be:
1. Extend progress_reports with structured fields for:
   - activities_assigned
   - activities_completed
   - missing_activities
   - completion_rate
   - average_score
   - strengths
   - areas_for_improvement
   - behavioral_observation
   - recommendations
2. Add a dedicated attendance table or daily attendance log if F2F attendance is required, with:
   - student_id
   - attendance_date
   - status (Present / Absent / Late / Excused)
   - modality (Online / F2F)
   - source (submission / manual entry)
3. Add an explicit goal-to-activity mapping table later if automated IEP goal progress is required

## Safe changes
- Reuse existing lms_submissions, lms_grades, lms_logs, lesson_plans, lesson_assignments, iep_steps, and progress_reports
- Build a teacher-gradebook view on top of existing LMS data only
- Use current progress_reports.status as the gating mechanism for finalized vs draft states
- Add parent-view restrictions based on progress report status rather than inventing a new reporting layer
- Use existing IEP step links as the first reference point for any future goal progress integration

## Risky changes
- Creating new grading/attendance/progress tables that duplicate the current LMS data model
- Altering lms_activities to force F2F support without a verified schema design
- Adding a separate goal-progress system before confirming how activities map to iep_steps
- Implementing a parallel attendance engine that does not reuse existing submissions/logs

## Recommendation
- Do not implement Process 7 yet.
- Approve a narrow, audit-based phase that reuses the existing LMS and transition workflow tables only.
- For the teacher gradebook and progress report module, the safest path is:
  1. display existing LMS activity/submission/grade data in a teacher view,
  2. compute attendance summaries from existing lms_logs/lms_submissions as a first approximation,
  3. keep progress report status as the only finalized/draft gate,
  4. defer goal mapping and F2F attendance schema work until a separate approval is given.

## Final decision
Implementation should wait for approval. The current codebase already contains enough evidence to reuse existing data, but the requested Process 7 features still need explicit design approval before any schema or workflow changes are made.

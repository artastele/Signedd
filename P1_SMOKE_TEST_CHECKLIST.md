# P1 Smoke Test Checklist - SignED

Use existing demo accounts only. Do not reset, seed, or import the database during this smoke test.

## Test Accounts Needed

| Role | Needed | Actual account | Notes |
| --- | --- | --- | --- |
| Admin | Yes | Existing account required | Use for admin dashboard sanity check only |
| Parent | Yes | Existing account required | Must have linked learner/enrollment |
| Learner | Yes | Existing account required | Can also log in with LRN if configured |
| SPED Teacher | Yes | Existing account required | Main workflow driver |
| Guidance | Yes | Existing account required | IEP meeting/signature participant |
| Principal | Yes | Existing account required | Approval/signature participant |
| Master Teacher | Optional | Existing account required | Observation/COT routes are intentionally hidden for demo safety |

## Browser Test Order

| Step | Route / URL | Expected result | Actual result | Pass / Fail | Notes |
| --- | --- | --- | --- | --- | --- |
| 1 | Start Laragon Apache/MySQL | Apache and MySQL are running |  |  |  |
| 2 | `http://localhost/Signedd/public/login` | SignED login page loads |  |  |  |
| 3 | `http://localhost/Signedd/public/register` | Register page loads and allows parent registration |  |  |  |
| 4 | Parent login -> `/dashboard` | Parent dashboard loads |  |  |  |
| 5 | `/enrollment` | Enrollment type page loads |  |  |  |
| 6 | `/enrollment/create` | Enrollment form loads all required steps |  |  |  |
| 7 | Submit enrollment | Parent redirects to `/enrollment/status` with pending enrollment |  |  |  |
| 8 | SPED teacher login -> `/dashboard` | Teacher dashboard loads |  |  |  |
| 9 | `/enrollment/review` | Pending enrollments are listed |  |  |  |
| 10 | `/enrollment/review/{id}` | Enrollment detail/review page loads |  |  | Replace `{id}` with actual pending enrollment ID |
| 11 | Approve enrollment | Enrollment becomes verified and student/learner account is created |  |  |  |
| 12 | `/students` | New/selected student appears in records |  |  |  |
| 13 | `/assessment/conduct/{student_id}` | Assessment form loads for selected student |  |  |  |
| 14 | Submit assessment | Assessment is saved/finalized and visible in `/assessment` |  |  |  |
| 15 | `/iep/meetings/schedule` | Meeting scheduler loads |  |  |  |
| 16 | Create IEP meeting | Meeting appears in `/iep/meetings` |  |  |  |
| 17 | `/iep/meetings/{meeting_id}/pdsp` | PDSP upload/sign page loads |  |  | Replace `{meeting_id}` |
| 18 | Upload signed PDSP file | Upload succeeds and file is linked |  |  | Use small PDF/JPG/PNG demo file |
| 19 | Mark PDSP as signed | PDSP status becomes signed |  |  |  |
| 20 | `/iep` | IEP repository loads eligible student |  |  |  |
| 21 | `/iep/create?student_id={student_id}` | IEP draft is created only if signed PDSP exists |  |  | Replace `{student_id}` |
| 22 | `/iep/form/{iep_id}` | IEP form loads PDSP-derived sections |  |  |  |
| 23 | Submit/sign IEP | IEP reaches signed status |  |  | Use existing demo signing path |
| 24 | `/iep/implementation` | Signed IEP appears in implementation workspace |  |  |  |
| 25 | `/iep/implementation/workspace/{iep_id}` | Lesson/activity workspace loads |  |  |  |
| 26 | Create or select lesson/activity | Lesson is published and activity is available to learner |  |  |  |
| 27 | Learner login -> `/learning/dashboard` | Assigned lesson appears |  |  |  |
| 28 | `/learning/lesson/{lesson_plan_id}` | Lesson details and activities load |  |  |  |
| 29 | `/learning/activity/{activity_id}` | Activity player loads |  |  |  |
| 30 | Submit activity | Submission succeeds and score/feedback appears |  |  |  |
| 31 | `/learning/progress` | Learner progress reflects completed/submitted activity |  |  |  |
| 32 | Parent login -> `/parent/child-progress` | Parent sees child progress summary |  |  |  |
| 33 | `/notifications/get` | Notifications endpoint returns JSON while logged in |  |  |  |

## Demo Stop Conditions

| Condition | Action |
| --- | --- |
| Login fails | Record exact message, account role, and route |
| Upload fails | Record file type, size, route, and server message |
| Route returns 404 | Record route and expected controller |
| Dashboard loads but counters are wrong | Continue demo if core workflow works; log as P2/P3 data issue |
| OCR/Tesseract fails | Skip OCR and fill PDSP manually |
| Mail/OTP fails | Use already verified demo accounts if available |

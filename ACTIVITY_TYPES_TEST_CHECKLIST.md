# SignED Activity Types Test Checklist

Use this checklist with a real teacher account and a real learner account. Do not reset the database.

| Activity Type | Teacher Create | Learner Open | Learner Answer | Submit | Score | Progress | Pass/Fail | Notes |
|---|---|---|---|---|---|---|---|---|
| Multiple Choice - one question |  |  |  |  |  |  |  | Confirm only one correct option can be selected per question. |
| Multiple Choice - multiple questions |  |  |  |  |  |  |  | Confirm each question has its own options and correct answer. |
| True / False - one statement |  |  |  |  |  |  |  | Confirm instant correct/wrong feedback. |
| True / False - multiple statements |  |  |  |  |  |  |  | Confirm each statement saves and scores separately. |
| Fill in the Blanks - one blank |  |  |  |  |  |  |  | Confirm answer saves using the existing fill payload. |
| Fill in the Blanks - multiple questions/blanks |  |  |  |  |  |  |  | Test one sentence with multiple blanks and multiple blank questions. |
| Matching - one set |  |  |  |  |  |  |  | Confirm each left item saves the selected right item. |
| Matching - multiple sets |  |  |  |  |  |  |  | Confirm each set appears as its own mission step. |
| Drag & Drop Sort - one sorting question |  |  |  |  |  |  |  | Confirm reordered index payload saves and scores. |
| Drag & Drop Sort - multiple sorting questions |  |  |  |  |  |  |  | Confirm each question submits a separate ordered array. |
| Image Label - one image question |  |  |  |  |  |  |  | Image persistence still needs verification; label answers can score if label data exists. |
| Image Label - multiple image questions |  |  |  |  |  |  |  | Mark Partial unless image upload persistence is implemented. |
| Flashcards - one card |  |  |  |  |  |  |  | Confirm learner can flip the card and mark reviewed. |
| Flashcards - multiple cards |  |  |  |  |  |  |  | Confirm each card appears in the mission flow and completion saves. |
| Sequencing - one sequence question |  |  |  |  |  |  |  | Confirm sequence ordering saves and scores. |
| Sequencing - multiple sequence questions |  |  |  |  |  |  |  | Confirm each question submits a separate ordered array. |

## Required Flow Per Type

1. Teacher opens IEP Implementation Workspace.
2. Teacher creates the activity under a draft lesson plan.
3. Teacher publishes or uses an already published assigned lesson.
4. Learner opens My Lessons.
5. Learner opens the lesson and starts the activity mission.
6. Learner answers/reviews the activity.
7. Learner submits the mission.
8. Teacher opens submission review.
9. Learner opens My Progress and confirms the mission appears.

## Pass Criteria

- Activity appears in the teacher activity list after save.
- Activity appears in the learner lesson page.
- Learner UI is game-style, not a plain table/form.
- Submission is saved to `lms_submissions`.
- `auto_score` is saved for auto-gradable activities.
- Progress page reads the submission through the existing progress fallback.

## Multi-Entry Regression Checks

- Create one single-entry and one multi-entry activity for each supported type.
- Edit the activity metadata and confirm the activity data remains intact.
- For old demo activities, confirm the learner player still opens old single-question payloads.
- For new multi-entry activities, confirm the mission review shows every question/item/card/set before submit.
- For already-submitted activities, confirm the completed mission screen still shows the saved score and review.

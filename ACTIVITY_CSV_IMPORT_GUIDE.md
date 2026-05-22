# SignED Activity CSV Import Guide

CSV import route: `POST /iep/implementation/activity/import`

The importer reads a selected lesson plan, uploaded `.csv` file, and imports activity rows into `lms_activities`.

Each CSV row creates one activity. Multi-question activities are supported inside one row by using repeated numbered columns such as `question1`, `question2`, `sentence1`, `sentence2`, `left1`, `left2`, `item1`, and `step1`. The current importer does not group multiple rows into one activity.

## Supported Types

Fully mapped by importer:
- `multiple_choice`
- `true_false`
- `fill_in_blanks`
- `matching`
- `drag_drop_sort`
- `flashcards`
- `sequencing`

Accepted but not fully mapped by importer:
- `image_label`

Image label activities need an image asset and marker positions, so create them manually in the teacher builder.

## Common Headers

```csv
title,instructions,type,max_score,due_date
```

## Multiple Choice Headers

Up to five questions are supported by the current importer.

```csv
title,instructions,type,max_score,question1,q1_option1,q1_option2,q1_option3,q1_option4,q1_correct
```

Use `q1_correct` as the option number, for example `2`.

Example:

```csv
Math Quiz,Choose the correct answer,multiple_choice,2,,1+1=?,1,2,3,4,2
```

## True / False Headers

```csv
title,instructions,type,max_score,question1,q1_correct,question2,q2_correct
```

Example:

```csv
Science Fact,Read the statements,true_false,2,The Earth is round,true,The sun is cold,false
```

## Fill in the Blanks Headers

Up to five sentence/answer pairs are supported.

```csv
title,instructions,type,max_score,sentence1,answer1
```

Use `___` for the blank. Separate accepted answers with `|`.

Example:

```csv
Color Word,Complete the sentence,fill_in_blanks,1,,The ___ is red.,apple|ball
```

## Matching Headers

Up to eight pairs are supported.

```csv
title,instructions,type,max_score,left1,right1,left2,right2
```

Example:

```csv
Match Signs,Match each word,matching,2,,Hello,Kumusta,Thank you,Salamat
```

## Drag & Drop Sort Headers

Up to ten ordered items are supported. Put items in the correct order in the CSV.

```csv
title,instructions,type,max_score,item1,item2,item3
```

Example:

```csv
Sort Steps,Put the steps in order,drag_drop_sort,1,,Wake up,Brush teeth,Go to school
```

## Flashcards Headers

Up to ten cards are supported.

```csv
title,instructions,type,max_score,front1,back1,front2,back2
```

Example:

```csv
Sign Review,Review each card,flashcards,0,,Hello,Kumusta,Thank you,Salamat
```

## Sequencing Headers

Up to ten ordered steps are supported. Put steps in the correct order in the CSV.

```csv
title,instructions,type,max_score,step1,step2,step3
```

Example:

```csv
Daily Routine,Sequence the routine,sequencing,1,,Wake up,Eat breakfast,Go to class
```

## Validation Notes

- CSV file extension must be `.csv`.
- `title` is required.
- Unknown `type` values are converted to `multiple_choice` by the current importer.
- Multi-question support is column-based within one row; it is not row-grouped by activity id or title.
- Image label import creates no image/marker activity data; use the manual builder for image label demos.
- The current importer does not fully document row-level format errors in the UI beyond the JSON message.

## How To Test

1. Log in as a teacher with IEP implementation access.
2. Open the IEP Implementation Workspace.
3. Click Import from CSV.
4. Select a lesson plan.
5. Upload a small CSV with one supported activity type.
6. Confirm the import success message.
7. Reload the workspace and confirm the activity appears.
8. Log in as a learner assigned to the lesson.
9. Open the activity and submit it.
10. Confirm the submission appears in learner progress and teacher submission review.

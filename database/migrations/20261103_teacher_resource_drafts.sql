-- SignED teacher-owned editable Resource Hub drafts.
-- Non-destructive: existing lesson/IEP relationships are unchanged.
ALTER TABLE lesson_plans
    MODIFY COLUMN iep_id INT NULL;

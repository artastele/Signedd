---
inclusion: always
---

# SPED LMS — Development Workflow

## Core rule: one feature at a time
This applies to BOTH DFD processes AND security modules. No exceptions. Never bundle features.

## Documentation rule: minimize MD files
- **DO NOT** create temporary/redundant MD files (e.g., PART-A-SUMMARY.md, FIX-SUMMARY.md, etc.)
- **ONLY** update existing documentation files:
  - `CHANGELOG.md` - for version history
  - `README.md` - for main documentation
  - `SETUP-AND-TESTING-GUIDE.md` - for setup/testing instructions
  - `PROCESS-N-TEST-CHECKLIST.md` - for testing checklists (one per process)
- **NEVER** create multiple summary files for the same feature
- **NEVER** create temporary fix documentation files
- Keep documentation clean and consolidated

## The cycle (never skip steps)

### Step 1 — DESCRIBE (before writing any code)
Present a description of what will be built:
- What UI components will appear (form, table, modal, card, etc.)
- What controller methods will be created
- What model queries will be written
- What DB tables or columns will be added to `schema.sql`
- What security rules apply to this specific feature
- Which DFD process or security module this belongs to

### Step 2 — ASK
After describing, always ask:
> "Shall I proceed with this feature? Any changes before I build?"

Wait for explicit approval ("yes", "go", "proceed", or similar) before writing any code.

### Step 3 — PLAN MODE
Before writing code, design:
- Architecture and component structure
- DB schema changes (update `schema.sql` diff first)
- Controller/model/view breakdown

### Step 4 — BUILD
Write the code. One feature only.

### Step 5 — SELF-VERIFY
Run through every item in the self-verification checklist (see below). Fix silently before presenting.

### Step 6 — PRESENT & TEST
Present the code and say:
> "Ready to test. Confirm when tested and I'll mark it approved in CHANGELOG.md."

### Step 7 — APPROVE & LOG
On approval:
- Update `CHANGELOG.md`
- Describe the next feature
- Ask for approval before building it

## Self-verification checklist (run after every code block)
- [ ] DFD flow preserved exactly — no inputs, outputs, or actors missed
- [ ] MVC layers respected — no SQL in controllers/views, no logic in views
- [ ] `schema.sql` is the ONLY place DB changes were made
- [ ] SchemaManager migration block added with correct version number
- [ ] Prepared statements used everywhere (PDO)
- [ ] RBAC middleware applied to this route
- [ ] Permissions checked against `/config/permissions.php`
- [ ] Color scheme (#a01422, #1e4072) applied correctly in the view
- [ ] Bootstrap customized — no default blue/gray components
- [ ] No hardcoded credentials, API keys, or sensitive data
- [ ] File header comment added with process/module reference
- [ ] `CHANGELOG.md` flagged for update on approval

State **"Self-check: passed ✓"** at the end of every code response.
If any item fails — fix it silently before presenting.

## Standard response format
```
Feature:        [name and process/module number]
Description:    [UI, controller, model, schema changes]
Schema diff:    [new tables/columns going into schema.sql]
Implementation: [code — only after approval]
Self-check:     passed ✓
Next step:      [next feature — ask for approval]
```

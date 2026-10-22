# Project Rules

## UI & Design Guidelines
- All buttons must be neat, clean, modern, and simple.
- Maintain consistent button height, subtle border-radius (6px-8px), refined typography (font-size ~0.875rem, font-weight 500-600), clean padding, and subtle hover effects.
- Avoid bulky, oversized, or overly loud button styling.
- Ensure all button functionality, form actions, event handlers, IDs, names, and triggers remain 100% intact.

## Database & Production Data Protection (STRICT - LIVE CLIENTS)
- **ZERO DESTRUCTIVE ACTIONS ON LIVE / DEPLOYED SYSTEM**:
  - NEVER execute `TRUNCATE`, `DROP TABLE`, `DELETE`, or blanket `UPDATE` on user accounts or client data (`users`, `student_records`, `parent_profiles`, `enrollment_submissions`, `iep_records`, etc.).
- **NEVER OVERWRITE USER PASSWORDS & CREDENTIALS**:
  - NEVER run seeders, test scripts, or database imports (such as `seed_all_demo_accounts.php` or full SQL dump imports) that overwrite `password_hash`, `email`, `role`, or `status` of existing users on the live database.
  - When seeding demo accounts, NEVER touch or reset accounts that already exist.
- **ADDITIVE-ONLY DATABASE ALTERATIONS**:
  - All database schema updates on deployed/live environments must be strictly **additive and idempotent** (e.g. `ADD COLUMN IF NOT EXISTS`, `CREATE TABLE IF NOT EXISTS`).
  - Do NOT re-run scripts that drop or rebuild existing tables.
- **PROTECT LIVE CLIENT DATA AT ALL COSTS**:
  - Live client accounts, student records, parent links, and credentials are mission-critical. All deployment, migration, and maintenance actions must guarantee 100% data preservation and continuous login access.


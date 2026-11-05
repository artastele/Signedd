# Finance sandbox dataset — prepared only, not executed

No accounts, schools, packs, files containing credentials, or database records were created during preparation. Finance remains disabled. The entry point is `tools/seed_finance_sandbox.php`; never substitute the older demo/reset scripts, which refresh existing records.

## Planned identities and permissions

| Identity | Synthetic email | Existing role / scope |
|---|---|---|
| SANDBOX TEST DATA - Principal Nova | `principal.finance@example.test` | `principal`; existing school/staff approval permissions, own-school institutional application/renewal |
| SANDBOX TEST DATA - Teacher Aster (Creator) | `creator.finance@example.test` | `sped_teacher`; existing `iep.implement` / Hub permissions, creator pricing proposal, own earnings |
| SANDBOX TEST DATA - Teacher Beryl (Buyer) | `buyer.finance@example.test` | `sped_teacher`; existing Hub preview/buy/subscribe/use permissions; starts with no financial orders or entitlements |
| Existing active System Admin | Operator-selected existing admin; default `admin@spedlms.local` | Existing `admin` role and Finance approval rights, unchanged; no new admin or password reset |

One new school: **SANDBOX TEST DATA - SignED Finance Academy**, reserved code `SIGNED-FINANCE-SANDBOX-V1`. All three accounts share its school ID. Existing role-request models approve only these new identities: the reused admin approves the principal role, and the new principal approves the two teacher roles. The teacher assignment model links both new teachers to this school. New local-only identities are email-verified without sending mail to synthetic addresses.

This is role/staff authorization, **not paid access**. No institutional application order, enrollment marker, license entitlement, subscription, purchase, verified payment, earning, payout, usage, or refund is seeded. The institutional application still needs explicit principal consent, actual verified sandbox payment and required admin activation. Normal legacy school access is not a paid institutional license. No new role/permission system is introduced.

## Minimal existing-Hub fixtures

- Creator-owned **SANDBOX TEST DATA - Free Addition Practice**: one sanitized text page, zero price, free publication approved under the selected admin.
- Creator-owned **SANDBOX TEST DATA - Premium Addition Pack**: one sanitized text page, proposed PHP 79; `PENDING` review, no approved amount or reviewer. Teacher B cannot access full premium content before pricing/publication approval and verified payment or active subscription.

The CLI-only fixture adapter extends the existing `ResourceHubModel`, uses its tables and existing HTML/tag sanitization plus finance pricing validation. It deliberately creates source-less pack snapshots (`source_lesson_plan_id=NULL` is supported by the existing schema). The normal lesson-to-Hub publisher needs a learner-linked IEP/lesson; the seeder does not fabricate learner, IEP or signed-document history to satisfy that dependency. Teacher A owns the pending premium proposal and retains the existing creator role/price submission permissions. Normal new lesson publication still follows the existing authoring workflow.

The minimal dataset can exercise school payments, monthly Hub subscriptions, individual purchases and pack-sale commissions. Successful template use and subscription usage-pool allocation additionally need a separately authorized synthetic learner and signed IEP through the existing workflow. Those educational fixtures are **not** created here. Never attach the test teachers to real learners to work around this prerequisite. Text-only packs avoid private-upload prerequisites; media testing later uses the private-upload workflow.

## Future execution gates — do not execute yet

1. Explicit client approval and application of `database/migrations/20261103_sandbox_finance.sql` to the verified local MAMP database, with the reviewed license-gate safety fix present. This script performs no DDL and refuses legacy schema maintenance.
2. The verified backup report and matching SQL file must exist and pass their SHA-256 check. The report must identify the same local database, successful restore/source comparison, and the exact proposed migration hash. A passed backup report is not migration/seeder execution approval.
3. Use MAMP PHP 8.3, `APP_ENV=development`, loopback DB host, and `SIGNED_FINANCE_ENABLED=false`. The actual server must match the report's local MAMP port/data directory. All 14 finance tables plus existing user/school/staff/Hub tables must be InnoDB. Live PayMongo configuration is rejected; no provider API is called.
4. Select an existing active, verified, nondeleted/unlocked `admin`. Never create, rename, reassign, or reset it. The existing role permissions must be available.
5. Create a mode-0700 state directory outside the application, MAMP htdocs and every served alias. For example, after authorization:

```zsh
mkdir -m 700 -p "$HOME/SignED-sandbox/finance-v1"
/Applications/MAMP/bin/php/php8.3.30/bin/php tools/seed_finance_sandbox.php \
  --apply --migration-approved \
  --backup-report="$HOME/SignED-backups/20261103-064735-e0899a/verification.json" \
  --state-dir="$HOME/SignED-sandbox/finance-v1" \
  --admin-email=admin@spedlms.local
```

**This command is documentation only. It has not been run.** No options creates only a help/plan response without a database connection. There is no reset or cleanup option.

The future run stores `dataset.json` (ownership IDs plus nonce) and `credentials.json` (three distinct cryptographically random passwords) in this private directory, mode 0600. Password hashing uses existing `UserModel::create()` / bcrypt. Passwords, hashes and secrets are never printed in reports, arguments or logs. Read the private credential file locally when authorized; do not paste it into chat or commit it.

## Idempotency, collisions and cleanup policy

On first run, any existing reserved school code or email without the exact ownership manifest aborts. It does not adopt an existing account merely because its name looks synthetic. All database writes use one transaction; model role/school/email-verification updates apply only to user IDs just inserted in that transaction. No controllers, notification delivery, SMTP, payment provider or seeders are invoked.

The school marker, role-request nonce, exact IDs/emails/roles/school ownership and pack markers are rechecked on every rerun. A normal rerun verifies and reuses the dataset with **zero record changes**, preserving passwords, admin approvals and any payments/purchases made during testing. It never removes Buyer B's legitimately acquired access to restore an unpaid state. Drift causes a stop, not an update/reset.

Private credentials and a PREPARED ownership manifest are persisted before commit. If the process stops after commit, a rerun can validate the owned records and finalize the manifest without recreating anything. Incomplete/orphan private state or a rollback mismatch requires manual inspection; the script refuses blind repair. Existing private files are never overwritten as newly generated credentials.

No record cleanup is implemented. Any future cleanup needs separate approval, the exact manifest IDs and nonce, dependency inspection and preservation of financial audit history; it must exclude the reused admin and all unrelated/real school, user, learner, IEP, grade, upload, and Hub records. Transaction rollback affects only uncommitted changes from this run.

## Verification performed during preparation

- PHP syntax checks only for the prepared seeder/model adapter.
- `tests/FinanceSandboxPlanTest.php`: 44 pure specification/guard checks passed (identities, existing permissions, no embedded credentials, price review defaults, local/production/feature/maintenance gates, backup evidence and ownership collisions).
- The seeder entry point and dataset creation were **not executed**; no database was contacted. Runtime/schema-backed seeding and login verification remain unverified until the approved migration is applied and execution is separately authorized.

# Local sandbox activation — 3 November 2026

Finance is now enabled on local MAMP only. This report supersedes earlier “not applied / disabled” status in historical review sections. No production connection, deployment, public tunnel, commit, push or seeder execution was performed.

- Database: `sped_lms`, local MySQL 8.0.44, TCP 8889; PHP 8.3.30.
- Fresh backup: `/Users/jeffersonarnado/SignED-backups/20261103-091501-c1336c/sped_lms.sql` (573,456 bytes, private permissions).
- SHA256: `93cb5fb524e714a3477d503fe655191fac8dc761e18b5227e8f64e2f1209c644`.
- Successful isolated restore: `signed_backup_verify_20261103_091501_c1336c`. All 98 original table counts (3,265 records), effective schema and complete data matched. Restore rendering added only redundant explicit utf8mb4 declarations on 37 columns. Verification reports and dumps remain in the private backup directory.
- Applied only `database/migrations/20261103_sandbox_finance.sql`: 14 new empty tables, 124 columns, 12 internal foreign keys and 12 enforced CHECK constraints. No existing table alterations or educational data writes. Migration SHA256: `1a15009dfa52c0e0d20d631c5e5cd25ad5038176624f3fb217d0b5ffec6cc11a`.
- Original 98-table dump was byte-identical before and immediately after migration. After HTTP checks only technical `csrf_tokens` changed; all educational schemas and records remained byte-identical. Finance tables remain empty.
- Local `.env`: `SIGNED_FINANCE_ENABLED=true`; provider key verified as test-only format, never printed. Live credentials/mode remain rejected by the provider service. Legacy CLI schema-maintenance switch remains disabled.
- Private media directory: `/Users/jeffersonarnado/Library/Application Support/SignED/private-media`, outside web root, mode 0700. Existing media unchanged.
- All existing school license checks allow legacy access; no school was enrolled, expired or suspended.

## Verification

**505 checks across 13 isolated suites**, all passed, zero failures: NativeFinanceEntry (49), SchoolLicenseGate (72), FinanceRules (87), PremiumMedia (55), PayMongoFeeEvidence (27), FinanceFeePosting (12), ResourceHubInsightsPermissions (44), ResourceTagWorkflow (26), ResourceRecommendationDeduplication (13), LearningInsights (53), InterventionPermissions (16), InterventionMaterialSnapshot (7), FinanceSandboxPlan (44).

Local session-backed HTTP checks used existing verified active accounts with their actual roles, without password resets or account modifications. Temporary test sessions were removed. Eight GET requests returned HTTP 200 with expected content, no PHP errors or unexpected redirects:

- Principal: `/dashboard` (license status and activation control).
- Teacher: `/iep/implementation/resource-hub` (premium upgrade), `/finance` (billing history).
- System Admin: `/finance`, plus `?tab=revenue`, `?tab=earnings`, `?tab=payouts`, `?tab=refunds`.

Base URL: `http://localhost:8888/Signedd/public`. These were HTTP rendering checks, not interactive browser or real provider payment tests. Premium Buy Pack states are covered by isolated fixtures; the prepared sandbox seeder has not run.

## Remaining payment acceptance

No checkout, payment, refund or payout has been submitted to PayMongo. Key format verification does not establish provider acceptance. Browser checkout/return, real signed webhooks, entitlements, actual provider fee availability and refund reconciliation still need end-to-end sandbox testing. Missing fees must remain pending; no shares may be invented.

Webhook signing secret is not configured. With separate explicit tunnel approval, expose only HTTPS POST forwarding to `/Signedd/public/finance/webhook`, register TEST events `checkout_session.payment.paid`, `payment.refunded`, `payment.refund.updated`, and set that endpoint’s `PAYMONGO_WEBHOOK_SECRET` locally. Do not expose the entire MAMP app. Stop the tunnel and disable its endpoint after testing. No tunnel was started. Existing backup predates the finance migration; preserve it and the separate test restore.

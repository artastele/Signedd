# Sandbox verification report — 3 November 2026

> Current local status: migration applied and Finance enabled for sandbox testing on 3 November 2026. See [local activation verification](LOCAL_SANDBOX_ACTIVATION.md). Earlier disabled/not-applied statements below describe prior review stages. Provider end-to-end acceptance remains pending.

Follow-up review changed code and isolated tests only; it did not connect to any database or provider. No migrations were applied. No backup restore, real checkout, refund, payout, public tunnel, deployment, commit or push was performed. Finance remains disabled in the existing local configuration. Tests below do not use customer data or call PayMongo.

## Isolated verification
- Finance rules/security: 87 checks passed (integer split conservation; pending unverifiable fees; calendar/grace boundaries; exact raw-body signature validation and replay limits; wrong session/reference/buyer/mode/amount/currency/status; failed attempts within session; PDF labels and payment prerequisite; public-media fail-closed behavior; live credential rejection).
- Existing Resource Hub permissions: 44 checks passed.
- Existing Resource Tag Workflow: 26 checks passed. In-memory PDO fixture extended to answer schema-presence reads without opening a connection.
- Existing Recommendation Deduplication: 13 checks passed.
- Existing Learning Insights: 53 checks passed.
- Existing Intervention Permissions: 16 checks passed.
- Existing Intervention Material Snapshots: 7 checks passed.
- Initial-stage checks: all 30 changed/new PHP files passed PHP 8.3 syntax checks; 29 passed PHP 7.4 syntax checks (the existing PHP 8-only test fixture is excluded). Whitespace checks passed. Initial-stage total: 246 isolated checks passed.

Run isolated checks with MAMP PHP 8.3:
```
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/FinanceRulesTest.php
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/ResourceHubInsightsPermissionsTest.php
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/ResourceTagWorkflowTest.php
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/ResourceRecommendationDeduplicationTest.php
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/LearningInsightsTest.php
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/InterventionPermissionsTest.php
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/InterventionMaterialSnapshotTest.php
```

## Actual end-to-end acceptance

| Test | Status | Required evidence / remaining gate |
|---|---|---|
| A — PHP 15,000 institutional payment, admin activation, PDF, finance | NOT YET VERIFIED | Approved schema + verified backup + test key/webhook + principal/admin browser flow |
| B — PHP 199 subscription, eligible packs, pool, expiry | NOT YET VERIFIED | Actual test payment and database period/access assertions |
| C — PHP 79 permanent purchase, commission, confirmed payout | NOT YET VERIFIED | Actual test payment, fee data, purchase/payout database reconciliation |
| D — Failed/cancelled payment grants no access | NOT YET VERIFIED | Actual provider failure and session-expiry flows; isolated paid-status rejection passed |
| E — Duplicate webhook exactly once | NOT YET VERIFIED | Provider replay and concurrent delivery against migrated MySQL; unique IDs/locks implemented |
| F — Signature/amount/order/buyer/live tampering | NOT YET VERIFIED | Isolated validation passed; real HTTP endpoint and persisted side effects still untested |
| G — Confirmed full/partial refunds reverse accurately | NOT YET VERIFIED | Provider refund support/permissions and fee-return evidence; returned fees remain pending without provider ledger/statement evidence |
| H — Unauthorized direct resource access denied | NOT YET VERIFIED | Private delivery and legacy URL protection tested with in-memory fixtures; actual Apache/alias/browser checks after installation pending |
| I — Subscription expiry preserves individually owned packs | NOT YET VERIFIED | Persisted subscription and purchase expiry scenario |
| J — School grace period preserves records | NOT YET VERIFIED | Isolated date rules passed; migrated school route and record-count checks pending |
| K — Finance totals reconcile | NOT YET VERIFIED | Real fees/refunds/payouts; unresolved returned provider fees must remain flagged |
| L — Reloads cannot inflate subscription usage | NOT YET VERIFIED | Preview never writes usage; MySQL distinct-period/pack constraint and concurrent clone checks pending |

## Role/browser checklist after approval
Principal: create one school application, download the unpaid billing PDF, pay through test checkout, reconcile the verified payment, confirm immediate license activation/expiry, and manually renew without creating a duplicate pending order. Teacher: verify that a paid subscription activates immediately, propose Free/Premium creator packs, and confirm that only creator pack pricing/publication requires System Admin approval. Admin: inspect test-only transactions and provider fees, approve or reject creator pack pricing/publication, close ended subscription pools, review refunds, and record confirmed payouts with exact amounts/references. Unrelated teacher/parent/learner accounts cannot inspect orders, confirmations, payouts, or creator earnings; unauthorized direct content routes fail closed. Repeat authenticated requests with missing/invalid finance CSRF and confirm rejection even in development mode.

New-school licensing acceptance must also confirm that System Admin approval creates one pending ₱15,000 annual order; faculty applications remain submittable but cannot be approved while payment is unverified; verified payment activates access without another admin decision; and SPED Teacher, General Teacher, Master Teacher, and Guidance accounts share the 10-active-faculty limit. Existing schools without the new-school licensing marker must remain legacy-accessible.

## Focused review validation
- `PremiumMediaTest.php`: 55 checks passed — real temporary blobs, real media/finance access services and in-memory PDO fixtures; owner/admin/purchase/subscription/expiry/assigned learner/parent isolation, private URL binding, other creators, traversal, symlink aliases, encoded and JSON-escaped legacy references, MIME rejection, storage confinement, integrity, byte ranges, checkout disabled, and read-only delivery.
- `PayMongoFeeEvidenceTest.php`: 27 checks passed — exact individual-payment GET, evidence hashing, real integer fee/net split, unavailable/missing/mismatched amounts, live mode, unsupported deductions, and later reconciliation.
- `FinanceFeePostingTest.php`: 12 checks passed — real settlement service with in-memory transactions; embedded checkout fees cannot allocate earnings, retrieval outage stays pending, later individual-payment retrieval posts exactly one split, repeats are idempotent, and changed fees hold payouts without rewriting earnings.
- Actual A–L statuses above remain NOT YET VERIFIED. No SQL migrations, test database schemas, live HTTP payment calls, existing financial/client records, or existing uploads were used or changed by these tests.

Additional commands:
```
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/PremiumMediaTest.php
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/PayMongoFeeEvidenceTest.php
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/FinanceFeePostingTest.php
```

Remaining gates: approved migration and verified local backup; configured private storage and verified Apache/guarded-router behavior on every alias; PayMongo test key, endpoint secret, enabled methods, payment-fee/ledger availability and refund permissions; separately approved HTTPS webhook exposure before real A–L testing. See [exact sandbox setup](SANDBOX_SETUP.md). New premium content must use private uploaded assets; existing public/external references are not automatically converted.

Final focused-review result: **340 isolated checks passed, 0 failed** across the ten suites. PHP 8.3 syntax: 37 files passed. PHP 7.4 syntax: 34 files passed (three PHP 8-only test fixtures excluded). `git diff --check` passed. These results do not establish actual HTTP/server, migrated MySQL or PayMongo end-to-end acceptance.

## Institutional license-gate safety verification
`tests/SchoolLicenseGateTest.php`: **72 passed, 0 failed** with real middleware/service/rules and in-memory PDO fixtures. Covers disabled Finance with zero database lookups, legacy and historical expired schools, explicit principal consent, pending/paid-first-application access, unpaid/missing-provider-payment/missing-consent rejection, admin authorization, first atomic enrollment, repeated activation protection, school isolation, active/grace/inclusive-day-30/expired boundaries, early/within-grace/after-grace renewals, principal billing/webhook/auth access after expiry, server-side denial on direct/alias routes, forged session admin rejection, and preservation of educational/account records. An isolated CLI child verifies that real middleware stops protected route execution with a 403 and billing link.

All eleven isolated suites were rerun: **412 checks passed, 0 failed** (the prior 340 plus 72 license-gate checks). Modified PHP syntax checks passed on PHP 8.3 (6 files) and PHP 7.4 (5 application files; the new fixture uses PHP 8 syntax). Whitespace check passed. Saved `.env` still has Finance disabled; credentials were not displayed or modified. No real database, schema, provider, school, learner, IEP, grade, or existing upload was changed.

```
/Applications/MAMP/bin/php/php8.3.30/bin/php tests/SchoolLicenseGateTest.php
```

The prior table-presence licensing blocker is resolved at code level; pending migration approval and verified backup remain. Actual A–L statuses above remain NOT YET VERIFIED until separately authorized database/server/browser/provider testing. Browser checks must confirm: a legacy school remains usable after table creation and Finance enablement; an unpaid application retains access; explicit consent plus independently verified payment activates and enrolls the school immediately; Finance disabled restores school-route access; expired principals can reach renewal checkout; and System Admin retains creator pack review access. Premium-content entitlement protection must continue independently of the school gate.

## Native purchasing integration
[Native entry-point implementation and browser checklist](NATIVE_PURCHASE_ENTRY_POINTS.md): 49 new isolated checks passed. All 13 focused suites passed, **505 checks total, 0 failed**. Purchases start on Principal Dashboard or Resource Hub, use existing centralized order creation and Hosted Checkout, and return to one authorized payment-request/confirmation view. Billing history remains secondary. Existing license, media, fee, refund, ownership and authorization suites passed. Finance remains disabled; no schema, data, seeder, provider or deployment action was performed. Real browser/server/provider acceptance remains NOT YET VERIFIED.

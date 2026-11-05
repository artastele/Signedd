# Native purchase entry points — local implementation

Principal Dashboard now contains a school-license card with server-derived status, activation/expiry information, explicit application consent, Activate School License / Renew License, and continuation of an existing pending order. Paid applications awaiting admin activation show confirmation rather than a second purchase action. Legacy enrollment and 30-day grace/renewal rules remain in the existing FinanceService and school gate.

Resource Hub retains its hero, filters, tabs, previews and template modal. A compact subscription panel below the hero offers **Upgrade to Premium — ₱199/month**, active expiry/manual renewal or continuation of a pending payment. Eligible approved premium cards offer **Buy Pack** directly. Owned, included-in-subscription and free cards retain **Use as Template**. Preview continues to serve only content permitted by backend entitlements; locked previews show summary metadata and the native purchase/subscription controls. Creator sharing retains its existing Free/Premium proposal selection and admin review; My Shared Packs displays review state, with links to the existing creator-earnings tab.

All native forms POST to the existing `/finance/action` **create** action with financial CSRF. They send a product kind and pack ID when needed, never a price, buyer identity, access claim or redirect target. Existing `FinanceService::createOrder()` still derives price and ownership from the server and reuses pending orders. Successful creation goes to `/finance/payment/{id}`: one central payment-request/confirmation view, with billing PDF available before payment and the existing POST **checkout** action to Hosted Checkout. No new payment provider service, checkout service, marketplace or Finance dashboard was added.

PayMongo success and cancellation URLs return to that same order page. GET/redirect parameters never settle or activate payments; the page reads the authorized persisted order and offers a manual status refresh. Payment confirmation PDFs become available only after the existing verified-payment flow. Institutional approval remains a separate admin action. Existing webhook verification, fee reconciliation, refund handling, permanent-pack ownership, private-file checks and subscription usage rules are unchanged.

Billing history is secondary: purchase initiation moved to Principal Dashboard/Hub; history and documents are linked from account settings, the sidebar and payment confirmation. Pending transactions can still be reviewed/resumed from history. Existing System Admin Finance Management tabs (transactions, revenue, earnings, payouts, refunds) remain centralized.

## Files changed
- `app/Services/FinanceService.php`: read-only role-scoped native page context; no new payment/accounting logic.
- `app/Services/FinanceRules.php`: current/expired/scheduled subscription summary, respecting gaps between terms.
- `app/Services/PayMongoSandbox.php`: existing Checkout redirects target the central payment-request page.
- `app/Controllers/FinanceController.php`: existing create action redirects to the authorized payment-request view; GET reads only.
- `app/Controllers/IEPImplementationController.php`: sharing response accurately directs creators to publication status.
- `routes/web.php`: one GET route for the central payment request/confirmation.
- `app/Views/dashboard/principal.php`, `app/Views/iep_implementation/resource_hub.php`, `app/Views/iep_implementation/lesson_slide_builder.php`: native entry points, existing card/template/share behavior retained.
- `app/Views/finance/partials/{context,purchase_form,school_license,hub_subscription,pack_actions}.php`: shared view components.
- `app/Views/finance/payment_request.php`, `app/Views/finance/pack_preview.php`, `app/Views/finance/index.php`: central invoice/confirmation and secondary history.
- `app/Views/layouts/sidebar.php`, `app/Views/profile/index.php`: secondary history/account links; existing admin management link.
- `tests/NativeFinanceEntryTest.php`, this document, `ACCEPTANCE.md`.

No migration, seeder, database record, existing upload, credential, or actual `.env` value was changed. No database connection, provider payment request, tunnel, commit, push or deployment was performed. Saved `SIGNED_FINANCE_ENABLED=false` was verified without displaying credentials. Disabled/unavailable Finance renders disabled native purchase controls and does not query financial context tables.

## Focused verification
The focused checks use actual partial rendering, real finance order/read rules, an in-memory PDO fixture, and the existing provider client contract. They cover native forms and CSRF, no browser-supplied money/buyer/access fields, disabled setup, Principal legacy/active/grace/expired states, unlinked Principals, immediate verified-payment activation, subscription expiry and queued-term gaps, existing pending orders, owned/included/free cards, pending creator-review exclusion, escaping, server prices, already-owned rejection, authorized order visibility, payment return URLs, and renewal route exemption.

All 13 focused suites passed: **505 checks, 0 failures**. PHP 8.3 syntax checks passed for 20 modified/new PHP files; PHP 7.4 syntax checks passed for 19 application files (the new test fixture requires PHP 8). Whitespace checks passed. No database or provider integration was exercised.

## Remaining browser verification after authorization
- Principal: legacy status and disabled controls before enablement; explicit consent → billing PDF → PayMongo; paid/pending-admin state; first admin activation; early/grace/expired renewal.
- Teacher B: free preview/use; approved premium preview/Buy Pack; existing pending payment continuation; owned pack has Use as Template; active subscription includes eligible packs and shows expiry; expired subscription preserves purchases.
- Teacher A: Free/Premium proposal submission, My Shared Packs review state, creator-earnings link and unrelated-user isolation.
- Admin: existing five Finance Management tabs, approval and reconciliation actions unaffected.
- Account/history and confirmation links, keyboard/mobile card layout and preserved clone modal. Failed/cancelled/tampered provider returns cannot activate access. Replay actual signed webhooks and verify backend/private-file denial after expiry/refund.

Actual end-to-end acceptance remains NOT YET VERIFIED. Migration approval/application, sandbox setup, separately approved webhook exposure and real role/browser/payment testing are still pending. Keep Finance disabled in the current workspace.

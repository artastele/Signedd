# Exact local sandbox setup (no steps executed)

> Current local status: migration applied and Finance enabled for sandbox testing on 3 November 2026. See [local activation verification](LOCAL_SANDBOX_ACTIVATION.md). Earlier disabled/not-applied statements below describe prior review stages. Provider end-to-end acceptance remains pending.

1. Review `database/migrations/20261103_sandbox_finance.sql` (14 new tables, including `finance_fee_evidence`, `finance_media_assets`, `finance_pack_media`). It is ready for review, not approved for execution. Obtain explicit client approval and a verified local backup/restore before applying it. Do not use the legacy MAMP migration runner or turn on `SIGNED_SCHEMA_MAINTENANCE`.
2. Use PHP 8.3 with `pdo_mysql`, `curl` (trusted CA bundle), `fileinfo`, `dom`, and a local MySQL database. Set PHP `upload_max_filesize=50M`, `post_max_size=55M` or larger for private media. The application also enforces 50 MB and verifies MIME, file integrity and ownership.
3. Create a writable mode-0700 private storage directory OUTSIDE the application, MAMP `htdocs`, and every web-served document root/alias. Example user-created local directory: `/Users/jeffersonarnado/Library/Application Support/SignED/private-media`. Do not put it inside `public/uploads`. Include its blobs and finance metadata in private backups. No directory or existing upload has been moved by this implementation.
4. Configure only the local, Git-ignored `.env` after installation:

```dotenv
SIGNED_FINANCE_ENABLED=true
PAYMONGO_SECRET_KEY=sk_test_<your_server_test_secret>
PAYMONGO_WEBHOOK_SECRET=<secret_for_the_exact_test_webhook_endpoint>
PAYMONGO_PAYMENT_METHODS=card,gcash
SIGNED_FINANCE_BASE_URL=http://localhost:8888/Signedd/public
SIGNED_PREMIUM_STORAGE="/Users/jeffersonarnado/Library/Application Support/SignED/private-media"
```

Use only methods enabled on the test account. Never use `sk_live_`. The secret key must support Checkout v2 creation, Checkout v1 retrieval/expiry, `GET /v1/payments/{id}`, and refund creation/retrieval if refund tests are included. Account/ledger permissions and whether test payments provide actual fee/net data must be verified in the PayMongo dashboard. No configured account was contacted during development.

5. Serve through MAMP Apache with `mod_rewrite` and `AllowOverride All` so `public/.htaccess` runs. Educational uploads under `materials`, `lesson_pages`, `lesson_editor`, `activities`, `fsl_custom_videos`, and `lesson_plans` must pass through `PremiumMediaController`, even when a real file exists. Test both `/Signedd/public/uploads/...` and root/alias URLs. Disable CDN/proxy caches for `/finance/media/*` and these upload paths. Remove any alternate static alias that bypasses PHP. Never serve private storage as an alias. Private new assets remain outside the web root regardless of rewrite configuration.

For a local PHP development server, use the guarded router (do not use an unguarded static server):
```
/Applications/MAMP/bin/php/php8.3.30/bin/php -S 127.0.0.1:8080 -t public tools/finance_local_router.php
```
Then use `SIGNED_FINANCE_BASE_URL=http://127.0.0.1:8080` and browse that origin. Do not start this server as part of the migration process. It is a local development alternative to MAMP, not a public tunnel.

6. Existing schools retain legacy access after installation; enabling Finance does not automatically enroll them. A Principal must explicitly check the institutional application consent field. Pending or failed payments retain legacy access. The first independently verified paid institutional order automatically activates the annual term and records enrollment in the finance audit table; only then do institutional expiry/grace restrictions apply while Finance is enabled. Subscriptions and approved individual packs also activate only after independent payment verification. System Admin approval applies to creator pack pricing/publication, refunds, reconciliations requiring review, and payouts; it is not an access activation step for licenses or subscriptions. Historical records without the enrollment marker remain legacy. Login, Finance/renewal/payment and webhook endpoints are never restricted by school expiry. Premium-media entitlement checks remain independent when checkout is disabled.

Sign in with existing authorized local accounts. In Billing and access → Private premium media, upload a NEW image/PDF/video/audio and use its returned URL in the existing lesson editor, page media field or activity data. For editor image uploads, check **Store new images privately for premium sharing** before selecting the image. Failed private uploads never fall back to public/base64 copies. Submit a new pack with Premium pricing for admin review. Creator ownership is checked before private assets bind to the new pack. Existing public/external media is rejected for new premium publication; replace references manually in a new pack after an authorized content review. No bulk conversion, move or update is performed.
7. A public HTTPS webhook requires separate explicit approval. After approval, proxy ONLY POST from the temporary endpoint to the local `/Signedd/public/finance/webhook` path (or `/finance/webhook` for the guarded PHP server). Return 404 for every other external path; never expose MAMP root, MySQL, phpMyAdmin, file/media routes, or the entire app. Configure the TEST endpoint for `checkout_session.payment.paid`, `payment.refunded`, and `payment.refund.updated`; copy that endpoint's signing secret server-side. Never trust return URL parameters. Disable the endpoint and tunnel after the sandbox session.
8. Execute actual A–L tests and authenticated browser checks only after these gates. Provider fees absent/unreconciled → `PENDING_FEES`, with no earnings or share allocation. Admin **Reconcile payment** retries read-only provider retrieval. The individual payment must match the session payment ID, PHP amount, test mode and paid status; fee+net must reconcile. Ancillary tax/foreign-fee/withholding ambiguity stays pending for ledger review. Conflicting verified fees → `FEE_REVIEW` and payout hold. Confirmed refunds before fee availability → `REFUND_FEE_REVIEW`; no retroactive earnings. Refund fee returns require verified provider ledger/statement evidence; absent evidence must never be replaced by zero or an estimate.

Official sources checked: [Retrieve a Payment](https://docs.paymongo.com/reference/retrieve-a-payment), [Hosted Checkout](https://docs.paymongo.com/docs/payment-channels-hosted-checkout), [Refund Resource](https://docs.paymongo.com/reference/refund-resource), [Payout transaction fields](https://docs.paymongo.com/reference/payout-resources). Retrieving a payment provides payment-level fee data; checkout payment confirmation alone is not sufficient evidence for this module's fee allocation. Refund success alone does not establish whether a processing fee was returned.

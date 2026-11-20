# Certainty — Open Items Register

Every unanswered question, unconfirmed assumption, pending input or deferred task in one place.

- **Status:** Open → Answered (record the answer and the date) → Done (once implemented, note the commit or milestone).
- **Owner:** **Client** = business decision or content from the client · **You** = project lead · **Dev** = engineering follow-up.
- **Blocks** names the milestone that needs the answer. "—" means it only needs confirming.
- When an item is resolved, update the BRD section it came from.

_Last updated: 20 November 2026 (Milestone 6)._

---

## 1. Business decisions (Client)

| ID | Question | Current behaviour / default | Blocks | Source | Status |
|---|---|---|---|---|---|
| OI-01 | When an admin extends a draft that has **not** expired yet, are the days added to the current deadline or counted from today? | Not built yet. Recommendation: add to the current deadline. | M8 | BRD O-02, FR-039 | Open |
| OI-02 | Is the Word-file jQuery code the authoritative calculation reference, rather than the Netlify React build? | The Word file is treated as authoritative, and the engine matches it exactly. | M4 sign-off | BRD O-03 | Open |
| OI-03 | Does the old `submission.php` contain any calculations? Can the client supply `readableAmount()` if report number formatting must match the old reports exactly? | Assumed display only. Numbers are formatted with Latin digits and the selected decimals. | M7 | BRD O-04, M-01, M-03 | Open |
| OI-04 | Default study currency, and should the currency list be limited (for example to Gulf currencies)? | Default is SAR. 20 currencies are offered (Gulf, Arab, USD, EUR, GBP), configured in `config/certainty.php`. | — | BRD O-06 | Open |
| OI-05 | The study fee is in SAR. What should happen when a study uses another currency? | The fee is **not** added to setup costs and a warning is shown. Options: allow SAR only, or define an exchange-rate rule. | M5 / M7 | BRD O-07, §26.3 | Open |
| OI-06 | Is the 1,200 SAR fee VAT-inclusive? A-05 requires the invoice VAT split. | The fee is recorded as a gross amount. The legacy engine gives setup-cost VAT no numeric effect (Q-06), so the split would only appear in the report. | M7 | BRD A-05 | Open |
| OI-07 | Approve the dropdown lists: sectors (provisional ISIC Rev. 4 top-level sections), project types, and asset, fixed-cost and job categories. | Provisional lists are seeded by `ReferenceOptionSeeder`. Codes are stable and labels can be edited. | Production | BRD A-08 | Open |
| OI-08 | Provide a few real Cassandra studies (inputs and outputs) for the client-verified acceptance tests. | The engine is tested against outputs generated from the original code, not client-verified (A-12). | M4 acceptance | BRD A-12, M-05 | Open |
| OI-09 | Customers who own studies cannot delete their account, so payment and study records are kept. Confirm this policy. | Deletion is blocked with an Arabic message asking them to contact support. | — | Milestone 2 | Open |
| OI-10 | Confirm the validation ranges the BRD does not specify. | Loan repayment 1–50 years; asset life 0–100 years (0 = never replaced); employee count ≥ 1; annual growth −100% to 1000%; VAT 0–25%. Owner shares above 100% in total are an **error** (the old form only warned). | — | Milestone 3 | Open |
| OI-11 | Confirm how drafts save: empty fields never block a save, but an invalid value blocks that save until it is fixed. | Implemented as described. | — | BRD FR-010, Milestone 3 | Open |
| OI-12 | Confirm the progress rules: each of the 5 sections counts 20%, and empty optional groups don't count. | Implemented and documented in BRD FR-011. | — | Milestone 3 | Open |
| OI-13 | Confirm the preview defaults for unfinished drafts: no study length chosen → 2 years; no VAT chosen → 0%. Both show a warning. | Implemented in `LegacyFormMapper`. | M5 | Milestone 4, §26.3 | Open |
| OI-14 | Confirm the remaining BRD assumptions: A-04 (price fixed at creation, already implemented), A-06 (reopening needs no new payment), A-07 (report versions are immutable), A-09 (report keeps the legacy structure), A-11 (no mandatory email verification). | Built or planned on these assumptions. | M7 / M8 | BRD §24 | Open |
| OI-24 | Approve the figures in the live results panel. FR-029 only suggests a list. | Total capital, loans with interest, self-financing, monthly revenue (year 1, excluding VAT), monthly salaries, monthly fixed costs including loan instalments, total sales, market share, average annual net profit, capital recovery, a yearly revenue/expenses/profit chart and table, the capital split and the setup costs. | — | Milestone 5, BRD FR-029/FR-030 | Open |
| OI-25 | The live preview refreshes when the draft is saved (0.7 s after typing stops, plus the request time). That is about 1 second, the FR-029 target. Confirm this feels live enough. Narrative fields also trigger a save; that doesn't change the figures. | Implemented. The alternative is a separate preview request for unsaved input. | — | Milestone 5, BRD FR-029 | Open |
| OI-26 | Confirm the receipt rules: JPG, PNG or PDF; at most 5 MB; at most 10 uploads a minute. | Implemented. The size limit is configurable (`CERTAINTY_RECEIPT_MAX_KB`). | — | Milestone 6, BRD FR-020 | Open |
| OI-27 | Can a customer replace a receipt that is still under review? | Yes. The new upload replaces it, and the old one stays in the history as "replaced". Once a payment is approved, uploads are locked. | — | Milestone 6, BRD FR-019 | Open |
| OI-28 | Should anyone be notified by email: admins when a receipt arrives, and customers when it is approved or rejected? | No emails yet; the BRD doesn't require them. Admins see the pending count on their dashboard, and customers see the status and any rejection reason on the study page. | — | Milestone 6 | Open |
| OI-29 | Rejection reasons must be 5 to 1000 characters, and approval cannot be undone. Confirm. | Implemented. Undoing a decision would need a future admin correction tool (M8). | M8 | Milestone 6, BRD FR-021 | Open |

## 2. Content and assets (Client)

| ID | Item needed | Current placeholder | Blocks | Source | Status |
|---|---|---|---|---|---|
| OI-15 | Legal review of the Privacy Policy and Terms text. | Draft text written from how the platform works (`pages/public/Privacy.vue`, `Terms.vue`). | Launch | Milestone 2 | Open |
| OI-16 | Brand assets: logo, brand colours, company or institution name, and company stamp for reports. | A simple chart-in-square mark, provisional teal and gold colours. `favicon.ico` and `apple-touch-icon.png` are still Laravel's. | M7 report, launch | BRD FR-033, Milestone 2 | Open |
| OI-17 | Public contact email and phone. | Empty, so the contact card shows text only. Set `CERTAINTY_CONTACT_EMAIL` and `CERTAINTY_CONTACT_PHONE`. | Launch | Milestone 2 | Open |
| OI-18 | Bank account details for transfer instructions: bank, account name, IBAN. | The transfer instructions are built. Set `CERTAINTY_BANK_NAME`, `CERTAINTY_BANK_ACCOUNT_NAME` and `CERTAINTY_BANK_IBAN`. Until then the study page asks customers to contact support for the details. | Launch | BRD FR-018, A-10 | Open |

## 3. Engineering follow-ups (Dev / You)

| ID | Task | Notes | Blocks | Status |
|---|---|---|---|---|
| OI-19 | Visual QA of the landing page, auth pages, study editor and live results panel (sticky column at 2xl, sheet below that) at desktop, tablet and mobile, in light and dark mode. | Not seen rendered: headless screenshots were blocked by the permission system. Manual review needed, or allow the Playwright screenshot command. | Launch | Open |
| OI-20 | A way to create admin users in production, such as an `artisan` command. | Only the seeder creates an admin today, and it is for development. | M8 / launch | Open |
| OI-21 | Production setup: run `ReferenceOptionSeeder` on every deploy, run the scheduler (`studies:expire-drafts` hourly), and define a backup strategy for the database and private files. | Backups are BRD NFR-008. | M9 | Open |
| OI-22 | Delete the unused starter-kit auth layouts (`AuthSimpleLayout`, `AuthCardLayout`, `AuthSplitLayout`). | Replaced by `AuthShowcaseLayout`. | — | Open |
| OI-23 | Git housekeeping: delete the `milestone-1-foundation` branch, merge Milestone 3 and Milestone 4, then merge Milestone 5 and Milestone 6 (`milestone-6-bank-transfers`, stacked on 5). | After each squash-merge, rebase the next branch with `git rebase --onto origin/main <old-base>`. | — | Open |

---

## Resolved

| ID | Item | Resolution |
|---|---|---|
| — | Study fee treatment (BRD O-05) | Option A, confirmed 18 November 2026. |
| — | A-01, A-02, A-05, A-08, A-10 | Confirmed by the client clarification on 18 November 2026 (BRD §24). |
| — | Contact, Privacy and Terms page layout (BRD O-01) | Contact is a landing-page section; Privacy and Terms are separate pages (Milestone 2). |
| — | Q-04 "balance sheet may not balance" | Withdrawn: the original code balances in every reference scenario (BRD §26.5). |
| — | Three skipped tests | Starter-kit two-factor authentication tests. 2FA is disabled and not in V1 scope, so they are expected to skip. |

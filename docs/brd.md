# BUSINESS REQUIREMENTS DOCUMENT (BRD)
# CERTAINTY — VERSION 1 (MVP)

**Version:** 1.1  
**Date:** 18 November 2026  
**Status:** Implementation-ready baseline. Assumptions A-01, A-02, A-05, A-08 and A-10 confirmed (see §24). Remaining open items listed in §25.6.  
**Application type:** Business Feasibility Study SaaS Platform  
**Technology stack:** Laravel 13, PHP 8.4+, Inertia.js, Vue 3, TypeScript, Tailwind CSS, relational database  
**Primary language:** Arabic (RTL)  
**Product name:** Certainty (formerly Cassandra)

---

# 1. Project Overview

## 1.1 Purpose

Certainty is a web-based platform that enables individuals and businesses to create financial feasibility studies through a guided, multi-step process.

The platform shall collect project information, market details, startup costs, financing, employee expenses, operating costs, sales projections, and qualitative business analysis.

It shall calculate financial results using the existing Cassandra financial formulas and produce a professional feasibility-study report.

Version 1 focuses on providing the minimum complete commercial workflow without implementing advanced SaaS functionality.

## 1.2 Business Objectives

1. Launch a redesigned, professional Arabic SaaS website under the Certainty brand.
2. Allow users to register, authenticate, and purchase individual feasibility studies.
3. Enable customers to create and save studies over a configurable completion period.
4. Allow customers to complete their data before their bank-transfer payment has been approved.
5. Preserve the calculation behavior of the existing Cassandra application.
6. Produce readable financial reports with Certainty branding.
7. Allow administrators to verify payments and manage studies.
8. Provide a controlled process for correcting finalized studies when technical issues occur.

## 1.3 Scope Authority

Where requirements conflict, the following order shall apply:

1. The user's eight confirmed V1 decisions and additional notes dated 18 November 2026.
2. The client's latest document, "اخر متطلبات مبدئية".
3. The prior client requirements document, only for compatible details.
4. The existing Cassandra form and JavaScript code, as the legacy functional reference.
5. Implementation recommendations in this BRD, where no explicit business requirement exists.

Any functionality not clearly included in V1 must not be introduced merely because it appears in an earlier requirements document.

## 1.4 Delivery Principle

Version 1 shall be a functional product, not a demonstration.

The delivered application must support the complete workflow from account registration through study creation, financial calculations, manual payment verification, final report access, and administrative management.

---

# 2. User Roles and Permissions

## 2.1 Roles

Version 1 shall support two roles:

**Customer**

An authenticated user who creates, edits, pays for, finalizes, and downloads feasibility studies.

**Administrator**

An authorized employee who manages users, studies, bank-transfer receipts, payment approvals, study corrections, and audit history.

A separate supervisor/owner role is not required in V1.

## 2.2 Permission Matrix

| Action | Visitor | Customer | Administrator |
|---|---|---|---|
| Browse public website | Yes | Yes | Yes |
| Register | Yes | — | — |
| Log in / reset password | Yes | Yes | Yes |
| Create study | No | Yes | No, unless explicitly authorized later |
| Edit active draft | No | Own studies | No direct edits in V1 |
| Save draft | No | Own studies | No |
| View live financial summary | No | Own studies | View existing results |
| Upload bank receipt | No | Own studies | No |
| Approve or reject payment | No | No | Yes |
| Finalize study | No | Own eligible studies | No direct finalization |
| Download final report | No | Own eligible studies | Authorized read-only access |
| Reopen finalized study | No | No | Yes, with required audit reason |
| Extend draft expiration | No | No | Yes |
| View payment history | No | Own studies | All |
| Manage users | No | Own profile only | Yes |
| View audit history | No | No | Yes |

Authorization must be enforced on the server. Hiding a button in Vue is not sufficient.

---

# 3. Public Website

## FR-001 — Marketing Website

### Required landing page (single page)

The public marketing site shall be **one landing page** containing these sections, each reachable from the navigation by an in-page anchor:

1. Home (hero)
2. Features
3. How It Works
4. Pricing
5. Frequently Asked Questions (FAQ)

### Supporting public pages

6. Contact
7. Privacy Policy
8. Terms and Conditions

These remain available as public pages or landing-page sections (see open item O-01 in §25.6).

Authentication and customer-dashboard pages are additional application screens, not part of the marketing site.

### Pricing

- The current study price is **1,200 SAR per study**.
- The price must be configurable server-side (configuration value or `app_settings`). It must not be hardcoded in Vue components or page content.
- The landing page Pricing section and the payment instructions must read the price from the same configured value.
- A price change applies only to studies created after the change (see A-04).

### Functional requirements

- All public pages must be accessible without authentication.
- Navigation shall provide clear access to registration and login.
- Content shall explain what a feasibility study includes and how Certainty works.
- Pricing must be presented before a customer commits to purchasing a study.
- The website should preview the appearance and content of the resulting feasibility report without exposing another customer's information.
- Layout shall support **Arabic right-to-left only**. No left-to-right layout or language switcher is required.
- Light and dark mode shall both be supported across the public site, customer area and admin panel.
- The website must be responsive across mobile, tablet, and desktop devices.
- Basic SEO metadata shall be available for public pages.
- Contact information or a basic contact form shall be provided.

### Design references

- https://casandra.netlify.app/home/start
- https://madrous.com/
- https://mawzoon.ai/
- https://www.jadwaha.com/

These are references for content structure and user experience, not designs to duplicate.

### Acceptance criteria

- Every required page is accessible through navigation.
- Core calls to action direct unauthenticated users to authentication or study creation as appropriate.
- All pages render correctly in Arabic RTL.
- Pricing and the bank-transfer process are explained clearly.
- No account is required to inspect the platform's features.

---

# 4. Authentication and Account Management

## FR-002 — Registration

Users must be able to create accounts with:

- Full name
- Email address
- Password
- Password confirmation

The system must prevent duplicate accounts with the same email address.

Password requirements and form validation shall be enforced by Laravel.

## FR-003 — Login

Users must be able to authenticate using their email and password.

Only authenticated users may access their own dashboard, studies, and financial information.

## FR-004 — Password Reset

The application must provide:

1. Forgot-password screen.
2. Password-reset email.
3. Secure, expiring reset token.
4. New-password confirmation screen.
5. Confirmation or redirect after a successful reset.

## FR-005 — Authentication Security

- Store passwords using Laravel-supported secure password hashing.
- Protect application routes with authentication middleware.
- Apply login and password-reset rate limits.
- Protect forms against CSRF.
- Invalidate sessions appropriately during logout.
- Prevent customers from accessing other customers' records.

### Acceptance criteria

- New users can register and log in.
- Duplicate email registration is rejected.
- Password reset works using a valid reset link.
- Invalid or expired reset tokens are rejected.
- Unauthenticated requests to private pages are redirected or denied.
- A customer's session cannot access another customer's studies.

Email verification is not a mandatory V1 requirement unless separately confirmed.

---

# 5. Customer Dashboard

## FR-006 — Customer Dashboard

After login, the customer shall see a dashboard displaying their feasibility studies.

Each study card or table row shall include:

- Study title
- Project name
- Creation date
- Last updated date
- Study status
- Payment status
- Completion percentage
- Days remaining, where applicable
- Available actions

### Required actions

- Create new study.
- Continue editing an active study.
- View an existing study.
- Upload or replace a payment receipt when permitted.
- View payment approval status.
- Finalize an eligible study.
- Download an available final report.

Customers may purchase and manage more than one study.

Each new study is an independent record with its own financial inputs, payment record, report, and history.

### Acceptance criteria

- Users see only their own studies.
- Multiple studies are supported.
- Each study has a distinct identifier and payment reference.
- Study status and payment status are displayed separately.
- Expired or finalized studies do not offer normal editing controls.
- Available actions change according to the study's lifecycle status.

---

# 6. Feasibility Study Form

## FR-007 — Multi-Step Study Creation

The original Cassandra form shall be redesigned using a guided, multi-step interface.

**Reference form:** https://casandra.netlify.app/home/start. The new form shall be very similar to this reference in fields and content, except that dropdowns are used wherever convenient (FR-008).

The redesigned interface should preserve all calculation-required information from the legacy source. The complete legacy field mapping is in §25.3.

The five main logical sections shall be:

### Step 1 — Basic Project Information (معلومات أساسية)

Fields include:

- Study name
- Project name
- Project start date
- Project description
- Currency
- Decimal precision
- Study duration
- VAT percentage
- Organizational/administrative feasibility description
- Project owners

Each owner entry shall support:

- Name
- Ownership percentage
- Relevant experience

Users can add and remove owners.

### Step 2 — Market Analysis (دراسة السوق)

Fields include:

- Business sector
- Project type
- Target market name (legacy `marketName`)
- Market size/trading volume (annual; required for the market-share calculation)
- Market description
- Products and services in the sector
- Target customers
- Suppliers
- Marketing plan
- Competitive analysis
- Competitors

Each competitor entry may include:

- Name
- Market share
- Competitive characteristics

### Step 3 — Startup and Operating Costs (التكاليف)

This step shall include repeatable groups for:

**Startup assets**

- Asset name/category
- Asset value
- Useful life
- Depreciation rate
- Additional calculation-required flags from the legacy source, where applicable

**Loans**

- Lending institution
- Principal amount
- Interest percentage
- Repayment period

**Monthly fixed costs**

- Expense name/category
- Monthly amount
- Duration and VAT treatment, if required by the original engine

**Employees**

- Job title/category
- Number of employees
- Monthly salary

### Step 4 — Products and Sales (المبيعات)

Each product entry shall include:

- Product name
- Variable cost per unit
- Selling price per unit
- Expected monthly sales quantity
- Monthly production/sales capacity
- Expected annual sales growth percentage

Users can add and remove products.

### Step 5 — SWOT Analysis

Fields include:

- Strengths (نقاط القوة)
- Weaknesses (نقاط الضعف)
- Opportunities (الفرص)
- Threats (المخاطر)

The qualitative descriptions shall be preserved for reporting purposes.

They must not be treated as numerical inputs unless an explicit formula from the original engine depends on them.

## FR-008 — Dropdown Redesign

Where a value is a predefined classification, use a dropdown instead of unrestricted text input.

Expected dropdown candidates include:

- Business sector
- Project type
- Currency
- Study duration
- Decimal precision
- Asset category
- Expense category
- Job category
- VAT applicability

Dropdown fields must not replace numerical inputs or long-form narrative fields.

Business sectors should use a consistent, approved classification list.

A project-type dropdown may provide an "Other" option. The business-sector dropdown should use controlled sector values rather than unrestricted custom entries.

Where no approved classification dataset exists, the implementation shall preserve necessary functional input without inventing authoritative classifications.

## FR-009 — Dynamic Form Rows

The application must support adding and removing:

- Owners
- Competitors
- Startup assets
- Loans
- Fixed costs
- Employee categories
- Products

Every row must be validated independently.

## FR-010 — Save Draft

Users must be able to save incomplete studies without finalizing them.

Draft saving shall preserve all entered information.

An invalid or incomplete field should not prevent saving an otherwise valid partial draft, but it must prevent finalization if the field is required for a complete study.

## FR-011 — Progress Indicator

The interface shall display:

- Current step
- Total steps
- Completed steps or sections
- Study completion percentage
- Remaining days before expiration

Progress must be calculated deterministically using documented rules.

The progress indicator must not be based solely on the number of visited pages.

**Implemented rules (Milestone 3):** progress is computed from the saved input only. Each of the five sections scores the share of its required items that are filled in, and the overall percentage is the rounded average of the five section scores (20% each).
- Owners, setup assets and products need at least one row.
- Every row the customer adds, in any group, must be complete.
- An empty optional group (loans, fixed costs, employees, competitors) adds no item.
- A category of "أخرى" requires a description.

The rules live in `CalculateStudyProgressService`. The percentage is stored on the study on every draft save.

## FR-012 — Input Validation

Validation shall include:

- Required fields
- Numeric input types
- Valid ranges
- Nonnegative quantities where appropriate
- Positive repayment periods
- Valid percentage ranges
- Production capacity consistency
- Loan amount and repayment consistency
- Ownership percentage totals, if a complete ownership list is required
- Duplicate or missing rows where relevant

The original calculation engine's unusual handling of numeric inputs must be documented separately. UI validation must not silently modify legacy formulas.

### Acceptance criteria

- Users can navigate forward and backward without losing information.
- Users can save an incomplete draft.
- Dynamic rows can be added, edited, and removed.
- Dropdowns contain approved values.
- All calculation-required legacy inputs are represented.
- The application prevents finalization when mandatory inputs are missing or invalid.
- Progress persists correctly across sessions.
- No general study attachment upload is available in V1.

---

# 7. Study Lifecycle and Expiration

## FR-013 — Study States

The backend shall manage an explicit study state.

Suggested states:

- `draft`
- `expired`
- `finalized`

Payment approval is an independent state and must not be mixed into the study status.

## FR-014 — Draft Duration

Default completion duration: **60 days**.

The duration must be configurable by the application administrator or through a server-side configuration value.

Recommended fields:

- `created_at`
- `expires_at`
- `finalized_at`
- `reopened_at`, where applicable

## FR-015 — Draft Expiration

Once a draft expires:

- Editing is blocked.
- Existing data remains stored.
- The customer can view the status and a suitable explanation.
- An administrator can extend the expiration date by entering a **number of days**. The new `expires_at` is calculated from that number (see FR-039).
- The extension must be recorded in the audit history.

Expired studies must not be automatically deleted in V1.

## FR-016 — Finalization

Finalization must occur only after an explicit user confirmation.

Suggested button:

**إنهاء الدراسة وإصدار التقرير النهائي**

Before finalization, the backend must verify that:

1. The user owns the study.
2. The study has not expired.
3. All required fields are valid.
4. The calculations can be completed.
5. The bank-transfer payment has been approved.
6. The study has not already been finalized.

After finalization:

- Normal editing is blocked.
- The final data snapshot is retained.
- The financial results are retained.
- The report is generated and made available.
- The study is marked `finalized`.

Draft saving does not finalize a study.

### Acceptance criteria

- The default expiration period is 60 days.
- Expired studies cannot be edited by customers.
- Admin extension permits further editing. The administrator enters the number of days to extend.
- Draft saving remains available until expiration or finalization.
- Finalization requires explicit confirmation.
- Finalization is idempotent and cannot create duplicate report versions through repeated requests.
- Finalized studies remain read-only until an authorized reopening operation.

---

# 8. Manual Bank Transfer and Payment Verification

## FR-017 — Payment Method

The only payment method in Version 1 shall be **manual bank transfer**.

There shall be no online payment gateway integration.

## FR-018 — Purchase Workflow

1. Customer registers or logs in.
2. Customer creates a study.
3. System creates a payment record containing the applicable study price.
4. Customer receives bank-transfer instructions.
5. Customer may continue entering the study information.
6. Customer uploads an image or supported document representing the bank-transfer receipt.
7. Payment status becomes pending review.
8. Administrator reviews the uploaded receipt.
9. Administrator approves or rejects the payment.
10. Approved payment permits final report generation after successful study completion and explicit finalization.

Payment approval is not required to begin entering study information.

## FR-019 — Payment States

Recommended states:

- `pending`
- `under_review`
- `approved`
- `rejected`

Suggested transitions:

- New purchase → `pending`
- Receipt submitted → `under_review`
- Admin approves → `approved`
- Admin rejects → `rejected`
- Rejected customer resubmits receipt → `under_review`

## FR-020 — Bank Receipt Upload

The upload interface shall accept only permitted receipt file formats.

Recommended support:

- JPG
- PNG
- PDF

The application must validate file size, type, authorization, and storage location.

Receipt files must be stored privately and made accessible only to the customer who uploaded them and authorized administrators.

This is the only file-upload feature required for customer input in V1.

**Implemented (Milestone 6):**
- **Accepted files:** JPG, PNG or PDF up to 5 MB (`config/certainty.php` → `receipts`). The type is checked against both the extension and the detected contents.
- **Storage:** files go on the private `local` disk under `receipts/{payment id}/{uuid}`, so the customer's file name never reaches the storage path. They are served only through `GET /receipts/{id}`, with `nosniff` and `no-store`, to the owner or an administrator. Anyone else gets a 404.
- **Rate limit:** uploads are limited to 10 a minute.
- **Replacing a receipt:** a new upload while a receipt is still under review replaces it, and the old one is kept as "replaced". After approval, uploads are refused.

## FR-021 — Payment Approval

The administrator must be able to:

- View the payment reference.
- View the customer and associated study.
- View the expected amount.
- Open the submitted receipt.
- Approve the transfer.
- Reject the transfer with a reason.
- See prior review actions.

A rejected payment may be resubmitted.

**Implemented (Milestone 6):** review details are stored on each receipt in `payment_receipts`: `status`, `reviewed_by`, `reviewed_at` and `rejection_reason`. The list of receipts is therefore the review history. `study_payments` only gains `approved_by` and `approved_at`. The rejection reason is not copied onto the payment, which differs from the column list in §13. Approval and rejection lock the payment row and only act on a payment that is `under_review` and has a submitted receipt. An approved payment cannot be reviewed again. The admin panel adds a payments list (under review first, with status filters and search) and a review page that shows the receipt inline.

## FR-022 — Study Price in Startup Costs

The amount paid for the study must be included under startup expenses in the financial study.

Implementation requirements:

- Store the purchase amount separately from customer-entered costs.
- Inject it as a dedicated system-generated startup-cost line.
- Do not allow the customer to edit that line directly.
- Prevent duplicate inclusion on repeated calculations.
- Ensure its inclusion in calculations and report totals is consistent with the legacy engine.

Suggested name:

**رسوم إعداد دراسة الجدوى**

### Acceptance criteria

- Study data entry remains available while payment approval is pending.
- Report generation/download is blocked unless payment is approved.
- Only admins can approve or reject bank receipts.
- Rejection records a reason.
- A customer can resubmit a rejected receipt.
- Each study has an independent payment record.
- The paid study fee appears exactly once in the startup-cost calculations.
- Payment records cannot be modified by unauthorized users.

---

# 9. Financial Calculation Engine

## FR-023 — Preserve Original Calculation Behavior

Version 1 must reproduce the existing Cassandra financial calculations.

Do not introduce unapproved accounting formula changes during migration.

The original implementation uses JavaScript and jQuery. The new implementation shall move authoritative financial calculation logic to Laravel backend services.

The legacy source is the calculation reference, subject to the approved input mapping and the added study-purchase expense.

## FR-024 — Calculation Modules

The calculation engine must support:

1. Loan and liability calculations.
2. Loan repayment installments.
3. Startup asset totals.
4. Self-financing.
5. Employee salary totals.
6. Monthly fixed costs.
7. Product revenue.
8. Product variable costs.
9. Sales growth projections.
10. Production-capacity limits.
11. Asset depreciation.
12. Asset replacement.
13. VAT-related computations.
14. Annual income statements.
15. Operating, investing, and financing cash flow.
16. Balance-sheet calculations.
17. Profitability figures.
18. Product break-even pricing.
19. Capital recovery indicators.
20. Market-share estimates, where the required input remains available.

## FR-025 — Backend Calculation Boundary

The Laravel backend shall be the authoritative source for financial results.

Input:

A validated structured representation of the study.

Output:

A structured result containing:

- Summary metrics
- Initial capital calculations
- Financing totals
- Annual projections
- Income statements
- Cash-flow statements
- Balance-sheet results
- Product-level metrics
- Financial indicators
- Chart-ready series
- Warnings and validation findings

Do not return pre-rendered HTML as the calculation result.

## FR-026 — Calculation Engine Design

The engine must:

- Accept a structured input object.
- Avoid dependencies on Vue, the DOM, jQuery, and presentation HTML.
- Avoid database writes during pure calculation.
- Return a predictable result structure.
- Separate financial formulas from formatting and report generation.
- Be callable from the draft-preview and finalization workflows.
- Use an explicit calculation version identifier.

Suggested calculation version:

`cassandra_legacy_v1`

## FR-027 — Legacy Compatibility

Before implementation, create a field-to-formula mapping for the original source.

For each source input, document:

- Original input identifier
- New field name
- Data type
- Units
- Default
- Validation requirements
- Formula dependencies
- Whether the field affects numerical output
- Whether the field is report-only

Preserve the original code's relevant rounding, time-period, and state-transition behavior.

Document known financial limitations without silently fixing them.

## FR-028 — Financial Testing

Create automated tests using known input and expected-output fixtures.

At minimum, include:

- No loans
- Interest-free loan
- Interest-bearing loan
- Multiple loans
- Single and multiple products
- Zero sales
- Annual sales growth
- Capacity limits
- Employee costs
- Fixed operating costs
- Depreciating assets
- Asset replacement
- VAT zero and nonzero
- Multi-year projections
- Negative profitability
- Market-share calculations
- Study purchase fee added once

Existing Cassandra results shall be used as regression references where possible.

Any differences between the original and migrated results must be documented.

### Acceptance criteria

- Backend calculations do not require browser-side computation.
- Identical normalized input produces deterministic results.
- Regression fixtures pass within an explicitly approved numerical tolerance.
- All financial output displayed to users originates from the backend calculation result.
- Calculation logic is covered by automated tests.
- Formatting and chart generation do not alter the calculated values.
- No accounting formula corrections are implemented without a documented scope change.

---

# 10. Live Financial Summary and Charts

## FR-029 — Draft Preview

While editing a study, the customer shall see a basic live financial summary.

**Live updates:** whenever the customer changes a field that affects a calculation (for example asset values, loans, salaries, fixed costs, product prices, quantities, VAT or study years), the affected figures and charts shall update as the customer types, without a manual save or refresh. Narrative-only fields (descriptions and SWOT) do not trigger recalculation. This matches the legacy form, which recalculated on every keystroke.

Suggested indicators based on available legacy results:

- Total capital
- Total monthly costs
- Expected revenue
- Expected profit
- Loan repayment amount
- Basic profitability indicators

Preview values shall use backend calculation results.

The frontend may debounce requests to avoid excessive server calls. The debounce must be short enough that the update still feels live (target: results visible within about one second of the last keystroke).

**Implemented (Milestone 5):** the preview is part of the draft autosave. The editor saves 0.7 s after the last keystroke, and the save response reloads `progress` and `preview` together, so the figures always come from `FeasibilityCalculationService` and match the stored draft. While a save is blocked by an invalid value, the panel keeps the last results and marks them as waiting for the save. The panel shows total capital, loans with interest, self-financing, expected monthly revenue, monthly salaries, monthly fixed costs (including loan instalments), total sales, market share, average annual net profit and capital recovery. It also shows the revenue, expenses and profit chart and yearly table, the capital split, the setup costs and the FR-031 warnings, each with a link to its step. A draft that cannot be calculated (for example, figures that overflow) shows a notice instead of results and never blocks editing.

## FR-030 — Charts

Version 1 shall include basic visualizations using the existing financial projection data.

Recommended charts:

- Revenue versus expenses
- Profit by year
- Capital distribution
- Initial cost distribution

Only implement charts supported by actual calculation outputs.

## FR-031 — Validation Warnings

Show helpful warnings when inputs are invalid or inconsistent.

Examples:

- Sales quantity exceeds permitted capacity.
- Loan repayment period is invalid.
- Required costs or product values are missing.
- Financial values are negative where disallowed.
- A forecast cannot be calculated because required data is incomplete.

Losses or unfavorable profitability may be displayed as informational financial warnings without claiming that the system has performed a professional investment risk assessment.

### Acceptance criteria

- Preview figures update after changes to financial inputs.
- The preview works for valid partial data where possible.
- Invalid data produces a clear message rather than a server error.
- Charts use the same backend results as the report.
- Basic input warnings appear before finalization.
- Optimistic, expected, and pessimistic scenarios are not part of V1.

---

# 11. Final Feasibility Report

## FR-032 — Report Content

The final report shall preserve the essential structure and narrative content of the existing Cassandra report.

The redesigned report must support:

- Study identification
- Project overview
- Project owners
- Market analysis
- Competitor information
- Startup expenses
- Loans and liabilities
- Employees and fixed costs
- Products and sales
- SWOT analysis
- Financial projections
- Income statements
- Cash-flow statements
- Balance-sheet information
- Profitability indicators
- Charts
- Study conclusions and available legacy outputs

Report sections must be driven by the submitted inputs and calculation results.

## FR-033 — Branding

Reports shall include:

- Certainty branding
- Certainty logo
- Institution/company name
- Company stamp, if approved branding assets are supplied
- A professional, consistent layout

## FR-034 — Report Output

The primary required report format is PDF.

Customers shall be able to view their available finalized study and download or print its PDF.

## FR-035 — Final Report Access

The system shall block final PDF access unless:

- Payment has been approved.
- Study is finalized.
- Requesting user is authorized.

Reports must not be publicly accessible through predictable file paths.

## FR-036 — Report Snapshot

A generated final report must correspond to an immutable version of the study input and calculation output.

The system should store:

- Input snapshot
- Calculation result snapshot
- Calculation version
- Report version number
- Generated timestamp
- PDF storage path

### Acceptance criteria

- A completed, paid study can be finalized.
- Finalization generates a PDF report.
- The report includes the supported financial statements and descriptive sections.
- Arabic text and tables render correctly.
- The same final report can be downloaded repeatedly.
- Unapproved payments cannot access final reports.
- Changes made after an authorized reopening produce a new report version rather than silently overwriting the existing history.

**Implemented (Milestone 7):**
- **Finalizing:** `POST /studies/{id}/finalize` requires `confirmed` to be accepted. `FinalizeStudyAction` locks the study row and re-checks the FR-016 conditions (`FinalizationReadiness`: not expired, every section complete, figures calculable, payment approved). It then writes `study_reports` version `current_version + 1` with:
  - the normalized input, plus the title, project name and frozen dropdown labels
  - the full `FeasibilityCalculationService` result and the calculation version

  Finally it marks the study `finalized`. Repeating the request returns the existing report.
- **PDF generation:** `spatie/laravel-pdf` v2 with the `chrome` driver (chrome-php/chrome; a Chromium binary on the server, no Node). The `GenerateStudyReportPdf` job renders `resources/views/reports/study.blade.php` from the snapshots only, after the transaction commits. The download route renders it on demand if the job hasn't finished, and a cache lock prevents rendering it twice.
- **Fonts and charts:** IBM Plex Sans Arabic (OFL, in `resources/fonts`) is embedded, so rendering never depends on the server's fonts. Charts are static SVG drawn in PHP.
- **Report layout:** follows the legacy `OUTPUT_PART*` sections and labels. The financial statements are shown as tables of up to five years each instead of one table per year.
- **Access:** `GET /studies/{id}/report[?version=&download=1]` is open to the owner or an administrator, only when the study is finalized and its payment approved. Everyone else gets 404 or 403. Files are stored privately under random names.

---

# 12. Administrative Dashboard

## FR-037 — Administration Overview

Version 1 shall provide an essential administrative interface.

**Design standard:** after login, administrators land in a dedicated, professionally designed admin panel with its own layout, navigation, dashboard widgets, data tables, filters and status badges. Reusing the starter-kit dashboard and sidebar unchanged is not acceptable. The panel must follow the same Certainty branding, Arabic RTL layout and light/dark mode as the rest of the application.

Required modules:

1. Dashboard summary
2. Users
3. Studies
4. Payment receipts
5. Payment approval/rejection
6. Study reopening and expiration extension
7. Action history

## FR-038 — User Management

The administrator can:

- List users.
- Search users by name or email.
- View user profiles.
- View studies belonging to a user.
- View payment statuses.

Destructive user-account management is not mandatory in V1.

## FR-039 — Study Management

The administrator can:

- List all studies.
- Filter by study status.
- Filter by payment status.
- Search by customer or study name.
- Inspect study inputs and results in read-only mode.
- View creation, expiration, and finalization timestamps.
- Extend an expired or expiring draft by entering a number of days and a reason. For an expired draft the extension counts from the time of extension. For a draft that has not expired yet, it is added to the current `expires_at` (see open item O-02).
- Reopen a finalized study when a technical issue is reported.

## FR-040 — Reopening Studies

Reopening requires:

- Administrator authentication.
- A mandatory reason.
- A recorded timestamp.
- Identification of the administrator.
- Identification of the original report version.

After reopening:

- The authorized customer may edit the study again.
- The previous finalized report remains in version history.
- The new version must be explicitly finalized.
- A new calculation snapshot and report version are generated.

## FR-041 — Payment Administration

The administrator can:

- View submitted bank receipts.
- Approve a payment.
- Reject a payment.
- Record rejection reasons.
- Inspect payment history.

## FR-042 — Audit History

The system must record critical actions, including:

- Payment receipt submission.
- Payment approval.
- Payment rejection.
- Draft expiration extension.
- Study finalization.
- Study reopening.
- Final report version generation.

Each event should contain:

- Action type
- Actor ID
- Study or payment ID
- Timestamp
- Relevant previous/new state
- Mandatory explanation when required

### Acceptance criteria

- Only administrators can access administrative routes.
- Admin filters work on actual stored records.
- Payment approvals and rejections update customer-facing statuses.
- Reopening requires a reason.
- Previous report versions remain preserved.
- Every privileged state change is recorded in audit history.

---

# 13. Recommended Database Model

The following schema is a recommended implementation design. It is not a requirement to create a separate physical table for every conceptual entity.

## 13.1 Core Entities

### `users`

- `id`
- `name`
- `email`
- `password`
- `role`
- `timestamps`

### `studies`

- `id`
- `user_id`
- `title`
- `project_name`
- `status`
- `expires_at`
- `finalized_at`
- `current_version`
- `calculation_version`
- `timestamps`

### `study_inputs`

- `id`
- `study_id`
- `input_data` (JSON)
- `schema_version`
- `timestamps`

The JSON object should follow a documented input schema rather than an arbitrary unvalidated blob.

### `study_payments`

- `id`
- `study_id`
- `user_id`
- `reference_number`
- `amount`
- `currency`
- `status`
- `approved_by`
- `approved_at`
- `rejection_reason`
- `timestamps`

### `payment_receipts`

- `id`
- `study_payment_id`
- `uploaded_by`
- `file_path`
- `file_name`
- `mime_type`
- `file_size`
- `submitted_at`
- `timestamps`

### `study_reports`

- `id`
- `study_id`
- `version`
- `input_snapshot` (JSON)
- `calculation_snapshot` (JSON)
- `calculation_version`
- `pdf_path`
- `generated_at`
- `timestamps`

### `study_extensions`

- `id`
- `study_id`
- `previous_expires_at`
- `extension_days`
- `new_expires_at`
- `reason`
- `approved_by`
- `created_at`

### `study_audit_logs`

- `id`
- `study_id`
- `actor_id`
- `action`
- `old_values` (JSON)
- `new_values` (JSON)
- `reason`
- `created_at`

### `sectors`

- `id`
- `name_ar`
- `code`
- `is_active`
- `sort_order`

### `reference_options`

- `id`
- `group`
- `code`
- `label_ar`
- `is_active`
- `sort_order`

This can hold controlled options such as project types, expense categories, and asset categories.

### `app_settings`

- `key`
- `value`
- `type`

Suitable for settings such as default study duration, study price, company name, and supported currencies.

## 13.2 Relationships

- One user has many studies.
- One study belongs to one user.
- One study has one current editable input set.
- One study has one primary purchase/payment record for V1.
- One payment may have multiple receipt submissions.
- One study has many report versions.
- One study has many audit events.
- One study may have multiple expiration extensions.

Foreign-key constraints and appropriate indexes must enforce these relationships.

Use database transactions for finalization, payment approval, and other critical state changes.

---

# 14. Backend Architecture

## 14.1 Laravel Modules

Recommended organization:

**Authentication**

- Registration
- Login
- Password reset
- Authentication middleware

**Studies**

- Study creation
- Ownership authorization
- Draft updates
- Progress calculation
- Expiration
- Finalization

**Financial Calculation**

- Input normalization
- Loan calculations
- Asset calculations
- Operating costs
- Revenue forecasting
- Tax calculations
- Financial statements
- Financial indicators

**Payments**

- Purchase creation
- Receipt upload
- Receipt validation
- Payment review
- Approval/rejection

**Reports**

- Report input snapshots
- PDF generation
- Version history
- Authorized downloads

**Administration**

- User listing
- Study listing
- Payment review
- Expiration extensions
- Study reopening
- Audit log

## 14.2 Suggested Laravel Classes

Possible names:

- `CreateStudyAction`
- `UpdateStudyDraftAction`
- `CalculateStudyProgressService`
- `StudyExpirationService`
- `FinalizeStudyAction`
- `ReopenStudyAction`
- `SubmitBankTransferReceiptAction`
- `ApproveStudyPaymentAction`
- `RejectStudyPaymentAction`
- `FeasibilityCalculationService`
- `StudyReportGenerator`
- `StudyAuditService`

The financial engine may internally use smaller calculators for loans, products, assets, cash flow, and reporting indicators.

## 14.3 Calculation Input Contract

Illustrative structure only; finalize field names after extracting the complete legacy input mapping.

```json
{
  "settings": {
    "currency": "SAR",
    "study_years": 3,
    "vat_percent": 15
  },
  "project": {},
  "market": {},
  "owners": [],
  "competitors": [],
  "assets": [],
  "loans": [],
  "fixed_costs": [],
  "employees": [],
  "products": [],
  "swot": {},
  "system_expenses": []
}
```

## 14.4 Calculation Output Contract

```json
{
  "calculation_version": "cassandra_legacy_v1",
  "summary": {},
  "capital": {},
  "financing": {},
  "yearly_projections": [],
  "income_statements": [],
  "cash_flows": [],
  "balance_sheets": [],
  "product_pricing": [],
  "indicators": {},
  "charts": {},
  "warnings": []
}
```

## 14.5 Backend Responsibilities

Laravel shall own:

- Authentication and authorization
- Study lifecycle state
- Draft persistence
- Payment authorization
- Financial calculation results
- PDF generation
- Report versioning
- Audit history
- Input validation

Vue shall not be considered the authoritative source of financial values.

---

# 15. Frontend Architecture

## 15.1 Required Inertia Pages

### Public

- `Landing` (sections: Home, Features, How It Works, Pricing, FAQ)
- `Contact`
- `PrivacyPolicy`
- `Terms`

### Authentication

- `Login`
- `Register`
- `ForgotPassword`
- `ResetPassword`

### Customer

- `Dashboard`
- `Studies/Index`
- `Studies/Create`
- `Studies/Edit`
- `Studies/Show`
- `Studies/Payment`
- `Studies/Report`

### Administration

- `Admin/Dashboard`
- `Admin/Users/Index`
- `Admin/Users/Show`
- `Admin/Studies/Index`
- `Admin/Studies/Show`
- `Admin/Payments/Index`
- `Admin/Payments/Show`
- `Admin/AuditLogs/Index`

These are suggested component names, not mandatory routes or filenames.

## 15.2 Reusable Vue Components

Recommended components:

- `AppLayout`
- `PublicLayout`
- `CustomerLayout`
- `AdminLayout`
- `StudyStepper`
- `StudyProgress`
- `StudyExpirationNotice`
- `FormField`
- `SelectField`
- `RepeatableFieldGroup`
- `FinancialSummary`
- `FinancialChart`
- `ValidationWarning`
- `PaymentStatusBadge`
- `BankReceiptUploader`
- `FinalizeStudyDialog`
- `StudyStatusBadge`

## 15.3 Frontend Behavior

- Use Inertia form handling for ordinary mutations.
- Use explicit backend endpoints for calculations and autosave where appropriate.
- Debounce financial previews.
- Preserve draft input while navigating steps.
- Prevent accidental data loss.
- Display submission and validation feedback.
- Keep report snapshots separate from editable draft state.

---

# 16. Core Workflow Specifications

## Workflow A — Create a Study

1. Visitor opens the public website.
2. Visitor selects Create Study.
3. If unauthenticated, redirect to registration/login.
4. Authenticated customer enters basic study information.
5. System creates a draft with a unique study ID.
6. System assigns its expiration date.
7. System creates the corresponding pending payment.
8. Customer is redirected to the study editor.
9. Customer can continue completing the study.

## Workflow B — Save and Resume

1. Customer edits an active draft.
2. Customer saves changes.
3. Backend validates that the user owns the draft and it is editable.
4. Backend stores valid partial input data.
5. Backend updates progress.
6. Customer leaves the application.
7. Customer returns before the expiration date.
8. Previous entries are restored.

## Workflow C — Bank Transfer

1. Customer opens study payment instructions.
2. System displays the study price, payment reference, and configured bank details.
3. Customer transfers the payment externally.
4. Customer uploads a receipt.
5. Backend records receipt and marks the payment for review.
6. Administrator approves or rejects.
7. Customer sees the updated status.
8. Approved payment enables final report access once the study has been finalized.

## Workflow D — Finalize Study

1. Customer completes all required fields.
2. Application checks finalization readiness.
3. If payment is not approved, explain that approval is required.
4. If all checks pass, show the finalization confirmation.
5. Customer explicitly confirms.
6. Backend recalculates using the stored validated inputs.
7. Backend creates immutable input and calculation snapshots.
8. Backend generates the report.
9. Backend marks the study as finalized.
10. Customer receives access to the report.

## Workflow E — Draft Expiration

1. Application identifies a draft whose expiration date has passed.
2. Backend rejects new customer editing operations.
3. Customer sees the expired state.
4. Existing data remains preserved.
5. Administrator may grant an extension.
6. The extension is logged.
7. Customer may continue editing within the new deadline.

## Workflow F — Reopen Finalized Study

1. A technical problem is reported.
2. Administrator opens the study.
3. Administrator provides a reason for reopening.
4. Backend records the previous finalized version and the reopening action.
5. Study returns to an editable state.
6. Customer corrects the input data.
7. Customer explicitly finalizes again.
8. Backend creates a new report version.
9. Both report versions remain in history.

---

# 17. Security Requirements

## NFR-001 — Authorization

All private operations require server-side authorization.

Use Laravel Policies or equivalent authorization mechanisms.

## NFR-002 — Sensitive File Handling

Payment receipts and generated reports must be stored in private locations.

Do not expose private files directly from publicly accessible storage URLs.

Downloads must use authorization-checked endpoints.

## NFR-003 — File Validation

Validate receipt uploads for:

- Allowed MIME types
- Allowed extensions
- Maximum file size
- Valid file contents
- Safe storage names

## NFR-004 — Data Integrity

Financially significant operations must use database transactions where necessary.

Prevent duplicate finalization and inconsistent payment approval states.

## NFR-005 — Auditability

Administrative changes to study or payment state must be attributable to an authenticated actor.

## NFR-006 — Secrets

Passwords, mail credentials, application secrets, and private payment configuration must not be embedded in client-side code.

## NFR-007 — Validation

Do not trust frontend calculations, hidden fields, or client-submitted monetary totals as authoritative.

## NFR-008 — Backup and Recovery

The production system must have a documented database and private-file backup strategy.

---

# 18. Non-Functional Requirements

## Performance

- Avoid unnecessary full-page reloads.
- Use pagination for administrative lists.
- Debounce financial preview requests.
- Avoid recalculating the same study multiple times during one finalization.
- Use queued processing for PDF generation if needed.

## Accessibility and UX

- Arabic RTL is mandatory and is the only supported layout direction.
- Light and dark mode are supported throughout. System preference is the default and the user can override it.
- Forms must have understandable labels and validation messages.
- Navigation must be accessible by keyboard.
- Financial values must display with the selected currency.
- Date and number formatting must be consistent.
- Mobile users must be able to complete the study.

## Maintainability

- Keep business rules out of controllers and Vue components.
- Keep presentation formatting out of calculator classes.
- Use migrations and seeders for database structure and reference data.
- Add automated tests for all important state transitions.
- Keep calculation versions explicit.

## Observability

Log failures involving:

- Calculation errors
- Payment-review operations
- PDF generation
- Failed uploads
- Unauthorized access attempts

Do not write passwords, private financial input payloads, or uploaded receipt contents into ordinary application logs.

---

# 19. Explicit Version 1 Exclusions

The following are **not part of the approved minimum V1 scope**:

1. AI-generated descriptions or AI financial analysis.
2. Online payment gateway integration.
3. Mada, Apple Pay, Visa, Mastercard gateway processing.
4. Tabby or Tamara.
5. Recurring subscriptions.
6. Subscription plans or tiered memberships.
7. Coupons and promotional codes.
8. Automated refunds or payment-provider refund integrations.
9. Advanced CRM and customer-support ticketing.
10. Employee performance tracking.
11. Multiple administrative permission levels.
12. Optimistic/pessimistic scenario comparison.
13. Newly developed accounting formulas beyond the existing engine.
14. Automated external market research.
15. External market-data API integration.
16. General project-file attachments.
17. AI document analysis.
18. Public study-sharing links.
19. Real-time collaboration or shared editing.
20. Word and Excel report exports.
21. Multi-language UI beyond Arabic.
22. Full content-management-system functionality.
23. Blog publishing platform.
24. Customer-facing notification center or advanced reminder automation.
25. Integration with external accounting systems.
26. SMS/WhatsApp integrations.
27. Historical data migration from the old platform.
28. Full enterprise analytics or business-intelligence dashboards.

These features may be implemented in later phases through separate approved requirements.

---

# 20. Delivery Milestones for the Coding Agent

## Milestone 1 — Foundation

- Laravel application setup.
- Inertia and Vue setup.
- Tailwind styling.
- RTL application layouts.
- Authentication.
- Database migrations.
- Authorization structure.

**Done when:** Users can register, log in, reset passwords, and access protected dashboard routes.

## Milestone 2 — Public Website and Dashboard

- Public SaaS pages.
- Certainty branding.
- Customer dashboard.
- Study listing.
- Study creation.

**Done when:** A customer can navigate the website and create a new draft study.

## Milestone 3 — Study Form and Draft Lifecycle

- All five study sections.
- Dynamic rows.
- Dropdown reference data.
- Input validation.
- Draft persistence.
- Progress calculation.
- Expiration handling.

**Done when:** A customer can complete, save, leave, and resume a study without losing data.

## Milestone 4 — Financial Engine

- Legacy input mapping.
- Laravel financial calculation services.
- Formula migration.
- Calculation response schema.
- Regression tests.

**Done when:** Valid study inputs produce independently testable financial results from Laravel.

## Milestone 5 — Live Results

- Financial summary panel.
- Charts.
- Basic input warnings.
- Calculation preview integration.

**Done when:** Changes to valid numerical inputs update the financial preview without finalization.

## Milestone 6 — Bank Transfers

- Payment record creation.
- Bank-transfer instruction page.
- Receipt upload.
- Administrator review.
- Approval/rejection workflow.

**Done when:** A customer can submit payment evidence and an administrator can approve or reject it securely.

## Milestone 7 — Report and Finalization

- Finalization validation.
- Explicit confirmation.
- Report snapshot.
- Branded PDF generation.
- Authorized download.
- Study locking.

**Done when:** An approved, completed study can be finalized and its report downloaded.

## Milestone 8 — Administration and Recovery

- User and study management.
- Draft extensions.
- Study reopening.
- Audit logging.
- Report version history.

**Done when:** Administrators can manage the essential operational workflow without directly modifying financial data.

## Milestone 9 — QA and Release

- Automated tests.
- End-to-end acceptance tests.
- Security checks.
- Deployment setup.
- Production configuration.
- Backup strategy.
- Basic deployment documentation.

**Done when:** All approved V1 acceptance scenarios pass.

---

# 21. Required Automated Test Coverage

## Authentication

- Register successfully.
- Reject duplicate email.
- Log in successfully.
- Reject invalid credentials.
- Reset password with valid token.
- Reject expired reset token.

## Studies

- Create draft.
- Edit own draft.
- Prevent editing another customer's draft.
- Save incomplete draft.
- Restore saved input.
- Add and remove repeatable rows.
- Enforce finalization validation.
- Expire draft after configured deadline.
- Prevent expired draft editing.
- Extend expired draft as admin.
- Create multiple studies for one customer.

## Payments

- Create payment record for study.
- Upload valid receipt.
- Reject unsupported files.
- Prevent unauthorized receipt access.
- Approve as admin.
- Reject as admin with reason.
- Resubmit rejected receipt.
- Prevent non-admin approval.
- Prevent duplicate approval side effects.

## Financial Calculations

- Compare defined source fixtures against backend output.
- Verify yearly projections.
- Verify loan calculations.
- Verify depreciation.
- Verify VAT behavior.
- Verify sales-capacity limits.
- Verify startup-fee injection occurs exactly once.
- Verify deterministic output for identical inputs.

## Reports

- Block report before payment approval.
- Block final report for incomplete study.
- Finalize eligible study.
- Generate PDF.
- Prevent finalization duplication.
- Prevent editing finalized study.
- Reopen finalized study as admin.
- Preserve historical report versions.
- Prevent cross-customer report access.

---

# 22. Final Acceptance Criteria

Version 1 shall be considered functionally complete only when all of the following are true:

- All mandatory public pages are available.
- Registration, login, and password reset work.
- Customers can create multiple feasibility studies.
- The entire study form works with required dropdowns and dynamic sections.
- Drafts persist and expire according to the configured period.
- Customers can enter study data before bank-transfer approval.
- Administrators can verify uploaded bank receipts.
- Financial calculations run using the migrated legacy engine.
- Live previews and basic charts display backend-generated values.
- Required financial and qualitative outputs appear in the redesigned report.
- A final report cannot be obtained without approved payment.
- Study finalization requires explicit user confirmation.
- Finalized studies are read-only.
- Administrators can extend drafts and reopen finalized studies.
- Study and payment history is auditable.
- Private data, receipts, and reports are protected by authorization.
- Automated tests for core financial and transactional behavior pass.
- The application is deployable using documented production configuration.

---

# 23. Coding Agent Instructions

An AI coding agent receiving this BRD must follow these rules:

1. Treat the BRD as the functional scope baseline.
2. Do not invent additional product functionality.
3. Distinguish confirmed requirements from recommended implementation decisions.
4. First inspect the existing repository and project configuration.
5. Review the full Cassandra calculation source before implementing any financial formulas.
6. Create a feature-level implementation plan before writing substantial code.
7. Implement one vertical feature at a time, including migrations, backend logic, authorization, UI, and tests.
8. Keep controllers thin and business rules in dedicated services/actions.
9. Use Laravel backend calculations as the source of truth.
10. Never generate financial results by guessing missing formulas.
11. Do not silently correct legacy accounting behavior.
12. Do not use frontend-only validation for security-critical operations.
13. Implement the study and payment states explicitly.
14. Protect all customer resources with ownership authorization.
15. Preserve historical study reports during authorized reopening.
16. Keep all user-facing content Arabic and RTL by default.
17. Use clear, testable acceptance criteria to mark each feature complete.
18. Stop and flag business-rule conflicts that could alter financial results, payment entitlement, or report immutability.
19. Do not classify a feature as complete until its relevant tests pass.
20. Keep V1 exclusions out of the initial implementation.

**Final instruction:** Convert this BRD into a prioritized, dependency-aware engineering backlog. For each feature, identify its database changes, backend components, Inertia pages, Vue components, authorization rules, tests, and definition of done. Do not start by building unrelated landing-page decoration while core study and payment workflows remain undefined.

---

# 24. Remaining Assumptions Requiring Confirmation

This BRD intentionally uses the following implementation assumptions. They are reasonable starting points but are not all explicitly confirmed by the client's final agreement.

**A-01 — Expiration begins at study creation. ✅ CONFIRMED**  
- Set `expires_at = created_at + 60 days`.
- The 60-day duration must be configurable, with 60 as the default.
- Customers can create, edit, and save studies while payment is pending.
- Payment approval does not reset or extend the deadline.
- After expiration, editing is locked.
- Administrators can extend the deadline by entering a number of days. The reason and action are recorded in audit history.
- Finalized studies remain locked regardless of the expiration date.

**A-02 — Payment approval is required for finalization. ✅ CONFIRMED**  
- Customers can complete all form steps and save their studies before payment approval.
- They can view the basic live financial summary and charts during editing.
- They cannot finalize the study, generate the final PDF, or download the final report until payment status is `approved`.
- The backend must enforce this restriction, not just the Vue frontend.
- After approval, the customer can explicitly finalize the study, provided the study is complete, valid, and not expired.
- Saving a draft must never finalize or lock it.

**A-03 — One primary payment per study.**  
Each study has one purchase price and one primary payment record, with multiple possible receipt attempts. Separate refunds and additional charges are excluded.

**A-04 — Price is fixed when a study is created.**  
Changing the platform's advertised price later will not change the price of existing study purchases.

**A-05 — Study purchase fee is a one-time, non-depreciable setup expense. ✅ CONFIRMED**  
- Automatically add a dedicated entry named `رسوم إعداد دراسة الجدوى`.
- Use the actual amount charged for that particular study.
- The fee is a one-time expense, not a depreciating fixed asset.
- Set depreciation to `0%`.
- Do not apply asset replacement or recurring annual expense logic.
- Store the fee separately from user-entered costs to prevent duplication.
- VAT treatment must be based on the actual invoice:
  - If VAT is included, separate the VAT-exclusive amount and VAT amount using the applicable rate.
  - If VAT is not applicable, use zero VAT.
  - Do not assume the fee automatically attracts 15% VAT.
- Apply the fee exactly once and ensure it flows consistently into the financial statements.

**Important:** Preserve the existing Cassandra financial formulas. If the legacy engine cannot correctly represent a one-time non-depreciable expense, isolate the required fee-handling adjustment and document it rather than modifying unrelated financial formulas. See §25.5 for how the legacy engine would treat this entry. That section raises a conflict that needs a decision.

**A-06 — Reopening preserves the payment.**  
An administrator reopening a finalized study for technical correction does not require the user to pay again.

**A-07 — Report versions are immutable.**  
Previous finalized versions remain available internally for audit and troubleshooting, even after a new final report is generated.

**A-08 — The development team prepares reference data and the client approves it. ✅ CONFIRMED**  
- Use an official Saudi business-activity classification source for sectors where applicable.
- Store predefined options in database tables, not hardcoded inside Vue components.
- The client must approve the initial business-sector list.
- Other dropdowns can be prepared from the existing form and approved during development.

| Field | Implementation |
|---|---|
| Business sector | Approved official classification list; no free-text option |
| Project type | Dropdown with an "Other" option |
| Currency | Predefined supported currencies |
| Study duration | Predefined durations supported by the engine (legacy: 1, 2, 3, 5, 10, 15 years) |
| Asset category | Predefined categories with optional custom description |
| Fixed-cost category | Rent, utilities, maintenance, etc., with an "Other" option |
| Job category | Predefined common job categories with an "Other" option |
| VAT applicability | Yes/No |
| Decimal precision | Predefined numeric options (legacy: 0–3) |

- Provide database seeders for the approved datasets.
- Preserve stable IDs/codes even when labels change.
- The dropdown selection must not alter financial formulas unless an explicit rule requires it.
- If the official dataset is unavailable or awaiting approval, use clearly marked provisional data in development and do not represent it as an approved official classification.
- The V1 admin panel does not require a dropdown-management interface. Seeders or configuration manage the initial datasets.

**A-09 — Reports use the existing content structure.**  
The report receives a visual redesign but no additional professional accounting certification or financial-analysis methodology.

**A-10 — Administrative prices and configuration. ✅ CONFIRMED**  
The initial study price is **1,200 SAR**. The study price, bank account details and draft duration are server-configurable. A complex settings-management interface is not required unless separately approved.

**A-11 — Email verification.**  
Registration does not require mandatory email verification beyond what the approved authentication specification includes. Password-reset emails remain required.

**A-12 — Reporting acceptance fixtures.**  
Before final financial-engine acceptance, the customer or project team must approve representative input/output examples. Test data can be created during development, but must not be presented as verified original-system results without comparison.

---

# 25. Legacy Cassandra Source Analysis

This section records what the supplied legacy material contains, what is missing, and the legacy calculation behavior that FR-023 and FR-027 require the migration to preserve.

## 25.1 Supplied Material

| File | Contents | Usable as |
|---|---|---|
| `docs/cassandra Main source  (1).docx` | The complete jQuery form, the calculation engine (`fieldsUpdated()`, `otherFieldsUpdated()`), the dynamic-row builders, save/restore (`pushToFeasibilityForm()`) and the report output markup (`OUTPUT_PART1`–`OUTPUT_PART3`). | **Authoritative calculation reference** for `cassandra_legacy_v1`. |
| `docs/casandar.html` | Only the empty HTML shell of the Netlify React build (`cassandra-react`). The form and logic live in `/static/js/main.8b638a1a.chunk.js`, which is not included. | Not usable as source. The live site https://casandra.netlify.app/home/start remains the visual and field reference for the form (FR-007). |

## 25.2 Missing Material

| ID | Missing item | Impact |
|---|---|---|
| M-01 | Helper `readableAmount(value, decimals, currency)` is called throughout but not defined in the source. | Display formatting only (rounding, thousands separators, negative format). Must be reproduced or approved before report-format acceptance. Values are unaffected. |
| M-02 | Helpers `htmlspecialchars()`, `popup()`, `tool_toggle()`, `tools_renew_captcha()` are not defined. | UI only. Not needed for V1. |
| M-03 | Server endpoint `submission.php` (save, generate PDF, view link) is not included. | Report layout is reconstructed from the `OUTPUT_PART*` markup instead. No server-side formulas are expected there. Confirm with the client (O-04). |
| M-04 | The React bundle for the Netlify reference form. | We cannot confirm that the Netlify form uses the same formulas as the docx source. The docx is treated as authoritative (O-03). |
| M-05 | Regression fixtures: real legacy inputs with the outputs they produced. | Required by FR-028 and A-12. They can be produced by running the legacy code with agreed inputs, but the client must approve them. |

## 25.3 Field Mapping (legacy → V1)

Legacy saves two JSON blobs. `inputData1` holds the calculation inputs and `inputData2` holds the narrative inputs.

| Legacy id / key | Meaning | Type | Legacy default / range | Affects numbers | V1 step |
|---|---|---|---|---|---|
| `currencyName` / `_cn` | Currency symbol | dropdown (≈165 options) | fallback `د.ك.` | No (display) | 1 |
| `currencyDecimals` / `_cd` | Decimal places | dropdown 0–3 | fallback 2 | Display only (see Q-01) | 1 |
| `currencyVAT` / `_cvat` | VAT % | dropdown 0–25 | out of range → 0 | **Yes** | 1 |
| `studyYears` / `_sy` | Projection years | dropdown 1, 2, 3, 5, 10, 15 | UI default 2, fallback 3 | **Yes** | 1 |
| `projectName` | Project name | text | — | No | 1 |
| `projectBrief` | Project description | textarea | — | No | 1 |
| `projectOF` | Organizational feasibility | textarea | — | No | 1 |
| `Owner[].Name / Share` | Owners | rows | share must be > 0 | No (warning if total > 100) | 1 |
| `marketName` | Target market name | text | — | No | 2 (**missing from §6, add**) |
| `marketValue` | Annual market size | number | 0 | **Yes** (market share) | 2 |
| `marketDescription`, `marketProducts`, `marketTargetSegment`, `marketVendors`, `marketPlan`, `competitionDescription` | Market narratives | textarea | — | No | 2 |
| `Competitor[].Name / Share` | Competitors | rows | share must be > 0 | No (warning if total > 100) | 2 |
| `Asset[].Name / Value / LifeSpan / Depreciation / IncludesVAT` | Setup costs | rows | LifeSpan 0 = never replaced | **Yes** (IncludesVAT: no effect, see Q-06) | 3 |
| `Liability[].Bank / Amount / Interest / Period` | Loans (annual interest %, years) | rows | Period 0 → row dropped | **Yes** | 3 |
| `Employee[].Title / Total / Salary` | Staff (count, monthly salary) | rows | — | **Yes** | 3 |
| `FC[].Name / Amount / IncludesVAT` | Monthly fixed costs | rows | period always 0 (lifetime) | **Yes** | 3 |
| `Product[].Name / VC / Quantity / Price / Capacity / YearlyIncrease` | Products (monthly quantity and capacity, unit VC and price, annual growth %) | rows | Capacity < Quantity → Quantity | **Yes** | 4 |
| `swotStrengths / swotWeaknesses / swotOpportunities / swotThreats` | SWOT | textarea | — | No | 5 |

**V1 fields with no legacy equivalent** (report-only and must not affect calculations): study name, project start date, owner experience, business sector, project type, competitor characteristics, asset / expense / job category.

**Fixed-cost "duration"** (§6 Step 3): legacy user-entered fixed costs have no duration and always recur every year. Only system-generated loan instalments have a period. V1 shall not add a duration input for user fixed costs.

**System-generated rows** (never edited by the customer):
- Loan instalment fixed cost `قسط مديونية: <bank>`, one per loan, with period = loan years.
- Unused borrowed funds `مبالغ غير مستغلة`, created when total loans exceed total setup costs.
- Study fee `رسوم إعداد دراسة الجدوى` (FR-022 and A-05).

## 25.4 Legacy Calculation Behavior to Preserve

Notation: *r* = VAT rate. Quantities and amounts are monthly unless stated. Each year multiplies monthly figures by 12.

1. **Loan instalment.** If interest > 0: `P = (i/12 · A) / (1 − (1 + i/12)^(−12n))`. If interest = 0: `P = A / n / 12`. Total payable = `P · 12 · n`.
2. **Loan principal reduction** on the balance sheet is straight-line: `A / n` per year, regardless of interest.
3. **Setup capital.** `TotalAssets = Σ asset values (+ unused borrowed funds)`. `SelfFinancing = max(TotalAssets − TotalBorrowed, 0)`.
4. **Unused borrowed funds.** If `TotalBorrowed > Σ assets`, the difference becomes a cash asset (no depreciation) and the opening cash of year 1.
5. **Depreciation** is declining-balance on book value: `dep = bookValue · rate%`. Life span drives only replacement.
6. **Asset replacement.** When an asset with LifeSpan > 0 reaches the end of its life, its remaining book value is booked as "sale of consumed assets" (revenue and investing inflow) and its original value is repurchased (expense and investing outflow). LifeSpan 0 assets are never replaced. The initial purchase never appears in the income statement or the cash flow.
7. **Sales.** `capacity = round(max(capacity, quantity))`. From year 2: `quantity = min(round(quantity · (1 + growth%)), capacity)`, compounding on the rounded value.
8. **VAT on sales.** The price is treated as VAT-exclusive: `revenue_wTax = revenue · (1 + r)`.
9. **VAT on variable and fixed costs.** Amounts are treated as VAT-inclusive, but VAT is computed as `amount · r` (not `amount · r / (1 + r)`), so net = `amount · (1 − r)`. Fixed-cost VAT applies only to rows marked "includes VAT".
10. **Income statement.**
    - Revenue = sale of consumed assets + revenue incl. VAT − VC excl. VAT.
    - Expenses = assets bought + depreciation + net fixed costs (including loan instalments with interest) + VAT paid + salaries.
    - `PendingVAT = max(VATin − VATout, 0)`.
    - Profit after tax = revenue − expenses − PendingVAT.
11. **Cash flow.**
    - Operating = revenue excl. VAT + VATin − fixed costs excluding loan instalments − salaries − VC excl. VAT − VATout − PendingVAT.
    - Investing = assets sold − assets bought.
    - Financing = −(loan instalments net of VAT).
12. **Balance sheet.**
    - Assets = ending cash + long-term assets − that year's depreciation − cash assets.
    - Liabilities = remaining principal.
    - Equity = retained earnings + self-financing + profit + (−investing CF) + principal repaid to date.
    - The equity formula reconciles with the asset side: in all 13 reference scenarios run through the original code (Milestone 4), assets equal liabilities plus equity. No balancing adjustment is applied either way.
13. **Retained earnings** grow by profit after tax − investing CF each year.
14. **Capital recovery** counts the years in which retained earnings < TotalAssets. The output is "خلال N سنوات" or "أكثر من N سنوات". A count of 0 (recovered in year 1) outputs "غير متوفر".
15. **Average profit** = final-year retained earnings ÷ years.
16. **Market share** = (Σ yearly revenue incl. VAT ÷ years) ÷ market value × 100.
17. **Profitability table.**
    - Expenses = VC excl. VAT + expenses + PendingVAT.
    - Revenue = income-statement revenue + VC excl. VAT.
    - Profit = their difference.
    - The table also shows monthly values and the cost and profit ratios.
18. **Cost-based break-even price**, per product per year:
    - `gap = (yearProfit/12 − (monthlyRevenue_woTax − monthlyVC_woTax)) / quantity`.
    - `breakEven = max(VC·(1−r) − gap, VC·(1−r))`.
    - Margin = (price − breakEven) / breakEven.
19. **Charts.**
    - Setup-cost distribution (pie, user assets only).
    - Capital distribution (pie: borrowed vs self-financing).
    - Revenue, costs and profit by year (area chart, shown only when two or more years have revenue).
20. **Row acceptance.**
    - A row is silently dropped if its name is empty or a numeric field is not a number.
    - Empty numeric fields count as 0.
    - Owners and competitors with share ≤ 0 are dropped.
    - Totals over 100% only produce a warning.

## 25.5 Conflict: Study Fee (A-05) versus the Legacy Engine

The legacy engine has no "one-time expense" concept. Setup costs are capitalized: they raise TotalAssets and SelfFinancing, stay on the balance sheet, and never pass through the income statement or cash flow (25.4 items 3 and 6). Asset VAT flags have no numeric effect (Q-06).

The two ways to apply A-05 are:

- **Option A (legacy-consistent):** inject the fee as a setup-cost row with LifeSpan 0 and depreciation 0%. It is never depreciated and never replaced. It counts toward startup capital and self-financing, as every legacy setup cost does. It does not appear as an income-statement expense. The VAT split is recorded for the report only. **No formula changes.**
- **Option B (true one-time expense):** additionally expense the fee in year 1 of the income statement and the operating cash flow. This is a new formula (§19 item 13) and requires an approved scope change.

**Decision: Option A. ✅ CONFIRMED (18 November 2026).** It satisfies "apply exactly once", "0% depreciation" and "no replacement" without altering legacy formulas, and it is the isolated adjustment A-05 asks for.

## 25.6 Open Items Requiring Confirmation

> **All open items are tracked in [`docs/open-items.md`](open-items.md).** The table below is kept for history.

| ID | Question |
|---|---|
| O-01 | Contact, Privacy Policy and Terms: separate pages, or sections in the single landing page? **Implemented per recommendation (Milestone 2):** Contact is a landing-page section; Privacy and Terms are separate pages. Their text is a draft pending client legal review. |
| O-02 | Extension of a draft that has not expired: add the days to the current `expires_at` (recommended), or count from today? |
| O-03 | Confirm that the docx source, not the Netlify React build, is the authoritative calculation reference. |
| O-04 | Confirm that `submission.php` contains no additional calculations, and supply `readableAmount()` if exact number formatting must match. |
| O-05 | ~~Study fee treatment~~: **resolved, Option A** (§25.5). |
| O-06 | Default currency: legacy falls back to Kuwaiti Dinar (`د.ك.`). V1 default to SAR (`ر.س.`)? Restrict the currency list to the Gulf currencies? |
| O-07 | Study fee when the study currency differs from the fee currency (SAR): currently not added, with a warning. Restrict studies to SAR, or supply an exchange-rate rule? |

## 25.7 Legacy Quirks to Preserve, Not Fix (FR-027)

| ID | Quirk |
|---|---|
| Q-01 | The loan instalment is passed through `.toFixed(currencyDecimals)`, where `currencyDecimals` resolves to the `<select>` element, not the number. That gives `toFixed(NaN)`, which equals `toFixed(0)`. **The instalment used in the statements is rounded to a whole number**, while the total payable uses the unrounded value. |
| Q-02 | VAT is extracted from VAT-inclusive costs as `amount · r` instead of `amount · r/(1+r)` (25.4 item 9). |
| Q-03 | The "includes VAT" checkbox on loan-instalment rows resets on every recalculation and is never saved, so it is effectively always off. |
| Q-04 | ~~The balance sheet does not necessarily balance.~~ **Withdrawn (Milestone 4):** running the original code shows the balance sheet balances in every reference scenario. |
| Q-05 | Capital recovery shows "غير متوفر" when the capital is recovered within the first year (25.4 item 14). |
| Q-06 | Asset "includes VAT" computes `VATofDepreciation`, which is never used in any output. |
| Q-07 | Depreciation on the balance sheet is that year's depreciation only, not the accumulated total. |
| Q-08 | Market share uses VAT-inclusive sales. |

Each quirk shall be covered by a regression test that asserts the legacy result, and listed in the migration-differences document.

---

# 26. Calculation Engine Migration Record (Milestone 4)

This section is the migration-differences document required by FR-028 and §25.7.

## 26.1 Approach

- `tests/Legacy/cassandra-legacy.js` is a verbatim copy of the calculation script from the docx source (O-03).
- `tests/Legacy/harness.mjs` runs that exact code in Node against a minimal stand-in for jQuery. The only additions are two lines that copy internal values out. Running `node tests/Legacy/harness.mjs` regenerates the fixtures in `tests/Fixtures/legacy/`.
- The Laravel port is `App\Services\Calculation\CassandraLegacyEngine`, version `cassandra_legacy_v1`. `LegacyParityTest` compares it with every fixture.
- The fixtures are produced by the original code. They are not client-verified results (A-12). Client-supplied studies will be added as acceptance fixtures when available.

## 26.2 Result

All 13 reference scenarios match the original code to within one part in 10⁹ on every compared figure: totals, each yearly income-statement, cash-flow and balance-sheet line, loan instalment rows, break-even prices and margins, and chart data. **No numerical differences.**

The scenarios cover:
- loans: none, interest-free, interest-bearing, and several loans with unused funds
- products: one, several, capacity limits, zero sales
- assets: depreciation and replacement
- VAT: zero and non-zero
- study length: 1, 2, 3, 5 and 10 years
- market share
- the study fee
- rows the legacy form ignores
- capital recovered in the first year

## 26.3 Input mapping decisions (`LegacyFormMapper`)

| Situation | Behaviour |
|---|---|
| Empty numeric field | Treated as 0, as the legacy `Number('')` did. |
| Row without a name | Category label is used. Rows with neither a name nor a category are ignored, as in the legacy form, with a warning. |
| Loan without a lender or repayment years | Ignored, with a warning. |
| VAT not chosen | 0%, with a warning. |
| Study length not chosen | 2 years (the legacy form's default selection), with a warning. |
| Study fee (A-05 Option A) | Added once as the setup cost `رسوم إعداد دراسة الجدوى` with life 0 and depreciation 0. It is never stored in the customer's input. |
| Fee currency differs from study currency (**O-07**) | The fee is not added, and a warning is shown, because no exchange rate is available. |
| Year with a negative profit | Informational warning (FR-031). |

## 26.4 Legacy behaviour not ported

- Display-only values: `readableAmount` formatting, the `AccDeprToTA` ratio text, and `VATofDepreciation` (Q-06).
- Two unreachable conditions: a NaN fixed-cost period and the `Period == 0` loan branch, since zero-year loans are dropped earlier.

## 26.5 Corrections to §25

- Q-04 is withdrawn: the legacy balance sheet balances in every reference scenario.
- Q-01, Q-02, Q-03, Q-05, Q-06, Q-07 and Q-08 are each pinned by a test in `LegacyQuirksTest`.

---

**END OF BRD — CERTAINTY VERSION 1.1**

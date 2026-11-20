# Certainty — V1 Acceptance Checklist

Maps the BRD's final acceptance criteria (§22) and required test coverage (§21) to the automated tests that prove them. Run everything with `composer test`, which runs Pint, PHPStan level 7 and the full Pest suite. For the frontend checks, run `composer ci:check`.

_Last verified: 20 November 2026 (Milestone 9). 186 tests pass. The 3 skipped tests are two-factor authentication tests; 2FA is not in V1._

## §22 Final acceptance criteria

| Criterion | Evidence |
|---|---|
| Mandatory public pages are available | `PublicPagesTest` |
| Registration, login and password reset work | `Auth/RegistrationTest`, `Auth/AuthenticationTest`, `Auth/PasswordResetTest` |
| Customers can create multiple studies | `CreateStudyTest` (several independent studies) |
| The study form works with dropdowns and dynamic sections | `UpdateStudyDraftTest`, `StudyProgressTest`, `EditStudyTest` |
| Drafts persist and expire on the configured period | `UpdateStudyDraftTest` (saving restores input), `ExpireDraftStudiesTest` |
| Data entry is possible before payment approval | `AcceptanceFlowTest` (drafts saved while the payment is pending) |
| Administrators verify uploaded receipts | `Admin/PaymentReviewTest` |
| Calculations use the migrated legacy engine | `Calculation/LegacyParityTest` (13 fixtures from the original JavaScript), `LegacyQuirksTest` |
| Live previews display backend values | `EditStudyTest` (preview prop, refreshed after saving) |
| The report contains the financial and qualitative outputs | `StudyReportTest` (renders the statements, SWOT and setup costs from snapshots) |
| No final report without approved payment | `StudyReportTest`, `FinalizeStudyTest`, `AcceptanceFlowTest` |
| Finalization requires explicit confirmation | `FinalizeStudyTest` |
| Finalized studies are read-only | `FinalizeStudyTest`, `AcceptanceFlowTest` |
| Administrators extend drafts and reopen finalized studies | `Admin/StudyManagementTest` |
| Study and payment history is auditable | `Admin/AuditLogTest`, `AcceptanceFlowTest` (full action sequence) |
| Private data, receipts and reports are protected | `ViewPaymentReceiptTest`, `StudyReportTest`, `SecurityTest` (no public storage URL) |
| Core financial and transactional tests pass | `composer test` |
| Deployable with documented production configuration | `docs/deployment.md`, `certainty:check-production` (`Admin/CheckProductionReadinessTest`) |

The **end-to-end scenario** in `AcceptanceFlowTest` goes through the HTTP layer:
1. Register and create a study.
2. Save a partial draft, then the complete one.
3. Try to finalize before paying, which is blocked.
4. Upload a receipt; an administrator approves it.
5. Finalize with confirmation; the PDF is generated and downloaded.
6. Check that another customer gets 404.
7. An administrator reopens the study; the customer corrects it and finalizes version 2.
8. Version 1 is still downloadable, and the audit history holds every step.

## §21 Required automated test coverage

| Area | Requirement | Test |
|---|---|---|
| Authentication | Register successfully | `RegistrationTest`: new users can register |
| | Reject duplicate email | `RegistrationTest`: email must be unique regardless of case |
| | Log in successfully | `AuthenticationTest`: users can authenticate |
| | Reject invalid credentials | `AuthenticationTest`: invalid password |
| | Reset password with a valid token | `PasswordResetTest`: valid token |
| | Reject an expired reset token | `PasswordResetTest`: expired token |
| Studies | Create draft | `CreateStudyTest` |
| | Edit own draft | `UpdateStudyDraftTest` |
| | Prevent editing another customer's draft | `UpdateStudyDraftTest`, `EditStudyTest` (404) |
| | Save an incomplete draft | `UpdateStudyDraftTest`: partial draft |
| | Restore saved input | `UpdateStudyDraftTest`: saving restores everything |
| | Add and remove repeatable rows | `UpdateStudyDraftTest`: rows can be removed |
| | Enforce finalization validation | `FinalizeStudyTest`: not ready cannot be finalized |
| | Expire a draft after the deadline | `ExpireDraftStudiesTest` |
| | Prevent expired-draft editing | `UpdateStudyDraftTest`: expired drafts cannot be saved |
| | Extend an expired draft as admin | `StudyManagementTest`: extending an expired draft |
| | Multiple studies per customer | `CreateStudyTest` |
| Payments | Create a payment record | `CreateStudyTest`: draft with a pending payment |
| | Upload a valid receipt | `SubmitPaymentReceiptTest` (PDF, JPG, PNG) |
| | Reject unsupported files | `SubmitPaymentReceiptTest` (wrong type, disguised image, too large) |
| | Prevent unauthorized receipt access | `ViewPaymentReceiptTest` |
| | Approve as admin | `PaymentReviewTest` |
| | Reject as admin with a reason | `PaymentReviewTest` |
| | Resubmit a rejected receipt | `SubmitPaymentReceiptTest` |
| | Prevent non-admin approval | `PaymentReviewTest`: customers cannot review |
| | Prevent duplicate approval side effects | `PaymentReviewTest`: approving twice |
| Calculations | Compare source fixtures with backend output | `LegacyParityTest` |
| | Yearly projections, loans, depreciation, VAT, capacity limits | `LegacyParityTest` scenarios, `LegacyQuirksTest` Q-01 to Q-08 |
| | Study fee added exactly once | `FeasibilityCalculationServiceTest` |
| | Deterministic output | `FeasibilityCalculationServiceTest` (repeated calculation is identical) |
| Reports | Block the report before payment approval | `StudyReportTest` |
| | Block the final report for an incomplete study | `FinalizeStudyTest`: incomplete data |
| | Finalize an eligible study | `FinalizeStudyTest` |
| | Generate the PDF | `StudyReportTest`: the queued job renders the report |
| | Prevent duplicate finalization | `FinalizeStudyTest`: finalizing twice |
| | Prevent editing a finalized study | `FinalizeStudyTest` |
| | Reopen a finalized study as admin | `StudyManagementTest` |
| | Preserve historical report versions | `StudyManagementTest`, `StudyReportTest` |
| | Prevent cross-customer report access | `StudyReportTest` |

## Manual and visual checks

- **Report PDF:** rendered with real Chrome for a sample seven-year study. Arabic shaping, RTL tables, negative numbers and the balanced balance sheet were verified (Milestone 7). It still needs a client comparison against old reports (OI-30).
- **Screens:** the admin pages (Milestone 8) and customer pages (Milestone 9) were captured with headless Chrome against a throwaway database, at desktop, wide and phone widths, in light and dark mode. The landing page was not part of this pass; see OI-19.
- **Still open before launch:** the client items in `docs/open-items.md`, namely bank details, legal text, brand assets and a sample-report review.

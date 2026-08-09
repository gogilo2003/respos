# Billing Domain Audit

## Audit Scope

Reviewed models, migrations, repositories, services, controllers, form requests, factories, seeders, and policies for the billing domain.

---

## Existing

| Area | File / Table | Status |
|------|--------------|--------|
| **Model** | `app/Models/Bill.php` | ✅ Exists |
| **Model** | `app/Models/BillItem.php` | ✅ Exists |
| **Model** | `app/Models/BillSplit.php` | ✅ Exists |
| **Model** | `app/Models/BillSplitItem.php` | ✅ Exists |
| **Model** | `app/Models/Payment.php` | ✅ Exists |
| **Model** | `app/Models/Refund.php` | ✅ Exists |
| **Model** | `app/Models/Receipt.php` | ✅ Exists |
| **Model** | `app/Models/ReceiptReprint.php` | ✅ Exists |
| **Model** | `app/Models/CashReconciliation.php` | ✅ Exists |
| **Migration** | `2026_06_16_002404_create_bills_payments_tables.php` | ✅ Creates `bills`, `bill_items`, `bill_splits`, `bill_split_items`, `payments`, `refunds` |
| **Migration** | `2026_06_16_002405_create_receipts_reconciliations_tables.php` | ✅ Creates `receipts`, `receipt_reprints`, `cash_reconciliations` |
| **Repository** | `app/Repositories/BillRepository.php` | ✅ Exists |
| **Repository Interface** | `app/Interfaces/Repositories/BillRepositoryInterface.php` | ✅ Exists |
| **Service** | `app/Services/BillService.php` | ✅ Exists |
| **Controller** | `app/Http/Controllers/BillController.php` | ✅ Exists |
| **Controller** | `app/Http/Controllers/PaymentController.php` | ✅ Exists |
| **Form Request** | `app/Http/Requests/StoreBillRequest.php` | ✅ Exists |
| **Form Request** | `app/Http/Requests/Bill/SplitBillRequest.php` | ✅ Exists |
| **Form Request** | `app/Http/Requests/Bill/ProcessPaymentRequest.php` | ✅ Exists |
| **Form Request** | `app/Http/Requests/StorePaymentRequest.php` | ✅ Exists |
| **Routes** | `routes/web.php` — `BillController` + `PaymentController` routes registered | ✅ Exists |

---

## Missing

| Area | Details |
|------|---------|
| **PaymentMethod model** | `payments.payment_method` is an enum; there is no `payment_methods` table or model for extensibility. |
| **Discount model / service** | Discounts are stored as scalar fields on `bills`; no `Discount` model, policy, or approval workflow exists. |
| **Tax model / service** | VAT/service charge are stored as scalar fields on `bills`; no `Tax` model or configurable tax rules exist. |
| **Factories** | No factories for `Bill`, `BillItem`, `BillSplit`, `BillSplitItem`, `Payment`, `Refund`, `Receipt`, `ReceiptReprint`, `CashReconciliation`. |
| **Seeders** | No billing seeders for development or demo data. |
| **Policies** | No `Policies` directory exists anywhere in the app; billing models have no authorization policies. |
| **ReceiptController** | Receipt generation is a method on `BillController`; no dedicated controller for receipt reprint/history. |
| **RefundController** | `Refund` model exists, but there is no controller or service for issuing refunds. |
| **CashReconciliationController** | `CashReconciliation` model and migration exist, but no controller or service. |
| **PaymentRepository** | No dedicated `PaymentRepository`; payment queries are mixed into `BillService`. |
| **ReceiptRepository** | No repository for receipt numbering or PDF management. |
| **Unit / Feature Tests** | No tests for `BillService`, `BillRepository`, `BillController`, or `PaymentController`. |

---

## Needs Refactoring

| Area | Issue | Recommendation |
|------|-------|----------------|
| **BillService** | `generateReceipt()` returns plain text; no PDF generation. | Introduce `ReceiptService` with PDF generation and move receipt logic out of `BillService`. |
| **BillService** | `processPayment()` mixes payment application, split updates, and bill status transitions in one method. | Extract payment application into a `PaymentService` or `PaymentRepository` method. |
| **BillController** | `receipt()` returns plain text attachment. | Replace with `ReceiptController@show` or `@download` that returns a generated PDF. |
| **BillController** | `processPayment()` is duplicated conceptually in `PaymentController@store`. | Keep one canonical endpoint; prefer `PaymentController` for REST consistency and remove duplication from `BillController`. |
| **BillRepository** | Query surface is small; missing methods like `findOpenBySession()`, `findPaidBetween()`, `findRecentForCashier()`. | Expand interface to support reconciliation and reporting queries. |
| **Bill model** | Contains discount/tax fields but no related models. | Decide whether discounts/taxes remain embedded or become first-class models with their own repositories/services. |
| **Payment model** | `payment_method` is an enum; adding new methods requires migrations. | Consider a `payment_methods` lookup table if methods are expected to grow. |
| **Authorization** | No policies; controllers use `Gate::authorize('cashier')` directly. | Add policies if granular per-model authorization is needed, or keep gate checks if role-based is sufficient. |
| **Validation** | Some billing requests still use inline validation in controllers. | Migrate remaining inline validation to dedicated Form Requests. |
| **Receipt numbering** | No unique receipt-number generation logic beyond DB unique constraint. | Add sequence-safe receipt number generation in `ReceiptService`. |

---

## Recommendations

1. **Do not build new billing models first.** The schema, models, migrations, repository, service, controllers, and requests are already scaffolded and partially functional.
2. **Refactor in this order:**
   - Extract `PaymentService` / `PaymentRepository` to remove payment logic from `BillService`.
   - Extract `ReceiptService` with PDF generation and replace `BillController::receipt()`.
   - Add missing `BillRepository` query methods for reporting/reconciliation.
   - Decide on `PaymentMethod`, `Discount`, and `Tax` modeling before creating new tables.
3. **Add tests before adding features.** Billing is high-risk; cover `BillService`, `PaymentService`, and controller endpoints with feature tests.
4. **Add factories/seeders** for local development once the service boundaries are stable.

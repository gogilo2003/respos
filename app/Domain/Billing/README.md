# Billing Module

This module owns all billing-domain structure: contracts, value objects, enums, DTOs, exceptions, and service-layer architecture. Existing Eloquent models, repositories, and controllers remain outside this folder until migration is planned.

## Architecture

- `Contracts/` — interfaces only.
- `Enums/` — strongly typed domain values.
- `ValueObjects/` — immutable primitives with no Eloquent dependency.
- `DTOs/` — immutable data carriers for transport between layers.
- `Exceptions/` — domain-specific exceptions.
- `Services/` — orchestration and use cases.

## Folder Structure

```
app/Domain/Billing/
├── Contracts/
├── DTOs/
├── Enums/
├── Exceptions/
├── Services/
└── ValueObjects/
```

## Responsibilities

- Represent money, percentages, and quantities safely.
- Define billing operation contracts.
- Carry bill, payment, and receipt data without business logic.
- Throw specific exceptions for invalid billing states.

## DTOs

- `BillData`
- `BillItemData`
- `PaymentData`
- `ReceiptData`

## Value Objects

- `Money`
- `Percentage`
- `Quantity`

## Enums

- `BillStatus`
- `PaymentStatus`
- `PaymentMethod`
- `DiscountType`
- `TaxType`

## Services

Service classes will implement `Contracts/` and orchestrate domain operations.

## Repositories

Repository interfaces live in `app/Interfaces/Repositories/`. Implementations live in `app/Repositories/`. The billing module depends on contracts, not concrete repositories.

## Coding Standards

- Use `readonly` DTOs and value objects where possible.
- Keep constructors private; use named factory methods.
- Throw domain exceptions instead of generic ones.
- Avoid floating-point arithmetic in `Money`.
- Avoid business logic in DTOs.

## Dependency Rules

- `ValueObjects` and `Enums` must not depend on Eloquent.
- `DTOs` must not depend on repositories or services.
- `Services` depend on `Contracts` and value objects, not controllers.
- Existing controllers may depend on new DTOs during migration.

## Future Extension Points

- Receipt PDF generation service.
- Discount and tax calculation services.
- Payment method strategy implementations.
- Reconciliation and audit service integration.

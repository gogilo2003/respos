# Backend Patterns

## Repository Contracts

Repository interfaces should describe persistence operations, not HTTP concerns. Keep names explicit and domain-oriented.

Example methods:

```php
interface MenuItemRepositoryInterface
{
    public function paginateForAdmin(array $filters = [], int $perPage = 15): LengthAwarePaginator;
    public function findOrFail(int $id): MenuItem;
    public function create(array $data): MenuItem;
    public function update(MenuItem $menuItem, array $data): MenuItem;
}
```

Concrete repositories should use Eloquent and return models, collections, or paginators. Do not return Inertia responses from repositories.

## Services

Use services for workflows that combine validation-independent business rules, transactions, multiple repositories, status transitions, audit logging, notifications, or calculations.

Examples:

- Opening or closing a table session.
- Placing an order with order items.
- Generating a bill from served items.
- Recording cash payment and receipt output.

Wrap multi-write workflows in `DB::transaction()`.

## Controllers

Controllers should be thin and predictable:

1. authorize
2. validate via Form Request
3. call service or repository
4. return redirect, JSON, or Inertia response

Use route model binding where practical. Avoid duplicating status transition rules in controllers.

## Validation And Authorization

Use Form Requests for non-trivial input. Use policies or gates for role restrictions. Enforce role boundaries from the URS: waiters cannot process payments, kitchen cannot edit menu pricing, cashiers cannot modify menu items, and customers are unauthenticated for QR flows.

## Models

Keep relationships explicit. Add casts for booleans, decimals, JSON, dates, and generated values. Numeric IDs are intentional; do not add UUID traits.

## Tests

Prefer feature tests for workflows: auth, role restrictions, CRUD, order placement, billing, cash payment, and reconciliation. Use unit tests only for isolated calculations or pure domain logic.

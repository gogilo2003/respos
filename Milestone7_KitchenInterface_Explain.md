# Milestone 7: Kitchen Interface — Expounded Explanation

This document expands **Milestone 7 (Kitchen Interface)** into an implementation-oriented specification that fits the existing architecture:
- Laravel 12 backend (controllers/models)
- Inertia + Vue 3 frontend
- Repository/service separation style used elsewhere

Milestone 7 turns created orders into kitchen-work items and provides:
- a kitchen dashboard (order queue)
- item-level status progression (Accepted → Preparing → Ready)
- SLA timestamps tracking

> Note: Milestone 6 (waiter) creates orders with `placed_by_role = waiter` and initial `status = accepted`. Milestone 5 (customer QR) should create the same kind of orders, so kitchen can treat both uniformly.

---

## 1) Goal
Kitchen interface must enable kitchen staff to:
1. View the queue of orders assigned to them (typically by active table sessions)
2. See each order’s items and their current preparation status
3. Update each item status through a controlled workflow:
   - **accepted** / **received** (initial)
   - **preparing**
   - **ready**
4. Track timestamps and compute SLA (so you can measure how long items spend in the kitchen)
5. Keep the UI in sync reliably (start with polling; push notifications later in Milestone 9)

---

## 2) Data model expectations (based on existing code patterns)
Your project already has:
- `app/Models/Order.php`
- `app/Models/OrderItem.php`

From the Milestone 6 controller implementation, `Order` has:
- a linkage to table session: `session_id`
- initial `status = accepted`
- `placed_by_role`, `placed_by_user`

Kitchen milestone requires **item-level** tracking, so `OrderItem` should support at least:
- `id`, `order_id`, `menu_item_id`, `quantity`, `unit_price`
- `status` (or equivalent)
- timestamps such as:
  - `accepted_at`
  - `preparing_at`
  - `ready_at`

If your schema uses different column names, the logic below still applies—map the concept to your actual fields.

---

## 3) Kitchen Dashboard (Order queue)

### UI responsibilities
The kitchen dashboard should list orders in a way optimized for speed and clarity. Suggested sections/views:
- **Incoming queue**: items/orders that are not yet preparing
- **In progress**: items currently preparing
- **Ready/served-ready**: items marked ready

Minimum columns per order card/row:
- order id
- table/session reference (table id/number if you have it)
- placed time
- order-level status (optional)
- count of items by state (e.g., 2 preparing, 1 ready)

### Backend responsibilities
A `GET /kitchen/dashboard`-style endpoint must return compact queue data.

Practical return shape:
```json
{
  "orders": [
    {
      "order_id": 123,
      "session": {"table_session_id": 10, "table_number": 5},
      "placed_at": "2026-...",
      "items": [
        {"order_item_id": 77, "menu_item_id": 9, "name": "Burger", "quantity": 2, "status": "preparing"}
      ],
      "item_counts": {"accepted": 0, "preparing": 2, "ready": 1}
    }
  ]
}
```

Performance requirements:
- Eager-load order items.
- Eager-load menu item names if you need them.
- Avoid N+1 queries (especially for item status and menu item lookups).

---

## 4) Item-level status updates

Kitchen staff needs controlled transitions. The UI should display action buttons appropriate for the current item status.

### Suggested state machine
Even if you already have `Order.status`, kitchen milestone requires **OrderItem.status**.

Allowed transitions (example):
- `accepted` → `preparing`
- `preparing` → `ready`

Guards:
- Prevent jumping backwards (unless you explicitly support “undo”).
- Prevent updates for items that belong to a different/closed session if your system enforces it.

### Backend responsibilities
Provide an endpoint like:
- `PATCH /kitchen/order-items/{orderItem}` with body `{ status: "preparing" | "ready" }`

Validation:
- item exists
- item belongs to an order that is relevant to kitchen processing
- status is one of allowed values
- transition is valid from current status

Timestamp rules:
- when status becomes `preparing`, set `preparing_at = now()`
- when status becomes `ready`, set `ready_at = now()`

After updating an item, optionally update derived order status:
- If all items in an order are `ready`, set `orders.status = ready` (or `completed`)
- If any item is `preparing`, set `orders.status = preparing`
- Otherwise keep `accepted`

This is optional, but it helps the customer tracking UI.

---

## 5) SLA timestamp tracking

Milestone 7 explicitly calls out SLA timestamp tracking.

### What SLA means in practice
Define SLA as time spent between key milestones. Common examples:
- `SLA_received_to_preparing` = `preparing_at - accepted_at`
- `SLA_preparing_to_ready` = `ready_at - preparing_at`
- total `SLA_received_to_ready` = `ready_at - accepted_at`

### Implementation approach
1. Ensure you store at least the key timestamps (`accepted_at`, `preparing_at`, `ready_at`).
2. Kitchen dashboard can compute SLA durations on the server and return them as numbers (seconds/minutes) or as formatted strings.
3. Alternatively, compute on the frontend, but server-side is safer/consistent.

### Backend responsibilities
In dashboard endpoint, include fields like:
- `sla_minutes_preparing_to_ready`
- `sla_minutes_total`

…and/or flags:
- `is_breached` (boolean), based on restaurant SLA thresholds stored in `SystemSetting` (if you add that later).

---

## 6) Real-time behavior (start with polling)
Milestone 9 covers WebSockets. For Milestone 7, implement reliable polling:
- kitchen UI calls `GET /kitchen/dashboard` every N seconds (e.g., 2–5s)
- after updating an item, UI either:
  - immediately updates local state, then refetches; or
  - relies on the next poll

Key requirement:
- polling must reflect the latest server truth

---

## 7) Security & authorization
All kitchen actions must be protected:
- dashboard endpoint requires `auth`
- authorize role with `Gate::authorize('kitchen')` (or your actual gate)
- only kitchen staff can PATCH order items

Also ensure staff cannot update items outside the current restaurant scope.

---

## 8) Acceptance criteria (Milestone 7 “done”)

### Kitchen Dashboard
- [x] Kitchen dashboard loads with a list of active orders/items
- [x] Dashboard clearly shows item states
- [x] Dashboard shows timestamps/SLA durations (or at least raw timestamps)

### Item-level status updates
- [x] Kitchen staff can move an item from accepted → preparing
- [x] Kitchen staff can move an item from preparing → ready
- [x] Timestamps are recorded for each transition
- [x] Derived order status updates correctly (optional but recommended)

### Polling / synchronization
- [ ] Kitchen dashboard updates after changes (polling or refetch)

---

## 9) How Milestone 7 feeds Milestone 8 & Milestone 5 tracking
Once item statuses become `ready`, it enables:
- cashier to generate bills for served items only (Milestone 8)
- customer tracking to progress (customer sees ready/completed)

Therefore, Milestone 7 must ensure consistent status semantics between:
- `OrderItem.status`
- `Order.status` (if you derive it)
- what customer tracking UI expects

---

# End
Milestone 7 turns the system into a real “back-of-house” workflow by:
1) providing a kitchen queue
2) enabling item-level status progression
3) recording timestamps for SLA measurement


# Milestone 6: Waiter Interface — Expounded Explanation

This document expands **Milestone 6 (Waiter Interface)** into an implementation-oriented specification that fits the existing architecture:
- Laravel 12 backend
- Inertia + Vue 3 frontend
- Repository/service separation approach (as used in prior milestones)

Milestone 6 provides the internal staff workflow that lets waiters:
- see and manage tables/sessions,
- take orders manually (for walk-ins or for cases where QR is not used),
- handle assistance requests raised by the customer during ordering.

---

## 1) Goal
Waiter interface must enable these tasks reliably:
1. **Waiter dashboard (table grid)**
   - show tables and their current session state (open/closed)
   - indicate whether there are active orders and/or pending assistance requests

2. **Manual order entry**
   - waiter selects a table/session
   - chooses items from the active menu
   - enters quantities
   - places the order (persisted as an `Order` + `OrderItem`)

3. **Assistance request handling**
   - customer (from public QR flow) can request help
   - waiter sees requests, updates their status (e.g., assigned → resolved)
   - waiter can attach notes

---

## 2) Prerequisites / dependencies (what Milestone 6 relies on)
Milestone 6 depends on Milestone 4 and (implicitly) Milestone 5 concepts:

### A) Table + TableSession identity
- Every waiter action must attach to an **open** `table_session`.
- The UI should only allow order entry for sessions that are `open`.

### B) Orders and OrderItems data model
- Orders created by waiters must become the same type of records created by the public QR flow.
- Kitchen/billing milestones later depend on these records.

### C) Menu dataset
- Waiter manual entry uses the same active menu item list as customers.

### D) AssistanceRequest model + lifecycle (introduced/used here)
You already have `AssistanceRequest` in `app/Models/AssistanceRequest.php`, so Milestone 6’s main work is building the staff UI + controller endpoints around it.

---

## 3) Waiter Dashboard (Table grid)

### UI responsibilities
The dashboard is a grid/list of tables with actionable affordances.
For each table, the UI should display at least:
- table name/number
- whether there is an **open session**
- an indicator of active orders (e.g., “has orders”, “pending kitchen”)
- an indicator of assistance requests (e.g., count / badges)

### Suggested UX rules
- When a session is closed:
  - disable “Manual order” and assistance actions
  - show “Session closed”
- When a session is open:
  - enable actions

### Backend responsibilities
A dashboard backend endpoint should return a compact data structure suitable for the grid without heavy coupling.

Example response shape:
```json
{
  "tables": [
    {
      "table_id": 1,
      "table_name": "Table 1",
      "active_session": {
        "table_session_id": 10,
        "status": "open"
      },
      "order_summary": {
        "active_orders_count": 2,
        "latest_order_status": "preparing"
      },
      "assistance": {
        "open_requests_count": 1
      }
    }
  ]
}
```

Implementation notes:
- avoid N+1 queries by eager loading session data, and aggregating counts in a single query where possible.
- if your schema does not have “latest_order_status” yet, dashboard can show only counts until kitchen milestone is complete.

---

## 4) Manual Order Entry for Waiters

### Core flow
1. Waiter clicks a table with an open session.
2. UI loads:
   - menu categories + items (active ones)
   - current order state (optional)
3. Waiter adds items to a cart-like draft.
4. Waiter submits.
5. Backend creates an `Order` tied to the chosen `table_session_id`.
6. Backend returns the newly created `order_id` and initial status.

### What must be validated (server-side)
On submit, backend must validate:
- `table_session_id` exists and is open
- each `menu_item_id` exists and is active/available
- quantities are positive integers
- (recommended) unit price is captured at order time

### Order status at creation
At this milestone (before kitchen interface automation), a pragmatic approach is:
- created orders start in a default status like `received` or `accepted`

Later milestones (kitchen status updates) will refine item/order status.

### Suggested endpoints (backend)
These should mirror how public ordering persists orders, but under auth + waiter context.

- `POST /waiter/orders`
  - body: `{ table_session_id, items: [{ menu_item_id, quantity }] }`
  - returns: `{ order_id, status }`

- `GET /waiter/tables/summary`
  - returns grid data

- `GET /waiter/table-sessions/{table_session_id}/menu`
  - returns menu categories/items (if you don’t reuse public menu endpoints)

*(Exact routes/naming can match your conventions; the responsibilities above are the key.)*

---

## 5) Assistance Request Handling

### Customer → backend → Waiter UI
Assistance requests should already be created from the customer side (public QR flow).
Milestone 6 focuses on staff consumption and resolution.

### UI responsibilities
Waiter should have an assistance panel with:
- list of open requests (newest first)
- table reference (table/session)
- request status
- ability to mark as resolved
- optional notes (e.g., “sent salt”, “waiting on kitchen”)

### Assistance request status lifecycle (suggested)
Even if you don’t have all statuses in DB yet, design it conceptually:
- `open` (requested)
- `assigned` (someone acknowledged)
- `resolved` (customer assistance done)

### Backend responsibilities / endpoints
- `GET /waiter/assistance/requests?status=open` (or similar)
- `PATCH /waiter/assistance/requests/{id}` to update status and notes

Validation:
- only allow update if request exists
- only allow updates by authenticated staff (and optionally by role)

---

## 6) Security & authorization
Waiter features must be protected.

Minimum rules:
- endpoints require auth
- only staff roles (e.g., waiter) can access waiter routes
- staff can only operate on sessions that exist in the same restaurant context

(If the app is single-restaurant for now, restaurant scoping is still useful conceptually.)

---

## 7) Data consistency with Milestone 5
Orders created via QR (customers) and via waiter (manual entry) should converge into the same downstream pipelines.

Therefore:
- do not create “different” order tables for waiter
- create the same `Order` / `OrderItem` records
- ensure order status fields align with the kitchen interface milestone

---

## 8) Acceptance Criteria (Milestone 6 “done”)
Milestone 6 is considered complete when:

### Waiter Dashboard
- [x] Waiter sees a table grid showing open sessions
- [x] Waiter can identify assistance pending at a glance

### Manual order entry
- [ ] Waiter can open a table session and add items
- [ ] Waiter submit creates persistent `Order` + `OrderItem` linked to `table_session_id`
- [ ] Closed sessions cannot accept manual orders

### Assistance requests
- [ ] Waiter can view open assistance requests
- [ ] Waiter can mark a request resolved (with optional notes)

---

## 9) How Milestone 6 feeds Milestone 7
Kitchen interface (Milestone 7) will consume orders created by both:
- customers (Milestone 5)
- waiters (Milestone 6)

So Milestone 6 must ensure:
- orders have the correct foreign keys
- order statuses start in a consistent initial state

---

## End
Milestone 6 turns the system from “customer ordering” into a full staff workflow by adding waiter visibility, manual ordering, and assistance resolution.


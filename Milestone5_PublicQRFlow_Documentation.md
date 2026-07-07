# Milestone 5: Customer-Facing Ordering (Public QR Flow) — Expound & Explain

This file is a structured “Milestone 5” specification: what the customer experience must be, what backend endpoints must do, what data is required, and what success looks like.

---

## Milestone 5 Objective
Enable a customer to:
- open the ordering flow via **public QR** (from Milestone 4)
- view menu categories and items
- manage a cart
- place an order tied to the correct **table session**
- view order status (start with polling)

---

## 1) Public Ordering Entry (QR → Session Context)
### Inputs
The customer arrives at the system via a QR content formatted like:

`{restaurant_id}|{table_id}|{base_url}`

### Required behavior
1. Parse the QR payload
2. Validate:
   - restaurant exists
   - table exists and is active
   - table session is open (or open it)
3. Load data needed to render the public menu

### Output to the frontend
Frontend must be able to retrieve:
- the **active table session** identifier (id or token)
- optional context like table name/id
- whether ordering is currently allowed

---

## 2) Public Menu View (Mobile-first)
### Must show
- menu categories
- items under each category
- item price
- optional item image

### Data shape (recommended)
- categories: `[{ id, name, items: [{ id, name, price, image_url? }] }]`

### Backend responsibilities
- return only active menu categories/items (if you have status columns)
- avoid N+1 queries:
  - eager load categories → items

### Edge cases
- If session is invalid/closed: show a message instead of ordering UI

---

## 3) Cart Management (Frontend State + Server Consistency)
### Frontend cart requirements
- add item (increase quantity)
- remove item
- change quantity
- compute totals in UI

### Server consistency requirement
Even if totals are calculated client-side, the order placement must:
- revalidate item prices/availability server-side
- store unit prices at time of order (recommended)

### Acceptance criteria
- Cart changes reflect instantly
- Cart totals match server calculations once placed

---

## 4) Order Placement Logic (Core Remaining Work)
### “Place Order” must do
1. Validate table session is still open
2. Validate each cart line:
   - `menu_item_id` exists and is active
   - `quantity` is a positive integer
3. Create `Order` linked to the session
4. Create `OrderItem` rows linked to the order
5. Save pricing info (unit_price) to order items (recommended)
6. Return `order_id` (and maybe initial status)
7. Clear cart

### Safety requirements
- Prevent duplicate orders (at least disable button while submitting)
- Optionally support idempotency keys (Phase 1 may skip)

### Acceptance criteria
- Order is persisted and tied to correct session
- Invalid/stale session cannot create orders

---

## 5) Real-time Order Tracking for Customers (Start with Polling)
### Minimum viable tracking (Phase 1)
Customer sees status updates by polling:
- `GET /public/orders/{order_id}`

The endpoint returns:
- order status (e.g., `received`, `preparing`, `ready`, `completed`)

### Future upgrade path
Milestone 9 will add WebSockets so status updates become push-based.

---

## 6) Required Endpoints (Suggested)
Even if your route names differ, these are the responsibilities you need.

### Public entry
- `GET /public/table-session/{token or ids}` or similar
  - resolves session and returns public menu view

### Menu
- `GET /public/menu` (optional if served by entry endpoint)

### Orders
- `POST /public/orders`
- `GET /public/orders/{order_id}` (polling)

---

## 7) UX and failure states
Customers should see:
- “Table session ended” if session closes
- “Item unavailable” if menu changed between load and checkout
- “Order could not be placed” on network errors with retry

---

## 8) Deliverables Checklist (Maps to todo.md)
In `todo.md`, Milestone 5 currently shows:
- [/] Public Menu view (Mobile-first, by table session).
- [/] Cart management (Frontend).
- [ ] Order placement logic.
- [ ] Real-time Order Tracking for customers.

This milestone doc should be considered complete when:
- order placement persists correct Order + OrderItems linked to table_session
- customer polling endpoint updates UI from server state

---

## End
This document intentionally focuses on customer-facing behavior and the backend responsibilities that must exist for a stable ordering flow.


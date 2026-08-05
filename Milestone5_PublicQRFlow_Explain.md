# Milestone 5: Customer-Facing Ordering (Public QR Flow) — Expounded Explanation  “Order placement logic”.

This document expands **Milestone 5** in plain language and provides an implementation-oriented guide aligned with the existing architecture (Laravel + Inertia + Vue + repository/service separation).

Milestone 5 turns a scanned QR into a working **customer ordering flow**:
1) customer lands on a page for their table/session
2) they browse a menu (public)
3) they add items to a cart
4) they place an order tied to the open table session

> In your `todo.md`, Milestone 5 currently shows:
- [/] Public Menu view
- [/] Cart management
- [ ] Order placement logic
- [ ] Real-time Order Tracking for customers

So this milestone doc focuses on what remains (and what the “in-progress” parts should ultimately satisfy).

---

## 1) Goal (what “Public QR Flow” means)
A customer should be able to:
- Scan a QR placed on a table
- Automatically arrive at the correct UI for that table/session
- See menu categories + items (public, not admin-only)
- Add items to a cart
- Place an order that is associated with the correct `table_session`

---

## 2) Required concepts from Milestone 4
Milestone 5 depends on Milestone 4 providing a reliable mapping:
- QR payload → **table identity** + **open table session**

The public flow must ultimately attach all cart/order actions to the same **active `TableSession`**.

Expected resolution data passed to the public pages:
- `table_session_id` (or token)
- `table_id` (optional for UI display)
- maybe `restaurant_id`

---

## 3) Public Menu view (in-progress) — what it should do
### UI responsibilities
- Render categories (menu grouped by category)
- Render menu items within a category
- Provide a way to add items to cart
- Should be mobile-first (fast scanning + quick add)

### Backend responsibilities
A public menu endpoint (or controller method) should:
- Accept session context (derived from QR/session resolution)
- Return only **available** menu items
  - If you support “active menu only”, filter by status
- Return the structure expected by the frontend:
  - categories[] { id, name }
  - for each category: items[] { id, name, price, image? }

### Acceptance criteria for “done enough”
- If the QR session is invalid/closed/inactive, the menu page should not load an ordering state.
- Menu load time should be acceptable (avoid N+1 queries; eager-load categories/items).

---

## 4) Cart management (in-progress) — what it should do
Cart in a QR ordering app needs two types of state:

### (A) Client-side cart state
- Maintain a list of items + quantity
- Allow increment/decrement
- Allow “remove item”
- Compute totals (sum of price * qty)

### (B) Server-side cart/order association
Even if you store cart in the browser for UX, the app must reliably transform it into a **persistent order** when the customer places an order.

Recommended Phase-1 approach:
- Keep cart in client state
- When “Place Order” is clicked, POST cart → create order(s) linked to the session
- Clear cart after successful placement

### Acceptance criteria
- Cart survives navigation within the public ordering UI (optional), or at least behaves predictably.
- Cart totals are consistent with server calculations rules.

---

## 5) Order placement logic (not started) — the core remaining work
Order placement is where customers become “real orders” in the system.

### What “place order” must do
When customer clicks **Place Order**:
1. Validate the session is still open
2. Validate menu items (exist + active)
3. Validate quantities (>= 1, not absurd)
4. Create an `Order` record linked to `table_session_id`
5. Create `OrderItem` records linked to that order
6. Return an order confirmation to the client (order id)
7. Clear cart

### Key validation rules
- Only allow ordering if the session is `open`.
- Prevent ordering using a stale/invalid session id/token.
- Ensure ordered items come from the menu dataset.

### Data model expectations
Even without seeing your DB schema directly, the naming in the project suggests:
- `Order` has: `table_session_id` (or token)
- `OrderItem` has: `order_id`, `menu_item_id`, `quantity`, `unit_price` (recommended)

Storing `unit_price` at order time is important for pricing consistency.

### API shape (example)
- `POST /public/orders` with body:
  - `table_session_id`
  - `items: [{ menu_item_id, quantity }]`

Response:
- `order_id`
- `status`

---

## 6) Real-time Order Tracking for customers (not started)
In the future milestones, kitchen updates will be broadcast. For Milestone 5, the customer should at least see **status progression**.

### What the customer needs to see
- “Order received”
- “Preparing” / “Ready” (depending on kitchen milestones)

### Implementation options
Phase 1 options (choose based on project readiness):
1. **Polling** (simpler):
   - client periodically calls `GET /public/orders/{order_id}`
2. **Server-sent events / WebSockets** (later milestones align with this)

Given Milestone 9 is “WebSockets integration”, your Milestone 5 can start with polling.

### Acceptance criteria
- Customer UI updates from server-confirmed status.
- Tracking works even if the user refreshes (order history fetch optional).

---

## 7) UX and edge cases you should design now
Even if they’re “later”, it’s better to define behavior now:

### Duplicate ordering
- Prevent accidental double-click creating duplicate orders.
  - Client disables button while placing
  - Server can optionally support idempotency keys (optional)

### Session close during ordering
- If the session becomes closed after menu load:
  - disable ordering
  - show a message: “Table session ended”

### Empty cart
- Disable Place Order when cart is empty.

---

## 8) Milestone 5 deliverables checklist
Use this to guide completion:

### Public menu
- [/] Public menu endpoint returns categories + items
- [/] Menu UI displays categories and add-to-cart actions

### Cart
- [/] Cart UI adds/removes quantities
- [/] Cart totals computed correctly

### Order placement
- [x] Validate table session open
- [x] Persist Order + OrderItems
- [x] Return order id and clear cart

### Customer tracking
- [x] Implement order status fetch (polling to start)
- [x] UI shows status progression

---

## 9) Suggested file boundaries (implementation guidance)
In your codebase you already have:
- `TableSessionController.php`
- `CartController.php`

A common split for Milestone 5:
- **Public controllers**
  - `PublicMenuController` (or reuse Menu controllers with public mode)
  - `PublicOrderController` (place order + list customer orders)
- **CartController**
  - may handle transient cart operations or just compute totals
  - final persistence should happen at order placement
- **Frontend pages**
  - public menu page (already partially in place)
  - public cart + place order page
  - optional “tracking panel” component

---

## 10) How Milestone 5 feeds later milestones
- Waiter/Kitchen interfaces (Milestone 6/7) will act on the orders created here.
- Billing & cashier (Milestone 8) depends on order completion / served state.
- Real-time notifications (Milestone 9) will improve tracking beyond polling.

---

# End of Milestone 5 explanation


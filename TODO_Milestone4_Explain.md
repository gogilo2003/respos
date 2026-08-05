# Milestone 4: Tables, QR Codes & Session Management — Expounded Explanation

This document expands **Milestone 4** in plain language and also provides a practical implementation guide that matches the patterns already present in the codebase (Laravel + Inertia + Vue + Repository/Service layer).

---

## 1. Why this milestone exists
The restaurant flow needs a bridge between:
- **Physical tables** on the floor
- A **customer ordering UI** opened via QR code
- A persistent server-side concept of “this table is currently ordering”

Milestone 4 introduces:
1) **Table records** (so admins can define tables)
2) **QR payloads** (so customers can arrive at the correct table)
3) **Table sessions** (so orders can be associated to an open ordering window)

Without this milestone, Milestone 5 (public menu ordering via QR) cannot be properly secured or associated to a table.

---

## 2. Table Management CRUD (Admin)
### Admin use-cases
Admin should be able to:
- Create a new table
- Rename it
- Enable/disable it
- See a list of tables

### Recommended data shape
A table should include:
- `id`
- `restaurant_id` (if you support multiple restaurants)
- `name` / `label`
- `status` (active/inactive) or similar

### CRUD scope for Phase 1
Minimum viable:
- list tables
- create table
- update table
- deactivate/activate (or delete if you prefer)

### Backend responsibilities
- validate uniqueness (optional): don’t allow duplicate table labels per restaurant
- prevent editing a table that is referenced by active sessions (optional guard)

### Frontend responsibilities (Admin)
- tables index page
- tables create/edit form
- basic confirmation for destructive actions

---

## 3. QR Code generation logic
### Required QR payload format
The task requires the QR content to be:

`{restaurant_id}|{table_id}|{base_url}`

### How QR is used
When scanned, your app must:
- read the payload string
- parse the three components
- validate them server-side
- route the customer to a “table ordering” entrypoint

### Server-first validation (important)
Never trust QR payload alone.
Always validate:
- `restaurant_id` exists
- `table_id` exists
- table is active
- session rules allow the requested operation

### Implementation patterns
Two typical approaches:
1. **Stateless QR**: QR encodes IDs; server derives session.
2. **Stateful QR**: QR encodes a pre-generated session token.

For Phase 1, approach (1) is usually simplest while still secure due to server validation.

---

## 4. Table Session Management (Open/Close + Validation)
### What a session is
A **table session** represents an open ordering period for a single table.

Rules you should enforce:
- A table can have **only one active session** at a time.
- Opening a session should create it if missing; otherwise reuse the existing active one.
- Closing a session prevents further ordering.

### Session statuses
Common statuses:
- `open`
- `closed`

Additionally track:
- `opened_at`
- `closed_at`

### Open flow
When a QR is scanned:
1. Parse payload: restaurant_id, table_id, base_url
2. Validate table exists + is active
3. Check for an active session:
   - If exists: return it to the UI
   - If not: create a new open session
4. Return session context to the frontend (for Milestone 5)

### Close flow
Close can be triggered by:
- admin action (optional now, later for waiter/kitchen)
- cashier/billing completion (later milestone)

Close sets:
- session status to closed
- closed timestamp

### Token validation strategies
You have two main patterns:
- **Session token stored in DB**: QR points to table; open endpoint creates token and returns it.
- **Session key embedded**: QR includes token directly.

Given the milestone’s stated payload format, the simplest is:
- QR payload identifies table
- server creates/returns session

---

## 5. How Milestone 4 enables Milestone 5
Milestone 5 (“Public QR Flow”) needs:
- a reliable way to resolve QR -> table session
- a session identifier to attach cart/order operations

Milestone 4 should therefore expose to the frontend:
- current open session id/token
- table id context
- allowed ordering state

---

## 6. Practical acceptance checklist
When Milestone 4 is done, these should be true:
- Admin can create/edit tables.
- QR scans for a valid active table result in an open session.
- QR scans for invalid/inactive tables return an error.
- Re-scanning while a session is active reuses the same session (or blocks appropriately).
- Closing a session prevents further ordering on it.

---

## 7. Suggested file boundaries (implementation guidance)
Even if your exact naming differs, aim for boundaries like:
- Model: `RestaurantTable`, `TableSession`
- Repository/Service: create/find open session, close session
- Controller: endpoints for open/close and for returning session context
- Inertia pages/components:
  - Admin tables
  - Public table entry UI (consuming session context)

---

End of Milestone 4 explanation.


# Milestone 4: Tables, QR Codes & Session Management

Expands and documents what needs to be built for **Milestone 4** and how it should behave in the restaurant ordering system.

> Milestone 4 is the backend + admin foundation required for Milestone 5 (Public QR Flow).

---

## 1) Goals of Milestone 4

This milestone adds three core capabilities:

1. **Table Management CRUD (Admin)**
   - Admin defines the set of physical tables on the floor.
   - Admin can enable/disable tables.

2. **QR Code generation / scanning contract**
   - QR encodes enough information so the server can identify the table.
   - Required QR payload format:
     - `{restaurant_id}|{table_id}|{base_url}`

3. **Table Session Management**
   - When customers start ordering, you create an “open session” for that table.
   - Closing a session prevents new ordering.
   - Server must validate QR-derived table/session state.

---

## 2) Data Model (what you should have)

### `RestaurantTable`
Represents a physical table.

Minimum viable fields:
- `id`
- `restaurant_id` (optional if single-restaurant app)
- `name` or `label` (e.g., “Table 1”, “VIP Room”)
- `status` (active/inactive)
- timestamps

### `TableSession`
Represents an ordering window for a table.

Minimum viable fields:
- `id`
- `restaurant_table_id` (or `table_id`)
- `status` (`open`, `closed`)
- `opened_at`
- `closed_at` (nullable)

Important invariant:
- **At most one active session per table**.

---

## 3) Admin: Table Management CRUD

### Admin use-cases
Admin must be able to:
- view all tables
- create a table
- edit a table (rename / status)
- deactivate a table (or delete, but deactivation is safer)

### Backend responsibilities
- Validate inputs (label/name required, unique per restaurant if you enforce uniqueness).
- Prevent obvious invalid state (e.g., empty labels).
- Optional guard (recommended):
  - If a table has an active session, either block changes or allow status changes only after session close.

### Frontend responsibilities (Inertia)
- Tables index page with a list/grid
- create/edit forms
- simple confirmation for destructive actions

---

## 4) QR Code Contract

### QR payload format
The milestone requires:

`{restaurant_id}|{table_id}|{base_url}`

### Why include `base_url`
This is useful when:
- you want QR data to remain portable between environments
- you need to generate or validate absolute URLs

### QR payload parsing
When a QR is scanned:
1. Split payload by `|`
2. Extract:
   - `restaurant_id`
   - `table_id`
   - `base_url`
3. Validate types and existence.

---

## 5) Table Session Management (Open/Close)

### Session states
Recommended:
- `open`
- `closed`

### Opening a session
When a customer hits the QR-derived entry endpoint:
1. Validate `restaurant_id`
2. Validate `table_id`
3. Ensure the table is `active`
4. Check if there is an existing `open` session
   - If it exists: reuse it
   - If it does not exist: create a new open session
5. Return session context to the frontend

Returned context should include enough for Milestone 5, such as:
- `table_id`
- `table_session_id`
- `status`

### Closing a session
Close should:
- set `status = closed`
- set `closed_at`
- prevent any further ordering attempts

Close is typically triggered by later milestones, but you should make the behavior correct now:
- closing makes the session invalid
- reopening after close should create a new open session

---

## 6) Token validation (security considerations)

Even though the QR contains IDs, you must **not** trust the QR alone.

Validation must always be server-side:
- restaurant exists
- table exists and is active
- table session rules respected (one open session)
- session open/closed state checked before allowing ordering/cart actions

---

## 7) End-to-end behavior checklist

When Milestone 4 is done, verify:

### Tables
- Admin can add/edit/disable tables

### QR -> session
- QR for an active table opens a session
- QR for an inactive table is rejected
- Re-scanning the same QR while session is open returns/reuses the existing open session

### Session state
- Closing a session prevents ordering

---

## 8) Suggested endpoints (implementation guidance)

Exact routes depend on your project conventions, but typical Phase 1 endpoints are:

- `GET /admin/tables` (list)
- `POST /admin/tables` (create)
- `PUT /admin/tables/{table}` (update)
- `POST /tables/qr/resolve` (QR payload -> session context)
- `POST /tables/sessions/{session}/close` (close session; may be admin-driven for now)

---

## 9) Files you typically touch (to keep boundaries clean)

- **Models**: `RestaurantTable`, `TableSession`
- **Migrations**: `create_menu_tables` already exists; ensure tables/session migrations are present
- **Repositories/Services**: create/find open session; close session; QR payload parsing
- **Controllers**:
  - Admin Tables CRUD controller
  - QR resolve controller
  - Session open/close controller
- **Frontend**:
  - Admin tables pages (Inertia)
  - Public table session entry page/components for Milestone 5

---

## Milestone 4 acceptance criteria (summary)

- [ ] Admin can CRUD tables.
- [ ] QR payload parsing works with `{restaurant_id}|{table_id}|{base_url}`.
- [ ] QR resolve endpoint validates table and returns/creates an open session.
- [ ] Table session open/close rules are enforced.



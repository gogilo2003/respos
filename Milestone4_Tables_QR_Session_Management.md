# Milestone 4: Tables, QR Codes & Session Management (Admin + Public Integration)

## Goal
Enable restaurants to manage physical **dining tables** and convert each table into a secure, time-safe **QR entry point** that starts a customer ordering flow.

This milestone covers:
1. **Table Management (Admin CRUD)**
2. **QR Code generation logic** (stable payload format)
3. **Table Session Management** (Open/Close + token validation)

---

## 1) Table Management CRUD (Backend + Frontend)
### What we need
Admin can:
- create a restaurant table (e.g., “Table 1”, “Patio - Booth A”)
- update table properties
- deactivate/rename tables (optional but recommended)
- delete tables (optional; if you do, ensure foreign-key safety)
- view a table list (with pagination/search optional)

### Data model (expected)
Typically represented by `RestaurantTable`.
Key fields (minimum):
- `id`
- `restaurant_id` (if multi-restaurant is supported)
- `name` / `label` (e.g., “Table 12”)
- `status` (e.g., active/inactive)
- timestamps

> Note: your migrations already include `RestaurantTable` and `TableSession` models in the codebase, so this milestone should wire CRUD to those models.

### Backend routes / controllers (Admin)
You should implement routes similar to:
- `GET /admin/tables`
- `POST /admin/tables` (create)
- `PUT/PATCH /admin/tables/{table}` (update)
- `DELETE /admin/tables/{table}` (delete)

Controller responsibilities:
- validate input
- call repository/service for persistence
- return Inertia responses with the required props

### Frontend (Admin)
Create an Admin UI that includes:
- Tables index page (list + create button)
- Tables form (create/edit)
- Optional: confirmation modal for delete

### Acceptance criteria
- Tables persist in the database.
- Admin can update a table and changes reflect immediately.
- Inactive tables don’t appear in the public QR entry flow (if you implement status filtering).

---

## 2) QR Code Generation Logic
### Payload format
The milestone requires QR payload:

`{restaurant_id}|{table_id}|{base_url}`

Where:
- `{restaurant_id}`: the restaurant context (or fixed value for single-restaurant installs)
- `{table_id}`: the table being ordered from
- `{base_url}`: the root URL used by the app (so the QR can be portable)

### Recommended strategy
1. Build a token string using the payload format.
2. (Best practice) Prefer a **server-validated token** rather than accepting raw IDs directly.
3. If you truly must store raw payload, validate it robustly before starting a session.

A pragmatic approach for Phase 1:
- Encode the payload into the QR content (QR stores a string).
- On scan, parse the string: `{restaurant_id}`, `{table_id}`, `{base_url}`.
- Derive the session flow from `restaurant_id` + `table_id`.

### QR generation endpoint (optional but typical)
Admin might generate QR for each table via:
- a page action “Show QR”
- or a backend endpoint returning a QR SVG/image

If you generate QR images server-side:
- use a QR library
- store nothing (stateless), or optionally cache

If you generate QR client-side:
- keep the payload generation in the frontend but validate server-side.

### Acceptance criteria
- QR scans should reliably route to the correct table session endpoint.
- Invalid payload formats should return a clear error.

---

## 3) Table Session Management (Open/Close + Token Validation)
### What a table session means
A **table session** represents an active ordering window for a specific table.

Common behaviors:
- A session can be **opened** when a customer starts ordering.
- A session can be **closed** when ordering ends (or manually by admin/waiter).
- Only **one active session** per table at a time.

### Session lifecycle
1. Customer visits the table session URL (from the QR).
2. System validates QR payload.
3. System checks for existing active session:
   - If active exists: reuse (or ask user to confirm continue)
   - If none: create new session marked as open
4. Customer ordering UI associates all cart/orders to that session token/id.
5. Close session logic:
   - called explicitly when the flow ends
   - or when billing/checkout completes (future milestone)

### Token validation
Token validation should ensure:
- payload parses correctly
- restaurant exists
- table exists and is active
- session safety rules:
  - if session token is required: confirm token matches table+restaurant
  - prevent opening multiple sessions concurrently

**Security note:** Avoid trusting QR payload blindly. Always validate against server-side records.

### Backend responsibilities
You will likely implement a controller/service that includes:
- open session: `POST /tables/{table}/sessions/open` (or similar)
- close session: `POST /tables/{table}/sessions/close`
- get current session state for the public ordering page

For token validation you may implement:
- parse payload
- confirm `RestaurantTable` exists
- start or fetch `TableSession`

### Database expectations
`TableSession` model should include at least:
- `id`
- `table_id`
- `restaurant_id` (optional if table already implies it)
- `token` or `session_key` (recommended)
- `status` (open/closed)
- `opened_at`, `closed_at`
- timestamps

### Acceptance criteria
- Scanning a QR for the same table returns the same active session (or creates one if none exists).
- Closing a session prevents further ordering on that session token.
- Invalid QR payload does not create sessions.

---

## Implementation Checklist (Milestone-ready)
- [ ] Admin CRUD for tables implemented end-to-end.
- [ ] QR payload string matches required format: `{restaurant_id}|{table_id}|{base_url}`.
- [ ] Public/session entry endpoint validates QR payload.
- [ ] Table session open logic enforces single active session per table.
- [ ] Close session logic marks session closed and records timestamps.
- [ ] UI wiring for table session page to use the opened session context.

---

## How this milestone feeds Milestone 5
Milestone 5 (“Public Menu view”) will depend on:
- the ability to resolve a scanned QR into a `TableSession`
- the ability for the public menu UI to fetch menu items and attach cart/order actions to the active session

---

## Deliverables
- Admin tables UI + API
- QR payload generator and/or QR rendering
- Table session open/close + validation
- Documentation in this file to guide follow-up milestones


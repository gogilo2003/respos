# Project TODO - Restaurant Point of Sale (respos)

This TODO list tracks the progress of the Phase 1 (Cash-First Release) implementation, based on `materials/prompts.md`.

## Legend
- [ ] Not Started
- [/] In Progress
- [x] Completed

---

## Milestone 1: Project Setup & Database Foundation
- [x] Initialize Laravel 12 project with required packages (Inertia, Vue 3, TS, Tailwind).
- [x] Create Eloquent Models + Relationships (based on `database.sql`).
- [x] Run migrations for all tables.
- [x] Implement Repository Pattern Base and core repositories:
    - [x] `BaseRepository` / `RepositoryInterface`
    - [x] `UserRepository` / `UserRepositoryInterface`
    - [x] `RoleRepository` / `RoleRepositoryInterface`
- [x] `MenuItemRepository`
- [x] `TableRepository`
- [/] `OrderRepository`
- [ ] `BillRepository`

## Milestone 2: Authentication & User Management (Admin + Staff)
- [x] Staff Login/Auth implementation (Supports email/username and password_hash).
- [x] Role-Based Authorization (Gates/Policies and Inertia shared data).
- [x] Admin CRUD for Users (Backend + Frontend).
- [x] Seed default admin user and roles.

## Milestone 3: Menu Management (Admin)
- [x] Menu Categories CRUD (Backend + Frontend).
- [x] Menu Items CRUD (Backend + Frontend).
- [x] Image upload handling for menu items.

## Milestone 4: Tables, QR Codes & Session Management
- [x] Table Management CRUD (Backend + Frontend).
- [x] QR Code generation logic (`{restaurant_id}|{table_id}|{base_url}`).
- [x] Table Session Management (Open/Close logic, token validation).

## Milestone 5: Customer-Facing Ordering (Public QR Flow)

- [x] Public Menu view (Mobile-first, by table session).
- [x] Cart management (Frontend).
- [x] Order placement logic.
- [x] Real-time Order Tracking for customers.

## Milestone 6: Waiter Interface
- [x] Waiter Dashboard (Table grid).
- [ ] Manual order entry for waiters.
- [ ] Assistance request handling.

##
# Milestone 7: Kitchen Interface
- [x] Kitchen Dashboard (Order queue)
- [x] Item-level status updates (Accepted → Preparing → Ready)
- [x] SLA timestamp tracking [timestamps stored in OrderItem]
- [ ] Real-time polling / synchronization (Frontend)

## Milestone 8: Cashier, Billing & Payments (Cash Only)
- [ ] Bill Generation (Served items only).
- [ ] Split Billing logic (Equally, By Item, Custom).
- [ ] Cash Payment processing & Change calculation.
- [ ] Receipt generation (Thermal/PDF) & Printing.

## Milestone 9: Real-time Features & Notifications
- [ ] WebSockets integration (Reverb/Pusher).
- [ ] Event broadcasting (Order placed, Item ready, Bill requested).
- [ ] Real-time UI listeners.

## Milestone 10: Reporting, Reconciliation & Admin Polish
- [ ] Sales Reports & Analytics.
- [ ] End-of-Day Cash Reconciliation.
- [ ] Audit Logging for sensitive actions.

## Milestone 11: Testing, Error Handling, Polish & Deployment
- [ ] Write Pest Feature Tests for critical flows.
- [ ] Error handling & offline mode banners.

- [ ] Final UI/UX polish.

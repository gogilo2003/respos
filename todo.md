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
    - [x] `UserRepository`
    - [x] `RoleRepository`
    - [ ] `MenuItemRepository`
    - [ ] `TableRepository`
    - [ ] `OrderRepository`
    - [ ] `BillRepository`

## Milestone 2: Authentication & User Management (Admin + Staff)
- [x] Staff Login/Auth implementation (Supports email/username and password_hash).
- [x] Role-Based Authorization (Gates/Policies and Inertia shared data).
- [x] Admin CRUD for Users (Backend + Frontend).
- [x] Seed default admin user and roles.

## Milestone 3: Menu Management (Admin)
- [ ] Menu Categories CRUD (Backend + Frontend).
- [ ] Menu Items CRUD (Backend + Frontend).
- [ ] Image upload handling for menu items.

## Milestone 4: Tables, QR Codes & Session Management
- [ ] Table Management CRUD (Backend + Frontend).
- [ ] QR Code generation logic (`{restaurant_id}|{table_id}|{branch_id}|{api_base_url}`).
- [ ] Table Session Management (Open/Close logic, token validation).

## Milestone 5: Customer-Facing Ordering (Public QR Flow)
- [ ] Public Menu view (Mobile-first, by table session).
- [ ] Cart management (Frontend).
- [ ] Order placement logic.
- [ ] Real-time Order Tracking for customers.

## Milestone 6: Waiter Interface
- [ ] Waiter Dashboard (Table grid).
- [ ] Manual order entry for waiters.
- [ ] Assistance request handling.

## Milestone 7: Kitchen Interface
- [ ] Kitchen Dashboard (Order queue).
- [ ] Item-level status updates (Accepted -> Preparing -> Ready).
- [ ] SLA timestamp tracking.

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

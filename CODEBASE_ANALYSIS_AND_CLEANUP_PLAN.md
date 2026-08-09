# Respos — Codebase Analysis & Cleanup Plan

> **Project:** QR-Based Restaurant Ordering and Billing Management System (Phase 1 — Cash-First)
> **Branch:** `fern`
> **Analysis Date:** 2026-08-06
> **Status:** Milestones 5–7 scaffolded; core flows partially broken

---

## 1. Executive Summary

`respos` is a Laravel 12 + Inertia + Vue 3 + TypeScript restaurant point-of-sale system. The backend models, migrations, and several role-specific controllers are in place, but the application is not yet production-ready due to critical authentication and order-creation bugs, missing frontend pages, incomplete repository coverage, and a failing test suite.

**Current test status:** 20 FAILED, 5 PASSED

**Bottom line:** Fix the foundation first (auth, orders, tests), then complete the missing Milestone 8 billing backend and frontend, followed by waiter/kitchen UI and real-time features.

---

## 2. What Exists (Scaffolded vs. Working)

| Domain | Scaffolded | Working |
|--------|-----------|---------|
| Auth & Roles | ✅ | ⚠️ (auth bug breaks login) |
| Users CRUD | ✅ | ✅ |
| Menu Categories CRUD | ✅ | ✅ |
| Menu Items CRUD | ✅ | ✅ |
| Tables CRUD | ✅ | ✅ |
| QR Codes & Sessions | ✅ | ✅ |
| Customer Ordering (Public) | ⚠️ | ❌ (broken controller + hardcoded menu) |
| Waiter Interface | ⚠️ | ❌ (routes unregistered + no UI) |
| Kitchen Interface | ⚠️ | ❌ (no frontend page) |
| Cashier / Billing / Payments | ❌ | ❌ |
| Receipts / Reconciliation | ❌ | ❌ |
| Real-time Notifications | ❌ | ❌ |
| Reporting & Audit | ❌ | ❌ |
| Tests | ⚠️ | ❌ (20 failing) |

---

## 3. Critical Bugs (Fix Immediately)

| # | Bug | Location | Impact |
|---|-----|----------|--------|
| 1 | **`getAuthPasswordName()` instead of `getAuthPassword()`** | `app/Models/User.php:51` | All authentication fails. Laravel expects `getAuthPassword()`. |
| 2 | **Wrong column `table_session_id` instead of `session_id`** | `app/Http/Controllers/OrderController.php:28` | Mass assignment ignores the field → `NOT NULL` constraint violation. |
| 3 | **`$menuItem->price` instead of `$menuItem->base_price`** | `app/Http/Controllers/OrderController.php:35` | `unit_price` becomes null → `NOT NULL` constraint violation. |
| 4 | **No database transaction in `OrderController::store()`** | `app/Http/Controllers/OrderController.php:27-44` | Partial inserts leave orphaned orders. |
| 5 | **Tests fail due to missing roles in test DB** | `tests/Feature/ProfileTest.php` | RoleSeeder isn't running before tests. |
| 6 | **`MenuService::getMenuItems()` returns hardcoded dummy data** | `app/Services/MenuService.php:19-68` | Customer menu is fake, not synced with DB. |
| 7 | **`Notification::scopeForRole()` has ungrouped `orWhere`** | `app/Models/Notification.php:46` | SQL logic bug when more `where` clauses are chained. |
| 8 | **`WebMenuBar` uses hardcoded `<a>` tags** | `resources/js/Components/WebMenuBar.vue` | Full page reloads instead of Inertia navigation. |
| 9 | **`Welcome.vue` references missing `LandingNavBar` component** | `resources/js/Pages/Welcome.vue` | Build/runtime error. |
| 10 | **`Cart.vue` navigates to `/checkout` which doesn't exist** | `resources/js/Pages/Cart.vue:46` | Dead navigation → 404. |
| 11 | **`WaiterController` routes are not registered in `web.php`** | `routes/web.php` | Waiter backend is completely unreachable. |
| 12 | **No `Kitchen/Dashboard.vue` page** | Frontend missing | `KitchenController::dashboard()` renders a non-existent page. |
| 13 | **No `OrderRepository` exists** | `app/Repositories/` | Inconsistent with repo pattern; duplicated logic across controllers. |

---

## 4. Code Quality & Architecture Issues

| Issue | Details |
|--------|---------|
| **No Policies directory** | `Gate::authorize('waiter')` in controllers will fail without global gate definitions or Policies. |
| **No Form Requests** | Controllers use inline `$request->validate()` instead of dedicated Form Request classes. |
| **No `OrderRepository` / `BillRepository`** | Core business logic bypasses the repository pattern. |
| **No Pinia stores** | Frontend state management is ad-hoc (refs, localStorage for cart). |
| **No shadcn-vue components** | Project convention says use shadcn-vue, but `Components/ui/` doesn't exist. |
| **Duplicate order creation logic** | `OrderController` and `WaiterController` both manually create orders instead of sharing a service. |
| **Missing interfaces on frontend** | No TypeScript interfaces for `Order`, `Bill`, `Payment`, `Receipt`, `OrderItem`. |
| **No TypeScript strictness** | `Menu.vue` defines its own inline types instead of importing from `interfaces/menu.ts`. |
| **`User` factory hardcodes `role_id = 2`** | `database/factories/UserFactory.php` likely assumes role 2 exists, breaking tests when roles aren't seeded. |

---

## 5. Missing Features by Milestone

### Milestone 5 — Customer Ordering (Public QR Flow)
- [ ] Fix `OrderController::store()` (bugs #2, #3, #4)
- [ ] Replace `MenuService::getMenuItems()` with real DB query
- [ ] Add order tracking page (`resources/js/Pages/Orders/Track.vue`)
- [ ] Sync cart with backend session (currently localStorage-only)

### Milestone 6 — Waiter Interface
- [ ] Register `WaiterController` routes in `web.php`
- [ ] Build `Waiter/Dashboard.vue` (table grid, quick order form)
- [ ] Build waiter order entry form
- [ ] Build assistance request UI + notifications

### Milestone 7 — Kitchen Interface
- [ ] Build `Kitchen/Dashboard.vue` (order queue, bump button, prep time highlights)
- [ ] Add `served` status transition in `KitchenController`
- [ ] Real-time polling/sync frontend

### Milestone 8 — Cashier, Billing & Payments (HIGH PRIORITY)
- [ ] `BillRepository` + `BillService` (generate from served items, tax/service charge calc)
- [ ] `BillController` (create, show, void)
- [ ] `PaymentController` (record cash, calculate change)
- [ ] `ReceiptController` + PDF generation (DomPDF)
- [ ] Split bill logic (equally, by item, custom)
- [ ] Cashier dashboard UI

### Milestone 9 — Real-time Features & Notifications
- [ ] Laravel Reverb / WebSocket setup
- [ ] Notification service (broadcast + DB log)
- [ ] Frontend Echo listeners for all roles

### Milestone 10 — Reporting, Reconciliation & Admin Polish
- [ ] Sales reports controller + UI
- [ ] End-of-day cash reconciliation
- [ ] Audit logging for sensitive actions

### Milestone 11 — Testing, Error Handling, Polish & Deployment
- [ ] Fix 20 failing tests (seed roles, fix auth, fix factories)
- [ ] Pest feature tests for critical flows
- [ ] Error handling & offline mode banners

---

## 6. Recommended Cleanup & Completion Plan

### Phase A: Stabilize Foundation (Week 1)
**Goal:** Fix all critical bugs so the app can actually run and authenticate.

1. **Fix `User.php` auth bug** — replace `getAuthPasswordName()` with `getAuthPassword()`.
2. **Fix `OrderController::store()`** — use `session_id`, `base_price`, wrap in `DB::transaction()`, set `placed_by_role`/`placed_by_user`.
3. **Fix test suite** — ensure `RoleSeeder` runs in `TestCase::setUp()`, fix `UserFactory` to attach a role properly.
4. **Replace dummy `MenuService::getMenuItems()`** with `MenuItemRepository` query.
5. **Fix `Notification` scope** — group `orWhere` in closure.
6. **Remove/repair `LandingNavBar` reference** in `Welcome.vue`.
7. **Fix `WebMenuBar`** — use Inertia `<Link>` instead of `<a>` tags.

### Phase B: Complete Missing Backend Repositories & Services (Week 1-2)
**Goal:** Enforce repository pattern and add billing backend.

1. **Create `OrderRepository` + `OrderRepositoryInterface`** — move order creation logic here.
2. **Create `BillRepository` + `BillRepositoryInterface`** — bill generation, split logic, totals.
3. **Create `PaymentRepository` + `PaymentRepositoryInterface`** — payment recording, change calculation.
4. **Create `ReceiptRepository` + `ReceiptRepositoryInterface`** — receipt numbering, PDF generation.
5. **Create `OrderService`** — shared order creation logic for customer + waiter.
6. **Add Form Requests** for all critical endpoints (`StoreOrderRequest`, `BillRequest`, `PaymentRequest`, `KitchenUpdateItemRequest`).

### Phase C: Build Cashier & Billing UI (Week 2-3)
**Goal:** Complete the core revenue flow (bill → pay → receipt).

1. **`Cashier/Dashboard.vue`** — active bills, quick payment entry.
2. **`Bills/Show.vue`** — bill detail with split tabs (equally, by item, custom).
3. **`Payments/Create.vue`** — cash payment form with auto-change calculation.
4. **`Receipts/Show.vue`** — receipt preview + print button.
5. **Register all cashier routes** in `web.php`.

### Phase D: Complete Waiter & Kitchen Frontends (Week 3)
**Goal:** Make existing backend usable.

1. **`Waiter/Dashboard.vue`** — color-coded table grid, quick order form.
2. **`Kitchen/Dashboard.vue`** — order queue, item status buttons, prep time SLA highlights.
3. **Register `WaiterController` routes** in `web.php`.
4. **Add role-based dashboard redirect** — after login, send users to their role-specific page.

### Phase E: Real-time & Notifications (Week 4)
**Goal:** Live updates across all interfaces.

1. Install & configure **Laravel Reverb** (or Pusher/Soketi).
2. Broadcast events: `OrderPlaced`, `ItemReady`, `BillRequested`, `PaymentConfirmed`.
3. Add Echo listeners in Vue composables.
4. Build notification bell/dropdown UI.

### Phase F: Reporting, Reconciliation & Polish (Week 4-5)
**Goal:** Admin completeness and production readiness.

1. Sales reports dashboard.
2. End-of-day cash reconciliation form.
3. Audit log viewer.
4. Offline/degraded network banners.
5. Run full Pest suite — target 0 failures.
6. Run `npm run build` and `vendor/bin/pint`.

---

## 7. Immediate Next Steps (Priority Order)

1. **Run `vendor/bin/pint`** to format existing PHP.
2. **Fix `User.php`** — `getAuthPassword()` override.
3. **Fix `OrderController`** — correct columns, add transaction.
4. **Fix tests** — seed roles in `TestCase`, fix factory.
5. **Fix `MenuService`** — remove hardcoded data.
6. **Register `WaiterController` routes** in `web.php`.
7. **Create missing `OrderRepository`** and refactor order creation.
8. **Build `Kitchen/Dashboard.vue`** (backend exists, frontend missing).
9. **Build `Waiter/Dashboard.vue`** + register routes.
10. **Start Milestone 8 backend** — `BillRepository`, `BillController`, `PaymentController`.

---

## 8. Appendix: Key File Locations

| Concern | Path |
|---------|------|
| Auth model bug | `app/Models/User.php:51` |
| Broken order creation | `app/Http/Controllers/OrderController.php:27-44` |
| Dummy menu data | `app/Services/MenuService.php:19-68` |
| Missing waiter routes | `routes/web.php` |
| Missing kitchen page | `resources/js/Pages/Kitchen/Dashboard.vue` |
| Missing waiter page | `resources/js/Pages/Waiter/Dashboard.vue` |
| Test failures | `tests/Feature/ProfileTest.php` |
| Migrations | `database/migrations/2026_06_16_002401_create_branch_tables.php` |
| Schema reference | `materials/database.sql` |
| Milestone tracker | `todo.md` |

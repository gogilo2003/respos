**Project Breakdown: QR-Based Restaurant Ordering System (Phase 1 - Cash-First)**

**Tech Stack Reminder**:
- **Backend**: Laravel 12, Repository Pattern (thin controllers → Repository → Service where complex logic), Eloquent Models, Form Requests, Resource classes, Policies/Gates for authorization.
- **Frontend**: Inertia.js + Vue 3 (Script Setup + TypeScript) + Tailwind (assumed).
- Use UUIDs where specified in schema.
- All tasks assume the attached `database.sql` is the final schema.

---

### **Milestone 1: Project Setup & Database Foundation**

**Backend Tasks:**

1. **Initialize Laravel 12 project with required packages**  
   "Create a fresh Laravel 12 project. Install and configure: Inertia.js (Vue 3 + TypeScript), Laravel Sanctum (for API if needed), Laravel Breeze or Jetstream (minimal auth scaffold), Spatie Laravel Permission or custom roles (since we have a roles table), Laravel Pint, PHPStan. Set up MySQL connection and run the provided `database.sql` schema. Create basic `.env` example. Commit initial structure."

2. **Create Eloquent Models + Relationships**  
   "Using the provided database.sql, generate all Eloquent models (`User`, `Role`, `RestaurantTable`, `QrCode`, `MenuCategory`, `MenuItem`, `TableSession`, `Order`, `OrderItem`, `Bill`, `BillItem`, `BillSplit`, `Payment`, `Receipt`, etc.). Define all relationships, casts (UUID, JSON, Decimal), and scopes (e.g., active, pending). Ensure UUID primary keys where used."

3. **Implement Repository Pattern Base**  
   "Create a base `Repository` interface and abstract class. Then implement repositories for core entities: `UserRepository`, `MenuItemRepository`, `TableRepository`, `OrderRepository`, `BillRepository`, etc. Include common methods (find, create, update, paginate, with relations)."

---

### **Milestone 2: Authentication & User Management (Admin + Staff)**

**Backend Tasks:**

1. **User & Role Management**  
   "Implement registration/login for staff (waiter, kitchen, cashier, manager, admin). Use the existing `roles` and `users` tables. Create `AuthController`, `UserRepository`, `UserService`. Add Form Requests for validation. Seed default admin user."

2. **Role-Based Authorization**  
   "Set up Laravel Gates/Policies for all roles. Create middleware or Inertia shared data to pass current user role/permissions. Enforce role restrictions (e.g., waiter cannot access admin routes)."

3. **Admin CRUD for Users**  
   "Build admin CRUD for users (list, create, edit, deactivate) via thin controllers calling repositories."

**Frontend Tasks:**

1. **Login & Dashboard Layout**  
   "Create Vue + TypeScript login page with Inertia. Implement protected layout with sidebar navigation based on user role. Use script setup + Pinia for auth store."

2. **User Management UI (Admin)**  
   "Build admin user management pages: list with filters, create/edit forms, role assignment."

---

### **Milestone 3: Menu Management (Admin)**

**Backend Tasks:**

1. **Menu Categories CRUD**  
   "Create `MenuCategoryRepository`, `MenuCategoryController` (CRUD). Support sort_order and is_active."

2. **Menu Items CRUD**  
   "Implement full MenuItem management: CRUD, image upload (use Laravel Storage), modifier_groups JSON handling, availability toggle. Create Form Requests and Resources."

**Frontend Tasks:**

1. **Menu Admin Interface**  
   "Build Vue pages for managing categories (drag-drop sort) and menu items (form with image preview, JSON modifier editor using simple key-value or structured inputs)."

---

### **Milestone 4: Tables, QR Codes & Session Management**

**Backend Tasks:**

1. **Table & QR Management**  
   "Implement `RestaurantTableRepository` and `QrCodeRepository`. Generate QR payload as specified (`{restaurant_id}|{table_id}|{branch_id}|{api_base_url}`). Create endpoint to generate/regenerate QR image (use `simple-qrcode` or similar)."

2. **Table Session Management**  
   "Implement `TableSessionRepository` and logic for opening/closing sessions, status transitions (enforce valid transitions), token generation/expiry."

**Frontend Tasks:**

1. **Table Management UI (Admin/Manager)**  
   "Build table list with status, capacity, location, and QR code display/download."

2. **Session Validation Backend Hook**  
   "Create middleware/API endpoint that validates table session token from QR scan."

---

### **Milestone 5: Customer-Facing Ordering (Public QR Flow)**

**Backend Tasks:**

1. **Public Menu & Ordering API**  
   "Create public routes (no auth) for menu by table session. Implement `OrderRepository` methods for customer orders. Handle cart → order creation with special instructions validation."

2. **Order Status & Session Logic**  
   "Implement order placement, status updates, and session persistence logic."

**Frontend Tasks:**

1. **Customer Menu Page (Mobile-First)**  
   "Build Vue customer interface: menu by category tabs, item cards with images, cart (floating), special instructions textarea (120 char limit), order placement. Use TypeScript interfaces for MenuItem, CartItem."

2. **Order Tracking Page**  
   "Create real-time order status view for customers (Pending → Accepted → Preparing → Ready → Served)."

---

### **Milestone 6: Waiter Interface**

**Backend Tasks:**

1. **Waiter Ordering & Session Management**  
   "Extend Order creation for waiter role. Add ability to open sessions, add items to existing sessions, mark as served."

2. **Waiter-Specific Endpoints**  
   "Create endpoints for table overview, quick order search, assistance requests."

**Frontend Tasks:**

1. **Waiter Dashboard**  
   "Build Vue dashboard: color-coded table grid, quick order form (search + category tabs), session management."

---

### **Milestone 7: Kitchen Interface**

**Backend Tasks:**

1. **Kitchen Order Processing**  
   "Implement item-level status updates (`OrderItemRepository`). Add SLA timestamp tracking (accepted, preparing, ready)."

2. **Kitchen Notifications**  
   "Create service to log notifications for kitchen role."

**Frontend Tasks:**

1. **Kitchen Dashboard**  
   "Build kitchen display (large screen friendly): order queue grouped by table or by item (Expeditor mode toggle), bump button, prep time highlights (yellow/red). Use script setup + TypeScript."

---

### **Milestone 8: Cashier, Billing & Payments (Cash Only)**

**Backend Tasks:**

1. **Bill Generation & Split Logic**  
   "Implement `BillRepository`, `BillService` for generating bills from served items only, split logic (by_item, equally, custom), tax/service charge calculations."

2. **Cash Payment & Receipt**  
   "Implement payment recording, change calculation, receipt generation (PDF via DomPDF or similar), reprint logic."

**Frontend Tasks:**

1. **Cashier Billing Interface**  
   "Build bill view, split bill UI (tabs for split types), cash payment form (amount received → auto change), receipt preview/print."

---

### **Milestone 9: Real-time Features & Notifications**

**Backend Tasks:**

1. **Real-time Setup**  
   "Integrate Laravel Echo + WebSockets (Pusher or Soketi) or Laravel Reverb. Create Notification service that logs to `notifications` table and broadcasts events."

2. **Event Broadcasting**  
   "Broadcast order placed, item ready, bill requested, etc."

**Frontend Tasks:**

1. **Real-time Listeners**  
   "Implement Inertia + Vue listeners for real-time updates across customer, waiter, kitchen, cashier views using Laravel Echo."

---

### **Milestone 10: Reporting, Reconciliation & Admin Polish**

**Backend Tasks:**

1. **Reports & Reconciliation**  
   "Implement sales reports, order analytics, end-of-day cash reconciliation in `CashReconciliationRepository` and dedicated controller."

2. **Audit Logging**  
   "Ensure all critical actions (void, discount >20%, refunds, force close) are logged via `AuditLog`."

**Frontend Tasks:**

1. **Admin Reports Dashboard**  
   "Build Vue pages for reports, reconciliation form, audit log viewer."

---

### **Milestone 11: Testing, Error Handling, Polish & Deployment**

**Backend Tasks:**

1. **Feature Tests & Validation**  
   "Write PHPUnit feature tests for critical flows (QR order, bill + cash payment, reconciliation, role restrictions)."

2. **Error Handling & Offline Messages**  
   "Implement graceful error pages and network degradation messages as per URS."

**Frontend Tasks:**

1. **UI/UX Polish & Accessibility**  
   "Ensure mobile responsiveness, loading states, toast notifications, dark mode (optional), accessibility improvements."

2. **Final Integration**  
   "Connect all interfaces and test full user journeys end-to-end."

---

**Next Steps Recommendation**:
Start with **Milestone 1** → **Milestone 2**. Once core auth and models are solid, proceed sequentially.

Each task prompt above is self-contained and sized for one focused AI agent session. You can copy-paste them directly. Let me know if you want me to expand any specific milestone with more granular sub-tasks or start generating code for Milestone 1.

---
title: "Restaurant Ordering System"
author: "Ogilo George"
date: today

fontsize: 11pt
geometry: margin=1in
colorlinks: true
linkcolor: blue

mainfont: "Quicksand"
sansfont: "sans-serif"
monofont: "DejaVu Sans Mono"

toc: true
toc-depth: 3

header-includes:
  - \usepackage{xcolor}
  - \definecolor{primary}{HTML}{1F4E79}
  - \usepackage{titlesec}
  - \titleformat{\section}{\Large\bfseries\color{primary}}{\thesection}{1em}{}
  - \titleformat{\subsection}{\large\bfseries\color{primary}}{\thesubsection}{1em}{}
---

# User Requirements Specification (URS)

## QR-Based Restaurant Ordering and Billing Management System

---

# Document Control

| Item           | Description                                                |
| -------------- | ---------------------------------------------------------- |
| Document Title | User Requirements Specification (URS)                      |
| System Name    | QR-Based Restaurant Ordering and Billing Management System |
| Version        | 1.1 (Cash-First Release)                                   |
| Prepared For   | Restaurant Management and Operations                       |
| Prepared By    | System Analysis Team                                       |
| Date           | May 2026                                                   |
| Status         | Approved for Development (Phase 1)                         |

---

# 1. Introduction

## 1.1 Purpose

This User Requirements Specification (URS) document defines the functional and non-functional requirements for a QR-based Restaurant Ordering and Billing Management System.

The purpose of the system is to digitize and streamline restaurant operations including customer ordering, waiter-assisted ordering, kitchen coordination, billing, cash payment processing, and receipt generation.

The system aims to improve service efficiency, reduce ordering errors, improve customer experience, and provide operational visibility for restaurant staff and management.

**Phase 1 Scope:** Cash and manual payment methods only. M-Pesa and other electronic payments are deferred to a future release.

---

## 1.2 Scope

The system shall support restaurant operations from customer seating to payment completion.

The solution shall include:

- QR code-based customer ordering
- Waiter-assisted manual ordering
- Digital menu management
- Real-time kitchen notifications
- Kitchen order processing
- Table session management
- Billing and invoicing
- Cash payment processing
- Receipt generation (print and PDF)
- Administrative management
- Reporting and analytics (cash reconciliation focus)

The system shall support usage across:

- Customers
- Waiters
- Kitchen staff
- Cashiers
- Restaurant administrators

---

## 1.3 Objectives

The primary objectives of the system are:

1. Reduce order processing time.
2. Minimize manual ordering errors.
3. Improve communication between waiters and kitchen staff.
4. Enable customers to place orders independently.
5. Automate billing workflows.
6. Improve restaurant operational efficiency.
7. Provide accurate reporting and sales tracking.
8. Support scalable restaurant operations.
9. Enable accurate cash reconciliation at end of day.

---

# 2. Stakeholders

| Stakeholder           | Role                                   | Key Success Metric                                      |
| --------------------- | -------------------------------------- | ------------------------------------------------------- |
| Restaurant Customers  | Place and monitor orders               | Order placement < 60 sec; status visibility always     |
| Waiters               | Assist customers and manage orders     | Reduce walking distance by 40% via digital pings       |
| Kitchen Staff         | Prepare and update order statuses      | Food ready within promised prep time ±2 min            |
| Cashiers              | Process cash payments and issue receipts | Bill closure < 90 sec after payment request          |
| Restaurant Managers   | Monitor operations and cash reconciliation | End-of-day variance < 0.5%                           |
| System Administrators | Manage system configuration and users  | Zero unauthorized access; audit log completeness 100%  |

---

# 3. System Overview

The Restaurant Ordering and Billing Management System shall provide an integrated digital platform for restaurant operations.

Each restaurant table shall have a unique QR code. Customers shall scan the QR code using their mobile devices to access the digital menu and place orders.

Orders placed by customers or waiters shall be transmitted to the kitchen in real time.

Kitchen staff shall process the orders and update preparation statuses.

The system shall support automated billing, cash payment processing, and receipt generation.

**Network Degradation Handling:** If network drops, the customer-facing interface shall display: *"Connection lost. Please ask your waiter for assistance."* Orders already placed persist via browser storage until connection resumes.

---

# 4. User Roles and Permissions

## 4.1 Customer

Customers shall be able to:

- Scan table QR code
- View menu items
- Browse menu categories
- Add items to cart
- Place orders
- Add special instructions to items
- View order status
- View bill summary
- Request waiter assistance

Customers shall **not** require system login credentials.

**Customers cannot:**
- See cost prices of items
- Cancel an order after kitchen accepts it
- Process payments (cashier role only)

---

## 4.2 Waiter

Waiters shall be able to:

- Log into the system
- View restaurant tables
- Open and manage table sessions
- Place manual orders on behalf of customers
- Add additional orders to existing sessions
- Update order notes
- View order statuses
- Request bill generation
- Mark orders as served
- Receive notifications when orders are ready

**Waiters cannot:**
- Process payments
- Edit menu pricing
- Access sales reports

---

## 4.3 Kitchen Staff

Kitchen staff shall be able to:

- Log into the kitchen dashboard
- Receive real-time order notifications
- View pending orders
- View item preparation instructions
- Update item preparation status
- Mark items as ready
- Track preparation time

**Kitchen staff cannot:**
- Edit menu pricing
- Access customer personal information
- Cancel customer orders without manager override

---

## 4.4 Cashier

Cashiers shall be able to:

- View active bills
- Generate invoices
- Process cash payments
- Split bills
- Print receipts
- Close table sessions
- Perform end-of-day cash reconciliation

**Cashiers cannot:**
- Modify menu items
- Change order details after billing starts
- Void items without manager approval

---

## 4.5 Administrator

Administrators shall be able to:

- Manage users and roles
- Manage menu categories
- Manage menu items
- Manage restaurant tables
- Configure system settings (tax rates, service charge)
- View reports and analytics
- Monitor active restaurant operations
- Force-close stuck sessions (with reason logging)
- Perform manager overrides for refunds/voids

---

# 5. Functional Requirements

## 5.1 QR Code and Table Management

### FR-001: QR Code Generation
The system shall generate a unique QR code for each restaurant table.

### FR-001a: QR Code Data Structure
Each QR code shall encode: `{restaurant_id}|{table_id}|{branch_id}|{api_base_url}` to support multi-branch from day 1.

### FR-002: Table Identification
The system shall identify the table associated with a scanned QR code.

### FR-003: Table Session Management
The system shall create and manage table sessions for active customers.

### FR-004: Table Status Tracking
The system shall support the following table statuses:

- Available
- Occupied
- Ordering
- Preparing
- Served
- Billing
- Paid
- Cleaning
- Reserved

### FR-004a: Table Status Transitions
The system shall enforce valid status transitions:

| From          | To            | Allowed Action                     |
| ------------- | ------------- | ---------------------------------- |
| Available     | Occupied      | Customer scans OR waiter opens     |
| Occupied      | Ordering      | First item added to cart           |
| Ordering      | Preparing     | Order confirmed by customer/waiter |
| Preparing     | Served        | At least one item marked "Ready"   |
| Served        | Billing       | Waiter or customer requests bill   |
| Billing       | Paid          | Payment confirmed                  |
| Paid          | Cleaning      | Cashier closes session             |
| Cleaning      | Available     | Admin or manager resets            |

---

## 5.2 Menu Management

### FR-005: Menu Categories
The system shall allow administrators to create and manage menu categories.

Examples include:
- Breakfast
- Main Meals
- Beverages
- Desserts

### FR-006: Menu Items
The system shall allow administrators to:
- Add menu items
- Edit menu items
- Set pricing
- Upload item images
- Define item availability
- Add preparation times

### FR-006a: Menu Item Data Definition
Each menu item shall have:

| Field                | Type        | Required | Example                     |
| -------------------- | ----------- | -------- | --------------------------- |
| Item ID              | UUID        | Yes      | `menu_123`                  |
| Name                 | String(80)  | Yes      | "Grilled Chicken"           |
| Category             | Enum        | Yes      | "Main Meals"                |
| Description          | String(200) | No       | "Served with rice & salad"  |
| Base Price           | Decimal     | Yes      | 12.99                       |
| Image URL            | String      | No       | `/images/grilled_chicken.png` |
| Prep Time (min)      | Integer     | Yes      | 15                          |
| Available Today      | Boolean     | Yes      | true                        |
| Modifier Groups      | JSON        | No       | `{"spice":["mild","hot"]}`  |

### FR-007: Menu Availability
The system shall allow unavailable menu items to be hidden or marked unavailable.

---

## 5.3 Customer Ordering

### FR-008: Digital Menu Access
Customers shall access the menu by scanning the table QR code.

### FR-009: Cart Management
Customers shall be able to:
- Add items to cart
- Remove items from cart
- Update quantities

### FR-010: Special Instructions
Customers shall be able to provide special preparation instructions.

Examples:
- No onions
- Extra spicy
- Well cooked

### FR-010a: Special Instructions Validation
Special instructions shall be limited to **120 characters** and automatically scanned for profanity.

### FR-011: Order Placement
Customers shall be able to submit orders electronically.

### FR-012: Multiple Orders Per Session
The system shall allow multiple orders within the same table session.

### FR-013: Order Tracking
Customers shall be able to view order statuses.

Statuses include:
- Pending
- Accepted
- Preparing
- Ready
- Served
- Cancelled

### FR-013a: Order Cancellation Rules
- Customer can cancel **only before** kitchen accepts order.
- After acceptance, only waiter/manager can cancel (with reason: "ingredient issue", "customer request", "system error").

---

## 5.4 Waiter Ordering

### FR-014: Manual Order Entry
Waiters shall be able to place manual orders for customers.

### FR-015: Existing Session Support
Waiters shall be able to add orders to an active customer session.

### FR-016: Quick Order Interface
The waiter interface shall support fast order entry using:
- Item search
- Category tabs
- Frequently ordered items
- Quantity controls

### FR-017: Order Source Identification
The system shall identify whether an order was placed by:
- Customer QR
- Waiter
- Cashier

---

## 5.5 Kitchen Management

### FR-018: Real-Time Kitchen Notifications
The kitchen shall receive new order notifications in real time.

### FR-019: Kitchen Dashboard
Kitchen staff shall view orders in a kitchen display dashboard.

### FR-020: Item-Level Tracking
Kitchen staff shall update preparation status for individual order items.

### FR-021: Preparation Workflow
The system shall support the following kitchen workflow statuses:
- Pending
- Accepted
- Preparing
- Ready
- Served
- Cancelled

### FR-022: Kitchen Preparation Time Monitoring
The system shall record order preparation timestamps.

### FR-022a: Preparation Time SLAs
The system shall track:
- **Time to Accept** (target < 10 sec)
- **Time to Start** (target < 2 min)
- **Time to Ready** (target $\leq$ menu item's prep time + 20% buffer)

If SLA breached, kitchen dashboard highlights order in **yellow** (warning) then **red** (escalated).

---

## 5.6 Billing and Payments (Phase 1: Cash Only)

### FR-023: Bill Generation
The system shall generate bills based on **served order items only**.

### FR-024: Bill Summary
Bills shall include:
- Ordered items (with timestamps when served)
- Quantities
- Unit prices
- Subtotal
- Taxes (e.g., VAT 16%)
- Service charge (optional, configurable by admin)
- Discounts (with reason if >20%)
- Grand total
- Payment method field (set at time of payment)

### FR-024a: Tax Calculation
The system shall support multiple tax rates:
- VAT (configurable percentage)
- Service charge (configurable percentage)
- Separate handling of "inclusive" vs "exclusive" tax per item.

### FR-025: Split Billing
The system shall support split billing:

| Split Type      | Description                                                                 |
|----------------|-----------------------------------------------------------------------------|
| By Item         | Each person pays for specific items they ordered                            |
| Equally         | Total ÷ number of payers (cashier inputs number of people)                  |
| Custom Amount   | Manual entry per payer (e.g., "Customer A pays 1500, Customer B pays 800") |

After split, the system generates **separate sub-bills** but tracks them under the same table session until fully paid.

### FR-026: Supported Payment Methods (Phase 1)
The system shall support:

| Payment Method | Handling                                                                 |
|----------------|--------------------------------------------------------------------------|
| Cash           | Cashier receives cash, enters amount received, calculates change due     |
| Manual/Other   | Free text field for "Voucher", "House Account", "Complimentary" (logged) |

**No electronic payment integration in Phase 1.**

### FR-027: Cash Payment Workflow

When a customer pays by cash:

1. Cashier selects **"Cash"** on bill screen.
2. System displays **Total Due**.
3. Cashier enters **Amount Received**.
4. System calculates **Change Due**.
5. Cashier confirms payment.
6. System marks bill as **"Paid - Cash"**.
7. Receipt printed with note: *"Paid in cash. Thank you."*

### FR-028: Payment Confirmation
The system shall require **cashier confirmation** before bill closure.  
Once confirmed, the bill cannot be modified without manager override (logged).

### FR-029: Receipt Generation
After payment confirmation, the system shall generate a receipt containing:
- Restaurant name, address, phone
- Table number and session ID
- Date and time of payment
- Itemized list with prices
- Subtotal, tax, service charge, total
- Amount received, change due (for cash)
- Payment method (Cash / Manual)
- Cashier name or ID
- Receipt number (sequential, unique per day)

### FR-030: Receipt Printing
The system shall support:
- Direct printing to a **thermal receipt printer** (USB or network)
- PDF generation (for email or WhatsApp sharing)
- Reprint function (last 3 receipts only, logged)

---

## 5.7 Notifications and Real-Time Communication

### FR-031: Real-Time Updates
The system shall provide real-time updates between:
- Customer interface
- Waiter dashboard
- Kitchen dashboard
- Cashier dashboard

### FR-032: Kitchen Alerts
The system shall notify kitchen staff when new orders are placed.

### FR-033: Waiter Alerts
The system shall notify waiters when:
- Orders are ready
- Bills are requested

### FR-034: Customer Notifications
Customers shall receive order progress updates.

### FR-034a: Notification Channels
- Customer: browser notification + on-screen badge
- Waiter: mobile app push OR browser audio alert
- Kitchen: large-screen visual + distinct chime

### FR-034b: Quiet Mode
Kitchen staff can enable "quiet mode" – visual only, no sound, to reduce noise fatigue.

---

## 5.8 Reporting and Analytics

### FR-035: Sales Reports
The system shall generate sales reports (daily, weekly, monthly).

### FR-036: Order Reports
The system shall generate reports on:
- Order volumes
- Most ordered items
- Peak service times

### FR-037: Payment Reports
The system shall provide payment reconciliation reports including:
- Total cash received per cashier
- Total cash received per day
- Variance reports

### FR-038: Staff Activity Reports
The system shall provide waiter and cashier activity reports.

### FR-039: End-of-Day Cash Reconciliation Report (New)
The system shall generate a report comparing:
- Total cash received per system
- Physical cash counted by manager
- Variance amount and percentage
- Variance must be < 0.5% or flagged for review

---

## 5.9 Error and Exception Handling

| Error Scenario                          | System Behavior                                                                 |
| --------------------------------------- | ------------------------------------------------------------------------------- |
| Cashier enters amount less than total   | Show error: "Amount received is less than total. Please request full payment."  |
| Cashier closes bill without printing    | Auto-save receipt as PDF; allow reprint within 10 minutes                       |
| System crashes during cash payment      | Upon restart, show: "Pending cash payments. Please verify manually."            |
| Split bill partially paid then leaves   | Mark remaining balance as "Outstanding - Follow Up"; manager must clear         |
| QR code scan leads to invalid table     | Show error: "Table not found. Please notify staff." Log event.                  |
| Customer adds item that just went unavailable | Immediately remove from cart, notify: "Item no longer available."          |
| Kitchen rejects order (no ingredients)  | Send reason to waiter dashboard; waiter contacts customer; order voided.        |

---

# 6. Non-Functional Requirements

## 6.1 Performance Requirements

| NFR ID  | Requirement                          | Metric                                           |
| ------- | ------------------------------------ | ------------------------------------------------ |
| NFR-001 | Real-time kitchen delivery           | 95th percentile < 3 sec                          |
| NFR-002 | Concurrent users                     | 200 customers + 30 staff simultaneously          |
| NFR-003 | QR scan to menu load                 | < 2 sec on 4G                                    |
| NFR-004 | Bill generation after "Request Bill" | < 1 sec                                          |
| NFR-005 | Database query (reports)             | < 5 sec for 30-day range                         |

## 6.2 Availability and Reliability

### NFR-006: System Availability
The system shall be available during restaurant operational hours (minimum 99.5% uptime).

### NFR-007: Data Persistence
The system shall store and preserve transaction records for 7 years (tax compliance).

### NFR-008: Fault Tolerance
The system shall recover gracefully from failures without data corruption.

## 6.3 Security Requirements

### NFR-009: Cash Handling Security
- Every cash transaction logged with **cashier ID**, timestamp, and amount.
- The system shall support **cash drawer opening** only after payment confirmation.
- End-of-day cash reconciliation report must compare system total vs physical count.

### NFR-009a: No Payment Credentials Stored
Since Phase 1 has no M-Pesa or cards, the system stores **no payment credentials** beyond transaction amount and method.

### NFR-010: Authentication
Staff users shall authenticate with username + strong password before accessing the system.

### NFR-011: Authorization
The system shall enforce role-based access control.

### NFR-012: Session Security
The system shall prevent unauthorized access to table sessions. Table session tokens expire after 4 hours of inactivity.

### NFR-013: Data Retention
Order and payment records retained for 7 years. Customer browsing data (abandoned carts) deleted after 30 days.

## 6.4 Usability Requirements

### NFR-014: Mobile Compatibility
The customer interface shall support all modern mobile browsers (Chrome, Safari, Firefox).

### NFR-015: Ease of Use
The system interfaces shall be intuitive and require minimal training.

### NFR-016: Accessibility
The system shall support readable fonts (minimum 16px for customer interface) and sufficient color contrast.

## 6.5 Scalability Requirements

### NFR-017: Branch Scalability
The system shall support future multi-branch expansion.

### NFR-018: Modular Architecture
The system shall support modular feature expansion (including future M-Pesa integration).

---

# 7. Business Rules

### BR-001
Each table shall have one active session at a time.

### BR-002
Orders shall only be added to active table sessions.

### BR-003
Bills shall only include served items.

### BR-004
A table session shall only be closed after payment confirmation.

### BR-005
Menu items marked unavailable shall not be orderable.

### BR-006
All payment transactions shall be recorded.

### BR-007
If a table has an unpaid bill from a previous session, a new session cannot be started until cashier closes or transfers it.

### BR-008
Discounts above 20% require manager approval (logged with reason).

### BR-009
A waiter can only close their own table session unless manager override.

### BR-010
Refund after bill closure requires admin password and prints a credit note.

### BR-011 (New)
Cash payments cannot be reversed or refunded through the system alone. Any cash refund requires:
- Manager override
- Printed credit note
- Physical cash returned to customer
- Log entry: "Refunded [amount] in cash for [reason]"

### BR-012 (New)
A table session closed with "Cash - Paid" cannot be reopened to add new items. If a customer returns to add an order after paying → new session required.

---

# 8. System Interfaces

## 8.1 Customer Interface

The customer interface shall provide:
- Digital menu with categories
- Cart management (floating button)
- Order tracking with estimated ready time
- Bill viewing
- Waiter assistance request button

**Must work without login.**

## 8.2 Waiter Interface

The waiter interface shall provide:
- Table overview (color-coded by status)
- Manual order entry (search, categories, quantity)
- Session management
- Order monitoring
- Real-time notifications when orders are ready

## 8.3 Kitchen Interface

The kitchen interface shall provide:
- Order queue
- Preparation tracking
- Real-time notifications
- Two modes:
  - **Normal mode:** grouped by table
  - **Expeditor mode:** grouped by item type for batch cooking
- Manual "bump" button to mark item ready

## 8.4 Cashier Interface

The cashier interface shall provide:
- Billing tools
- Split bill functionality
- Cash payment processing (amount received, change due)
- Receipt printing
- Table session closure

## 8.5 Administrator Interface

The administrator interface shall provide:
- User management
- Menu management (bulk import/export CSV)
- Table management
- System configuration (tax rates, service charge)
- Reporting dashboards with:
  - Orders per hour
  - Average prep time per item
  - Table turnover rate
- User action audit log

---

# 9. Future Enhancements

The following features are **not in Phase 1** and will be considered for future releases:

| Priority | Enhancement             | Description                                                                 |
|----------|-------------------------|-----------------------------------------------------------------------------|
| **High** | **M-Pesa Integration**  | STK Push, timeout handling, callback confirmation, automatic bill closure  |
| High     | Inventory integration   | Auto-hide menu item when stock < 1                                          |
| Medium   | Table reservations      | Allow customers to book tables in advance                                   |
| Medium   | Customer loyalty        | Points per order; free item after N visits                                  |
| Medium   | Offline mode            | Local cache & sync when network returns                                     |
| Low      | Card payments           | Via mobile POS or third-party gateway (e.g., Pesapal)                       |
| Low      | AI menu recommendation  | "You might also like..." based on past orders per table                     |
| Low      | Multi-branch operations | Centralized management across multiple restaurant locations                 |
| Low      | Kitchen display analytics| Performance metrics for kitchen staff                                      |

### M-Pesa Specific Requirements (Reference for Future Phase)

When M-Pesa is prioritized, the following requirements shall apply:

- Initiate STK Push to customer phone number.
- Handle timeout (retry once after 60 sec).
- On success: auto-close bill.
- On failure: mark bill as "Payment Pending" and notify cashier.
- No reversal of successful M-Pesa transactions within system (use M-Pesa API for reversals).

---

# 10. Assumptions and Constraints

## Assumptions

- Customers possess mobile devices capable of scanning QR codes with internet access.
- The restaurant has reliable internet connectivity (minimum 4G or broadband).
- Kitchen staff have access to display devices (tablet, TV, or monitor).
- Each restaurant table has a printed QR code that is physically tamper-resistant.
- Staff have basic digital literacy.

## Constraints

- System performance depends on network stability.
- Cash handling accuracy depends on staff training.
- Hardware availability (printers, displays) may vary by restaurant.
- M-Pesa integration depends on future budget and third-party provider.

## Risk

If the network is down for > 5 minutes, the system shall display a banner: *"Connection degraded. Use offline mode or manual ordering."*

---

# 11. Acceptance Criteria

The system shall be considered acceptable when:

| ID   | Criterion                                                                 | Test Method                                      |
|------|---------------------------------------------------------------------------|--------------------------------------------------|
| AC-1 | Customer scans QR → menu loads < 2 sec on 4G                              | Real device test (5 repetitions)                 |
| AC-2 | Customer places order → kitchen sees it < 3 sec                           | Timestamp logging                                |
| AC-3 | Waiter adds item to occupied table → item appears in kitchen queue        | Integration test                                 |
| AC-4 | Split bill equally among 4 people → each sees correct share               | Manual test case                                 |
| AC-5 | Cash payment: Cashier enters amount received > total → change shown correctly | Manual test (200 paid for 150 total → change 50) |
| AC-6 | Cashier underpayment: Cashier enters amount < total → error message & no closure | Negative test                                    |
| AC-7 | Receipt prints with all required fields (restaurant name, table, items, change) | Visual inspection                                |
| AC-8 | End-of-day cash reconciliation matches physical count within 0.5%         | Run full day test data                           |
| AC-9 | Admin disables menu item → customer cannot order it within 10 sec         | Browser refresh test                             |
| AC-10 | Audit log shows who voided an item and why (manager override)             | Negative test (tampering attempt)                |
| AC-11 | No M-Pesa option visible anywhere in Phase 1 UI                          | UI audit                                         |
| AC-12 | Role-based permissions function correctly (waiter cannot process payment) | Access control test                              |
| AC-13 | Bill generation is accurate with taxes and service charge applied         | Calculate vs expected                            |

---

# 12. Glossary

| Term                 | Definition                                                               |
| -------------------- | ------------------------------------------------------------------------ |
| Bump                 | Action by kitchen staff to mark an item or order as ready                |
| Cash Reconciliation  | Process of matching system-recorded cash total with physical cash in drawer |
| Credit Note          | Printed document for refund or void, requiring manager signature         |
| Expeditor mode       | Kitchen view grouping same menu items across different tables            |
| Split Bill           | Dividing a table's total bill among multiple customers                   |
| STK Push             | M-Pesa API call that prompts customer on their phone to enter PIN (future) |
| Table session        | Logical unit from first order to payment closure                         |
| Void                 | Cancelling an already ordered item before it is served (requires reason) |

---

# 13. Conclusion

This User Requirements Specification defines the operational, functional, and technical expectations for the **QR-Based Restaurant Ordering and Billing Management System (Phase 1: Cash-First Release)** .

The system is intended to modernize restaurant workflows, improve operational efficiency, reduce manual errors, and enhance customer service delivery.

**Phase 1 delivers:**
- QR code ordering (customer and waiter)
- Kitchen display system
- Cash payment processing
- Receipt printing (thermal and PDF)
- Split billing
- Basic reporting and cash reconciliation

**Deferred to Phase 2:**
- M-Pesa integration
- Card payments
- Inventory management
- Customer loyalty programs

The requirements documented herein shall guide the design, development, implementation, testing, and deployment phases of the system lifecycle.

---

**Sign-off required by:**

| Role               | Name                          | Signature | Date       |
| ------------------ | ----------------------------- | --------- | ---------- |
| Restaurant Owner   | _____________________________ | ________  | __________ |
| Head Chef          | _____________________________ | ________  | __________ |
| IT Manager         | _____________________________ | ________  | __________ |
| Head Cashier       | _____________________________ | ________  | __________ |

---

*End of Document*
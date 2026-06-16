-- ============================================================
-- QR-Based Restaurant Ordering and Billing Management System
-- Database Schema — Phase 1 (Cash-First Release)
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- 1. SYSTEM CONFIGURATION
-- ============================================================

CREATE TABLE system_settings (
    id              CHAR(36)       NOT NULL PRIMARY KEY,
    `key`           VARCHAR(80)    NOT NULL,
    value           VARCHAR(255)   NOT NULL,
    description     VARCHAR(200),
    updated_by      CHAR(36),
    updated_at      TIMESTAMP      NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- 2. USERS & ROLES
-- ============================================================

CREATE TABLE roles (
    id          TINYINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        ENUM('customer','waiter','kitchen','cashier','manager','admin') NOT NULL UNIQUE
) ENGINE=InnoDB;

INSERT INTO roles (name) VALUES
  ('customer'), ('waiter'), ('kitchen'), ('cashier'), ('manager'), ('admin');

CREATE TABLE users (
    id              CHAR(36)     NOT NULL PRIMARY KEY,

    role_id         TINYINT UNSIGNED NOT NULL,
    name            VARCHAR(100) NOT NULL,
    username        VARCHAR(60)  NOT NULL,
    email           VARCHAR(100),
    password_hash   VARCHAR(255) NOT NULL,
    is_active       TINYINT(1)   NOT NULL DEFAULT 1,
    last_login_at   TIMESTAMP    NULL,
    created_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_username (username),
    CONSTRAINT fk_users_role   FOREIGN KEY (role_id)   REFERENCES roles(id)
) ENGINE=InnoDB;

-- ============================================================
-- 3. TABLES & QR CODES
-- ============================================================

CREATE TABLE restaurant_tables (
    id          CHAR(36)     NOT NULL PRIMARY KEY,

    table_number VARCHAR(20) NOT NULL,
    capacity    TINYINT UNSIGNED NOT NULL DEFAULT 4,
    location    VARCHAR(80),              -- e.g. "Patio", "Ground Floor"
    status      ENUM(
                    'available','occupied','ordering',
                    'preparing','served','billing','paid','cleaning','reserved'
                ) NOT NULL DEFAULT 'available',
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_table_number (table_number),
) ENGINE=InnoDB;

CREATE TABLE qr_codes (
    id              CHAR(36)     NOT NULL PRIMARY KEY,
    table_id        CHAR(36)     NOT NULL UNIQUE,
    payload         TEXT         NOT NULL,
    image_path      VARCHAR(255),
    generated_at    TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    regenerated_at  TIMESTAMP    NULL,
    CONSTRAINT fk_qr_table FOREIGN KEY (table_id) REFERENCES restaurant_tables(id)
) ENGINE=InnoDB;

-- ============================================================
-- 4. MENU
-- ============================================================

CREATE TABLE menu_categories (
    id          CHAR(36)     NOT NULL PRIMARY KEY,
    name        VARCHAR(80)  NOT NULL,
    description VARCHAR(200),
    sort_order  SMALLINT     NOT NULL DEFAULT 0,
    is_active   TINYINT(1)   NOT NULL DEFAULT 1,
    created_at  TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE menu_items (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    category_id     CHAR(36)        NOT NULL,
    name            VARCHAR(80)     NOT NULL,
    description     VARCHAR(200),
    base_price      DECIMAL(10,2)   NOT NULL,
    tax_inclusive   TINYINT(1)      NOT NULL DEFAULT 1, -- price includes VAT or not
    prep_time_min   TINYINT UNSIGNED NOT NULL DEFAULT 10,
    image_url       VARCHAR(255),
    modifier_groups JSON,           -- e.g. {"spice":["mild","hot"],"size":["regular","large"]}
    is_available    TINYINT(1)      NOT NULL DEFAULT 1,
    sort_order      SMALLINT        NOT NULL DEFAULT 0,
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_item_category  FOREIGN KEY (category_id) REFERENCES menu_categories(id)
) ENGINE=InnoDB;

-- ============================================================
-- 5. TABLE SESSIONS
-- ============================================================

CREATE TABLE table_sessions (
    id              CHAR(36)     NOT NULL PRIMARY KEY,
    table_id        CHAR(36)     NOT NULL,
    session_token   CHAR(64)     NOT NULL UNIQUE, -- for unauthenticated customers
    opened_by       CHAR(36),    -- waiter user_id (NULL if customer self-opened via QR)
    open_source     ENUM('customer_qr','waiter','cashier') NOT NULL DEFAULT 'customer_qr',
    status          ENUM('open','billing','closed','force_closed') NOT NULL DEFAULT 'open',
    customer_count  TINYINT UNSIGNED,
    notes           VARCHAR(255),
    token_expires_at TIMESTAMP   NOT NULL,         -- NFR-012: 4h inactivity expiry
    opened_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at       TIMESTAMP    NULL,
    closed_by       CHAR(36),    -- cashier/manager user_id
    close_reason    VARCHAR(200),                  -- for force-closed sessions
    CONSTRAINT fk_session_table  FOREIGN KEY (table_id)  REFERENCES restaurant_tables(id),
    CONSTRAINT fk_session_opener FOREIGN KEY (opened_by) REFERENCES users(id),
    CONSTRAINT fk_session_closer FOREIGN KEY (closed_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 6. ORDERS & ORDER ITEMS
-- ============================================================

CREATE TABLE orders (
    id              CHAR(36)     NOT NULL PRIMARY KEY,
    session_id      CHAR(36)     NOT NULL,
    placed_by_role  ENUM('customer','waiter','cashier') NOT NULL,
    placed_by_user  CHAR(36)     NULL,      -- NULL if placed by unauthenticated customer
    status          ENUM('pending','accepted','preparing','ready','served','cancelled') NOT NULL DEFAULT 'pending',
    cancel_reason   VARCHAR(200) NULL,
    cancelled_by    CHAR(36)     NULL,      -- user_id of canceller
    placed_at       TIMESTAMP    NOT NULL DEFAULT CURRENT_TIMESTAMP,
    accepted_at     TIMESTAMP    NULL,
    first_ready_at  TIMESTAMP    NULL,
    fully_served_at TIMESTAMP    NULL,
    notes           VARCHAR(255),           -- order-level notes
    CONSTRAINT fk_order_session      FOREIGN KEY (session_id)      REFERENCES table_sessions(id),
    CONSTRAINT fk_order_placed_user  FOREIGN KEY (placed_by_user)  REFERENCES users(id),
    CONSTRAINT fk_order_cancelled_by FOREIGN KEY (cancelled_by)    REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id                  CHAR(36)        NOT NULL PRIMARY KEY,
    order_id            CHAR(36)        NOT NULL,
    menu_item_id        CHAR(36)        NOT NULL,
    quantity            TINYINT UNSIGNED NOT NULL DEFAULT 1,
    unit_price          DECIMAL(10,2)   NOT NULL,  -- price at time of order (snapshot)
    special_instructions VARCHAR(120),             -- FR-010a: 120 char limit
    modifiers           JSON,                      -- selected modifier values
    status              ENUM('pending','accepted','preparing','ready','served','cancelled','voided') NOT NULL DEFAULT 'pending',
    -- Kitchen SLA tracking (FR-022a)
    accepted_at         TIMESTAMP       NULL,
    preparing_at        TIMESTAMP       NULL,
    ready_at            TIMESTAMP       NULL,
    served_at           TIMESTAMP       NULL,
    cancelled_at        TIMESTAMP       NULL,
    void_reason         VARCHAR(200)    NULL,
    voided_by           CHAR(36)        NULL,      -- manager user_id
    CONSTRAINT fk_oi_order     FOREIGN KEY (order_id)     REFERENCES orders(id),
    CONSTRAINT fk_oi_menu_item FOREIGN KEY (menu_item_id) REFERENCES menu_items(id),
    CONSTRAINT fk_oi_voided_by FOREIGN KEY (voided_by)    REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 7. BILLS & PAYMENTS
-- ============================================================

CREATE TABLE bills (
    id                  CHAR(36)        NOT NULL PRIMARY KEY,
    session_id          CHAR(36)        NOT NULL,
    generated_by        CHAR(36)        NOT NULL,   -- cashier user_id
    status              ENUM('draft','open','partially_paid','paid','voided') NOT NULL DEFAULT 'draft',
    subtotal            DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    vat_rate            DECIMAL(5,2)    NOT NULL DEFAULT 0.00,   -- snapshot at generation
    vat_amount          DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    service_charge_rate DECIMAL(5,2)    NOT NULL DEFAULT 0.00,
    service_charge_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    discount_reason     VARCHAR(200)    NULL,
    discount_approved_by CHAR(36)       NULL,        -- manager, required if > 20%
    grand_total         DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    generated_at        TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at             TIMESTAMP       NULL,
    voided_at           TIMESTAMP       NULL,
    voided_by           CHAR(36)        NULL,
    void_reason         VARCHAR(200)    NULL,
    CONSTRAINT fk_bill_session    FOREIGN KEY (session_id)           REFERENCES table_sessions(id),
    CONSTRAINT fk_bill_generated  FOREIGN KEY (generated_by)         REFERENCES users(id),
    CONSTRAINT fk_bill_discount   FOREIGN KEY (discount_approved_by) REFERENCES users(id),
    CONSTRAINT fk_bill_voided_by  FOREIGN KEY (voided_by)            REFERENCES users(id)
) ENGINE=InnoDB;

-- Itemised line items on the bill (only served items — FR-023)
CREATE TABLE bill_items (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    bill_id         CHAR(36)        NOT NULL,
    order_item_id   CHAR(36)        NOT NULL,
    quantity        TINYINT UNSIGNED NOT NULL,
    unit_price      DECIMAL(10,2)   NOT NULL,
    line_total      DECIMAL(10,2)   NOT NULL,
    served_at       TIMESTAMP       NULL,
    CONSTRAINT fk_bi_bill       FOREIGN KEY (bill_id)       REFERENCES bills(id),
    CONSTRAINT fk_bi_order_item FOREIGN KEY (order_item_id) REFERENCES order_items(id)
) ENGINE=InnoDB;

-- Split bill sub-bills (FR-025)
CREATE TABLE bill_splits (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    bill_id         CHAR(36)        NOT NULL,
    split_type      ENUM('by_item','equally','custom') NOT NULL,
    split_label     VARCHAR(60),                -- e.g. "Customer A", "Person 2"
    amount_due      DECIMAL(10,2)   NOT NULL,
    amount_paid     DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    status          ENUM('unpaid','paid','outstanding') NOT NULL DEFAULT 'unpaid',
    CONSTRAINT fk_split_bill FOREIGN KEY (bill_id) REFERENCES bills(id)
) ENGINE=InnoDB;

-- Items assigned to a specific split (for by_item splits)
CREATE TABLE bill_split_items (
    id              CHAR(36)  NOT NULL PRIMARY KEY,
    split_id        CHAR(36)  NOT NULL,
    bill_item_id    CHAR(36)  NOT NULL,
    CONSTRAINT fk_spi_split     FOREIGN KEY (split_id)     REFERENCES bill_splits(id),
    CONSTRAINT fk_spi_bill_item FOREIGN KEY (bill_item_id) REFERENCES bill_items(id)
) ENGINE=InnoDB;

-- ============================================================
-- 8. PAYMENTS (Phase 1: Cash & Manual only)
-- ============================================================

CREATE TABLE payments (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    bill_id         CHAR(36)        NOT NULL,
    split_id        CHAR(36)        NULL,           -- populated for split payments
    cashier_id      CHAR(36)        NOT NULL,
    payment_method  ENUM('cash','manual') NOT NULL DEFAULT 'cash',
    manual_note     VARCHAR(100)    NULL,            -- for voucher / house account / complimentary
    amount_due      DECIMAL(10,2)   NOT NULL,
    amount_received DECIMAL(10,2)   NOT NULL,
    change_due      DECIMAL(10,2)   NOT NULL DEFAULT 0.00,
    status          ENUM('confirmed','refunded') NOT NULL DEFAULT 'confirmed',
    confirmed_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_pay_bill     FOREIGN KEY (bill_id)   REFERENCES bills(id),
    CONSTRAINT fk_pay_split    FOREIGN KEY (split_id)  REFERENCES bill_splits(id),
    CONSTRAINT fk_pay_cashier  FOREIGN KEY (cashier_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- Cash refunds / credit notes (BR-011)
CREATE TABLE refunds (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    payment_id      CHAR(36)        NOT NULL,
    amount          DECIMAL(10,2)   NOT NULL,
    reason          VARCHAR(255)    NOT NULL,
    approved_by     CHAR(36)        NOT NULL,   -- manager/admin user_id
    credit_note_ref VARCHAR(50)     NOT NULL,   -- printed credit note number
    refunded_at     TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_refund_payment  FOREIGN KEY (payment_id)  REFERENCES payments(id),
    CONSTRAINT fk_refund_approver FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 9. RECEIPTS
-- ============================================================

CREATE TABLE receipts (
    id              CHAR(36)        NOT NULL PRIMARY KEY,
    bill_id         CHAR(36)        NOT NULL,
    cashier_id      CHAR(36)        NOT NULL,
    receipt_number  VARCHAR(30)     NOT NULL,    -- sequential per day, unique
    pdf_path        VARCHAR(255),                -- stored PDF path
    print_count     TINYINT UNSIGNED NOT NULL DEFAULT 0,  -- FR-030: reprint limit
    generated_at    TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_receipt_number (receipt_number),
    CONSTRAINT fk_receipt_bill    FOREIGN KEY (bill_id)    REFERENCES bills(id),
    CONSTRAINT fk_receipt_cashier FOREIGN KEY (cashier_id) REFERENCES users(id)
) ENGINE=InnoDB;

CREATE TABLE receipt_reprints (
    id          CHAR(36)    NOT NULL PRIMARY KEY,
    receipt_id  CHAR(36)    NOT NULL,
    reprinted_by CHAR(36)   NOT NULL,
    reprinted_at TIMESTAMP  NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_reprint_receipt FOREIGN KEY (receipt_id)   REFERENCES receipts(id),
    CONSTRAINT fk_reprint_user   FOREIGN KEY (reprinted_by)  REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 10. END-OF-DAY CASH RECONCILIATION (FR-039)
-- ============================================================

CREATE TABLE cash_reconciliations (
    id                  CHAR(36)        NOT NULL PRIMARY KEY,
    reconciliation_date DATE            NOT NULL,
    prepared_by         CHAR(36)        NOT NULL,   -- cashier
    approved_by         CHAR(36)        NULL,        -- manager
    system_total        DECIMAL(10,2)   NOT NULL,   -- sum of confirmed cash payments
    physical_count      DECIMAL(10,2)   NOT NULL,   -- manually counted by manager
    variance_amount     DECIMAL(10,2)   GENERATED ALWAYS AS (physical_count - system_total) STORED,
    variance_pct        DECIMAL(6,3)    GENERATED ALWAYS AS (
                            CASE WHEN system_total = 0 THEN 0
                                 ELSE ((physical_count - system_total) / system_total) * 100
                            END
                        ) STORED,
    flagged             TINYINT(1)      GENERATED ALWAYS AS (ABS((physical_count - system_total) / system_total) > 0.005) STORED,
    notes               VARCHAR(500),
    created_at          TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_reconciliation_date (reconciliation_date),
    CONSTRAINT fk_recon_preparer  FOREIGN KEY (prepared_by) REFERENCES users(id),
    CONSTRAINT fk_recon_approver  FOREIGN KEY (approved_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 11. WAITER ASSISTANCE REQUESTS
-- ============================================================

CREATE TABLE assistance_requests (
    id              CHAR(36)    NOT NULL PRIMARY KEY,
    session_id      CHAR(36)    NOT NULL,
    request_type    ENUM('assistance','bill_request') NOT NULL DEFAULT 'assistance',
    handled_by      CHAR(36)    NULL,
    status          ENUM('pending','handled') NOT NULL DEFAULT 'pending',
    requested_at    TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    handled_at      TIMESTAMP   NULL,
    CONSTRAINT fk_ar_session FOREIGN KEY (session_id) REFERENCES table_sessions(id),
    CONSTRAINT fk_ar_handler FOREIGN KEY (handled_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 12. NOTIFICATIONS (real-time event log for FR-031–034)
-- ============================================================

CREATE TABLE notifications (
    id              CHAR(36)    NOT NULL PRIMARY KEY,
    target_role     ENUM('customer','waiter','kitchen','cashier','admin') NOT NULL,
    target_user_id  CHAR(36)    NULL,           -- NULL means broadcast to role
    session_id      CHAR(36)    NULL,
    event_type      VARCHAR(60) NOT NULL,       -- e.g. 'order.placed', 'item.ready', 'bill.requested'
    payload         JSON,
    is_read         TINYINT(1)  NOT NULL DEFAULT 0,
    created_at      TIMESTAMP   NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notif_role_read (target_role, is_read),
    CONSTRAINT fk_notif_session FOREIGN KEY (session_id)     REFERENCES table_sessions(id),
    CONSTRAINT fk_notif_user    FOREIGN KEY (target_user_id) REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- 13. AUDIT LOG (NFR-009, BR-008–011)
-- ============================================================

CREATE TABLE audit_logs (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id         CHAR(36)        NULL,        -- NULL for system-generated events
    action          VARCHAR(100)    NOT NULL,    -- e.g. 'discount.approved', 'void.item', 'session.force_closed'
    entity_type     VARCHAR(60)     NOT NULL,    -- 'order_item', 'bill', 'table_session', etc.
    entity_id       CHAR(36)        NOT NULL,
    old_value       JSON,
    new_value       JSON,
    reason          VARCHAR(255),
    ip_address      VARCHAR(45),
    created_at      TIMESTAMP       NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_entity  (entity_type, entity_id),
    INDEX idx_audit_user    (user_id),
    INDEX idx_audit_created (created_at),
    CONSTRAINT fk_audit_user   FOREIGN KEY (user_id)   REFERENCES users(id)
) ENGINE=InnoDB;

-- ============================================================
-- INDEXES FOR COMMON QUERY PATHS
-- ============================================================

-- Active sessions per table (BR-001)
CREATE INDEX idx_session_table_status ON table_sessions (table_id, status);

-- Kitchen dashboard: pending/accepted items per branch
CREATE INDEX idx_oi_status_branch ON order_items (status);

-- Bill lookup by session
CREATE INDEX idx_bill_session ON bills (session_id, status);

-- Payments per day per cashier (FR-037)
CREATE INDEX idx_payment_cashier_date ON payments (cashier_id, confirmed_at);

-- Receipts by date for reprint (FR-030)
CREATE INDEX idx_receipt_date ON receipts (generated_at);

-- Audit retention: aged records (NFR-007: 7-year retention)
CREATE INDEX idx_audit_retention ON audit_logs (created_at);

SET FOREIGN_KEY_CHECKS = 1;

-- ============================================================
-- QR-Based Restaurant Ordering and Billing Management System
-- Database Schema - Phase 1 (Cash-First Release)
-- Aligned with Laravel migrations; primary keys use numeric IDs.
-- ============================================================

SET FOREIGN_KEY_CHECKS = 0;

-- ============================================================
-- LARAVEL SUPPORT TABLES
-- ============================================================

CREATE TABLE password_reset_tokens (
    email      VARCHAR(255) NOT NULL PRIMARY KEY,
    token      VARCHAR(255) NOT NULL,
    created_at TIMESTAMP NULL
) ENGINE=InnoDB;

CREATE TABLE sessions (
    id            VARCHAR(255) NOT NULL PRIMARY KEY,
    user_id       BIGINT UNSIGNED NULL,
    ip_address    VARCHAR(45) NULL,
    user_agent    TEXT NULL,
    payload       LONGTEXT NOT NULL,
    last_activity INT NOT NULL,
    INDEX sessions_user_id_index (user_id),
    INDEX sessions_last_activity_index (last_activity)
) ENGINE=InnoDB;

CREATE TABLE cache (
    `key`      VARCHAR(255) NOT NULL PRIMARY KEY,
    value      MEDIUMTEXT NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE cache_locks (
    `key`      VARCHAR(255) NOT NULL PRIMARY KEY,
    owner      VARCHAR(255) NOT NULL,
    expiration INT NOT NULL
) ENGINE=InnoDB;

CREATE TABLE jobs (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    queue        VARCHAR(255) NOT NULL,
    payload      LONGTEXT NOT NULL,
    attempts     TINYINT UNSIGNED NOT NULL,
    reserved_at  INT UNSIGNED NULL,
    available_at INT UNSIGNED NOT NULL,
    created_at   INT UNSIGNED NOT NULL,
    INDEX jobs_queue_index (queue)
) ENGINE=InnoDB;

CREATE TABLE job_batches (
    id             VARCHAR(255) NOT NULL PRIMARY KEY,
    name           VARCHAR(255) NOT NULL,
    total_jobs     INT NOT NULL,
    pending_jobs   INT NOT NULL,
    failed_jobs    INT NOT NULL,
    failed_job_ids LONGTEXT NOT NULL,
    options        MEDIUMTEXT NULL,
    cancelled_at   INT NULL,
    created_at     INT NOT NULL,
    finished_at    INT NULL
) ENGINE=InnoDB;

CREATE TABLE failed_jobs (
    id         BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    uuid       VARCHAR(255) NOT NULL UNIQUE,
    connection TEXT NOT NULL,
    queue      TEXT NOT NULL,
    payload    LONGTEXT NOT NULL,
    exception  LONGTEXT NOT NULL,
    failed_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ============================================================
-- SYSTEM CONFIGURATION, USERS & ROLES
-- ============================================================

CREATE TABLE system_settings (
    public_id   CHAR(26) NOT NULL,
    `key`       VARCHAR(80) NOT NULL,
    value       VARCHAR(255) NOT NULL,
    description VARCHAR(200) NULL,
    updated_by  VARCHAR(36) NULL,
    updated_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY system_settings_public_id_unique (public_id),
    INDEX system_settings_public_id_index (public_id),
    INDEX system_settings_key_index (`key`)
) ENGINE=InnoDB;

CREATE TABLE roles (
    id   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name ENUM('customer','waiter','kitchen','cashier','manager','admin') NOT NULL UNIQUE
) ENGINE=InnoDB;

CREATE TABLE users (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    role_id        BIGINT UNSIGNED NOT NULL,
    name           VARCHAR(100) NOT NULL,
    username       VARCHAR(60) NOT NULL UNIQUE,
    email          VARCHAR(100) NULL,
    password_hash  VARCHAR(255) NOT NULL,
    is_active      TINYINT(1) NOT NULL DEFAULT 1,
    last_login_at  TIMESTAMP NULL,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT users_role_id_foreign FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- TABLES, QR CODES & MENU
-- ============================================================

CREATE TABLE restaurant_tables (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    table_number VARCHAR(20) NOT NULL,
    capacity     TINYINT UNSIGNED NOT NULL DEFAULT 4,
    location     VARCHAR(80) NULL,
    status       ENUM('available','occupied','ordering','preparing','served','billing','paid','cleaning','reserved') NOT NULL DEFAULT 'available',
    is_active    TINYINT(1) NOT NULL DEFAULT 1,
    created_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uq_table_number (table_number)
) ENGINE=InnoDB;

CREATE TABLE qr_codes (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    table_id       BIGINT UNSIGNED NOT NULL UNIQUE,
    payload        TEXT NOT NULL,
    image_path     VARCHAR(255) NULL,
    generated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    regenerated_at TIMESTAMP NULL,
    CONSTRAINT qr_codes_table_id_foreign FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE menu_categories (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    name        VARCHAR(80) NOT NULL,
    description VARCHAR(200) NULL,
    sort_order  SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    is_active   TINYINT(1) NOT NULL DEFAULT 1,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

CREATE TABLE menu_items (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    category_id     BIGINT UNSIGNED NOT NULL,
    name            VARCHAR(80) NOT NULL,
    description     VARCHAR(200) NULL,
    base_price      DECIMAL(10,2) NOT NULL,
    tax_inclusive   TINYINT(1) NOT NULL DEFAULT 1,
    prep_time_min   TINYINT UNSIGNED NOT NULL DEFAULT 10,
    image_url       VARCHAR(255) NULL,
    modifier_groups JSON NULL,
    is_available    TINYINT(1) NOT NULL DEFAULT 1,
    sort_order      SMALLINT UNSIGNED NOT NULL DEFAULT 0,
    created_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at      TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT menu_items_category_id_foreign FOREIGN KEY (category_id) REFERENCES menu_categories(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ============================================================
-- SESSIONS, ORDERS & ORDER ITEMS
-- ============================================================

CREATE TABLE table_sessions (
    id               BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    table_id         BIGINT UNSIGNED NOT NULL,
    session_token    VARCHAR(64) NOT NULL UNIQUE,
    opened_by        BIGINT UNSIGNED NULL,
    open_source      ENUM('customer_qr','waiter','cashier') NOT NULL DEFAULT 'customer_qr',
    status           ENUM('open','billing','closed','force_closed') NOT NULL DEFAULT 'open',
    customer_count   TINYINT UNSIGNED NULL,
    notes            VARCHAR(255) NULL,
    token_expires_at TIMESTAMP NOT NULL,
    opened_at        TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    closed_at        TIMESTAMP NULL,
    closed_by        BIGINT UNSIGNED NULL,
    close_reason     VARCHAR(200) NULL,
    CONSTRAINT table_sessions_table_id_foreign FOREIGN KEY (table_id) REFERENCES restaurant_tables(id) ON DELETE CASCADE,
    CONSTRAINT table_sessions_opened_by_foreign FOREIGN KEY (opened_by) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT table_sessions_closed_by_foreign FOREIGN KEY (closed_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE orders (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    session_id      BIGINT UNSIGNED NOT NULL,
    placed_by_role  ENUM('customer','waiter','cashier') NOT NULL,
    placed_by_user  BIGINT UNSIGNED NULL,
    status          ENUM('pending','accepted','preparing','ready','served','cancelled') NOT NULL DEFAULT 'pending',
    cancel_reason   VARCHAR(200) NULL,
    cancelled_by    BIGINT UNSIGNED NULL,
    placed_at       TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    accepted_at     TIMESTAMP NULL,
    first_ready_at  TIMESTAMP NULL,
    fully_served_at TIMESTAMP NULL,
    notes           VARCHAR(255) NULL,
    CONSTRAINT orders_session_id_foreign FOREIGN KEY (session_id) REFERENCES table_sessions(id) ON DELETE CASCADE,
    CONSTRAINT orders_placed_by_user_foreign FOREIGN KEY (placed_by_user) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT orders_cancelled_by_foreign FOREIGN KEY (cancelled_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE order_items (
    id                   BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    order_id             BIGINT UNSIGNED NOT NULL,
    menu_item_id         BIGINT UNSIGNED NOT NULL,
    quantity             TINYINT UNSIGNED NOT NULL DEFAULT 1,
    unit_price           DECIMAL(10,2) NOT NULL,
    special_instructions VARCHAR(120) NULL,
    modifiers            JSON NULL,
    status               ENUM('pending','accepted','preparing','ready','served','cancelled','voided') NOT NULL DEFAULT 'pending',
    accepted_at          TIMESTAMP NULL,
    preparing_at         TIMESTAMP NULL,
    ready_at             TIMESTAMP NULL,
    served_at            TIMESTAMP NULL,
    cancelled_at         TIMESTAMP NULL,
    void_reason          VARCHAR(200) NULL,
    voided_by            BIGINT UNSIGNED NULL,
    CONSTRAINT order_items_order_id_foreign FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    CONSTRAINT order_items_menu_item_id_foreign FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE,
    CONSTRAINT order_items_voided_by_foreign FOREIGN KEY (voided_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- BILLS, PAYMENTS & REFUNDS
-- ============================================================

CREATE TABLE bills (
    id                    BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    session_id            BIGINT UNSIGNED NOT NULL,
    generated_by          BIGINT UNSIGNED NOT NULL,
    status                ENUM('draft','open','partially_paid','paid','voided') NOT NULL DEFAULT 'draft',
    subtotal              DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    vat_rate              DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    vat_amount            DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    service_charge_rate   DECIMAL(5,2) NOT NULL DEFAULT 0.00,
    service_charge_amount DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_amount       DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    discount_reason       VARCHAR(200) NULL,
    discount_approved_by  BIGINT UNSIGNED NULL,
    grand_total           DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    generated_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    paid_at               TIMESTAMP NULL,
    voided_at             TIMESTAMP NULL,
    voided_by             BIGINT UNSIGNED NULL,
    void_reason           VARCHAR(200) NULL,
    CONSTRAINT bills_session_id_foreign FOREIGN KEY (session_id) REFERENCES table_sessions(id) ON DELETE CASCADE,
    CONSTRAINT bills_generated_by_foreign FOREIGN KEY (generated_by) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT bills_discount_approved_by_foreign FOREIGN KEY (discount_approved_by) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT bills_voided_by_foreign FOREIGN KEY (voided_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE bill_items (
    id            BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bill_id       BIGINT UNSIGNED NOT NULL,
    order_item_id BIGINT UNSIGNED NOT NULL,
    quantity      TINYINT UNSIGNED NOT NULL,
    unit_price    DECIMAL(10,2) NOT NULL,
    line_total    DECIMAL(10,2) NOT NULL,
    served_at     TIMESTAMP NULL,
    CONSTRAINT bill_items_bill_id_foreign FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    CONSTRAINT bill_items_order_item_id_foreign FOREIGN KEY (order_item_id) REFERENCES order_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE bill_splits (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bill_id     BIGINT UNSIGNED NOT NULL,
    split_type  ENUM('by_item','equally','custom') NOT NULL,
    split_label VARCHAR(60) NULL,
    amount_due  DECIMAL(10,2) NOT NULL,
    amount_paid DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status      ENUM('unpaid','paid','outstanding') NOT NULL DEFAULT 'unpaid',
    CONSTRAINT bill_splits_bill_id_foreign FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE bill_split_items (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    split_id     BIGINT UNSIGNED NOT NULL,
    bill_item_id BIGINT UNSIGNED NOT NULL,
    CONSTRAINT bill_split_items_split_id_foreign FOREIGN KEY (split_id) REFERENCES bill_splits(id) ON DELETE CASCADE,
    CONSTRAINT bill_split_items_bill_item_id_foreign FOREIGN KEY (bill_item_id) REFERENCES bill_items(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE payments (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bill_id         BIGINT UNSIGNED NOT NULL,
    split_id        BIGINT UNSIGNED NULL,
    cashier_id      BIGINT UNSIGNED NOT NULL,
    payment_method  ENUM('cash','manual') NOT NULL DEFAULT 'cash',
    manual_note     VARCHAR(100) NULL,
    amount_due      DECIMAL(10,2) NOT NULL,
    amount_received DECIMAL(10,2) NOT NULL,
    change_due      DECIMAL(10,2) NOT NULL DEFAULT 0.00,
    status          ENUM('confirmed','refunded') NOT NULL DEFAULT 'confirmed',
    confirmed_at    TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT payments_bill_id_foreign FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE CASCADE,
    CONSTRAINT payments_split_id_foreign FOREIGN KEY (split_id) REFERENCES bill_splits(id) ON DELETE RESTRICT,
    CONSTRAINT payments_cashier_id_foreign FOREIGN KEY (cashier_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE refunds (
    id              BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    payment_id      BIGINT UNSIGNED NOT NULL,
    amount          DECIMAL(10,2) NOT NULL,
    reason          VARCHAR(255) NOT NULL,
    approved_by     BIGINT UNSIGNED NOT NULL,
    credit_note_ref VARCHAR(50) NOT NULL,
    refunded_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT refunds_payment_id_foreign FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE RESTRICT,
    CONSTRAINT refunds_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

-- ============================================================
-- RECEIPTS, RECONCILIATION, NOTIFICATIONS & AUDIT
-- ============================================================

CREATE TABLE receipts (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    bill_id        BIGINT UNSIGNED NOT NULL,
    cashier_id     BIGINT UNSIGNED NOT NULL,
    receipt_number VARCHAR(30) NOT NULL,
    pdf_path       VARCHAR(255) NULL,
    print_count    TINYINT UNSIGNED NOT NULL DEFAULT 0,
    generated_at   TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_receipt_number (receipt_number),
    CONSTRAINT receipts_bill_id_foreign FOREIGN KEY (bill_id) REFERENCES bills(id) ON DELETE RESTRICT,
    CONSTRAINT receipts_cashier_id_foreign FOREIGN KEY (cashier_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE receipt_reprints (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    receipt_id   BIGINT UNSIGNED NOT NULL,
    reprinted_by BIGINT UNSIGNED NOT NULL,
    reprinted_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT receipt_reprints_receipt_id_foreign FOREIGN KEY (receipt_id) REFERENCES receipts(id) ON DELETE CASCADE,
    CONSTRAINT receipt_reprints_reprinted_by_foreign FOREIGN KEY (reprinted_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE cash_reconciliations (
    id                  BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    reconciliation_date DATE NOT NULL,
    prepared_by         BIGINT UNSIGNED NOT NULL,
    approved_by         BIGINT UNSIGNED NULL,
    system_total        DECIMAL(10,2) NOT NULL,
    physical_count      DECIMAL(10,2) NOT NULL,
    variance_amount     DECIMAL(10,2) GENERATED ALWAYS AS (physical_count - system_total) STORED,
    variance_pct        DECIMAL(6,3) GENERATED ALWAYS AS (CASE WHEN system_total = 0 THEN 0 ELSE ((physical_count - system_total) / system_total) * 100 END) STORED,
    flagged             TINYINT(1) GENERATED ALWAYS AS (ABS((physical_count - system_total) / system_total) > 0.005) STORED,
    notes               VARCHAR(500) NULL,
    created_at          TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uq_reconciliation_date (reconciliation_date),
    CONSTRAINT cash_reconciliations_prepared_by_foreign FOREIGN KEY (prepared_by) REFERENCES users(id) ON DELETE RESTRICT,
    CONSTRAINT cash_reconciliations_approved_by_foreign FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE assistance_requests (
    id           BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    session_id   BIGINT UNSIGNED NOT NULL,
    request_type ENUM('assistance','bill_request') NOT NULL DEFAULT 'assistance',
    handled_by   BIGINT UNSIGNED NULL,
    status       ENUM('pending','handled') NOT NULL DEFAULT 'pending',
    requested_at TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    handled_at   TIMESTAMP NULL,
    CONSTRAINT assistance_requests_session_id_foreign FOREIGN KEY (session_id) REFERENCES table_sessions(id) ON DELETE CASCADE,
    CONSTRAINT assistance_requests_handled_by_foreign FOREIGN KEY (handled_by) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE notifications (
    id             BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    target_role    ENUM('customer','waiter','kitchen','cashier','admin') NOT NULL,
    target_user_id BIGINT UNSIGNED NULL,
    session_id     BIGINT UNSIGNED NULL,
    event_type     VARCHAR(60) NOT NULL,
    payload        JSON NULL,
    is_read        TINYINT(1) NOT NULL DEFAULT 0,
    created_at     TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_notif_role_read (target_role, is_read),
    CONSTRAINT notifications_session_id_foreign FOREIGN KEY (session_id) REFERENCES table_sessions(id) ON DELETE CASCADE,
    CONSTRAINT notifications_target_user_id_foreign FOREIGN KEY (target_user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

CREATE TABLE audit_logs (
    id          BIGINT UNSIGNED NOT NULL AUTO_INCREMENT PRIMARY KEY,
    user_id     BIGINT UNSIGNED NULL,
    action      VARCHAR(100) NOT NULL,
    entity_type VARCHAR(60) NOT NULL,
    entity_id   BIGINT UNSIGNED NOT NULL,
    old_value   JSON NULL,
    new_value   JSON NULL,
    reason      VARCHAR(255) NULL,
    ip_address  VARCHAR(45) NULL,
    created_at  TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
    INDEX idx_audit_entity (entity_type, entity_id),
    INDEX idx_audit_user (user_id),
    INDEX idx_audit_created (created_at),
    CONSTRAINT audit_logs_user_id_foreign FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE RESTRICT
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;

-- 004_payment_gateway.sql
-- Adds payment-gateway tables required by checkout/payment flow.
-- Safe to run multiple times on MySQL 8+.

USE globentech_db;

-- Ensure orders supports completion timestamp used by order history / completion APIs
ALTER TABLE orders
    ADD COLUMN IF NOT EXISTS completed_at TIMESTAMP NULL AFTER updated_at;

-- Payments table
CREATE TABLE IF NOT EXISTS payments (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    customer_id INT NOT NULL,
    provider ENUM('stripe') NOT NULL DEFAULT 'stripe',
    provider_payment_intent_id VARCHAR(255) UNIQUE NOT NULL,
    amount DECIMAL(10, 2) NOT NULL,
    currency VARCHAR(10) NOT NULL DEFAULT 'cad',
    status ENUM('created', 'requires_payment_method', 'requires_action', 'processing', 'succeeded', 'failed', 'canceled', 'refunded') DEFAULT 'created',
    payment_method_type VARCHAR(50) NULL,
    failure_reason TEXT NULL,
    paid_at TIMESTAMP NULL,
    provider_payload JSON NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_payment_order (order_id),
    INDEX idx_payment_customer (customer_id),
    INDEX idx_payment_status (status)
);

-- Payment events table for idempotent webhook processing
CREATE TABLE IF NOT EXISTS payment_events (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NULL,
    provider ENUM('stripe') NOT NULL DEFAULT 'stripe',
    provider_event_id VARCHAR(255) UNIQUE NOT NULL,
    event_type VARCHAR(120) NOT NULL,
    payload JSON NOT NULL,
    processed_at TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    INDEX idx_event_type (event_type),
    INDEX idx_event_processed (processed_at)
);

-- Invoices / receipts storage
CREATE TABLE IF NOT EXISTS invoices (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    order_id INT NOT NULL,
    customer_id INT NOT NULL,
    invoice_number VARCHAR(64) UNIQUE NOT NULL,
    transaction_details JSON NOT NULL,
    receipt_html MEDIUMTEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_invoice_order (order_id),
    INDEX idx_invoice_customer (customer_id)
);

-- Notifications table for payment alerts
CREATE TABLE IF NOT EXISTS notifications (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    order_id INT NULL,
    payment_id INT NULL,
    notification_type VARCHAR(60) NOT NULL,
    severity ENUM('info', 'warning', 'critical') DEFAULT 'info',
    title VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE SET NULL,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE SET NULL,
    INDEX idx_notification_user (user_id),
    INDEX idx_notification_read (is_read),
    INDEX idx_notification_type (notification_type)
);

-- Accounting synchronization table
CREATE TABLE IF NOT EXISTS accounting_sync (
    id INT AUTO_INCREMENT PRIMARY KEY,
    payment_id INT NOT NULL,
    order_id INT NOT NULL,
    sync_status ENUM('pending', 'synced', 'failed') DEFAULT 'pending',
    reporting_period VARCHAR(20) NOT NULL,
    synced_at TIMESTAMP NULL,
    error_message TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (payment_id) REFERENCES payments(id) ON DELETE CASCADE,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_accounting_sync_status (sync_status),
    INDEX idx_accounting_period (reporting_period)
);

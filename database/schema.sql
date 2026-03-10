-- GlobenTech Laboratory Order Management System Database Schema

CREATE DATABASE IF NOT EXISTS globentech_db;
USE globentech_db;

-- Users table
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    full_name VARCHAR(255) NOT NULL,
    email VARCHAR(255) UNIQUE NOT NULL,
    password_hash VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    company_name VARCHAR(255),
    address TEXT,
    role ENUM('customer', 'technician', 'administrator') DEFAULT 'customer',
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    INDEX idx_email (email),
    INDEX idx_role (role)
);

-- Orders table
CREATE TABLE IF NOT EXISTS orders (
    id INT AUTO_INCREMENT PRIMARY KEY,
    customer_id INT NOT NULL,
    order_number VARCHAR(50) UNIQUE NOT NULL,
    status ENUM('submitted', 'pending_approval', 'approved', 'payment_pending', 
                'payment_confirmed', 'in_queue', 'preparation_in_progress', 
                'testing_in_progress', 'results_available', 'completed', 'rejected') 
                DEFAULT 'submitted',
    priority ENUM('standard', 'priority') DEFAULT 'standard',
    total_cost DECIMAL(10, 2) DEFAULT 0.00,
    estimated_completion DATETIME NULL,
    approved_by INT NULL,
    approved_at TIMESTAMP NULL,
    rejection_reason TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (customer_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (approved_by) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_customer (customer_id),
    INDEX idx_status (status),
    INDEX idx_order_number (order_number)
);

-- Samples table
CREATE TABLE IF NOT EXISTS samples (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    sample_type ENUM('ore', 'liquid') NOT NULL,
    compound_name VARCHAR(255) NOT NULL,
    quantity DECIMAL(10, 2) NOT NULL,
    unit VARCHAR(50) NOT NULL,
    preparation_time INT DEFAULT 0,
    testing_time INT DEFAULT 0,
    status ENUM('pending', 'preparing', 'ready', 'testing', 'completed') DEFAULT 'pending',
    results TEXT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    INDEX idx_order (order_id),
    INDEX idx_status (status)
);

-- Equipment table
CREATE TABLE IF NOT EXISTS equipment (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    equipment_type VARCHAR(100) NOT NULL,
    processing_time_per_sample INT NOT NULL,
    warmup_time INT DEFAULT 0,
    break_interval INT DEFAULT 0,
    break_duration INT DEFAULT 0,
    daily_capacity INT DEFAULT 0,
    is_available BOOLEAN DEFAULT TRUE,
    last_maintenance TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_available (is_available)
);

-- Queue table
CREATE TABLE IF NOT EXISTS queue (
    id INT AUTO_INCREMENT PRIMARY KEY,
    order_id INT NOT NULL,
    equipment_id INT NULL,
    position INT NOT NULL,
    scheduled_start DATETIME NULL,
    scheduled_end DATETIME NULL,
    actual_start DATETIME NULL,
    actual_end DATETIME NULL,
    queue_type ENUM('standard', 'priority') DEFAULT 'standard',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (order_id) REFERENCES orders(id) ON DELETE CASCADE,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE SET NULL,
    INDEX idx_order (order_id),
    INDEX idx_position (position)
);

-- Equipment delays table
CREATE TABLE IF NOT EXISTS equipment_delays (
    id INT AUTO_INCREMENT PRIMARY KEY,
    equipment_id INT NOT NULL,
    delay_start TIMESTAMP NOT NULL,
    delay_duration INT NOT NULL,
    reason TEXT,
    logged_by INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (equipment_id) REFERENCES equipment(id) ON DELETE CASCADE,
    FOREIGN KEY (logged_by) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_equipment (equipment_id)
);

-- Audit logs table
CREATE TABLE IF NOT EXISTS audit_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NULL,
    action VARCHAR(255) NOT NULL,
    entity_type VARCHAR(100),
    entity_id INT,
    details TEXT,
    ip_address VARCHAR(45),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_user (user_id),
    INDEX idx_action (action),
    INDEX idx_created (created_at)
);

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

-- Payment events table (webhook/event log for idempotent processing)
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

-- Notifications table for customer/admin payment alerts
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

-- Insert default admin user (password: admin123)
INSERT INTO users (full_name, email, password_hash, role) VALUES
('System Administrator', 'admin@globentech.com', '$2y$12$ispYnU5LnCx0XMyJ8x2aiOC6YteIq8ZqOFyVzuvp1vrnamajyv07m', 'administrator');

-- Insert default technician (password: tech123)
INSERT INTO users (full_name, email, password_hash, role) VALUES
('Lab Technician', 'tech@globentech.com', '$2y$12$p8DMkw4Jo6j.9vgKiwfV5u25FE2VvWf9fYnHEd/67YL57cQBkZSoS', 'technician');

-- Insert default customer (password: customer123)
INSERT INTO users (full_name, email, password_hash, role, company_name) VALUES
('Test Customer', 'customer@globentech.com', '$2y$12$hBiceBSwHp9Rcqk5mh1k0uPjWflxDNEn/JMOUYzVekFGW4pKTSMQu', 'customer', 'Test Company Inc.');

-- Insert sample equipment
INSERT INTO equipment (name, equipment_type, processing_time_per_sample, warmup_time, break_interval, break_duration, daily_capacity) VALUES
('ICP Spectrometer', 'ICP', 2, 10, 20, 20, 200),
('XRF Analyzer', 'XRF', 3, 15, 30, 15, 150),
('Mass Spectrometer', 'MS', 5, 20, 25, 25, 100);

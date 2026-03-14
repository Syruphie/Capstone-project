-- Run this if you already have the database and need to add the order_types table
USE globentech_db;

CREATE TABLE IF NOT EXISTS order_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
);

INSERT IGNORE INTO order_types (name, description, cost) VALUES
('Gold Ore Analysis', 'Comprehensive gold content analysis', 150.00),
('Silver Ore Analysis', 'Silver content determination', 120.00),
('Water Quality Testing', 'Standard water analysis', 85.00);

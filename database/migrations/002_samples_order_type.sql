-- Add Order Catalogue link to samples for revenue tracking
-- Run after 001_order_types.sql if you have the old samples table without these columns
USE globentech_db;

-- Add columns (ignore errors if already present)
ALTER TABLE samples ADD COLUMN order_type_id INT NULL AFTER order_id;
ALTER TABLE samples ADD COLUMN unit_cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER order_type_id;

-- Backfill existing samples with first active order type
UPDATE samples s SET s.order_type_id = (SELECT id FROM order_types WHERE is_active = 1 LIMIT 1) WHERE s.order_type_id IS NULL;
UPDATE samples s INNER JOIN order_types ot ON ot.id = s.order_type_id SET s.unit_cost = ot.cost WHERE s.unit_cost = 0;

-- Enforce NOT NULL and FK
ALTER TABLE samples MODIFY order_type_id INT NOT NULL;
ALTER TABLE samples ADD FOREIGN KEY (order_type_id) REFERENCES order_types(id) ON DELETE RESTRICT;
ALTER TABLE samples ADD INDEX idx_order_type (order_type_id);

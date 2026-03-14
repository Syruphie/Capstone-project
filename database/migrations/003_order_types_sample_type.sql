-- Add sample_type to order_types (for existing DBs that have order_types without it)
USE globentech_db;

ALTER TABLE order_types ADD COLUMN sample_type ENUM('ore', 'liquid') NOT NULL DEFAULT 'ore' AFTER description;

-- Optionally backfill: e.g. set liquid for water-related types
UPDATE order_types SET sample_type = 'liquid' WHERE name LIKE '%Water%' OR name LIKE '%Liquid%' OR LOWER(COALESCE(description,'')) LIKE '%water%';

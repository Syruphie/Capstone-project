-- Upgrade an existing globentech_db created from an older schema.sql (no order_types / old samples).
-- Safe to run more than once. Run against RDS from EC2 or any mysql client:
--   mysql -h HOST -u USER -p globentech_db < database/patch_order_catalogue_existing.sql

USE globentech_db;

-- 1) order_types (full definition for fresh CREATE)
CREATE TABLE IF NOT EXISTS order_types (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(255) NOT NULL,
    description TEXT,
    sample_type ENUM('ore', 'liquid') NOT NULL DEFAULT 'ore',
    cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_active (is_active)
);

-- 2) Legacy DBs may have order_types from 001_order_types.sql without sample_type
SET @c = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'order_types' AND COLUMN_NAME = 'sample_type'
);
SET @sql = IF(
    @c = 0,
    'ALTER TABLE order_types ADD COLUMN sample_type ENUM(''ore'', ''liquid'') NOT NULL DEFAULT ''ore'' AFTER description',
    'SELECT 1'
);
PREPARE _patch_ot_st FROM @sql;
EXECUTE _patch_ot_st;
DEALLOCATE PREPARE _patch_ot_st;

UPDATE order_types SET sample_type = 'liquid'
WHERE (name LIKE '%Water%' OR name LIKE '%Liquid%' OR LOWER(COALESCE(description, '')) LIKE '%water%')
  AND sample_type = 'ore';

INSERT INTO order_types (name, description, sample_type, cost)
SELECT 'Gold Ore Analysis', 'Comprehensive gold content analysis', 'ore', 150.00
WHERE NOT EXISTS (SELECT 1 FROM order_types t WHERE t.name = 'Gold Ore Analysis' LIMIT 1);
INSERT INTO order_types (name, description, sample_type, cost)
SELECT 'Silver Ore Analysis', 'Silver content determination', 'ore', 120.00
WHERE NOT EXISTS (SELECT 1 FROM order_types t WHERE t.name = 'Silver Ore Analysis' LIMIT 1);
INSERT INTO order_types (name, description, sample_type, cost)
SELECT 'Water Quality Testing', 'Standard water analysis', 'liquid', 85.00
WHERE NOT EXISTS (SELECT 1 FROM order_types t WHERE t.name = 'Water Quality Testing' LIMIT 1);

-- 3) samples: add catalogue columns (old schema had sample_type only on samples, no order_type_id)
SET @c = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'samples' AND COLUMN_NAME = 'order_type_id'
);
SET @sql = IF(
    @c = 0,
    'ALTER TABLE samples ADD COLUMN order_type_id INT NULL AFTER order_id',
    'SELECT 1'
);
PREPARE _patch_s_ot FROM @sql;
EXECUTE _patch_s_ot;
DEALLOCATE PREPARE _patch_s_ot;

SET @c = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'samples' AND COLUMN_NAME = 'unit_cost'
);
SET @sql = IF(
    @c = 0,
    'ALTER TABLE samples ADD COLUMN unit_cost DECIMAL(10, 2) NOT NULL DEFAULT 0.00 AFTER order_type_id',
    'SELECT 1'
);
PREPARE _patch_s_uc FROM @sql;
EXECUTE _patch_s_uc;
DEALLOCATE PREPARE _patch_s_uc;

UPDATE samples s
SET s.order_type_id = (SELECT id FROM order_types WHERE is_active = 1 ORDER BY id ASC LIMIT 1)
WHERE s.order_type_id IS NULL;

UPDATE samples s
INNER JOIN order_types ot ON ot.id = s.order_type_id
SET s.unit_cost = ot.cost
WHERE s.unit_cost = 0;

ALTER TABLE samples MODIFY order_type_id INT NOT NULL;

SET @fk = (
    SELECT COUNT(*) FROM information_schema.REFERENTIAL_CONSTRAINTS
    WHERE CONSTRAINT_SCHEMA = DATABASE()
      AND TABLE_NAME = 'samples'
      AND REFERENCED_TABLE_NAME = 'order_types'
);
SET @sql = IF(
    @fk = 0,
    'ALTER TABLE samples ADD CONSTRAINT fk_samples_order_type FOREIGN KEY (order_type_id) REFERENCES order_types(id) ON DELETE RESTRICT',
    'SELECT 1'
);
PREPARE _patch_s_fk FROM @sql;
EXECUTE _patch_s_fk;
DEALLOCATE PREPARE _patch_s_fk;

SET @idx = (
    SELECT COUNT(*) FROM information_schema.STATISTICS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'samples' AND INDEX_NAME = 'idx_order_type'
);
SET @sql = IF(
    @idx = 0,
    'CREATE INDEX idx_order_type ON samples (order_type_id)',
    'SELECT 1'
);
PREPARE _patch_s_ix FROM @sql;
EXECUTE _patch_s_ix;
DEALLOCATE PREPARE _patch_s_ix;

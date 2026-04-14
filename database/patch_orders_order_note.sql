-- Add orders.order_note if missing.
-- Idempotent and safe for existing databases.
--   mysql -h HOST -u USER -p globentech_db < database/patch_orders_order_note.sql

USE globentech_db;

SET @c = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'order_note'
);
SET @sql = IF(
    @c = 0,
    'ALTER TABLE orders ADD COLUMN order_note TEXT NULL AFTER rejection_reason',
    'SELECT 1'
);
PREPARE _patch_o_note FROM @sql;
EXECUTE _patch_o_note;
DEALLOCATE PREPARE _patch_o_note;

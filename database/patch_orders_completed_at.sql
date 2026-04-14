-- Add orders.completed_at if missing (required by order history SQL in OrderRepository).
-- Idempotent. Run on RDS if /orders/order-history.php returns HTTP 500.
--   mysql -h HOST -u USER -p globentech_db < database/patch_orders_completed_at.sql

USE globentech_db;

SET @c = (
    SELECT COUNT(*) FROM information_schema.COLUMNS
    WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'orders' AND COLUMN_NAME = 'completed_at'
);
SET @sql = IF(
    @c = 0,
    'ALTER TABLE orders ADD COLUMN completed_at TIMESTAMP NULL AFTER updated_at',
    'SELECT 1'
);
PREPARE _patch_o_ca FROM @sql;
EXECUTE _patch_o_ca;
DEALLOCATE PREPARE _patch_o_ca;

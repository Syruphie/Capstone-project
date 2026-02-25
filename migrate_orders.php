<?php
require_once __DIR__ . '/config/database.php';
$db = Database::getInstance()->getConnection();

try {
    // check for each column, add if missing (older MySQL versions don't support IF NOT EXISTS)
    $cols = $db->query("SELECT COLUMN_NAME FROM information_schema.COLUMNS WHERE TABLE_SCHEMA=DATABASE() AND TABLE_NAME='orders'")->fetchAll(PDO::FETCH_COLUMN);
    if (!in_array('estimated_completion', $cols)) {
        $db->exec("ALTER TABLE orders ADD COLUMN estimated_completion DATETIME NULL");
        echo "added estimated_completion\n";
    }
    if (!in_array('completed_at', $cols)) {
        $db->exec("ALTER TABLE orders ADD COLUMN completed_at DATETIME NULL");
        echo "added completed_at\n";
    }
    echo "Migration finished\n";
} catch (PDOException $e) {
    echo "Migration failed: " . $e->getMessage();
}

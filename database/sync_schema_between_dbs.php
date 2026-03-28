<?php
declare(strict_types=1);

/**
 * Syncs table structures from globentech_db to capstone_test.
 *
 * Usage:
 *   php database/sync_schema_between_dbs.php --yes
 */

require_once __DIR__ . '/../config/database.php';

$confirmed = in_array('--yes', $argv, true);

if (!$confirmed) {
    fwrite(STDOUT, "This will replace table schemas in capstone_test using globentech_db as source.\n");
    fwrite(STDOUT, "Run with --yes to continue.\n");
    exit(1);
}

$pdo = new PDO(
    'mysql:host=' . DB_HOST . ';charset=utf8mb4',
    DB_USER,
    DB_PASS,
    [
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
    ]
);

$sourceDb = 'globentech_db';
$targetDb = 'capstone_test';

$tableStmt = $pdo->prepare(
    'SELECT table_name
     FROM information_schema.tables
     WHERE table_schema = ?
     ORDER BY table_name'
);
$tableStmt->execute([$sourceDb]);
$tables = array_map(static fn(array $row): string => (string)$row['table_name'], $tableStmt->fetchAll());

if ($tables === []) {
    throw new RuntimeException('No tables found in source database: ' . $sourceDb);
}

$pdo->exec('SET FOREIGN_KEY_CHECKS = 0');

foreach ($tables as $table) {
    $createStmt = $pdo->query('SHOW CREATE TABLE `' . $sourceDb . '`.`' . $table . '`');
    $createRow = $createStmt->fetch();
    if (!$createRow || !isset($createRow['Create Table'])) {
        throw new RuntimeException('Unable to read CREATE TABLE for ' . $table);
    }

    $ddl = (string)$createRow['Create Table'];

    // Ensure DDL targets the destination database.
    $ddl = preg_replace('/^CREATE TABLE `[^`]+`/m', 'CREATE TABLE `' . $table . '`', $ddl) ?? $ddl;

    $pdo->exec('DROP TABLE IF EXISTS `' . $targetDb . '`.`' . $table . '`');
    $pdo->exec('USE `' . $targetDb . '`');
    $pdo->exec($ddl);
}

$pdo->exec('SET FOREIGN_KEY_CHECKS = 1');

echo 'Schema sync complete: ' . $sourceDb . ' -> ' . $targetDb . PHP_EOL;


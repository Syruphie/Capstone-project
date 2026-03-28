<?php
declare(strict_types=1);

$root = __DIR__;
$domains = ['queue', 'user', 'order', 'sample', 'payment', 'equipment', 'email', 'order_type'];

function runPhp(string $scriptPath): int
{
    $cmd = sprintf('php %s', escapeshellarg($scriptPath));
    passthru($cmd, $exitCode);
    return (int)$exitCode;
}

echo "=== Reset Test DB ===\n";
$resetExit = runPhp($root . '/reset_test_db.php');
if ($resetExit !== 0) {
    fwrite(STDERR, "[FAIL] Could not reset test database\n");
    exit($resetExit);
}

echo "\n=== Run Domain Tests ===\n";
$failures = [];

foreach ($domains as $domain) {
    $domainDir = $root . '/' . $domain;
    if (!is_dir($domainDir)) {
        continue;
    }

    $tests = glob($domainDir . '/test_*.php') ?: [];
    sort($tests);

    foreach ($tests as $testFile) {
        echo "\n--- {$testFile} ---\n";
        $exitCode = runPhp($testFile);
        if ($exitCode !== 0) {
            $failures[] = $testFile;
        }
    }
}

echo "\n=== Summary ===\n";
if (count($failures) === 0) {
    echo "[PASS] All domain tests passed\n";
    exit(0);
}

echo "[FAIL] " . count($failures) . " test file(s) failed:\n";
foreach ($failures as $failed) {
    echo " - {$failed}\n";
}

exit(1);


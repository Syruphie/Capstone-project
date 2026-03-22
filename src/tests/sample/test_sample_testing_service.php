<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('SampleTestingService');

$repo = makeSampleRepository();
$prepService = new SamplePreparationService($repo);
$testingService = new SampleTestingService($repo);

/* arrange a READY sample */
assertTrue($prepService->completePreparation(2), 'sample 2 should move from preparing to ready');

/* startTesting */
assertTrue($testingService->startTesting(2), 'startTesting should succeed for ready sample');
assertSame(SampleStatus::TESTING, $repo->getById(2)->getStatus(), 'sample should now be testing');
printPass('startTesting works');

/* invalid startTesting */
assertFalse($testingService->startTesting(1), 'startTesting should fail for non-ready sample');
printPass('invalid startTesting transition works');

/* updateResults */
assertTrue($testingService->updateResults(2, 'Preliminary results'), 'updateResults should succeed');
assertSame('Preliminary results', $repo->getById(2)->getResults(), 'results should update');
printPass('updateResults works');

/* completeTesting */
assertTrue($testingService->completeTesting(2, 'Final result'), 'completeTesting should succeed');
$completed = $repo->getById(2);
assertSame(SampleStatus::COMPLETED, $completed->getStatus(), 'sample should now be completed');
assertSame('Final result', $completed->getResults(), 'final results should be saved');
printPass('completeTesting works');

/* invalid completeTesting */
assertFalse($testingService->completeTesting(1, 'Should fail'), 'completeTesting should fail for non-testing sample');
printPass('invalid completeTesting transition works');

/* calculateTestingTime placeholder */
assertSame(60, $testingService->calculateTestingTime(1, 1), 'placeholder testing time should be 60');
printPass('calculateTestingTime placeholder works');

/* getSamplesInTesting */
$rows = $testingService->getSamplesInTesting();
assertTrue(is_array($rows), 'getSamplesInTesting should return array');
printPass('getSamplesInTesting works');
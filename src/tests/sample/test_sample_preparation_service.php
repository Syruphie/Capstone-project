<?php
declare(strict_types=1);

require_once __DIR__ . '/../bootstrap.php';
require_once __DIR__ . '/../reset_test_db.php';

printSection('SamplePreparationService');

$repo = makeSampleRepository();
$service = new SamplePreparationService($repo);

/* startPreparation */
assertTrue($service->startPreparation(1), 'startPreparation should succeed for pending sample');
assertSame(SampleStatus::PREPARING, $repo->getById(1)->getStatus(), 'sample should now be preparing');
printPass('startPreparation works');

/* invalid transition */
assertFalse($service->startPreparation(2), 'startPreparation should fail for already preparing sample');
printPass('invalid startPreparation transition works');

/* completePreparation */
assertTrue($service->completePreparation(2), 'completePreparation should succeed for preparing sample');
assertSame(SampleStatus::READY, $repo->getById(2)->getStatus(), 'sample should now be ready');
printPass('completePreparation works');

/* invalid completePreparation */
assertFalse($service->completePreparation(4), 'completePreparation should fail when sample is not preparing');
printPass('invalid completePreparation transition works');

/* getSamplesInPreparation */
$rows = $service->getSamplesInPreparation();
assertTrue(is_array($rows), 'getSamplesInPreparation should return array');
printPass('getSamplesInPreparation works');
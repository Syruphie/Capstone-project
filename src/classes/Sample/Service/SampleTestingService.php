<?php
declare(strict_types=1);

/**
 * Class SampleTestingService
 *
 * Handles workflow transitions and logic for sample testing.
 *
 * Responsibilities:
 * - Start and complete testing phases
 * - Record and update test results
 * - Calculate testing time (if applicable)
 * - Enforce valid testing state transitions
 *
 * Non-Responsibilities:
 * - No preparation logic
 * - No direct database interaction beyond repository usage
 *
 * Design Notes:
 * - Encapsulates all testing-related domain rules
 * - Maintains separation from preparation and tracking concerns
 */

require_once __DIR__ . '/../Repository/SampleRepository.php';
require_once __DIR__ . '/../Support/SampleStatus.php';

class SampleTestingService
{
    private SampleRepository $sampleRepository;

    public function __construct(SampleRepository $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    public function startTesting(int $sampleId): bool
    {
        $sample = $this->sampleRepository->getById($sampleId);
        if ($sample === null || $sample->getStatus() !== SampleStatus::READY) {
            return false;
        }

        return $this->sampleRepository->updateStatus($sampleId, SampleStatus::TESTING);
    }

    public function completeTesting(int $sampleId, string $results): bool
    {
        $sample = $this->sampleRepository->getById($sampleId);
        if ($sample === null || $sample->getStatus() !== SampleStatus::TESTING) {
            return false;
        }

        $updatedResults = $this->sampleRepository->updateResults($sampleId, $results);
        if (!$updatedResults) {
            return false;
        }

        return $this->sampleRepository->updateStatus($sampleId, SampleStatus::COMPLETED);
    }

    public function updateResults(int $sampleId, string $results): bool
    {
        return $this->sampleRepository->updateResults($sampleId, $results);
    }

    // TODO: Implement lol
    public function calculateTestingTime(int $sampleId, int $equipmentId): int
    {
        return 60;
    }

    public function getSamplesInTesting(): array
    {
        return $this->sampleRepository->getByStatus(SampleStatus::TESTING);
    }
}
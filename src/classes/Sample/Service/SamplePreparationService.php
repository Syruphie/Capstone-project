<?php
declare(strict_types=1);

/**
 * Class SamplePreparationService
 *
 * Handles workflow transitions related to sample preparation.
 *
 * Responsibilities:
 * - Start and complete preparation stages
 * - Enforce valid state transitions for preparation
 * - Retrieve samples currently in preparation
 *
 * Non-Responsibilities:
 * - No database query logic beyond repository usage
 * - No testing or results handling
 *
 * Design Notes:
 * - Focused strictly on preparation lifecycle rules
 * - Ensures state transitions remain valid and predictable
 */

require_once __DIR__ . '/../Repository/SampleRepository.php';
require_once __DIR__ . '/../Support/SampleStatus.php';

class SamplePreparationService
{
    private SampleRepository $sampleRepository;

    public function __construct(SampleRepository $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    public function startPreparation(int $sampleId): bool
    {
        $sample = $this->sampleRepository->getById($sampleId);
        if ($sample === null || $sample->getStatus() !== SampleStatus::PENDING) {
            return false;
        }

        return $this->sampleRepository->updateStatus($sampleId, SampleStatus::PREPARING);
    }

    public function completePreparation(int $sampleId): bool
    {
        $sample = $this->sampleRepository->getById($sampleId);
        if ($sample === null || $sample->getStatus() !== SampleStatus::PREPARING) {
            return false;
        }

        return $this->sampleRepository->updateStatus($sampleId, SampleStatus::READY);
    }

    public function getSamplesInPreparation(): array
    {
        return $this->sampleRepository->getByStatus(SampleStatus::PREPARING);
    }
}
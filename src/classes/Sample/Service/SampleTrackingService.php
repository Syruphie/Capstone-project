<?php
declare(strict_types=1);

/**
 * Class SampleTrackingService
 *
 * Provides read-oriented operations for tracking sample status.
 *
 * Responsibilities:
 * - Retrieve samples by status
 * - Provide filtered views (pending, preparing, testing)
 *
 * Non-Responsibilities:
 * - No state mutations
 * - No business workflow logic
 *
 * Design Notes:
 * - Acts as a query-focused service for UI or reporting layers
 * - Delegates all data access to SampleRepository
 */

require_once __DIR__ . '/../Repository/SampleRepository.php';
require_once __DIR__ . '/../Support/SampleStatus.php';
require_once __DIR__ . '/../Support/ValidateSampleStatus.php';

class SampleTrackingService
{
    private SampleRepository $sampleRepository;

    public function __construct(SampleRepository $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    public function getSamplesByStatus(string $status): array
    {
        ValidateSampleStatus::validate($status);
        return $this->sampleRepository->getByStatus($status);
    }

    public function getPendingSamples(): array
    {
        return $this->sampleRepository->getByStatus(SampleStatus::PENDING);
    }

    public function getSamplesInPreparation(): array
    {
        return $this->sampleRepository->getByStatus(SampleStatus::PREPARING);
    }

    public function getSamplesInTesting(): array
    {
        return $this->sampleRepository->getByStatus(SampleStatus::TESTING);
    }
}
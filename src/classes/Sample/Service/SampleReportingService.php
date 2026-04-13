<?php
declare(strict_types=1);

/**
 * Class SampleReportingService
 *
 * Provides analytics and reporting functionality for samples.
 *
 * Responsibilities:
 * - Retrieve sample statistics over time
 * - Calculate total processing time for samples
 * - Support reporting and dashboard features
 *
 * Non-Responsibilities:
 * - No workflow state changes
 * - No direct database logic outside repository usage
 *
 * Design Notes:
 * - Focused on aggregation and derived data
 * - Works alongside SampleRepository for raw data access
 */

require_once __DIR__ . '/../Repository/SampleRepository.php';
require_once __DIR__ . '/../../Support/DateRangeValidator.php';

class SampleReportingService
{
    private SampleRepository $sampleRepository;

    public function __construct(SampleRepository $sampleRepository)
    {
        $this->sampleRepository = $sampleRepository;
    }

    /**
     * @throws Exception
     */
    public function getSampleStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $from = $startDate;
        $to = $endDate;
        if ($startDate === '' || $startDate === null) {
            $from = '1900-01-01';
        }
        if ($endDate === '' || $endDate === null) {
            $to = date('Y-m-d');
        }
        DateRangeValidator::validate($from, $to);
        return $this->sampleRepository->getStatistics($startDate, $endDate);
    }

    public function getTotalProcessingTime(int $sampleId): ?int
    {
        $sample = $this->sampleRepository->getById($sampleId);
        if ($sample === null) {
            return null;
        }

        return $sample->getPreparationTime() + (int)($sample->getTestingTime() ?? 0);
    }
}
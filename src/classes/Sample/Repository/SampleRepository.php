<?php
declare(strict_types=1);

/**
 * Class SampleRepository
 *
 * Handles all direct database interactions for samples.
 *
 * Responsibilities:
 * - Insert, update, and delete sample records
 * - Retrieve samples by ID, order, or status
 * - Execute query logic for sample-related data retrieval
 * - Persist updates to sample state (status, results, timing)
 *
 * Non-Responsibilities:
 * - No business workflow logic
 * - No preparation or testing orchestration
 * - No cross-domain coordination (e.g., order updates)
 *
 * Design Notes:
 * - Acts as the data access layer for the `samples` table
 * - Uses SampleMapper to convert rows into entities
 * - Should remain focused strictly on SQL execution
 */

require_once __DIR__ . '/../Entity/Sample.php';
require_once __DIR__ . '/../Support/SampleMapper.php';
require_once __DIR__ . '/../Support/ValidateSampleStatus.php';

class SampleRepository
{
    private PDO $db;
    private SampleMapper $mapper;

    public function __construct(PDO $db, ?SampleMapper $mapper = null)
    {
        $this->db = $db;
        $this->mapper = $mapper ?? new SampleMapper();
    }

    public function getById(int $sampleId): ?Sample
    {
        $stmt = $this->db->prepare("SELECT * FROM samples WHERE id = ?");
        $stmt->execute([$sampleId]);
        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ? $this->mapper->mapRowToEntity($row) : null;
    }

    public function createSample(Sample $sample): int
    {
        $stmt = $this->db->prepare(
            "INSERT INTO samples (
                order_id,
                order_type_id,
                unit_cost,
                sample_type,
                compound_name,
                quantity,
                unit,
                preparation_time,
                testing_time,
                status,
                results
            ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)"
        );

        $stmt->execute([
            $sample->getOrderId(),
            $sample->getOrderTypeId(),
            $sample->getUnitCost(),
            $sample->getSampleType(),
            $sample->getCompoundName(),
            $sample->getQuantity(),
            $sample->getUnit(),
            $sample->getPreparationTime(),
            $sample->getTestingTime(),
            $sample->getStatus(),
            $sample->getResults(),
        ]);

        return (int)$this->db->lastInsertId();
    }

    public function updateSample(Sample $sample): bool
    {
        if ($sample->getId() === null) {
            throw new InvalidArgumentException('Cannot update sample without ID.');
        }

        $stmt = $this->db->prepare(
            "UPDATE samples
             SET order_id = ?,
                 order_type_id = ?,
                 unit_cost = ?,
                 sample_type = ?,
                 compound_name = ?,
                 quantity = ?,
                 unit = ?,
                 preparation_time = ?,
                 testing_time = ?,
                 status = ?,
                 results = ?,
                 updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([
            $sample->getOrderId(),
            $sample->getOrderTypeId(),
            $sample->getUnitCost(),
            $sample->getSampleType(),
            $sample->getCompoundName(),
            $sample->getQuantity(),
            $sample->getUnit(),
            $sample->getPreparationTime(),
            $sample->getTestingTime(),
            $sample->getStatus(),
            $sample->getResults(),
            $sample->getId(),
        ]);
    }

    public function updateStatus(int $sampleId, string $status): bool
    {
        ValidateSampleStatus::validate($status);

        $stmt = $this->db->prepare(
            "UPDATE samples
             SET status = ?, updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([$status, $sampleId]);
    }

    public function updateResults(int $sampleId, string $results): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE samples
             SET results = ?, updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([$results, $sampleId]);
    }

    public function updateTestingTime(int $sampleId, int $testingTime): bool
    {
        $stmt = $this->db->prepare(
            "UPDATE samples
             SET testing_time = ?, updated_at = NOW()
             WHERE id = ?"
        );

        return $stmt->execute([$testingTime, $sampleId]);
    }

    public function deleteById(int $sampleId): bool
    {
        $stmt = $this->db->prepare("DELETE FROM samples WHERE id = ?");
        return $stmt->execute([$sampleId]);
    }

    public function getByOrderId(int $orderId): array
    {
        $stmt = $this->db->prepare(
            "SELECT * FROM samples
             WHERE order_id = ?
             ORDER BY created_at ASC"
        );
        $stmt->execute([$orderId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getByStatus(string $status): array
    {
        ValidateSampleStatus::validate($status);

        $stmt = $this->db->prepare(
            "SELECT s.*, o.order_number
             FROM samples s
             JOIN orders o ON s.order_id = o.id
             WHERE s.status = ?
             ORDER BY s.created_at ASC"
        );
        $stmt->execute([$status]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getStatistics(?string $startDate = null, ?string $endDate = null): array
    {
        $sql = "SELECT status, COUNT(*) AS cnt FROM samples WHERE 1=1";
        $params = [];

        if ($startDate !== null) {
            $sql .= " AND created_at >= ?";
            $params[] = $startDate;
        }

        if ($endDate !== null) {
            $sql .= " AND created_at <= ?";
            $params[] = $endDate;
        }

        $sql .= " GROUP BY status";

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getActiveOrderType(int $orderTypeId): ?array
    {
        $stmt = $this->db->prepare(
            "SELECT cost, sample_type
             FROM order_types
             WHERE id = ? AND is_active = 1"
        );
        $stmt->execute([$orderTypeId]);

        $row = $stmt->fetch(PDO::FETCH_ASSOC);

        return $row ?: null;
    }
}
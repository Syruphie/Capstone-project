<?php
declare(strict_types=1);

/**
 * Class SampleMapper
 *
 * Maps database result rows to Sample entity objects.
 *
 * Responsibilities:
 * - Transform associative array data into Sample entities
 * - Handle type casting from database values
 * - Centralize mapping logic between persistence and domain layers
 *
 * Non-Responsibilities:
 * - No database querying
 * - No business logic or validation
 *
 * Design Notes:
 * - Ensures consistent entity hydration across the application
 * - Used exclusively by SampleRepository
 */

require_once __DIR__ . '/../Entity/Sample.php';

class SampleMapper
{
    public function mapRowToEntity(array $row): Sample
    {
        $sample = new Sample();

        $sample->setId(isset($row['id']) ? (int)$row['id'] : null);
        $sample->setOrderId((int)$row['order_id']);
        $sample->setOrderTypeId((int)$row['order_type_id']);
        $sample->setUnitCost((float)$row['unit_cost']);
        $sample->setSampleType((string)$row['sample_type']);
        $sample->setCompoundName((string)$row['compound_name']);
        $sample->setQuantity((float)$row['quantity']);
        $sample->setUnit((string)$row['unit']);
        $sample->setPreparationTime((int)$row['preparation_time']);
        $sample->setTestingTime(isset($row['testing_time']) ? (int)$row['testing_time'] : null);
        $sample->setStatus((string)$row['status']);
        $sample->setResults(isset($row['results']) ? (string)$row['results'] : null);
        $sample->setCreatedAt(isset($row['created_at']) ? (string)$row['created_at'] : null);
        $sample->setUpdatedAt(isset($row['updated_at']) ? (string)$row['updated_at'] : null);

        return $sample;
    }

    public function mapArrayToEntity(array $data): Sample
    {
        $sample = new Sample();

        if (isset($data['id'])) {
            $sample->setId($data['id'] !== null ? (int)$data['id'] : null);
        }

        if (isset($data['order_id'])) {
            $sample->setOrderId((int)$data['order_id']);
        }

        if (isset($data['order_type_id'])) {
            $sample->setOrderTypeId((int)$data['order_type_id']);
        }

        if (isset($data['unit_cost'])) {
            $sample->setUnitCost((float)$data['unit_cost']);
        }

        if (isset($data['sample_type'])) {
            $sample->setSampleType((string)$data['sample_type']);
        }

        if (isset($data['compound_name'])) {
            $sample->setCompoundName((string)$data['compound_name']);
        }

        if (isset($data['quantity'])) {
            $sample->setQuantity((float)$data['quantity']);
        }

        if (isset($data['unit'])) {
            $sample->setUnit((string)$data['unit']);
        }

        if (isset($data['preparation_time'])) {
            $sample->setPreparationTime((int)$data['preparation_time']);
        }

        if (array_key_exists('testing_time', $data)) {
            $sample->setTestingTime($data['testing_time'] !== null ? (int)$data['testing_time'] : null);
        }

        if (isset($data['status'])) {
            $sample->setStatus((string)$data['status']);
        }

        if (array_key_exists('results', $data)) {
            $sample->setResults($data['results'] !== null ? (string)$data['results'] : null);
        }

        if (array_key_exists('created_at', $data)) {
            $sample->setCreatedAt($data['created_at'] !== null ? (string)$data['created_at'] : null);
        }

        if (array_key_exists('updated_at', $data)) {
            $sample->setUpdatedAt($data['updated_at'] !== null ? (string)$data['updated_at'] : null);
        }

        return $sample;
    }
}
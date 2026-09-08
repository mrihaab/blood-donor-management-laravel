<?php

namespace App\Services\AI\ValueObjects;

use InvalidArgumentException;

class CanonicalCommandEnvelope
{
    public readonly array $canonicalInventoryIds;
    public readonly string $hash;

    public function __construct(
        public readonly int|string $snapshotId,
        public readonly int|string $requestId,
        public readonly int|string $facilityId,
        public readonly string $logicalOperationId,
        array $inventoryIds,
        public readonly int $quantity,
        public readonly string $authContextHash
    ) {
        if (empty($inventoryIds)) {
            throw new InvalidArgumentException("Inventory IDs list cannot be empty.");
        }

        $canonicalIds = [];
        foreach ($inventoryIds as $id) {
            $numId = (int) $id;
            if ($numId <= 0) {
                throw new InvalidArgumentException("Inventory ID must be a positive integer.");
            }
            if (in_array($numId, $canonicalIds, true)) {
                throw new InvalidArgumentException("Duplicate inventory ID detected: {$numId}");
            }
            $canonicalIds[] = $numId;
        }

        sort($canonicalIds, SORT_NUMERIC);
        $this->canonicalInventoryIds = $canonicalIds;

        $payload = json_encode([
            'snapshot_id' => (string) $this->snapshotId,
            'request_id' => (string) $this->requestId,
            'facility_id' => (string) $this->facilityId,
            'logical_operation_id' => (string) $this->logicalOperationId,
            'inventory_ids' => $this->canonicalInventoryIds,
            'quantity' => (int) $this->quantity,
            'auth_context_hash' => (string) $this->authContextHash,
        ], JSON_UNESCAPED_SLASHES | JSON_UNESCAPED_UNICODE);

        $this->hash = hash('sha256', $payload);
    }

    public static function computeHash(
        int|string $snapshotId,
        int|string $requestId,
        int|string $facilityId,
        string $logicalOperationId,
        array $inventoryIds,
        int $quantity,
        string $authContextHash
    ): string {
        $envelope = new self($snapshotId, $requestId, $facilityId, $logicalOperationId, $inventoryIds, $quantity, $authContextHash);
        return $envelope->hash;
    }
}

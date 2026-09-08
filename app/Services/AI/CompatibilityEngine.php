<?php

namespace App\Services\AI;

use InvalidArgumentException;

class CompatibilityEngine
{
    /**
     * Strict ABO/Rh Compatibility Matrix.
     */
    private const COMPATIBILITY_MATRIX = [
        'O-' => ['O-'],
        'O+' => ['O-', 'O+'],
        'A-' => ['O-', 'A-'],
        'A+' => ['O-', 'O+', 'A-', 'A+'],
        'B-' => ['O-', 'B-'],
        'B+' => ['O-', 'O+', 'B-', 'B+'],
        'AB-' => ['O-', 'A-', 'B-', 'AB-'],
        'AB+' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
    ];

    /**
     * Verify ABO/Rh compatibility. FAIL-HARD if incompatible.
     */
    public function verifyCompatibility(string $recipientGroup, string $donorGroup): bool
    {
        $allowedDonors = self::COMPATIBILITY_MATRIX[$recipientGroup] ?? [];
        return in_array($donorGroup, $allowedDonors, true);
    }

    /**
     * Two-Layer Tag Matching & Compatibility Audit.
     */
    public function auditUnitCompatibility(string $recipientGroup, object|array $unit): array
    {
        $donorGroup = is_array($unit) ? ($unit['blood_group'] ?? null) : ($unit->blood_group ?? null);
        $isCompatible = $this->verifyCompatibility($recipientGroup, $donorGroup);

        if (!$isCompatible) {
            return [
                'compatible' => false,
                'status' => 'FAIL_HARD_INCOMPATIBLE',
                'sop_checklist_required' => true,
                'message' => "FAIL-HARD: Donor blood group {$donorGroup} is incompatible with recipient group {$recipientGroup}.",
            ];
        }

        return [
            'compatible' => true,
            'status' => 'COMPATIBLE',
            'sop_checklist_required' => false,
            'message' => "Unit is ABO/Rh compatible.",
        ];
    }
}

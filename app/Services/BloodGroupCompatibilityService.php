<?php

namespace App\Services;

class BloodGroupCompatibilityService
{
    /**
     * Map of recipient blood group to compatible donor blood groups.
     */
    protected const COMPATIBILITY_MATRIX = [
        'O-'  => ['O-'],
        'O+'  => ['O-', 'O+'],
        'A-'  => ['O-', 'A-'],
        'A+'  => ['O-', 'O+', 'A-', 'A+'],
        'B-'  => ['O-', 'B-'],
        'B+'  => ['O-', 'O+', 'B-', 'B+'],
        'AB-' => ['O-', 'A-', 'B-', 'AB-'],
        'AB+' => ['O-', 'O+', 'A-', 'A+', 'B-', 'B+', 'AB-', 'AB+'],
    ];

    /**
     * Get compatible donor blood groups for a target recipient blood group.
     */
    public function getCompatibleDonorGroups(string $recipientGroup): array
    {
        $normalized = strtoupper(trim($recipientGroup));
        return self::COMPATIBILITY_MATRIX[$normalized] ?? [$normalized];
    }

    /**
     * Check if a donor blood group is medically compatible with a recipient blood group.
     */
    public function isCompatible(string $donorGroup, string $recipientGroup): bool
    {
        $donorNorm = strtoupper(trim($donorGroup));
        $compatible = $this->getCompatibleDonorGroups($recipientGroup);
        return in_array($donorNorm, $compatible, true);
    }
}

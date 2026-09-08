<?php

namespace App\Enums;

enum BloodUnitAvailabilityState: string
{
    case IMMEDIATELY_USABLE = 'IMMEDIATELY_USABLE';
    case TENTATIVELY_HELD = 'TENTATIVELY_HELD';
    case ALLOCATED = 'ALLOCATED';
    case QUARANTINED = 'QUARANTINED';
    case RESERVED = 'RESERVED';
}

<?php

namespace App\Enums;

enum BloodUnitLifecycleState: string
{
    case AVAILABLE = 'AVAILABLE';
    case DISPATCHED = 'DISPATCHED';
    case TRANSFUSED = 'TRANSFUSED';
    case EXPIRED = 'EXPIRED';
    case DISCARDED = 'DISCARDED';
    case RECALLED = 'RECALLED';
    case UNRECONCILED_PHYSICAL_STATE = 'UNRECONCILED_PHYSICAL_STATE';
}

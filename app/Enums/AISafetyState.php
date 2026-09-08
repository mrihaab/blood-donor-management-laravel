<?php

namespace App\Enums;

enum AISafetyState: string
{
    case CONFIDENT = 'CONFIDENT';
    case DEGRADED = 'DEGRADED';
    case COLD_START = 'COLD_START';
    case MODEL_DRIFT = 'MODEL_DRIFT';
    case DATA_UNTRUSTWORTHY = 'DATA_UNTRUSTWORTHY';
    case MODEL_UNAVAILABLE = 'MODEL_UNAVAILABLE';
}

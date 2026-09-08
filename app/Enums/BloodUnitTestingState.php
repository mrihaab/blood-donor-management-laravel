<?php

namespace App\Enums;

enum BloodUnitTestingState: string
{
    case PASSED = 'PASSED';
    case TESTING_PENDING = 'TESTING_PENDING';
    case QUARANTINED_FAILED = 'QUARANTINED_FAILED';
}

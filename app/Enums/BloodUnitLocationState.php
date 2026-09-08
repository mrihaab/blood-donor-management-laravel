<?php

namespace App\Enums;

enum BloodUnitLocationState: string
{
    case AT_ORIGIN = 'AT_ORIGIN';
    case IN_TRANSIT = 'IN_TRANSIT';
    case AT_DESTINATION = 'AT_DESTINATION';
}

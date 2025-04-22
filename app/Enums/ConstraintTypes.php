<?php

namespace App\Enums;

enum ConstraintTypes: string
{
    case WEEKDAYS = 'weekdays';
    case MONTHLY = 'monthly';
    case DATES = 'dates';
    case TIME = 'time';
}

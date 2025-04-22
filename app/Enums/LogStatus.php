<?php

namespace App\Enums;

enum LogStatus: string
{
    case ASSIGNED = 'assigned';
    case SERVED = 'served';
    case COMPLETED = 'completed';
    case CANCELLED = 'cancelled';
    case SKIPPED = 'skipped';
    case REQUEUED = 'requeued';
}

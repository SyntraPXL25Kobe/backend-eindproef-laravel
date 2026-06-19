<?php

namespace App\Enums;

enum ShiftStatus: string
{
    case Open = 'open';
    case Full = 'full';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}

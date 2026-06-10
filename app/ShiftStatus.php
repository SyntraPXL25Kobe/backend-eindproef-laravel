<?php

namespace App;

enum ShiftStatus: string
{
    case Open = 'open';
    case Full = 'full';
    case Closed = 'closed';
    case Cancelled = 'cancelled';
}

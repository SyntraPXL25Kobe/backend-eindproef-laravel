<?php

namespace App\Enums;

enum CoordinatorRegistrationStatus: string
{
    case None = 'none';
    case Pending = 'pending';
    case Approved = 'approved';
    case Rejected = 'rejected';
}

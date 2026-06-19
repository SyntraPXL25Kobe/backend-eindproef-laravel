<?php

namespace App\Enums;

enum EventVisibility: string
{
    case Public = 'public';
    case InviteOnly = 'invite_only';
}

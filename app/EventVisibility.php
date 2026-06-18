<?php

namespace App;

enum EventVisibility: string
{
    case Public = 'public';
    case InviteOnly = 'invite_only';
}

<?php

namespace App\Enums;

enum Permission: string
{
    case ViewOpenShifts = 'view open shifts';
    case ApplyForShift = 'apply for shift';
    case CancelApplication = 'cancel application';
    case ManageOwnProfile = 'manage own profile';
    case ViewOwnSchedule = 'view own schedule';

    case ManageCoordinatorProfile = 'manage coordinator profile';
    case CreateEvents = 'create events';
    case EditEvents = 'edit events';
    case DeleteEvents = 'delete events';
    case ManageZones = 'manage zones';
    case ManageShifts = 'manage shifts';
    case ReviewApplications = 'review applications';
    case ManageCheckIns = 'manage check-ins';
    case MarkNoShows = 'mark no-shows';
    case TriggerReplacements = 'trigger replacements';
    case ViewEventReports = 'view event reports';

    case ManageUsers = 'manage users';
    case ManageRoles = 'manage roles';
    case ViewGlobalReports = 'view global reports';
    case ImpersonateUsers = 'impersonate users';
    case ApproveCoordinator = 'approve coordinator';
}
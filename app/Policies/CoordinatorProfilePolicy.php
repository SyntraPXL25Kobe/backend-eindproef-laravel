<?php

namespace App\Policies;

use App\Enums\Permission;
use App\Models\CoordinatorProfile;
use App\Models\User;

class CoordinatorProfilePolicy
{
    public function view(User $user, CoordinatorProfile $profile): bool
    {
        return $user->id === $profile->user_id;
    }

    public function update(User $user, CoordinatorProfile $profile): bool
    {
        return $user->id === $profile->user_id
            && $user->can(Permission::ManageCoordinatorProfile->value);
    }
}

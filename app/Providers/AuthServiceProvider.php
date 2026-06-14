<?php

namespace App\Providers;

use App\Models\Application;
use App\Models\Assignment;
use App\Models\CoordinatorProfile;
use App\Models\Event;
use App\Models\Shift;
use App\Models\Zone;
use App\Policies\ApplicationPolicy;
use App\Policies\AssignmentPolicy;
use App\Policies\CoordinatorProfilePolicy;
use App\Policies\EventPolicy;
use App\Policies\ShiftPolicy;
use App\Policies\ZonePolicy;
use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * @var array<class-string, class-string>
     */
    protected $policies = [
        CoordinatorProfile::class => CoordinatorProfilePolicy::class,
        Event::class => EventPolicy::class,
        Zone::class => ZonePolicy::class,
        Shift::class => ShiftPolicy::class,
        Application::class => ApplicationPolicy::class,
        Assignment::class => AssignmentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();
    }
}

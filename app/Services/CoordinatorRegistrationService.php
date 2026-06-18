<?php

namespace App\Services;

use App\CoordinatorRegistrationStatus;
use App\Models\CoordinatorProfile;
use App\Models\User;
use App\Notifications\CoordinatorApprovedNotification;
use App\Notifications\CoordinatorRegistrationNotification;
use App\Notifications\CoordinatorRejectedNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Notification;

class CoordinatorRegistrationService
{
    public function register(array $data): User
    {
        return DB::transaction(function () use ($data): User {
            $user = User::create([
                'name' => $data['name'],
                'email' => $data['email'],
                'password' => $data['password'],
                'phone' => $data['phone'] ?? '-',
                'city' => $data['city'] ?? null,
                'coordinator_registration_status' => CoordinatorRegistrationStatus::Pending,
            ]);

            $user->coordinatorProfile()->create([
                'organisation_name' => $data['organisation_name'],
                'vat_number' => $data['vat_number'] ?? null,
                'website' => $data['website'] ?? null,
                'city' => $data['city'] ?? null,
            ]);

            $admins = User::role('admin')->get();

            if ($admins->isNotEmpty()) {
                Notification::send($admins, new CoordinatorRegistrationNotification($user));
            }

            return $user;
        });
    }

    public function approve(User $user): void
    {
        DB::transaction(function () use ($user): void {
            $user->coordinator_registration_status = CoordinatorRegistrationStatus::Approved;
            $user->coordinator_rejected_reason = null;
            $user->save();

            if (! $user->hasRole('coordinator')) {
                $user->assignRole('coordinator');
            }

            if (! $user->coordinatorProfile()->exists()) {
                CoordinatorProfile::create([
                    'user_id' => $user->id,
                    'organisation_name' => $user->name,
                ]);
            }

            $user->notify(new CoordinatorApprovedNotification);
        });
    }

    public function reject(User $user, ?string $reason): void
    {
        DB::transaction(function () use ($user, $reason): void {
            $user->coordinator_registration_status = CoordinatorRegistrationStatus::Rejected;
            $user->coordinator_rejected_reason = $reason;
            $user->save();

            if ($user->hasRole('coordinator')) {
                $user->removeRole('coordinator');
            }

            $user->notify(new CoordinatorRejectedNotification($reason));
        });
    }
}

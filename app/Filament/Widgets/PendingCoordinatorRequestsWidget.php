<?php

namespace App\Filament\Widgets;

use App\Enums\CoordinatorRegistrationStatus;
use App\Filament\Resources\CoordinatorRequests\CoordinatorRequestResource;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class PendingCoordinatorRequestsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $pendingCount = User::query()
            ->where('coordinator_registration_status', CoordinatorRegistrationStatus::Pending->value)
            ->count();

        return [
            Stat::make('Pending coordinator aanvragen', (string) $pendingCount)
                ->description('Openstaande aanvragen om te beoordelen')
                ->url(CoordinatorRequestResource::getUrl('index'))
                ->color($pendingCount > 0 ? 'warning' : 'success'),
        ];
    }
}

<?php

namespace App\Filament\Widgets;

use App\Models\Assignment;
use App\Models\Event;
use App\Models\Shift;
use App\Models\User;
use Filament\Widgets\StatsOverviewWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class GlobalStatsWidget extends StatsOverviewWidget
{
    protected function getStats(): array
    {
        $totalUsers = User::query()->count();
        $activeEvents = Event::query()
            ->where('status', 'published')
            ->count();

        $shiftCount = Shift::query()->count();
        $filledShiftCount = Shift::query()
            ->whereHas('applications', fn ($query) => $query->where('status', 'approved'))
            ->count();

        $checkedAssignments = Assignment::query()->where('no_show', false)->count();
        $noShows = Assignment::query()->where('no_show', true)->count();
        $noShowRate = ($checkedAssignments + $noShows) > 0
            ? round(($noShows / ($checkedAssignments + $noShows)) * 100, 1)
            : 0;

        $fillRate = $shiftCount > 0
            ? round(($filledShiftCount / $shiftCount) * 100, 1)
            : 0;

        return [
            Stat::make('Totaal gebruikers', (string) $totalUsers),
            Stat::make('Actieve events', (string) $activeEvents),
            Stat::make('Shifts ingevuld', $fillRate.'%'),
            Stat::make('No-show rate', $noShowRate.'%'),
        ];
    }
}

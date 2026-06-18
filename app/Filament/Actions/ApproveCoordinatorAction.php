<?php

namespace App\Filament\Actions;

use App\Enums\Permission;
use App\Models\User;
use App\Services\CoordinatorRegistrationService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;

class ApproveCoordinatorAction
{
    public static function make(): Action
    {
        return Action::make('approve')
            ->label('Goedkeuren')
            ->color('success')
            ->icon('heroicon-o-check-circle')
            ->visible(fn (): bool => auth()->user()?->can(Permission::ApproveCoordinator->value) ?? false)
            ->requiresConfirmation()
            ->action(function (User $record, CoordinatorRegistrationService $service): void {
                $service->approve($record);

                Notification::make()
                    ->title('Coordinator aanvraag goedgekeurd')
                    ->success()
                    ->send();
            });
    }
}

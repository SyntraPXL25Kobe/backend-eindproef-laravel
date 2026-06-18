<?php

namespace App\Filament\Actions;

use App\Enums\Permission;
use App\Models\User;
use App\Services\CoordinatorRegistrationService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Notifications\Notification;

class RejectCoordinatorAction
{
    public static function make(): Action
    {
        return Action::make('reject')
            ->label('Afwijzen')
            ->color('danger')
            ->icon('heroicon-o-x-circle')
            ->visible(fn (): bool => auth()->user()?->can(Permission::ApproveCoordinator->value) ?? false)
            ->schema([
                Textarea::make('reason')
                    ->label('Reden (optioneel)')
                    ->maxLength(1000),
            ])
            ->action(function (User $record, array $data, CoordinatorRegistrationService $service): void {
                $service->reject($record, $data['reason'] ?? null);

                Notification::make()
                    ->title('Coordinator aanvraag afgewezen')
                    ->success()
                    ->send();
            });
    }
}

<?php

namespace App\Filament\Resources\CoordinatorProfiles\Pages;

use App\Filament\Resources\CoordinatorProfiles\CoordinatorProfileResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

class EditCoordinatorProfile extends EditRecord
{
    protected static string $resource = CoordinatorProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CoordinatorProfiles\Pages;

use App\Filament\Resources\CoordinatorProfiles\CoordinatorProfileResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCoordinatorProfile extends ViewRecord
{
    protected static string $resource = CoordinatorProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

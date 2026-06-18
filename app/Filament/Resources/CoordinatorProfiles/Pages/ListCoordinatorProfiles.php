<?php

namespace App\Filament\Resources\CoordinatorProfiles\Pages;

use App\Filament\Resources\CoordinatorProfiles\CoordinatorProfileResource;
use Filament\Actions\CreateAction;
use Filament\Resources\Pages\ListRecords;

class ListCoordinatorProfiles extends ListRecords
{
    protected static string $resource = CoordinatorProfileResource::class;

    protected function getHeaderActions(): array
    {
        return [
            CreateAction::make(),
        ];
    }
}

<?php

namespace App\Filament\Resources\CoordinatorRequests\Pages;

use App\Filament\Actions\ApproveCoordinatorAction;
use App\Filament\Actions\RejectCoordinatorAction;
use App\Filament\Resources\CoordinatorRequests\CoordinatorRequestResource;
use Filament\Resources\Pages\ViewRecord;

class ViewCoordinatorRequest extends ViewRecord
{
    protected static string $resource = CoordinatorRequestResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ApproveCoordinatorAction::make(),
            RejectCoordinatorAction::make(),
        ];
    }
}

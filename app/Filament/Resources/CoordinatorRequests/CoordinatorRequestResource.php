<?php

namespace App\Filament\Resources\CoordinatorRequests;

use App\CoordinatorRegistrationStatus;
use App\Enums\Permission;
use App\Filament\Resources\CoordinatorRequests\Pages\ListCoordinatorRequests;
use App\Filament\Resources\CoordinatorRequests\Tables\CoordinatorRequestsTable;
use App\Models\User;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;

class CoordinatorRequestResource extends Resource
{
    protected static ?string $model = User::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static ?string $navigationLabel = 'Coordinator Aanvragen';

    protected static ?string $modelLabel = 'Coordinator aanvraag';

    protected static ?string $pluralModelLabel = 'Coordinator aanvragen';

    protected static string|\UnitEnum|null $navigationGroup = 'Beheer';

    protected static ?int $navigationSort = 2;

    protected static ?string $slug = 'coordinator-requests';

    public static function table(Table $table): Table
    {
        return CoordinatorRequestsTable::configure($table);
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoordinatorRequests::route('/'),
        ];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::ApproveCoordinator->value) ?? false;
    }

    public static function getNavigationBadge(): ?string
    {
        return (string) static::getEloquentQuery()->count();
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->where('coordinator_registration_status', CoordinatorRegistrationStatus::Pending->value)
            ->with('coordinatorProfile');
    }
}

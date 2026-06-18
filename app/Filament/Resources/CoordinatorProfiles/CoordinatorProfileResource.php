<?php

namespace App\Filament\Resources\CoordinatorProfiles;

use App\Enums\Permission;
use App\Filament\Resources\CoordinatorProfiles\Pages\CreateCoordinatorProfile;
use App\Filament\Resources\CoordinatorProfiles\Pages\EditCoordinatorProfile;
use App\Filament\Resources\CoordinatorProfiles\Pages\ListCoordinatorProfiles;
use App\Filament\Resources\CoordinatorProfiles\Pages\ViewCoordinatorProfile;
use App\Filament\Resources\CoordinatorProfiles\Schemas\CoordinatorProfileForm;
use App\Filament\Resources\CoordinatorProfiles\Schemas\CoordinatorProfileInfolist;
use App\Filament\Resources\CoordinatorProfiles\Tables\CoordinatorProfilesTable;
use App\Models\CoordinatorProfile;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class CoordinatorProfileResource extends Resource
{
    protected static ?string $model = CoordinatorProfile::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static ?string $navigationLabel = 'Coordinator Profielen';

    protected static string|\UnitEnum|null $navigationGroup = 'Beheer';

    protected static ?int $navigationSort = 3;

    public static function form(Schema $schema): Schema
    {
        return CoordinatorProfileForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CoordinatorProfileInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CoordinatorProfilesTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::ApproveCoordinator->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::ApproveCoordinator->value) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can(Permission::ApproveCoordinator->value) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can(Permission::ApproveCoordinator->value) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCoordinatorProfiles::route('/'),
            'create' => CreateCoordinatorProfile::route('/create'),
            'view' => ViewCoordinatorProfile::route('/{record}'),
            'edit' => EditCoordinatorProfile::route('/{record}/edit'),
        ];
    }
}

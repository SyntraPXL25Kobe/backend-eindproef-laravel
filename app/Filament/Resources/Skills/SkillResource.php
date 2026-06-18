<?php

namespace App\Filament\Resources\Skills;

use App\Enums\Permission;
use App\Filament\Resources\Skills\Pages\CreateSkill;
use App\Filament\Resources\Skills\Pages\EditSkill;
use App\Filament\Resources\Skills\Pages\ListSkills;
use App\Filament\Resources\Skills\Pages\ViewSkill;
use App\Filament\Resources\Skills\Schemas\SkillForm;
use App\Filament\Resources\Skills\Schemas\SkillInfolist;
use App\Filament\Resources\Skills\Tables\SkillsTable;
use App\Models\Skill;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;

class SkillResource extends Resource
{
    protected static ?string $model = Skill::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSparkles;

    protected static ?string $navigationLabel = 'Skills';

    protected static string|\UnitEnum|null $navigationGroup = 'Beheer';

    protected static ?int $navigationSort = 4;

    public static function form(Schema $schema): Schema
    {
        return SkillForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return SkillInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return SkillsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [];
    }

    public static function canViewAny(): bool
    {
        return auth()->user()?->can(Permission::ManageRoles->value) ?? false;
    }

    public static function canCreate(): bool
    {
        return auth()->user()?->can(Permission::ManageRoles->value) ?? false;
    }

    public static function canEdit($record): bool
    {
        return auth()->user()?->can(Permission::ManageRoles->value) ?? false;
    }

    public static function canDelete($record): bool
    {
        return auth()->user()?->can(Permission::ManageRoles->value) ?? false;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListSkills::route('/'),
            'create' => CreateSkill::route('/create'),
            'view' => ViewSkill::route('/{record}'),
            'edit' => EditSkill::route('/{record}/edit'),
        ];
    }
}

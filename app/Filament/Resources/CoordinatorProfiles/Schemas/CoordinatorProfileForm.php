<?php

namespace App\Filament\Resources\CoordinatorProfiles\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class CoordinatorProfileForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('user_id')
                    ->relationship('user', 'name')
                    ->required(),
                TextInput::make('organisation_name')
                    ->required(),
                TextInput::make('vat_number'),
                TextInput::make('address'),
                TextInput::make('city'),
                TextInput::make('postal_code'),
                TextInput::make('country')
                    ->required()
                    ->default('België'),
                TextInput::make('website')
                    ->url(),
            ]);
    }
}

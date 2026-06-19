<?php

namespace App\Filament\Resources\Users\Schemas;

use App\Enums\CoordinatorRegistrationStatus;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class UserForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                TextInput::make('email')
                    ->label('Email address')
                    ->email()
                    ->required()
                    ->maxLength(255),
                DateTimePicker::make('email_verified_at'),
                TextInput::make('password')
                    ->password()
                    ->required(fn (string $operation): bool => $operation === 'create')
                    ->dehydrated(fn (?string $state): bool => filled($state))
                    ->minLength(8),
                TextInput::make('phone')
                    ->tel()
                    ->required()
                    ->maxLength(50),
                TextInput::make('address')
                    ->maxLength(255),
                TextInput::make('postal_code')
                    ->maxLength(20),
                TextInput::make('city')
                    ->maxLength(100),
                TextInput::make('country')
                    ->maxLength(100),
                Toggle::make('is_active')
                    ->required(),
                Select::make('coordinator_registration_status')
                    ->options(CoordinatorRegistrationStatus::class)
                    ->default(CoordinatorRegistrationStatus::None)
                    ->required(),
                Textarea::make('coordinator_rejected_reason')
                    ->columnSpanFull()
                    ->maxLength(1000),
                Select::make('roles')
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),
            ]);
    }
}

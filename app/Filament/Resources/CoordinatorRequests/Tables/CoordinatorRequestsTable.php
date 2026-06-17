<?php

namespace App\Filament\Resources\CoordinatorRequests\Tables;

use App\Filament\Actions\ApproveCoordinatorAction;
use App\Filament\Actions\RejectCoordinatorAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CoordinatorRequestsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label('Naam')
                    ->searchable(),
                TextColumn::make('email')
                    ->searchable(),
                TextColumn::make('coordinatorProfile.organisation_name')
                    ->label('Organisatie')
                    ->searchable(),
                TextColumn::make('coordinatorProfile.vat_number')
                    ->label('BTW-nummer')
                    ->toggleable(),
                TextColumn::make('created_at')
                    ->label('Aangevraagd op')
                    ->dateTime()
                    ->sortable(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ApproveCoordinatorAction::make(),
                RejectCoordinatorAction::make(),
            ]);
    }
}

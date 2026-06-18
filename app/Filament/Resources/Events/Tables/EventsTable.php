<?php

namespace App\Filament\Resources\Events\Tables;

use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TrashedFilter;
use Filament\Tables\Table;

class EventsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('coordinatorProfile.organisation_name')
                    ->label('Organisatie')
                    ->searchable(),
                TextColumn::make('location')
                    ->searchable(),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('start_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('end_date')
                    ->date()
                    ->sortable(),
                TextColumn::make('max_crew_members')
                    ->numeric()
                    ->label('Max vrijwilligers')
                    ->sortable(),
                TextColumn::make('zones_count')
                    ->counts('zones')
                    ->label('Zones'),
                TextColumn::make('shifts_count')
                    ->counts('shifts')
                    ->label('Shifts'),
            ])
            ->filters([
                TrashedFilter::make(),
            ])
            ->defaultSort('created_at', 'desc')
            ->recordActions([
                ViewAction::make(),
            ]);
    }
}

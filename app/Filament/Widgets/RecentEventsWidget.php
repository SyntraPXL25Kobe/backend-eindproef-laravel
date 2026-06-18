<?php

namespace App\Filament\Widgets;

use App\Models\Event;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

class RecentEventsWidget extends TableWidget
{
    public function table(Table $table): Table
    {
        return $table
            ->query(
                Event::query()
                    ->with('coordinatorProfile')
                    ->latest()
                    ->limit(5)
            )
            ->columns([
                TextColumn::make('title')
                    ->searchable(),
                TextColumn::make('coordinatorProfile.organisation_name')
                    ->label('Organisatie'),
                TextColumn::make('status')
                    ->badge(),
                TextColumn::make('start_date')
                    ->date(),
            ]);
    }
}

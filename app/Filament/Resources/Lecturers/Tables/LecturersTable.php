<?php

namespace App\Filament\Resources\Lecturers\Tables;

use App\Models\Lecturer;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class LecturersTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('full_name')
                    ->label('Jméno lektora')
                    ->state(fn (Lecturer $record): string => "{$record->first_name} {$record->last_name}")
                    ->searchable(['first_name', 'last_name'])
                    ->sortable(['last_name'])
                    ->weight('bold'),
                TextColumn::make('email')
                    ->label('E-mail')
                    ->searchable()
                    ->copyable(),
                TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable(),
                TextColumn::make('specialization')
                    ->label('Specializace')
                    ->badge()
                    ->color('info')
                    ->searchable(),
                TextColumn::make('total_hours')
                    ->label('Odučeno hodin')
                    ->state(fn (Lecturer $record): int => (int) $record->lessons()->sum('duration_hours'))
                    ->formatStateUsing(fn ($state) => "{$state} h")
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label('Zobrazit'),
                EditAction::make()->label('Upravit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

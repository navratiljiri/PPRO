<?php

namespace App\Filament\Resources\Courses\Tables;

use App\Models\Course;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class CoursesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('code')
                    ->label('Kód kurzu')
                    ->badge()
                    ->color('gray')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('name')
                    ->label('Název kurzu')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('duration_hours')
                    ->label('Rozsah')
                    ->formatStateUsing(fn ($state): string => Course::formatHours((int) $state))
                    ->sortable(),
                TextColumn::make('price')
                    ->label('Základní cena')
                    ->money('CZK', locale: 'cs_CZ')
                    ->sortable(),
                IconColumn::make('is_accredited')
                    ->label('Akreditace')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->falseColor('gray'),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktivní',
                        'archived' => 'Archivováno',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'archived' => 'danger',
                        default => 'secondary',
                    }),
                TextColumn::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d. m. Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stav kurzu')
                    ->options([
                        'active' => 'Aktivní',
                        'archived' => 'Archivovaný',
                    ]),
                TernaryFilter::make('is_accredited')
                    ->label('Pouze akreditované'),
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

<?php

namespace App\Filament\Resources\Lessons\Tables;

use App\Models\Lesson;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class LessonsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('courseTerm.course.name')
                    ->label('Kurz')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('topic')
                    ->label('Téma lekce')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('lecturer.full_name')
                    ->label('Vyučující lektor')
                    ->state(fn (Lesson $record): string => $record->lecturer ? "{$record->lecturer->first_name} {$record->lecturer->last_name}" : '—')
                    ->searchable()
                    ->badge()
                    ->color('info'),
                TextColumn::make('starts_at')
                    ->label('Začátek')
                    ->dateTime('d. m. Y H:i')
                    ->sortable(),
                TextColumn::make('ends_at')
                    ->label('Konec')
                    ->dateTime('d. m. Y H:i')
                    ->sortable(),
                TextColumn::make('duration_hours')
                    ->label('Délka')
                    ->formatStateUsing(fn ($state) => "{$state} h")
                    ->sortable()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('lecturer_id')
                    ->label('Lektor')
                    ->relationship('lecturer', 'last_name'),
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

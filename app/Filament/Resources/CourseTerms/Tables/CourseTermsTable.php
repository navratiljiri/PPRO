<?php

namespace App\Filament\Resources\CourseTerms\Tables;

use App\Filament\Resources\CourseTerms\CourseTermResource;
use Filament\Actions\Action;
use App\Models\CourseTerm;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class CourseTermsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('course.name')
                    ->label('Kurz')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('start_date')
                    ->label('Zahájení')
                    ->dateTime('d. m. Y H:i')
                    ->sortable(),
                TextColumn::make('end_date')
                    ->label('Konec')
                    ->dateTime('d. m. Y H:i')
                    ->sortable(),
                TextColumn::make('classroom')
                    ->label('Učebna')
                    ->searchable(),
                TextColumn::make('capacity')
                    ->label('Kapacita')
                    ->numeric()
                    ->sortable()
                    ->alignCenter(),
                TextColumn::make('occupancy_info')
                    ->label('Obsazenost')
                    ->state(function (CourseTerm $record): string {
                        $occupied = $record->activeRegistrations()->count();
                        $pct = $record->capacity > 0 ? round(($occupied / $record->capacity) * 100) : 0;
                        return "{$occupied} / {$record->capacity} ({$pct} %)";
                    })
                    ->badge()
                    ->color(function (CourseTerm $record): string {
                        $occupied = $record->activeRegistrations()->count();
                        if ($occupied >= $record->capacity) {
                            return 'danger';
                        }
                        if ($occupied >= $record->capacity * 0.7) {
                            return 'warning';
                        }
                        return 'success';
                    }),
                TextColumn::make('waiting_count')
                    ->label('V pořadníku')
                    ->state(fn (CourseTerm $record): int => $record->waitingRegistrations()->count())
                    ->badge()
                    ->color(fn (int $state): string => $state > 0 ? 'warning' : 'gray')
                    ->alignCenter(),
                TextColumn::make('status')
                    ->label('Stav')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'enrolling' => 'Zápis otevřen',
                        'in_progress' => 'Probíhá',
                        'completed' => 'Dokončeno',
                        'cancelled' => 'Zrušeno',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'enrolling' => 'success',
                        'in_progress' => 'warning',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'secondary',
                    }),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stav termínu')
                    ->options([
                        'enrolling' => 'Zápis otevřen',
                        'in_progress' => 'Probíhá',
                        'completed' => 'Dokončeno',
                        'cancelled' => 'Zrušeno',
                    ]),
                SelectFilter::make('course_id')
                    ->label('Kurz')
                    ->relationship('course', 'name'),
            ])
            ->recordActions([
                ViewAction::make()->label('Zobrazit'),
                EditAction::make()->label('Upravit'),
                Action::make('graduates')
                    ->label('Absolventi')
                    ->icon('heroicon-o-academic-cap')
                    ->color('success')
                    ->url(fn (CourseTerm $record): string => CourseTermResource::getUrl('graduates', ['record' => $record])),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

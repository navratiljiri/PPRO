<?php

namespace App\Filament\Resources\AttendanceRecords\Tables;

use App\Models\AttendanceRecord;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;

class AttendanceRecordsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('registration.student.full_name')
                    ->label('Student')
                    ->state(fn (AttendanceRecord $record): string => $record->registration?->student ? "{$record->registration->student->first_name} {$record->registration->student->last_name}" : '—')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('lesson.courseTerm.course.name')
                    ->label('Kurz')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('lesson.topic')
                    ->label('Lekce / Téma')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('lesson.starts_at')
                    ->label('Datum lekce')
                    ->dateTime('d. m. Y H:i')
                    ->sortable(),
                IconColumn::make('is_present')
                    ->label('Účast')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->alignCenter(),
                TextColumn::make('note')
                    ->label('Poznámka / Omluvenka')
                    ->placeholder('—'),
            ])
            ->filters([
                TernaryFilter::make('is_present')
                    ->label('Filtr účasti')
                    ->placeholder('Všichni')
                    ->trueLabel('Pouze přítomní')
                    ->falseLabel('Pouze nepřítomní'),
            ])
            ->recordActions([
                ViewAction::make()->label('Detail'),
                EditAction::make()->label('Zapsat / Upravit'),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Certificates\Tables;

use App\Models\Certificate;
use Filament\Actions\ViewAction;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class CertificatesTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('certificate_number')
                    ->label('Číslo osvědčení')
                    ->badge()
                    ->color('success')
                    ->fontFamily('mono')
                    ->weight('bold')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('registration.student.full_name')
                    ->label('Absolvent')
                    ->state(fn (Certificate $record): string => $record->registration?->student ? "{$record->registration->student->first_name} {$record->registration->student->last_name}" : '—')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('registration.courseTerm.course.name')
                    ->label('Absolvovaný kurz')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('issue_date')
                    ->label('Datum vydání')
                    ->date('d. m. Y')
                    ->sortable(),
                IconColumn::make('lecturer_approved')
                    ->label('Schváleno lektorem')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-badge')
                    ->falseIcon('heroicon-o-x-mark')
                    ->trueColor('success')
                    ->alignCenter(),
                TextColumn::make('created_at')
                    ->label('Zapsáno v matrice')
                    ->dateTime('d. m. Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                //
            ])
            ->recordActions([
                ViewAction::make()->label('Zobrazit'),
            ])
            ->toolbarActions([]);
    }
}

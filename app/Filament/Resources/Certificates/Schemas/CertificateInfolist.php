<?php

namespace App\Filament\Resources\Certificates\Schemas;

use App\Models\Certificate;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CertificateInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('certificate_number')
                    ->label('Číslo osvědčení')
                    ->weight('bold')
                    ->copyable(),
                TextEntry::make('registration.student.full_name')
                    ->label('Absolvent')
                    ->placeholder('—'),
                TextEntry::make('course_label')
                    ->label('Absolvovaný kurz')
                    ->state(fn (Certificate $record): string => $record->registration?->courseTerm
                        ? "{$record->registration->courseTerm->course?->name} ({$record->registration->courseTerm->formatted_date_range})"
                        : '—'),
                TextEntry::make('issue_date')
                    ->label('Datum vydání')
                    ->date('d. m. Y'),
                IconEntry::make('lecturer_approved')
                    ->label('Schváleno lektorem')
                    ->boolean(),
                TextEntry::make('created_at')
                    ->label('Zapsáno v matrice')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\Registration;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RegistrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('course_term_label')
                    ->label('Termín kurzu')
                    ->state(fn (Registration $record): string => $record->courseTerm
                        ? "{$record->courseTerm->course?->name} ({$record->courseTerm->classroom}, {$record->courseTerm->formatted_date_range})"
                        : '—'),
                TextEntry::make('student.full_name')
                    ->label('Student / Účastník')
                    ->placeholder('—'),
                TextEntry::make('status')
                    ->label('Stav přihlášky')
                    ->badge()
                    ->formatStateUsing(fn (Registration $record): string => $record->status_label)
                    ->color(fn (Registration $record): string => $record->status_color),
                TextEntry::make('waiting_order')
                    ->label('Pořadí v pořadníku')
                    ->numeric()
                    ->placeholder('—'),
                TextEntry::make('attendance_percentage')
                    ->label('Docházka')
                    ->state(fn (Registration $record): string => "{$record->attendance_percentage} %"),
                TextEntry::make('offer_expires_at')
                    ->label('Nabídka místa vyprší')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
                TextEntry::make('cancellation_reason')
                    ->label('Důvod zrušení')
                    ->placeholder('—')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->label('Podáno dne')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
                TextEntry::make('updated_at')
                    ->label('Naposledy upraveno')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
            ]);
    }
}

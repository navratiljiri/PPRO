<?php

namespace App\Filament\Resources\AttendanceRecords\Schemas;

use App\Models\AttendanceRecord;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AttendanceRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('registration.student.full_name')
                    ->label('Přihlášený student')
                    ->placeholder('—'),
                TextEntry::make('lesson_label')
                    ->label('Výuková lekce')
                    ->state(fn (AttendanceRecord $record): string => $record->lesson
                        ? "{$record->lesson->starts_at?->format('d. m. Y H:i')} – {$record->lesson->topic} ({$record->lesson->courseTerm?->course?->name})"
                        : '—'),
                IconEntry::make('is_present')
                    ->label('Účast')
                    ->boolean(),
                TextEntry::make('note')
                    ->label('Poznámka / Omluvenka lektora')
                    ->placeholder('—'),
                TextEntry::make('created_at')
                    ->label('Zapsáno')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
                TextEntry::make('updated_at')
                    ->label('Naposledy upraveno')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
            ]);
    }
}

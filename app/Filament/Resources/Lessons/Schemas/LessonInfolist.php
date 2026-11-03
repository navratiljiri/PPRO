<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Models\Lesson;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LessonInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('course_term_label')
                    ->label('Termín kurzu')
                    ->state(fn (Lesson $record): string => $record->courseTerm
                        ? "{$record->courseTerm->course?->name} ({$record->courseTerm->classroom}, {$record->courseTerm->formatted_date_range})"
                        : '—'),
                TextEntry::make('lecturer.full_name')
                    ->label('Vyučující lektor')
                    ->placeholder('—'),
                TextEntry::make('starts_at')
                    ->label('Začátek lekce')
                    ->dateTime('d. m. Y H:i'),
                TextEntry::make('ends_at')
                    ->label('Konec lekce')
                    ->dateTime('d. m. Y H:i'),
                TextEntry::make('duration_hours')
                    ->label('Délka výuky')
                    ->numeric()
                    ->suffix(' vyuč. hodin'),
                TextEntry::make('topic')
                    ->label('Téma výuky / Osnova'),
                TextEntry::make('created_at')
                    ->label('Vytvořeno')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
                TextEntry::make('updated_at')
                    ->label('Naposledy upraveno')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
            ]);
    }
}

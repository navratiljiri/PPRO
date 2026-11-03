<?php

namespace App\Filament\Resources\CourseTerms\Schemas;

use App\Models\CourseTerm;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CourseTermInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('course.name')
                    ->label('Kurz'),
                TextEntry::make('start_date')
                    ->label('Datum a čas zahájení')
                    ->dateTime('d. m. Y H:i'),
                TextEntry::make('end_date')
                    ->label('Datum a čas ukončení')
                    ->dateTime('d. m. Y H:i'),
                TextEntry::make('classroom')
                    ->label('Učebna / Místo konání'),
                TextEntry::make('capacity')
                    ->label('Kapacita učebny (míst)')
                    ->numeric(),
                TextEntry::make('occupancy')
                    ->label('Obsazenost')
                    ->state(fn (CourseTerm $record): string => "{$record->occupied_capacity} / {$record->capacity} ({$record->occupancy_percentage} %)"),
                TextEntry::make('status')
                    ->label('Stav termínu')
                    ->badge()
                    ->formatStateUsing(fn (CourseTerm $record): string => $record->status_label)
                    ->color(fn (string $state): string => match ($state) {
                        'enrolling' => 'success',
                        'in_progress' => 'warning',
                        'completed' => 'gray',
                        'cancelled' => 'danger',
                        default => 'gray',
                    }),
                TextEntry::make('note')
                    ->label('Organizační poznámka')
                    ->placeholder('—')
                    ->columnSpanFull(),
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

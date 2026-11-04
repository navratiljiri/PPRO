<?php

namespace App\Filament\Resources\Courses\Schemas;

use App\Models\Course;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class CourseInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('code')
                    ->label('Kód kurzu'),
                TextEntry::make('name')
                    ->label('Název kurzu'),
                TextEntry::make('annotation')
                    ->label('Podrobná anotace')
                    ->columnSpanFull(),
                TextEntry::make('duration_hours')
                    ->label('Hodinový rozsah')
                    ->formatStateUsing(fn ($state): string => Course::formatHours((int) $state)),
                TextEntry::make('price')
                    ->label('Základní cena')
                    ->money('CZK', locale: 'cs'),
                IconEntry::make('is_accredited')
                    ->label('Akreditovaný kurz (MŠMT / MPSV)')
                    ->boolean(),
                TextEntry::make('status')
                    ->label('Stav kurzu')
                    ->badge()
                    ->formatStateUsing(fn (string $state): string => match ($state) {
                        'active' => 'Aktivní',
                        'archived' => 'Archivováno',
                        default => $state,
                    })
                    ->color(fn (string $state): string => match ($state) {
                        'active' => 'success',
                        'archived' => 'danger',
                        default => 'gray',
                    }),
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

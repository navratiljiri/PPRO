<?php

namespace App\Filament\Resources\Lecturers\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class LecturerInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('first_name')
                    ->label('Jméno'),
                TextEntry::make('last_name')
                    ->label('Příjmení'),
                TextEntry::make('email')
                    ->label('E-mail'),
                TextEntry::make('phone')
                    ->label('Telefon')
                    ->placeholder('—'),
                TextEntry::make('specialization')
                    ->label('Odborná specializace')
                    ->placeholder('—'),
                TextEntry::make('bio')
                    ->label('Stručný profesní medailonek')
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

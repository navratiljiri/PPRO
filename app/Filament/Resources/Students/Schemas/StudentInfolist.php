<?php

namespace App\Filament\Resources\Students\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class StudentInfolist
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
                TextEntry::make('created_at')
                    ->label('Registrován v systému')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
                TextEntry::make('updated_at')
                    ->label('Naposledy upraveno')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—'),
            ]);
    }
}

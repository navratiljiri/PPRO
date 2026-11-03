<?php

namespace App\Filament\Resources\Lecturers\Schemas;

use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class LecturerForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('first_name')
                    ->label('Jméno')
                    ->required()
                    ->maxLength(100),
                TextInput::make('last_name')
                    ->label('Příjmení')
                    ->required()
                    ->maxLength(100),
                TextInput::make('email')
                    ->label('E-mail')
                    ->email()
                    ->required()
                    ->unique(ignoreRecord: true)
                    ->maxLength(150),
                TextInput::make('phone')
                    ->label('Telefon')
                    ->tel()
                    ->maxLength(50),
                TextInput::make('specialization')
                    ->label('Odborná specializace')
                    ->placeholder('např. Účetnictví a finance, Webový vývoj')
                    ->maxLength(150),
                Textarea::make('bio')
                    ->label('Stručný profesní medailonek')
                    ->rows(3)
                    ->columnSpanFull(),
            ]);
    }
}

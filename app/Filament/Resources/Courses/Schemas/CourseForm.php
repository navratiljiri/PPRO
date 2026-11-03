<?php

namespace App\Filament\Resources\Courses\Schemas;

use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CourseForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextInput::make('code')
                    ->label('Kód kurzu')
                    ->required()
                    ->maxLength(30)
                    ->unique(ignoreRecord: true)
                    ->placeholder('např. REK-UCET-01'),
                TextInput::make('name')
                    ->label('Název kurzu')
                    ->required()
                    ->maxLength(255)
                    ->placeholder('např. Účetnictví a daňová evidence'),
                Textarea::make('annotation')
                    ->label('Podrobná anotace')
                    ->required()
                    ->rows(4)
                    ->columnSpanFull()
                    ->placeholder('Stručný popis obsahu kurzu, cílové skupiny a výstupních kompetencí'),
                TextInput::make('duration_hours')
                    ->label('Hodinový rozsah')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->suffix('vyuč. hodin'),
                TextInput::make('price')
                    ->label('Základní cena')
                    ->required()
                    ->numeric()
                    ->minValue(0)
                    ->suffix('Kč'),
                Toggle::make('is_accredited')
                    ->label('Akreditovaný kurz (MŠMT / MPSV)')
                    ->helperText('Akreditované kurzy podléhají ministerskému výkaznictví a povinné 70% docházce')
                    ->default(false),
                Select::make('status')
                    ->label('Stav kurzu')
                    ->options([
                        'active' => 'Aktivní (lze vypisovat termíny)',
                        'archived' => 'Archivovaný (uzavřeno)',
                    ])
                    ->default('active')
                    ->required(),
            ]);
    }
}

<?php

namespace App\Filament\Resources\CourseTerms\Schemas;

use App\Models\Course;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
use Filament\Schemas\Schema;

class CourseTermForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_id')
                    ->label('Kurz')
                    ->relationship('course', 'name', fn ($query) => $query->where('status', 'active'))
                    ->searchable()
                    ->preload()
                    ->required(),
                DateTimePicker::make('start_date')
                    ->label('Datum a čas zahájení')
                    ->required(),
                DateTimePicker::make('end_date')
                    ->label('Datum a čas ukončení')
                    ->required(),
                TextInput::make('classroom')
                    ->label('Učebna / Místo konání')
                    ->placeholder('např. Učebna A101 (Počítačová lab.)')
                    ->required()
                    ->maxLength(100),
                TextInput::make('capacity')
                    ->label('Kapacita učebny (míst)')
                    ->helperText('Systém přísně hlídá kapacitu. Přihlášky nad limit jsou zařazeny do FIFO pořadníku.')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->default(15),
                Select::make('status')
                    ->label('Stav termínu')
                    ->options([
                        'enrolling' => 'Zápis otevřen (přijímání přihlášek)',
                        'in_progress' => 'Probíhá (výuka běží)',
                        'completed' => 'Dokončeno (uzavřeno)',
                        'cancelled' => 'Zrušeno',
                    ])
                    ->default('enrolling')
                    ->required(),
                Textarea::make('note')
                    ->label('Organizační poznámka')
                    ->columnSpanFull(),
            ]);
    }
}

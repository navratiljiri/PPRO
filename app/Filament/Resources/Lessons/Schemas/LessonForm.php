<?php

namespace App\Filament\Resources\Lessons\Schemas;

use App\Models\CourseTerm;
use App\Models\Lecturer;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Schema;

class LessonForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_term_id')
                    ->label('Termín kurzu')
                    ->relationship('courseTerm', 'id')
                    ->getOptionLabelFromRecordUsing(fn (CourseTerm $term) => "{$term->course?->name} ({$term->classroom}, {$term->formatted_date_range})")
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('lecturer_id')
                    ->label('Vyučující lektor')
                    ->relationship('lecturer', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn (Lecturer $lecturer) => "{$lecturer->first_name} {$lecturer->last_name} ({$lecturer->specialization})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('U delších kurzů lze jednotlivé lekce přiřadit různým lektorům (střídání lektorů).'),
                DateTimePicker::make('starts_at')
                    ->label('Začátek lekce')
                    ->required(),
                DateTimePicker::make('ends_at')
                    ->label('Konec lekce')
                    ->required(),
                TextInput::make('duration_hours')
                    ->label('Délka výuky')
                    ->required()
                    ->numeric()
                    ->minValue(1)
                    ->suffix('vyuč. hodin'),
                TextInput::make('topic')
                    ->label('Téma výuky / Osnova')
                    ->required()
                    ->maxLength(255)
                    ->columnSpanFull()
                    ->placeholder('např. Blok 1: Úvod do podvojného účetnictví a rozvaha'),
            ]);
    }
}

<?php

namespace App\Filament\Resources\AttendanceRecords\Schemas;

use App\Models\Lesson;
use App\Models\Registration;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class AttendanceRecordForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('registration_id')
                    ->label('Přihlášený student')
                    ->relationship('registration', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Registration $reg) => "{$reg->student?->full_name} ({$reg->courseTerm?->course?->name})")
                    ->searchable()
                    ->preload()
                    ->required(),
                Select::make('lesson_id')
                    ->label('Výuková lekce')
                    ->relationship('lesson', 'id')
                    ->getOptionLabelFromRecordUsing(fn (Lesson $lesson) => "{$lesson->starts_at?->format('d. m. Y H:i')} – {$lesson->topic} ({$lesson->courseTerm?->course?->name})")
                    ->searchable()
                    ->preload()
                    ->required(),
                Toggle::make('is_present')
                    ->label('Účast potvrzena (přítomen/na)')
                    ->helperText('Počítá se do povinné 70% docházky pro certifikaci.')
                    ->default(true),
                TextInput::make('note')
                    ->label('Poznámka / Omluvenka lektora')
                    ->placeholder('např. Omluvená neúčast z důvodu nemoci')
                    ->maxLength(255),
            ]);
    }
}

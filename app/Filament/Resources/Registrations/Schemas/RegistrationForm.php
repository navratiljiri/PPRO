<?php

namespace App\Filament\Resources\Registrations\Schemas;

use App\Models\CourseTerm;
use App\Models\Student;
use Filament\Forms\Components\Select;
use Filament\Schemas\Schema;

class RegistrationForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('course_term_id')
                    ->label('Termín kurzu')
                    ->relationship('courseTerm', 'id')
                    ->getOptionLabelFromRecordUsing(fn (CourseTerm $term) => "{$term->course?->name} – {$term->classroom} ({$term->formatted_date_range}) [Obsazeno {$term->occupied_capacity}/{$term->capacity}]")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Pokud je termín plně obsazen, systém automaticky zařadí zájemce do FIFO pořadníku.'),
                Select::make('student_id')
                    ->label('Student / Účastník')
                    ->relationship('student', 'last_name')
                    ->getOptionLabelFromRecordUsing(fn (Student $student) => "{$student->first_name} {$student->last_name} ({$student->email})")
                    ->searchable()
                    ->preload()
                    ->required()
                    ->helperText('Systém nedovolí souběžný zápis na stejný kurz v jiném termínu (Rozhodnutí 3).'),
            ]);
    }
}

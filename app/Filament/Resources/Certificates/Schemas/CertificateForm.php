<?php

namespace App\Filament\Resources\Certificates\Schemas;

use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Schemas\Schema;

class CertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('registration_id')
                    ->label('Přihláška')
                    ->relationship('registration', 'id')
                    ->required(),
                TextInput::make('certificate_number')
                    ->label('Číslo osvědčení')
                    ->required(),
                DatePicker::make('issue_date')
                    ->label('Datum vydání')
                    ->required(),
                Toggle::make('lecturer_approved')
                    ->label('Schváleno lektorem')
                    ->required(),
            ]);
    }
}

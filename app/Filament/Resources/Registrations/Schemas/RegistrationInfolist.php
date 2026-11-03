<?php

namespace App\Filament\Resources\Registrations\Schemas;

use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class RegistrationInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('courseTerm.id')
                    ->label('Course term'),
                TextEntry::make('student.id')
                    ->label('Student'),
                TextEntry::make('status'),
                TextEntry::make('waiting_order')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('offer_expires_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('cancellation_reason')
                    ->placeholder('-')
                    ->columnSpanFull(),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

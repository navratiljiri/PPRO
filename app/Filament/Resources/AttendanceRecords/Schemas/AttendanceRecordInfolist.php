<?php

namespace App\Filament\Resources\AttendanceRecords\Schemas;

use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Schema;

class AttendanceRecordInfolist
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('registration.id')
                    ->label('Registration'),
                TextEntry::make('lesson.id')
                    ->label('Lesson'),
                IconEntry::make('is_present')
                    ->boolean(),
                TextEntry::make('note')
                    ->placeholder('-'),
                TextEntry::make('created_at')
                    ->dateTime()
                    ->placeholder('-'),
                TextEntry::make('updated_at')
                    ->dateTime()
                    ->placeholder('-'),
            ]);
    }
}

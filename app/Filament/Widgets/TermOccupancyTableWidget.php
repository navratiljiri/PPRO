<?php

namespace App\Filament\Widgets;

use App\Models\CourseTerm;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;

class TermOccupancyTableWidget extends BaseWidget
{
    protected static ?int $sort = 2;

    protected static ?string $heading = 'Naplněnost a kapacity aktuálních termínů (Statistika klienta)';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->query(
                CourseTerm::query()
                    ->whereIn('status', ['enrolling', 'in_progress'])
                    ->with(['course', 'activeRegistrations', 'waitingRegistrations'])
                    ->orderBy('start_date', 'asc')
            )
            ->columns([
                TextColumn::make('course.name')
                    ->label('Kurz')
                    ->weight('bold'),
                TextColumn::make('classroom')
                    ->label('Učebna'),
                TextColumn::make('formatted_date_range')
                    ->label('Období konání'),
                TextColumn::make('capacity')
                    ->label('Kapacita')
                    ->alignCenter(),
                TextColumn::make('occupancy')
                    ->label('Obsazeno')
                    ->state(function (CourseTerm $record): string {
                        $occupied = $record->activeRegistrations->count();
                        $pct = $record->capacity > 0 ? round(($occupied / $record->capacity) * 100) : 0;
                        return "{$occupied} / {$record->capacity} ({$pct} %)";
                    })
                    ->badge()
                    ->color(function (CourseTerm $record): string {
                        $occupied = $record->activeRegistrations->count();
                        if ($occupied >= $record->capacity) {
                            return 'danger';
                        }
                        return 'success';
                    }),
                TextColumn::make('waiting')
                    ->label('Náhradníci v FIFO pořadníku')
                    ->state(fn (CourseTerm $record): int => $record->waitingRegistrations->count())
                    ->badge()
                    ->color(fn ($state) => $state > 0 ? 'warning' : 'gray')
                    ->alignCenter(),
            ])
            ->paginated(false);
    }
}

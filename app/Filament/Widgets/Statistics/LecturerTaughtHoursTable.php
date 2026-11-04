<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Lecturer;
use App\Services\StatisticService;
use Carbon\Carbon;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Taught hours per lecturer in a chosen period (Rozhodnutí 5 – hours counted per lesson).
 */
class LecturerTaughtHoursTable extends TableWidget
{
    protected static bool $isLazy = false;

    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Odučené hodiny lektorů ve zvoleném období';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(static::$heading)
            ->records(fn (array $filters): array => $this->rows($filters['period'] ?? []))
            ->columns([
                TextColumn::make('lecturer_name')
                    ->label('Lektor')
                    ->weight('bold'),
                TextColumn::make('specialization')
                    ->label('Specializace')
                    ->placeholder('—'),
                TextColumn::make('lesson_count')
                    ->label('Počet lekcí')
                    ->alignCenter(),
                TextColumn::make('hours')
                    ->label('Odučené hodiny')
                    ->badge()
                    ->color(fn (array $record): string => $record['hours_value'] > 0 ? 'success' : 'gray')
                    ->alignEnd(),
            ])
            ->filters([
                Filter::make('period')
                    ->schema([
                        DatePicker::make('from')
                            ->label('Od')
                            ->default(now()->startOfYear()),
                        DatePicker::make('to')
                            ->label('Do')
                            ->default(now()->endOfYear()),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->emptyStateHeading('V evidenci nejsou žádní lektoři')
            ->paginated(false);
    }

    /**
     * @param  array{from?: string|null, to?: string|null}  $period
     * @return array<int, array<string, mixed>>
     */
    private function rows(array $period): array
    {
        $from = filled($period['from'] ?? null) ? Carbon::parse($period['from'])->startOfDay() : null;
        $to = filled($period['to'] ?? null) ? Carbon::parse($period['to'])->endOfDay() : null;

        return app(StatisticService::class)
            ->getLecturerTaughtHours($from, $to)
            ->sortByDesc('period_taught_hours')
            ->mapWithKeys(fn (Lecturer $lecturer): array => [
                $lecturer->id => [
                    'lecturer_name' => $lecturer->full_name,
                    'specialization' => $lecturer->specialization,
                    'lesson_count' => $lecturer->lesson_count,
                    'hours_value' => $lecturer->period_taught_hours,
                    'hours' => "{$lecturer->period_taught_hours} h",
                ],
            ])
            ->all();
    }
}

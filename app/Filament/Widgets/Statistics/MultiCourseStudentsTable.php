<?php

namespace App\Filament\Widgets\Statistics;

use App\Models\Registration;
use App\Models\Student;
use App\Services\StatisticService;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget;

/**
 * Students registered in a given year for more than one (distinct) course.
 */
class MultiCourseStudentsTable extends TableWidget
{
    protected static bool $isLazy = false;

    protected static bool $isDiscovered = false;

    protected static ?string $heading = 'Studenti přihlášení v daném roce na více než jeden kurz';

    protected int|string|array $columnSpan = 'full';

    public function table(Table $table): Table
    {
        return $table
            ->heading(static::$heading)
            ->records(fn (array $filters): array => $this->rows((int) ($filters['year']['value'] ?? now()->year)))
            ->columns([
                TextColumn::make('student_name')
                    ->label('Student')
                    ->weight('bold')
                    ->description(fn (array $record): string => $record['email']),
                TextColumn::make('courses_count')
                    ->label('Počet kurzů')
                    ->badge()
                    ->color('info')
                    ->alignCenter(),
                TextColumn::make('courses')
                    ->label('Kurzy')
                    ->wrap(),
            ])
            ->filters([
                SelectFilter::make('year')
                    ->label('Rok')
                    ->options(fn (): array => $this->yearOptions())
                    ->default((string) now()->year)
                    ->selectablePlaceholder(false),
            ], layout: FiltersLayout::AboveContent)
            ->deferFilters(false)
            ->emptyStateHeading('V tomto roce není žádný student přihlášen na více kurzů')
            ->paginated(false);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function rows(int $year): array
    {
        return app(StatisticService::class)
            ->getMultiCourseStudents($year)
            ->mapWithKeys(fn (Student $student): array => [
                $student->id => [
                    'student_name' => $student->full_name,
                    'email' => $student->email,
                    'courses_count' => $student->distinct_courses_count,
                    'courses' => $student->registrations
                        ->map(fn ($reg) => $reg->courseTerm?->course?->name)
                        ->filter()
                        ->unique()
                        ->implode(', '),
                ],
            ])
            ->all();
    }

    /**
     * @return array<string, string>
     */
    private function yearOptions(): array
    {
        $years = Registration::query()
            ->selectRaw('DISTINCT ' . (Registration::query()->getConnection()->getDriverName() === 'sqlite'
                ? "CAST(strftime('%Y', created_at) AS INTEGER)"
                : 'YEAR(created_at)') . ' AS year')
            ->pluck('year')
            ->push(now()->year)
            ->map(fn ($year) => (int) $year)
            ->unique()
            ->sortDesc();

        return $years->mapWithKeys(fn (int $year): array => [(string) $year => (string) $year])->all();
    }
}

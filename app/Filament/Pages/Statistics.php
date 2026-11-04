<?php

namespace App\Filament\Pages;

use App\Filament\Widgets\Statistics\LecturerTaughtHoursTable;
use App\Filament\Widgets\Statistics\MultiCourseStudentsTable;
use App\Filament\Widgets\TermOccupancyTableWidget;
use BackedEnum;
use Filament\Pages\Page;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

/**
 * Client statistics (requirement 8): term occupancy, taught hours per lecturer
 * in a chosen period and students enrolled in more than one course in a year.
 */
class Statistics extends Page
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChartBar;

    protected static string|UnitEnum|null $navigationGroup = 'Přehledy';

    protected static ?string $navigationLabel = 'Statistiky';

    protected static ?string $title = 'Přehledy a statistiky';

    protected static ?string $slug = 'statistics';

    protected static ?int $navigationSort = 1;

    protected function getHeaderWidgets(): array
    {
        return [
            TermOccupancyTableWidget::class,
            LecturerTaughtHoursTable::class,
            MultiCourseStudentsTable::class,
        ];
    }

    public function getHeaderWidgetsColumns(): int|array
    {
        return 1;
    }
}

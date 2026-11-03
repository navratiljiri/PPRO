<?php

namespace App\Filament\Widgets;

use App\Models\Certificate;
use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Registration;
use App\Models\Student;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class AkademieStatsOverview extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $activeCourses = Course::where('status', 'active')->count();
        $enrollingTerms = CourseTerm::whereIn('status', ['enrolling', 'in_progress'])->count();
        $registeredStudents = Registration::where('status', 'registered')->count();
        $waitingListCount = Registration::where('status', 'waiting_list')->count();
        $issuedCertificates = Certificate::count();

        return [
            Stat::make('Aktivní kurzy', $activeCourses)
                ->description('V nabídce Akademie Trutnov')
                ->icon('heroicon-o-academic-cap')
                ->color('primary'),

            Stat::make('Aktuální termíny', $enrollingTerms)
                ->description('V zápisu nebo probíhající')
                ->icon('heroicon-o-calendar')
                ->color('info'),

            Stat::make('Zaregistrovaní studenti', $registeredStudents)
                ->description('Aktivně obsazená místa')
                ->icon('heroicon-o-user-group')
                ->color('success'),

            Stat::make('Čekající v pořadníku', $waitingListCount)
                ->description('FIFO náhradníci')
                ->icon('heroicon-o-clock')
                ->color($waitingListCount > 0 ? 'warning' : 'gray'),

            Stat::make('Vydaná osvědčení', $issuedCertificates)
                ->description('Úspěšní absolventi')
                ->icon('heroicon-o-document-check')
                ->color('success'),
        ];
    }
}

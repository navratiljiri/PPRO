<?php

namespace App\Services;

use App\Models\CourseTerm;
use App\Models\Lecturer;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Statistic Service (Application / Business Logic)
 *
 * Implements reporting & analytics (Požadavky klienta č. 8):
 * - Course term capacity occupancy (counts & percentages)
 * - Taught hours summary per lecturer in given date range
 * - Multi-course students in a given year
 */
class StatisticService
{
    /**
     * Term occupancy statistics
     */
    public function getTermOccupancyStats(): Collection
    {
        return CourseTerm::with('course')
            ->withCount([
                'registrations as registered_count' => function ($q) {
                    $q->where('status', 'registered');
                },
                'registrations as waiting_count' => function ($q) {
                    $q->where('status', 'waiting_list');
                },
                'registrations as offered_count' => function ($q) {
                    $q->where('status', 'offered');
                },
            ])
            ->orderBy('start_date', 'desc')
            ->get()
            ->map(function (CourseTerm $term) {
                $occupied = $term->registered_count + $term->offered_count;
                $term->occupied_spots = $occupied;
                $term->occupancy_rate = $term->capacity > 0 ? round(($occupied / $term->capacity) * 100, 1) : 0;
                return $term;
            });
    }

    /**
     * Taught hours by lecturer in a given date range
     */
    public function getLecturerTaughtHours(?Carbon $from = null, ?Carbon $to = null): Collection
    {
        $query = Lecturer::query()->with(['lessons' => function ($q) use ($from, $to) {
            if ($from) {
                $q->where('starts_at', '>=', $from);
            }
            if ($to) {
                $q->where('starts_at', '<=', $to);
            }
        }]);

        return $query->get()->map(function (Lecturer $lecturer) {
            $totalHours = (int) $lecturer->lessons->sum('duration_hours');
            // Note: must not reuse the name total_taught_hours – that is an all-time accessor on Lecturer
            $lecturer->period_taught_hours = $totalHours;
            $lecturer->lesson_count = $lecturer->lessons->count();
            return $lecturer;
        });
    }

    /**
     * Students enrolled in more than one course in a given year
     */
    public function getMultiCourseStudents(int $year): \Illuminate\Support\Collection
    {
        return Student::query()
            ->whereHas('registrations', function ($q) use ($year) {
                $q->whereYear('created_at', $year)
                  ->whereIn('status', ['registered', 'offered', 'completed']);
            })
            ->with(['registrations' => function ($q) use ($year) {
                $q->whereYear('created_at', $year)
                  ->whereIn('status', ['registered', 'offered', 'completed'])
                  ->with('courseTerm.course');
            }])
            ->get()
            ->filter(function (Student $student) {
                // Count distinct courses
                $uniqueCourses = $student->registrations
                    ->map(fn($reg) => $reg->courseTerm?->course_id)
                    ->filter()
                    ->unique();

                $student->distinct_courses_count = $uniqueCourses->count();
                return $uniqueCourses->count() > 1;
            })
            ->values();
    }
}

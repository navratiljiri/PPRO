<?php

namespace App\Services;

use App\Models\AttendanceRecord;
use App\Models\Lesson;
use App\Models\Registration;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Attendance Service (Application / Business Logic)
 *
 * Tracks lesson-by-lesson attendance and enforces completion requirements (Rozhodnutí 1: 70% docházka).
 */
class AttendanceService
{
    /**
     * Record or update attendance for all students on a given lesson
     *
     * @param Lesson $lesson
     * @param array<int, array{registration_id: int, is_present: bool, note?: string|null}> $records
     */
    public function recordLessonAttendance(Lesson $lesson, array $records): void
    {
        DB::transaction(function () use ($lesson, $records) {
            foreach ($records as $entry) {
                $registrationId = (int) $entry['registration_id'];
                $isPresent = (bool) ($entry['is_present'] ?? false);
                $note = !empty($entry['note']) ? trim($entry['note']) : null;

                // Security / Integrity: Verify registration belongs to the same course term
                $registration = Registration::findOrFail($registrationId);
                if ($registration->course_term_id !== $lesson->course_term_id) {
                    throw ValidationException::withMessages([
                        'attendance' => "Přihláška #{$registrationId} nepatří k termínu této lekce.",
                    ]);
                }

                AttendanceRecord::updateOrCreate(
                    [
                        'registration_id' => $registrationId,
                        'lesson_id' => $lesson->id,
                    ],
                    [
                        'is_present' => $isPresent,
                        'note' => $note,
                    ]
                );
            }
        });
    }

    /**
     * Calculate detailed attendance statistics for an enrolled registration
     *
     * @return array{total_lessons: int, attended_lessons: int, percentage: float, meets_threshold: bool}
     */
    public function calculateAttendanceStats(Registration $registration): array
    {
        $term = $registration->courseTerm;
        $totalLessons = $term ? $term->lessons()->count() : 0;

        if ($totalLessons === 0) {
            return [
                'total_lessons' => 0,
                'attended_lessons' => 0,
                'percentage' => 0.0,
                'meets_threshold' => false,
            ];
        }

        $attendedLessons = $registration->attendanceRecords()
            ->where('is_present', true)
            ->count();

        $percentage = round(($attendedLessons / $totalLessons) * 100, 1);

        return [
            'total_lessons' => $totalLessons,
            'attended_lessons' => $attendedLessons,
            'percentage' => $percentage,
            'meets_threshold' => $percentage >= 70.0,
        ];
    }
}

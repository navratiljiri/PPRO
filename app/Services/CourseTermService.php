<?php

namespace App\Services;

use App\Models\Course;
use App\Models\CourseTerm;
use App\Models\Lesson;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

/**
 * Course Term Service (Application / Business Logic)
 * 
 * Manages term schedules, room capacities, lesson assignments and multi-lecturer coordination.
 */
class CourseTermService
{
    public function __construct(
        protected RegistrationService $registrationService
    ) {}

    /**
     * Get paginated terms with optional filters
     */
    public function getTermsPaginated(?string $status = null, ?int $courseId = null, int $perPage = 10): LengthAwarePaginator
    {
        $query = CourseTerm::query()->with(['course', 'lecturers'])->withCount('lessons');

        if (!empty($status)) {
            $query->status($status);
        }

        if (!empty($courseId)) {
            $query->where('course_id', $courseId);
        }

        return $query->orderBy('start_date', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get a course term with full details
     */
    public function getTermById(int $id): CourseTerm
    {
        return CourseTerm::with(['course', 'lessons.lecturer', 'lecturers'])->findOrFail($id);
    }

    /**
     * Term aggregate statistics
     */
    public function getTermStats(): array
    {
        return [
            'total' => CourseTerm::count(),
            'enrolling' => CourseTerm::where('status', 'enrolling')->count(),
            'in_progress' => CourseTerm::where('status', 'in_progress')->count(),
            'completed' => CourseTerm::where('status', 'completed')->count(),
            'total_capacity' => (int) CourseTerm::whereIn('status', ['enrolling', 'in_progress'])->sum('capacity'),
        ];
    }

    /**
     * Create a new course term with business validations
     */
    public function createTerm(array $data): CourseTerm
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'Datum a čas konce termínu nemůže být dříve než začátek.',
            ]);
        }

        if (($data['capacity'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'capacity' => 'Kapacita učebny/termínu musí být alespoň 1 účastník.',
            ]);
        }

        $course = Course::findOrFail($data['course_id']);
        if ($course->status !== 'active') {
            throw ValidationException::withMessages([
                'course_id' => 'Nelze vypsat nový termín pro archivovaný kurz.',
            ]);
        }

        $data['status'] = $data['status'] ?? 'enrolling';

        return CourseTerm::create($data);
    }

    /**
     * Update an existing course term
     */
    public function updateTerm(CourseTerm $term, array $data): CourseTerm
    {
        $startDate = Carbon::parse($data['start_date']);
        $endDate = Carbon::parse($data['end_date']);

        if ($endDate->lt($startDate)) {
            throw ValidationException::withMessages([
                'end_date' => 'Datum a čas konce termínu nemůže být dříve než začátek.',
            ]);
        }

        if (($data['capacity'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'capacity' => 'Kapacita učebny/termínu musí být alespoň 1 účastník.',
            ]);
        }

        $updated = DB::transaction(function () use ($term, $data) {
            // Lock the term so that no registration can be added between the check and the update
            $lockedTerm = CourseTerm::where('id', $term->id)->lockForUpdate()->firstOrFail();

            // Business rule: capacity can never drop below the number of occupied spots
            $occupied = $lockedTerm->activeRegistrations()->count();
            if (array_key_exists('capacity', $data) && (int) $data['capacity'] < $occupied) {
                throw ValidationException::withMessages([
                    'capacity' => "Kapacitu nelze snížit pod počet obsazených míst ({$occupied}). Nejprve zrušte některé přihlášky.",
                ]);
            }

            $lockedTerm->update($data);

            return $lockedTerm;
        });

        // Capacity increase frees spots -> offer them to waiting list in FIFO order (Rozhodnutí 2)
        while ($this->registrationService->promoteWaitingList($updated)) {
            // each call offers exactly one freed spot to the next waiting applicant
        }

        return $updated->fresh();
    }

    /**
     * Delete course term
     */
    public function deleteTerm(CourseTerm $term): bool
    {
        return (bool) $term->delete();
    }

    /**
     * Add a lesson session to the term (Fulfills Decision 5: alternating lecturers & hours)
     */
    public function addLessonToTerm(CourseTerm $term, array $data): Lesson
    {
        $startsAt = Carbon::parse($data['starts_at']);
        $endsAt = Carbon::parse($data['ends_at']);

        if ($endsAt->lt($startsAt)) {
            throw ValidationException::withMessages([
                'ends_at' => 'Konec lekce nemůže být před jejím začátkem.',
            ]);
        }

        if (($data['duration_hours'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'duration_hours' => 'Délka lekce musí být alespoň 1 vyučovací hodina.',
            ]);
        }

        $data['course_term_id'] = $term->id;

        return Lesson::create($data);
    }

    /**
     * Remove an individual lesson session
     */
    public function removeLesson(Lesson $lesson): bool
    {
        return (bool) $lesson->delete();
    }
}

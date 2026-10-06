<?php

namespace App\Services;

use App\Models\Course;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Validation\ValidationException;

/**
 * Course Service (Application / Business Logic)
 * 
 * Handles business rules and data manipulation using Eloquent models directly.
 */
class CourseService
{
    /**
     * Get paginated active courses applying query scopes for search and accreditation
     */
    public function getCoursesPaginated(?string $search = null, ?string $filter = null, int $perPage = 9): LengthAwarePaginator
    {
        $query = Course::query()->active();

        if ($filter === 'accredited') {
            $query->accredited(true);
        } elseif ($filter === 'non_accredited') {
            $query->accredited(false);
        }

        if (!empty($search)) {
            $query->search($search);
        }

        return $query->orderBy('created_at', 'desc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Calculate summary metrics directly using Eloquent aggregations
     */
    public function getDashboardStats(): array
    {
        return [
            'total' => Course::active()->count(),
            'accredited' => Course::active()->accredited(true)->count(),
            'non_accredited' => Course::active()->accredited(false)->count(),
            'average_hours' => round((float) Course::active()->avg('duration_hours'), 1),
        ];
    }

    /**
     * Find a course by ID or throw 404
     */
    public function getCourseById(int $id): Course
    {
        return Course::findOrFail($id);
    }

    /**
     * Create a new course with business validations
     */
    public function createCourse(array $data): Course
    {
        $data['code'] = strtoupper(trim($data['code']));
        $data['is_accredited'] = !empty($data['is_accredited']);
        $data['status'] = $data['status'] ?? 'active';

        // Business rule: unique course code
        if (Course::where('code', $data['code'])->exists()) {
            throw ValidationException::withMessages([
                'code' => "Kurz s kódem {$data['code']} již v systému existuje.",
            ]);
        }

        // Business rule: minimum duration is 1 hour
        if (($data['duration_hours'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'duration_hours' => 'Rozsah kurzu musí být alespoň 1 vyučovací hodina.',
            ]);
        }

        // Business rule: price must not be negative
        if (($data['price'] ?? 0) < 0) {
            throw ValidationException::withMessages([
                'price' => 'Cena kurzu nemůže být záporná.',
            ]);
        }

        return Course::create($data);
    }

    /**
     * Update an existing course with business constraints
     */
    public function updateCourse(Course $course, array $data): Course
    {
        $data['code'] = strtoupper(trim($data['code']));
        $data['is_accredited'] = !empty($data['is_accredited']);

        // Check uniqueness when changing code
        $codeExists = Course::where('code', $data['code'])
            ->where('id', '!=', $course->id)
            ->exists();

        if ($codeExists) {
            throw ValidationException::withMessages([
                'code' => "Kód kurzu {$data['code']} již používá jiný kurz.",
            ]);
        }

        if (($data['duration_hours'] ?? 0) <= 0) {
            throw ValidationException::withMessages([
                'duration_hours' => 'Rozsah kurzu musí být alespoň 1 vyučovací hodina.',
            ]);
        }

        if (($data['price'] ?? 0) < 0) {
            throw ValidationException::withMessages([
                'price' => 'Cena kurzu nemůže být záporná.',
            ]);
        }

        $course->update($data);

        return $course->fresh();
    }

    /**
     * Delete course
     */
    public function deleteCourse(Course $course): bool
    {
        return $course->delete();
    }
}

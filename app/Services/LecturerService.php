<?php

namespace App\Services;

use App\Models\Lecturer;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Validation\ValidationException;

/**
 * Lecturer Service (Application / Business Logic)
 * 
 * Manages external lecturers and their teaching workloads.
 */
class LecturerService
{
    /**
     * Get paginated lecturers with search query
     */
    public function getLecturersPaginated(?string $search = null, int $perPage = 12): LengthAwarePaginator
    {
        $query = Lecturer::query()->withCount('lessons');

        if (!empty($search)) {
            $query->search($search);
        }

        return $query->orderBy('last_name', 'asc')
            ->orderBy('first_name', 'asc')
            ->paginate($perPage)
            ->withQueryString();
    }

    /**
     * Get all lecturers for selection dropdowns
     */
    public function getAllLecturers(): Collection
    {
        return Lecturer::orderBy('last_name')->orderBy('first_name')->get();
    }

    /**
     * Find a lecturer by ID
     */
    public function getLecturerById(int $id): Lecturer
    {
        return Lecturer::with(['lessons.courseTerm.course', 'courseTerms.course'])->findOrFail($id);
    }

    /**
     * Aggregate statistics across lecturers
     */
    public function getLecturerStats(): array
    {
        $totalHours = (int) \DB::table('lessons')->sum('duration_hours');

        return [
            'total_lecturers' => Lecturer::count(),
            'total_lessons' => \DB::table('lessons')->count(),
            'total_hours' => $totalHours,
            'active_in_terms' => Lecturer::has('lessons')->count(),
        ];
    }

    /**
     * Create a new lecturer with business validations
     */
    public function createLecturer(array $data): Lecturer
    {
        $data['email'] = strtolower(trim($data['email']));
        $data['first_name'] = trim($data['first_name']);
        $data['last_name'] = trim($data['last_name']);

        if (Lecturer::where('email', $data['email'])->exists()) {
            throw ValidationException::withMessages([
                'email' => "Lektor s e-mailem {$data['email']} již v systému existuje.",
            ]);
        }

        return Lecturer::create($data);
    }

    /**
     * Update an existing lecturer
     */
    public function updateLecturer(Lecturer $lecturer, array $data): Lecturer
    {
        $data['email'] = strtolower(trim($data['email']));
        $data['first_name'] = trim($data['first_name']);
        $data['last_name'] = trim($data['last_name']);

        $emailExists = Lecturer::where('email', $data['email'])
            ->where('id', '!=', $lecturer->id)
            ->exists();

        if ($emailExists) {
            throw ValidationException::withMessages([
                'email' => "E-mail {$data['email']} již používá jiný lektor.",
            ]);
        }

        $lecturer->update($data);

        return $lecturer->fresh();
    }

    /**
     * Delete lecturer with business constraint
     */
    public function deleteLecturer(Lecturer $lecturer): bool
    {
        // Business rule: Cannot delete a lecturer who has scheduled lessons
        if ($lecturer->lessons()->exists()) {
            throw ValidationException::withMessages([
                'lecturer' => "Lektora {$lecturer->full_name} nelze smazat, protože má přiřazené výukové lekce. Nejprve přeřaďte jeho lekce na jiného lektora.",
            ]);
        }

        return (bool) $lecturer->delete();
    }
}

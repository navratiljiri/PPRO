<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLecturerRequest;
use App\Http\Requests\UpdateLecturerRequest;
use App\Models\Lecturer;
use App\Services\LecturerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Lecturer Controller (Presentation Layer / MVC Controller)
 * 
 * Handles CRUD actions for external lecturers and displays their teaching history.
 */
class LecturerController extends Controller
{
    public function __construct(
        protected LecturerService $lecturerService
    ) {}

    /**
     * Display lecturers catalog with stats
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $lecturers = $this->lecturerService->getLecturersPaginated($search, perPage: 12);
        $stats = $this->lecturerService->getLecturerStats();

        return view('lecturers.index', compact('lecturers', 'stats', 'search'));
    }

    /**
     * Show form to add a new lecturer
     */
    public function create(): View
    {
        return view('lecturers.create');
    }

    /**
     * Store new lecturer
     */
    public function store(StoreLecturerRequest $request): RedirectResponse
    {
        $lecturer = $this->lecturerService->createLecturer($request->validated());

        return redirect()
            ->route('lecturers.show', $lecturer)
            ->with('success', "Lektor {$lecturer->full_name} byl úspěšně zaevidován.");
    }

    /**
     * Display lecturer profile and assigned lessons
     */
    public function show(int $id): View
    {
        $lecturer = $this->lecturerService->getLecturerById($id);

        return view('lecturers.show', compact('lecturer'));
    }

    /**
     * Show edit form
     */
    public function edit(Lecturer $lecturer): View
    {
        return view('lecturers.edit', compact('lecturer'));
    }

    /**
     * Update lecturer
     */
    public function update(UpdateLecturerRequest $request, Lecturer $lecturer): RedirectResponse
    {
        $updated = $this->lecturerService->updateLecturer($lecturer, $request->validated());

        return redirect()
            ->route('lecturers.show', $updated)
            ->with('success', "Údaje lektora {$updated->full_name} byly úspěšně aktualizovány.");
    }

    /**
     * Delete lecturer
     */
    public function destroy(Lecturer $lecturer): RedirectResponse
    {
        $name = $lecturer->full_name;

        try {
            $this->lecturerService->deleteLecturer($lecturer);
        } catch (\Illuminate\Validation\ValidationException $e) {
            return back()->withErrors($e->errors());
        }

        return redirect()
            ->route('lecturers.index')
            ->with('success', "Lektor {$name} byl úspěšně odstraněn.");
    }
}

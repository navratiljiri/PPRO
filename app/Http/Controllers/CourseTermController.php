<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseTermRequest;
use App\Http\Requests\UpdateCourseTermRequest;
use App\Models\Course;
use App\Models\CourseTerm;
use App\Services\CourseService;
use App\Services\CourseTermService;
use App\Services\LecturerService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Course Term Controller (Presentation Layer / MVC Controller)
 * 
 * Manages term schedules, classroom allocations and links with courses.
 */
class CourseTermController extends Controller
{
    public function __construct(
        protected CourseTermService $termService,
        protected CourseService $courseService,
        protected LecturerService $lecturerService
    ) {}

    /**
     * Display list of course terms with filters
     */
    public function index(Request $request): View
    {
        $status = $request->query('status');
        $courseId = $request->filled('course_id') ? (int) $request->query('course_id') : null;

        $terms = $this->termService->getTermsPaginated($status, $courseId, perPage: 10);
        $stats = $this->termService->getTermStats();
        $courses = Course::active()->orderBy('name')->get();

        return view('terms.index', compact('terms', 'stats', 'courses', 'status', 'courseId'));
    }

    /**
     * Show form to create a new term
     */
    public function create(Request $request): View
    {
        $courses = Course::active()->orderBy('name')->get();
        $selectedCourseId = $request->query('course_id');

        return view('terms.create', compact('courses', 'selectedCourseId'));
    }

    /**
     * Store new term
     */
    public function store(StoreCourseTermRequest $request): RedirectResponse
    {
        $term = $this->termService->createTerm($request->validated());

        return redirect()
            ->route('terms.show', $term)
            ->with('success', "Nový termín kurzu byl úspěšně vypsán v učebně {$term->classroom}.");
    }

    /**
     * Display term detail with scheduled lessons
     */
    public function show(CourseTerm $courseTerm): View
    {
        $term = $this->termService->getTermById($courseTerm->id);
        $lecturers = $this->lecturerService->getAllLecturers();

        return view('terms.show', compact('term', 'courseTerm', 'lecturers'));
    }

    /**
     * Show edit form
     */
    public function edit(CourseTerm $courseTerm): View
    {
        $courses = Course::orderBy('name')->get();

        return view('terms.edit', compact('courseTerm', 'courses'));
    }

    /**
     * Update term
     */
    public function update(UpdateCourseTermRequest $request, CourseTerm $courseTerm): RedirectResponse
    {
        $updated = $this->termService->updateTerm($courseTerm, $request->validated());

        return redirect()
            ->route('terms.show', $updated)
            ->with('success', "Termín kurzu byl úspěšně upraven.");
    }

    /**
     * Delete term
     */
    public function destroy(CourseTerm $courseTerm): RedirectResponse
    {
        $courseId = $courseTerm->course_id;
        $this->termService->deleteTerm($courseTerm);

        return redirect()
            ->route('courses.show', ['course' => $courseId])
            ->with('success', "Termín kurzu byl úspěšně odstraněn.");
    }
}

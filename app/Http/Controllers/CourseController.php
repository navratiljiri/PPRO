<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreCourseRequest;
use App\Http\Requests\UpdateCourseRequest;
use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Course Controller (Presentation Layer / MVC Controller)
 * 
 * Injects CourseService via Dependency Injection and uses Route Model Binding.
 */
class CourseController extends Controller
{
    public function __construct(
        protected CourseService $courseService
    ) {}

    /**
     * Display course catalog with search, filters, and metrics
     */
    public function index(Request $request): View
    {
        $search = $request->query('search');
        $filter = $request->query('filter');

        $courses = $this->courseService->getCoursesPaginated(
            search: $search,
            filter: $filter,
            perPage: 9
        );

        $stats = $this->courseService->getDashboardStats();

        return view('courses.index', compact('courses', 'stats', 'search', 'filter'));
    }

    /**
     * Show form to create a new course
     */
    public function create(): View
    {
        return view('courses.create');
    }

    /**
     * Store new course in database
     */
    public function store(StoreCourseRequest $request): RedirectResponse
    {
        $course = $this->courseService->createCourse($request->validated());

        return redirect()
            ->route('courses.show', $course)
            ->with('success', "Kurz „{$course->name}“ byl úspěšně vytvořen.");
    }

    /**
     * Display course details using Route Model Binding
     */
    public function show(Course $course): View
    {
        return view('courses.show', compact('course'));
    }

    /**
     * Show form to edit existing course using Route Model Binding
     */
    public function edit(Course $course): View
    {
        return view('courses.edit', compact('course'));
    }

    /**
     * Update existing course in database
     */
    public function update(UpdateCourseRequest $request, Course $course): RedirectResponse
    {
        $updatedCourse = $this->courseService->updateCourse($course, $request->validated());

        return redirect()
            ->route('courses.show', $updatedCourse)
            ->with('success', "Kurz „{$updatedCourse->name}“ byl úspěšně upraven.");
    }

    /**
     * Delete course from database
     */
    public function destroy(Course $course): RedirectResponse
    {
        $name = $course->name;
        $this->courseService->deleteCourse($course);

        return redirect()
            ->route('courses.index')
            ->with('success', "Kurz „{$name}“ byl úspěšně smazán.");
    }
}

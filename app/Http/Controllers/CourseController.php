<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Services\CourseService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public, read-only course catalog (presentation layer).
 * Course administration is available only in the Filament panel (/admin).
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
     * Display course detail
     */
    public function show(Course $course): View
    {
        return view('courses.show', compact('course'));
    }
}

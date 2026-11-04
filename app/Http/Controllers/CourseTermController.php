<?php

namespace App\Http\Controllers;

use App\Models\Course;
use App\Models\CourseTerm;
use App\Services\CourseTermService;
use Illuminate\Http\Request;
use Illuminate\View\View;

/**
 * Public, read-only list of course terms (presentation layer).
 * Term and lesson administration is available only in the Filament panel (/admin).
 */
class CourseTermController extends Controller
{
    public function __construct(
        protected CourseTermService $termService
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
     * Display term detail with its lesson schedule
     */
    public function show(CourseTerm $courseTerm): View
    {
        $term = $this->termService->getTermById($courseTerm->id);

        return view('terms.show', compact('term', 'courseTerm'));
    }
}

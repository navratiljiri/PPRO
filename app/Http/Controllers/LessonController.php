<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreLessonRequest;
use App\Models\CourseTerm;
use App\Models\Lesson;
use App\Services\CourseTermService;
use Illuminate\Http\RedirectResponse;

/**
 * Lesson Controller (Presentation Layer / MVC Controller)
 * 
 * Manages lesson sessions within a course term and lecturer assignments.
 */
class LessonController extends Controller
{
    public function __construct(
        protected CourseTermService $termService
    ) {}

    /**
     * Store new lesson session in a term
     */
    public function store(StoreLessonRequest $request, CourseTerm $courseTerm): RedirectResponse
    {
        $lesson = $this->termService->addLessonToTerm($courseTerm, $request->validated());

        return redirect()
            ->route('terms.show', $courseTerm)
            ->with('success', "Výukový blok „{$lesson->topic}“ byl úspěšně naplánován.");
    }

    /**
     * Remove lesson from a term
     */
    public function destroy(Lesson $lesson): RedirectResponse
    {
        $termId = $lesson->course_term_id;
        $this->termService->removeLesson($lesson);

        return redirect()
            ->route('terms.show', $termId)
            ->with('success', "Výukový blok byl odstraněn z harmonogramu.");
    }
}

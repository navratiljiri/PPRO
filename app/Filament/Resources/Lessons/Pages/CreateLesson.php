<?php

namespace App\Filament\Resources\Lessons\Pages;

use App\Filament\Concerns\MapsServiceValidationErrors;
use App\Filament\Resources\Lessons\LessonResource;
use App\Models\CourseTerm;
use App\Services\CourseTermService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateLesson extends CreateRecord
{
    use MapsServiceValidationErrors;

    protected static string $resource = LessonResource::class;

    /**
     * Lessons are added through CourseTermService (Rozhodnutí 5 – lessons with own lecturer and hours).
     */
    protected function handleRecordCreation(array $data): Model
    {
        $term = CourseTerm::findOrFail($data['course_term_id']);

        return $this->callService(fn () => app(CourseTermService::class)->addLessonToTerm($term, $data));
    }
}

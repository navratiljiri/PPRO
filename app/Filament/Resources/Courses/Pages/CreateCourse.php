<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Concerns\MapsServiceValidationErrors;
use App\Filament\Resources\Courses\CourseResource;
use App\Services\CourseService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateCourse extends CreateRecord
{
    use MapsServiceValidationErrors;

    protected static string $resource = CourseResource::class;

    /**
     * Persist through the business layer so all course rules are enforced.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return $this->callService(fn () => app(CourseService::class)->createCourse($data));
    }
}

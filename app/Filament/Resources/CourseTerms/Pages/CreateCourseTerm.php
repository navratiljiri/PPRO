<?php

namespace App\Filament\Resources\CourseTerms\Pages;

use App\Filament\Resources\CourseTerms\CourseTermResource;
use App\Services\CourseTermService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateCourseTerm extends CreateRecord
{
    protected static string $resource = CourseTermResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        try {
            return app(CourseTermService::class)->createTerm($data);
        } catch (ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $key => $fieldMessages) {
                $messages["data.{$key}"] = $fieldMessages;
            }

            throw ValidationException::withMessages($messages);
        }
    }
}

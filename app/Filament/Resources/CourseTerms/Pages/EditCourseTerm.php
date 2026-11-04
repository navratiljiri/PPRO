<?php

namespace App\Filament\Resources\CourseTerms\Pages;

use App\Filament\Resources\CourseTerms\CourseTermResource;
use App\Models\CourseTerm;
use App\Services\CourseTermService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditCourseTerm extends EditRecord
{
    protected static string $resource = CourseTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * Updates go through CourseTermService, which forbids lowering the capacity
     * below the number of occupied spots and offers newly freed spots to the waiting list.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        /** @var CourseTerm $record */
        try {
            return app(CourseTermService::class)->updateTerm($record, $data);
        } catch (ValidationException $e) {
            $messages = [];
            foreach ($e->errors() as $key => $fieldMessages) {
                $messages["data.{$key}"] = $fieldMessages;
            }

            throw ValidationException::withMessages($messages);
        }
    }
}

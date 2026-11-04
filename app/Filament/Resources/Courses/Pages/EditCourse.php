<?php

namespace App\Filament\Resources\Courses\Pages;

use App\Filament\Concerns\MapsServiceValidationErrors;
use App\Filament\Resources\Courses\CourseResource;
use App\Services\CourseService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;

class EditCourse extends EditRecord
{
    use MapsServiceValidationErrors;

    protected static string $resource = CourseResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make(),
        ];
    }

    /**
     * Persist through the business layer so all course rules are enforced.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->callService(fn () => app(CourseService::class)->updateCourse($record, $data));
    }
}

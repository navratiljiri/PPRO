<?php

namespace App\Filament\Resources\Lecturers\Pages;

use App\Filament\Concerns\MapsServiceValidationErrors;
use App\Filament\Resources\Lecturers\LecturerResource;
use App\Services\LecturerService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateLecturer extends CreateRecord
{
    use MapsServiceValidationErrors;

    protected static string $resource = LecturerResource::class;

    /**
     * Persist through the business layer so all lecturer rules are enforced.
     */
    protected function handleRecordCreation(array $data): Model
    {
        return $this->callService(fn () => app(LecturerService::class)->createLecturer($data));
    }
}

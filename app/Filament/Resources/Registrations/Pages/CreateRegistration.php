<?php

namespace App\Filament\Resources\Registrations\Pages;

use App\Filament\Resources\Registrations\RegistrationResource;
use App\Models\CourseTerm;
use App\Models\Student;
use App\Services\RegistrationService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;

class CreateRegistration extends CreateRecord
{
    protected static string $resource = RegistrationResource::class;

    protected function handleRecordCreation(array $data): Model
    {
        $term = CourseTerm::findOrFail($data['course_term_id']);
        $student = Student::findOrFail($data['student_id']);

        return app(RegistrationService::class)->registerStudent($term, $student, isAdminOverride: true);
    }
}

<?php

namespace App\Filament\Resources\Certificates\Pages;

use App\Filament\Resources\Certificates\CertificateResource;
use App\Models\Registration;
use App\Services\CertificateService;
use Filament\Resources\Pages\CreateRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class CreateCertificate extends CreateRecord
{
    protected static string $resource = CertificateResource::class;

    protected static ?string $title = 'Vystavit osvědčení';

    protected static bool $canCreateAnother = false;

    /**
     * Certificates are issued exclusively through the business layer (CertificateService),
     * which generates the unique number and enforces attendance / approval rules.
     */
    protected function handleRecordCreation(array $data): Model
    {
        $registration = Registration::findOrFail($data['registration_id']);

        try {
            return app(CertificateService::class)->issueCertificate(
                $registration,
                lecturerApproved: (bool) ($data['lecturer_approved'] ?? false),
                adminException: (bool) ($data['admin_exception'] ?? false),
                exceptionReason: $data['exception_reason'] ?? null,
            );
        } catch (ValidationException $e) {
            // Map service errors onto form fields so they are shown next to the relevant input.
            $messages = [];
            foreach ($e->errors() as $key => $fieldMessages) {
                $field = $key === 'lecturer_approved' ? 'lecturer_approved' : 'registration_id';
                $messages["data.{$field}"] = array_merge($messages["data.{$field}"] ?? [], $fieldMessages);
            }

            throw ValidationException::withMessages($messages);
        }
    }

    protected function getCreatedNotificationTitle(): ?string
    {
        return "Osvědčení {$this->record->certificate_number} bylo vystaveno";
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('view', ['record' => $this->record]);
    }
}

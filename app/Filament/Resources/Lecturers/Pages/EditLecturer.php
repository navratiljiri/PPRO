<?php

namespace App\Filament\Resources\Lecturers\Pages;

use App\Filament\Concerns\MapsServiceValidationErrors;
use App\Filament\Resources\Lecturers\LecturerResource;
use App\Models\Lecturer;
use App\Services\LecturerService;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\EditRecord;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Validation\ValidationException;

class EditLecturer extends EditRecord
{
    use MapsServiceValidationErrors;

    protected static string $resource = LecturerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            ViewAction::make(),
            DeleteAction::make()
                // Business rule (LecturerService): a lecturer with scheduled lessons cannot be deleted
                ->action(function (Lecturer $record, DeleteAction $action): void {
                    try {
                        app(LecturerService::class)->deleteLecturer($record);
                    } catch (ValidationException $e) {
                        Notification::make()
                            ->title('Lektora nelze smazat')
                            ->body(collect($e->errors())->flatten()->first())
                            ->danger()
                            ->send();

                        $action->cancel();
                    }

                    $action->success();
                }),
        ];
    }

    /**
     * Persist through the business layer so all lecturer rules are enforced.
     */
    protected function handleRecordUpdate(Model $record, array $data): Model
    {
        return $this->callService(fn () => app(LecturerService::class)->updateLecturer($record, $data));
    }
}

<?php

namespace App\Filament\Resources\CourseTerms\Pages;

use App\Filament\Resources\CourseTerms\CourseTermResource;
use Filament\Actions\DeleteAction;
use Filament\Actions\ViewAction;
use Filament\Resources\Pages\EditRecord;

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
}

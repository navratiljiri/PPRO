<?php

namespace App\Filament\Resources\CourseTerms\Pages;

use App\Filament\Resources\CourseTerms\CourseTermResource;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCourseTerm extends ViewRecord
{
    protected static string $resource = CourseTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            EditAction::make(),
        ];
    }
}

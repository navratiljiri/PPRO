<?php

namespace App\Filament\Resources\CourseTerms\Pages;

use App\Filament\Resources\CourseTerms\CourseTermResource;
use Filament\Actions\Action;
use Filament\Actions\EditAction;
use Filament\Resources\Pages\ViewRecord;

class ViewCourseTerm extends ViewRecord
{
    protected static string $resource = CourseTermResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Action::make('graduates')
                ->label('Seznam absolventů')
                ->icon('heroicon-o-academic-cap')
                ->color('success')
                ->url(fn (): string => CourseTermResource::getUrl('graduates', ['record' => $this->getRecord()])),
            EditAction::make(),
        ];
    }
}

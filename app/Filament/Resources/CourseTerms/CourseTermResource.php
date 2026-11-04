<?php

namespace App\Filament\Resources\CourseTerms;

use App\Filament\Resources\CourseTerms\Pages\CourseTermGraduates;
use App\Filament\Resources\CourseTerms\Pages\CreateCourseTerm;
use App\Filament\Resources\CourseTerms\Pages\EditCourseTerm;
use App\Filament\Resources\CourseTerms\Pages\ListCourseTerms;
use App\Filament\Resources\CourseTerms\Pages\ViewCourseTerm;
use App\Filament\Resources\CourseTerms\Schemas\CourseTermForm;
use App\Filament\Resources\CourseTerms\Schemas\CourseTermInfolist;
use App\Filament\Resources\CourseTerms\Tables\CourseTermsTable;
use App\Models\CourseTerm;
use BackedEnum;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use UnitEnum;

class CourseTermResource extends Resource
{
    protected static ?string $model = CourseTerm::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCalendar;

    protected static string|UnitEnum|null $navigationGroup = 'Katalog & Výuka';

    protected static ?string $navigationLabel = 'Termíny kurzů';

    protected static ?string $modelLabel = 'Termín kurzu';

    protected static ?string $pluralModelLabel = 'Termíny kurzů';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'classroom';

    public static function form(Schema $schema): Schema
    {
        return CourseTermForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return CourseTermInfolist::configure($schema);
    }

    public static function table(Table $table): Table
    {
        return CourseTermsTable::configure($table);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => ListCourseTerms::route('/'),
            'create' => CreateCourseTerm::route('/create'),
            'view' => ViewCourseTerm::route('/{record}'),
            'edit' => EditCourseTerm::route('/{record}/edit'),
            'graduates' => CourseTermGraduates::route('/{record}/graduates'),
        ];
    }
}

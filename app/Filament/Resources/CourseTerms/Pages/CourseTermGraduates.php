<?php

namespace App\Filament\Resources\CourseTerms\Pages;

use App\Filament\Resources\CourseTerms\CourseTermResource;
use App\Models\CourseTerm;
use App\Models\Registration;
use App\Services\CertificateService;
use Filament\Actions\Action;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\Concerns\InteractsWithRecord;
use Filament\Resources\Pages\Page;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Concerns\InteractsWithTable;
use Filament\Tables\Contracts\HasTable;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Contracts\Support\Htmlable;

/**
 * List of successful graduates of a course term (requirement 7).
 *
 * Data comes from CertificateService::getGraduationSummary() (attendance stats per registration);
 * certificates are issued from here through CertificateService as well.
 */
class CourseTermGraduates extends Page implements HasTable
{
    use InteractsWithRecord;
    use InteractsWithTable;

    protected static string $resource = CourseTermResource::class;

    protected string $view = 'filament.resources.course-terms.pages.course-term-graduates';

    public const RESULT_GRADUATED = 'graduated';
    public const RESULT_ELIGIBLE = 'eligible';
    public const RESULT_NOT_ELIGIBLE = 'not_eligible';

    public function mount(int|string $record): void
    {
        $this->record = $this->resolveRecord($record);
    }

    public function getTitle(): string|Htmlable
    {
        /** @var CourseTerm $term */
        $term = $this->getRecord();

        return "Absolventi – {$term->course?->name}";
    }

    public function getSubheading(): ?string
    {
        /** @var CourseTerm $term */
        $term = $this->getRecord();

        return "{$term->formatted_date_range}, {$term->classroom}. Úspěšným absolventem je student s vydaným osvědčením (min. 70 % docházky a schválení lektorem).";
    }

    public function getBreadcrumb(): string
    {
        return 'Absolventi';
    }

    public function table(Table $table): Table
    {
        return $table
            ->records(fn (array $filters): array => $this->graduationRows($filters['result']['value'] ?? null))
            ->columns([
                TextColumn::make('student_name')
                    ->label('Student')
                    ->weight('bold')
                    ->description(fn (array $record): string => $record['student_email']),
                TextColumn::make('attendance')
                    ->label('Docházka')
                    ->badge()
                    ->color(fn (array $record): string => $record['meets_threshold'] ? 'success' : 'danger'),
                TextColumn::make('status_label')
                    ->label('Stav přihlášky')
                    ->badge()
                    ->color(fn (array $record): string => $record['status_color']),
                TextColumn::make('result_label')
                    ->label('Výsledek')
                    ->badge()
                    ->color(fn (array $record): string => match ($record['result']) {
                        self::RESULT_GRADUATED => 'success',
                        self::RESULT_ELIGIBLE => 'info',
                        default => 'danger',
                    }),
                TextColumn::make('certificate_number')
                    ->label('Číslo osvědčení')
                    ->fontFamily('mono')
                    ->placeholder('—'),
                TextColumn::make('issue_date')
                    ->label('Datum vydání')
                    ->placeholder('—'),
                IconColumn::make('meets_threshold')
                    ->label('Splňuje 70 %')
                    ->boolean()
                    ->alignCenter(),
            ])
            ->filters([
                SelectFilter::make('result')
                    ->label('Výsledek')
                    ->options([
                        self::RESULT_GRADUATED => 'Úspěšní absolventi (osvědčení vydáno)',
                        self::RESULT_ELIGIBLE => 'Splňují docházku – čeká na vystavení',
                        self::RESULT_NOT_ELIGIBLE => 'Nesplňují 70% docházku',
                    ]),
            ])
            ->recordActions([
                Action::make('issue_certificate')
                    ->label('Vystavit osvědčení')
                    ->icon('heroicon-o-academic-cap')
                    ->color('primary')
                    ->modalHeading('Vystavení osvědčení o absolvování kurzu')
                    ->modalDescription('Číslo osvědčení se vygeneruje automaticky. Vyžaduje se min. 70% docházka a souhlas lektora.')
                    ->schema([
                        Toggle::make('lecturer_approved')
                            ->label('Lektor formálně schválil úspěšné absolvování')
                            ->default(true),
                        Toggle::make('admin_exception')
                            ->label('Udělit administrátorskou výjimku (při nesplnění 70% docházky)')
                            ->default(false)
                            ->live(),
                        Textarea::make('exception_reason')
                            ->label('Odůvodnění výjimky')
                            ->required(fn (Get $get): bool => (bool) $get('admin_exception'))
                            ->visible(fn (Get $get): bool => (bool) $get('admin_exception')),
                    ])
                    ->action(function (array $record, array $data): void {
                        $certificate = app(CertificateService::class)->issueCertificate(
                            Registration::findOrFail($record['id']),
                            lecturerApproved: (bool) ($data['lecturer_approved'] ?? false),
                            adminException: (bool) ($data['admin_exception'] ?? false),
                            exceptionReason: $data['exception_reason'] ?? null,
                        );

                        Notification::make()
                            ->title('Osvědčení vystaveno')
                            ->body("Vystaveno osvědčení číslo {$certificate->certificate_number}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (array $record): bool => $record['status'] === 'registered' && $record['certificate_number'] === null),
            ])
            ->emptyStateHeading('Na termínu nejsou žádní zapsaní studenti')
            ->paginated(false);
    }

    /**
     * @return array<int, array<string, mixed>>
     */
    private function graduationRows(?string $resultFilter): array
    {
        /** @var CourseTerm $term */
        $term = $this->getRecord();

        return app(CertificateService::class)
            ->getGraduationSummary($term)
            ->map(function (Registration $reg): array {
                $stats = $reg->attendance_stats;
                $certificate = $reg->certificate;

                $result = match (true) {
                    $certificate !== null => self::RESULT_GRADUATED,
                    $stats['meets_threshold'] => self::RESULT_ELIGIBLE,
                    default => self::RESULT_NOT_ELIGIBLE,
                };

                return [
                    'id' => $reg->id,
                    'student_name' => $reg->student?->full_name ?? '—',
                    'student_email' => $reg->student?->email ?? '',
                    'attendance' => "{$stats['attended_lessons']} / {$stats['total_lessons']} lekcí ({$stats['percentage']} %)",
                    'meets_threshold' => $stats['meets_threshold'],
                    'status' => $reg->status,
                    'status_label' => $reg->status_label,
                    'status_color' => $reg->status_color,
                    'result' => $result,
                    'result_label' => match ($result) {
                        self::RESULT_GRADUATED => 'Úspěšný absolvent',
                        self::RESULT_ELIGIBLE => 'Splňuje podmínky',
                        default => 'Nesplňuje docházku',
                    },
                    'certificate_number' => $certificate?->certificate_number,
                    'issue_date' => $certificate?->issue_date?->format('d. m. Y'),
                ];
            })
            ->when($resultFilter, fn ($rows) => $rows->where('result', $resultFilter))
            ->keyBy('id')
            ->all();
    }
}

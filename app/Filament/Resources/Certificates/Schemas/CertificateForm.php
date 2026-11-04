<?php

namespace App\Filament\Resources\Certificates\Schemas;

use App\Models\Registration;
use App\Services\AttendanceService;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Schema;

/**
 * Certificate issuing form.
 *
 * The certificate number and issue date are never entered manually – they are generated
 * by CertificateService (unique sequence AT-YYYY-XXXXX), which also enforces the
 * 70 % attendance threshold and lecturer approval (Rozhodnutí 1).
 */
class CertificateForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Vystavení osvědčení o absolvování')
                    ->description('Číslo osvědčení a datum vydání se vygenerují automaticky. Vydané osvědčení již nelze upravit ani smazat.')
                    ->columnSpanFull()
                    ->schema([
                        Select::make('registration_id')
                            ->label('Přihláška (absolvent a termín)')
                            ->options(fn (): array => self::eligibleRegistrationOptions())
                            ->searchable()
                            ->required()
                            ->live()
                            ->helperText('Nabízeny jsou pouze aktivní přihlášky (stav Zaregistrováno) bez vystaveného osvědčení.'),
                        TextEntry::make('attendance_info')
                            ->label('Docházka studenta')
                            ->state(fn (Get $get): string => self::attendanceInfo($get('registration_id')))
                            ->visible(fn (Get $get): bool => filled($get('registration_id'))),
                        Toggle::make('lecturer_approved')
                            ->label('Lektor formálně schválil úspěšné absolvování')
                            ->default(true),
                        Toggle::make('admin_exception')
                            ->label('Udělit administrátorskou výjimku (při nesplnění 70% docházky)')
                            ->default(false)
                            ->live(),
                        Textarea::make('exception_reason')
                            ->label('Odůvodnění výjimky')
                            ->placeholder('např. Uznání praxe a náhradní seminární práce')
                            ->required(fn (Get $get): bool => (bool) $get('admin_exception'))
                            ->visible(fn (Get $get): bool => (bool) $get('admin_exception')),
                    ]),
            ]);
    }

    /**
     * @return array<int, string>
     */
    private static function eligibleRegistrationOptions(): array
    {
        return Registration::query()
            ->with(['student', 'courseTerm.course'])
            ->where('status', 'registered')
            ->whereDoesntHave('certificate')
            ->get()
            ->mapWithKeys(fn (Registration $reg): array => [
                $reg->id => sprintf(
                    '%s – %s (%s)',
                    $reg->student?->full_name ?? '—',
                    $reg->courseTerm?->course?->name ?? '—',
                    $reg->courseTerm?->formatted_date_range ?? '—',
                ),
            ])
            ->all();
    }

    private static function attendanceInfo(mixed $registrationId): string
    {
        $registration = Registration::find($registrationId);

        if (! $registration) {
            return '—';
        }

        $stats = app(AttendanceService::class)->calculateAttendanceStats($registration);

        return $stats['meets_threshold']
            ? "{$stats['percentage']} % – splňuje povinnou 70% docházku"
            : "{$stats['percentage']} % – NESPLŇUJE 70% docházku (nutná administrátorská výjimka)";
    }
}

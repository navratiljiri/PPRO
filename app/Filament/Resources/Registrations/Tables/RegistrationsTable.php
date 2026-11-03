<?php

namespace App\Filament\Resources\Registrations\Tables;

use App\Models\Registration;
use App\Services\CertificateService;
use App\Services\RegistrationService;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Notifications\Notification;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;

class RegistrationsTable
{
    public static function configure(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('id')
                    ->label('# ID')
                    ->sortable()
                    ->width('60px'),
                TextColumn::make('student.full_name')
                    ->label('Student')
                    ->state(fn (Registration $record): string => $record->student ? "{$record->student->first_name} {$record->student->last_name}" : '—')
                    ->searchable(['students.first_name', 'students.last_name'])
                    ->sortable()
                    ->weight('bold'),
                TextColumn::make('courseTerm.course.name')
                    ->label('Kurz')
                    ->searchable()
                    ->sortable()
                    ->wrap(),
                TextColumn::make('courseTerm.classroom')
                    ->label('Učebna')
                    ->searchable(),
                TextColumn::make('status')
                    ->label('Stav přihlášky')
                    ->badge()
                    ->formatStateUsing(fn (Registration $record): string => match ($record->status) {
                        'registered' => 'Zaregistrováno',
                        'waiting_list' => "V pořadníku (#{$record->waiting_order})",
                        'offered' => 'Nabídnuto místo',
                        'cancelled' => 'Zrušeno',
                        'completed' => 'Absolvováno',
                        default => $record->status,
                    })
                    ->color(fn (Registration $record): string => match ($record->status) {
                        'registered' => 'success',
                        'waiting_list' => 'warning',
                        'offered' => 'info',
                        'cancelled' => 'danger',
                        'completed' => 'gray',
                        default => 'secondary',
                    }),
                TextColumn::make('attendance_percentage')
                    ->label('Docházka')
                    ->state(fn (Registration $record): string => "{$record->attendance_percentage} %")
                    ->badge()
                    ->color(fn (Registration $record): string => $record->attendance_percentage >= 70 ? 'success' : 'danger')
                    ->alignCenter(),
                TextColumn::make('offer_expires_at')
                    ->label('Nabídka vyprší')
                    ->dateTime('d. m. Y H:i')
                    ->placeholder('—')
                    ->toggleable(isToggledHiddenByDefault: true),
                TextColumn::make('created_at')
                    ->label('Podáno dne')
                    ->dateTime('d. m. Y H:i')
                    ->sortable(),
            ])
            ->filters([
                SelectFilter::make('status')
                    ->label('Stav přihlášky')
                    ->options([
                        'registered' => 'Zaregistrováno',
                        'waiting_list' => 'V pořadníku (náhradník)',
                        'offered' => 'Nabídnuto uvolněné místo',
                        'cancelled' => 'Zrušeno',
                        'completed' => 'Absolvováno',
                    ]),
            ])
            ->recordActions([
                ViewAction::make()->label('Detail'),

                // Action: Zrušit přihlášku (audit trail - nesmí smazat záznam z DB)
                Action::make('cancel')
                    ->label('Zrušit přihlášku')
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->requiresConfirmation()
                    ->modalHeading('Zrušení přihlášky a uvolnění místa')
                    ->modalDescription('Přihláška nebude smazána z databáze (uchování pro audit). Uvolněné místo bude ihned automaticky nabídnuto prvnímu náhradníkovi v pořadníku.')
                    ->form([
                        Textarea::make('reason')
                            ->label('Důvod zrušení / odhlášení')
                            ->required()
                            ->placeholder('např. Nemoc zájemce, odvolání zaměstnavatelem...'),
                    ])
                    ->action(function (Registration $record, array $data): void {
                        app(RegistrationService::class)->cancelRegistration($record, $data['reason']);

                        Notification::make()
                            ->title('Přihláška byla zrušena')
                            ->body('Záznam byl označen jako zrušený a uvolněné místo bylo nabídnuto dalšímu v pořadníku.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Registration $record): bool => !in_array($record->status, ['cancelled', 'completed'])),

                // Action: Přijmout nabídnuté místo
                Action::make('accept_offer')
                    ->label('Potvrdit místo')
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->requiresConfirmation()
                    ->action(function (Registration $record): void {
                        app(RegistrationService::class)->acceptOffer($record);

                        Notification::make()
                            ->title('Místo bylo úspěšně potvrzeno')
                            ->body('Student byl zařazen mezi aktivně registrované účastníky.')
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Registration $record): bool => $record->status === 'offered'),

                // Action: Odmítnout nabídku uvolněného místa
                Action::make('decline_offer')
                    ->label('Odmítnout nabídku')
                    ->icon('heroicon-o-arrow-uturn-left')
                    ->color('warning')
                    ->requiresConfirmation()
                    ->action(function (Registration $record): void {
                        app(RegistrationService::class)->declineOffer($record, 'Zájemce odmítl nabídku uvolněného místa.');

                        Notification::make()
                            ->title('Nabídka byla odmítnuta')
                            ->body('Místo bylo postoupeno dalšímu zájemci v FIFO pořadníku.')
                            ->info()
                            ->send();
                    })
                    ->visible(fn (Registration $record): bool => $record->status === 'offered'),

                // Action: Vystavit osvědčení
                Action::make('issue_certificate')
                    ->label('Vystavit osvědčení')
                    ->icon('heroicon-o-academic-cap')
                    ->color('primary')
                    ->requiresConfirmation()
                    ->modalHeading('Vystavení osvědčení o absolvování kurzu')
                    ->modalDescription('Pro vystavení je vyžadována alespoň 70% docházka a pedagogický souhlas lektora.')
                    ->form([
                        Toggle::make('lecturer_approved')
                            ->label('Lektor formálně schválil úspěšné absolvování')
                            ->default(true)
                            ->required(),
                        Toggle::make('admin_exception')
                            ->label('Udělit administrátorskou výjimku (při nesplnění 70% docházky)')
                            ->default(false),
                        TextInput::make('exception_reason')
                            ->label('Odůvodnění výjimky')
                            ->placeholder('např. Uznání praxe a náhradní seminární práce')
                            ->visible(fn ($get) => $get('admin_exception')),
                    ])
                    ->action(function (Registration $record, array $data): void {
                        $cert = app(CertificateService::class)->issueCertificate(
                            $record,
                            lecturerApproved: (bool) $data['lecturer_approved'],
                            adminException: (bool) ($data['admin_exception'] ?? false),
                            exceptionReason: $data['exception_reason'] ?? null
                        );

                        Notification::make()
                            ->title('Osvědčení vystaveno')
                            ->body("Úspěšně vystaveno osvědčení s unikátním číslem {$cert->certificate_number}.")
                            ->success()
                            ->send();
                    })
                    ->visible(fn (Registration $record): bool => $record->status === 'registered' && !$record->certificate()->exists()),
            ])
            ->toolbarActions([]);
    }
}

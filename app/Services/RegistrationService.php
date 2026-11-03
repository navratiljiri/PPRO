<?php

namespace App\Services;

use App\Models\CourseTerm;
use App\Models\Registration;
use App\Models\Student;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registration Service (Application / Business Logic)
 *
 * Enforces business constraints:
 * - Capacity cannot be exceeded; excess registrants enter FIFO waiting list
 * - Prohibition of concurrent active registrations on the same course (Rozhodnutí 3)
 * - Late registration cut-off after term start with max 20% exception rule (Rozhodnutí 4)
 * - Semi-automated waiting list promotion with 24h offer lock (Rozhodnutí 2)
 * - Physical retention of cancelled registrations for audit purposes
 */
class RegistrationService
{
    /**
     * Register a student to a course term
     */
    public function registerStudent(CourseTerm $term, Student $student, bool $isAdminOverride = false): Registration
    {
        return DB::transaction(function () use ($term, $student, $isAdminOverride) {
            // Lock term row to prevent race conditions during concurrent requests
            $lockedTerm = CourseTerm::where('id', $term->id)->lockForUpdate()->firstOrFail();

            // Business Rule: Term cannot be cancelled or completed
            if (in_array($lockedTerm->status, ['completed', 'cancelled'])) {
                throw ValidationException::withMessages([
                    'course_term_id' => 'Nelze se přihlásit na termín, který byl ukončen nebo zrušen.',
                ]);
            }

            // Rozhodnutí 3: Zákaz souběžných aktivních přihlášek na identický kurz
            $existingActiveCourseRegistration = Registration::query()
                ->where('student_id', $student->id)
                ->whereIn('status', ['registered', 'waiting_list', 'offered'])
                ->whereHas('courseTerm', function ($q) use ($lockedTerm) {
                    $q->where('course_id', $lockedTerm->course_id);
                })
                ->exists();

            if ($existingActiveCourseRegistration) {
                throw ValidationException::withMessages([
                    'student_id' => 'Tento student již má na daný kurz aktivní přihlášku (registrovanou nebo v pořadníku). Souběžný zápis na stejný kurz není povolen.',
                ]);
            }

            // Rozhodnutí 4: Dodatečné přihlášení po zahájení termínu
            if (Carbon::now()->greaterThanOrEqualTo($lockedTerm->start_date)) {
                if (!$isAdminOverride) {
                    throw ValidationException::withMessages([
                        'course_term_id' => 'Termín již byl zahájen. Veřejný zápis byl uzavřen.',
                    ]);
                }

                // Administrátorská výjimka: povolena pouze do 20 % časové dotace / lekcí
                $totalLessons = $lockedTerm->lessons()->count();
                if ($totalLessons > 0) {
                    $pastLessons = $lockedTerm->lessons()->where('starts_at', '<=', Carbon::now())->count();
                    $ratio = $pastLessons / $totalLessons;
                    if ($ratio > 0.20) {
                        throw ValidationException::withMessages([
                            'course_term_id' => 'Termín již překročil 20 % své výuky (' . round($ratio * 100) . ' %). Dodatečný zápis nelze povolit, protože student by nemohl splnit povinnou 70% docházku.',
                        ]);
                    }
                }
            }

            // Capacity Check: If active spots < capacity => registered; else => waiting list (FIFO)
            $activeCount = $lockedTerm->activeRegistrations()->count();

            if ($activeCount < $lockedTerm->capacity) {
                return Registration::create([
                    'course_term_id' => $lockedTerm->id,
                    'student_id' => $student->id,
                    'status' => 'registered',
                    'waiting_order' => null,
                ]);
            }

            // Calculate next FIFO waiting order
            $maxWaitingOrder = (int) Registration::query()
                ->where('course_term_id', $lockedTerm->id)
                ->where('status', 'waiting_list')
                ->max('waiting_order');

            return Registration::create([
                'course_term_id' => $lockedTerm->id,
                'student_id' => $student->id,
                'status' => 'waiting_list',
                'waiting_order' => $maxWaitingOrder + 1,
            ]);
        });
    }

    /**
     * Cancel a registration (audit trail preserved; triggers waiting list advancement)
     */
    public function cancelRegistration(Registration $registration, string $reason): void
    {
        DB::transaction(function () use ($registration, $reason) {
            $lockedRegistration = Registration::where('id', $registration->id)->lockForUpdate()->firstOrFail();
            $term = CourseTerm::where('id', $lockedRegistration->course_term_id)->lockForUpdate()->firstOrFail();

            $wasActive = in_array($lockedRegistration->status, ['registered', 'offered']);

            $lockedRegistration->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'waiting_order' => null,
                'offer_expires_at' => null,
            ]);

            // If an active spot was freed, offer it to the first candidate in waiting list
            if ($wasActive) {
                $this->promoteWaitingList($term);
            }
        });
    }

    /**
     * Promote the first candidate in FIFO queue to 'offered' (Rozhodnutí 2: 24h zámek)
     */
    public function promoteWaitingList(CourseTerm $term): ?Registration
    {
        return DB::transaction(function () use ($term) {
            $lockedTerm = CourseTerm::where('id', $term->id)->lockForUpdate()->firstOrFail();

            $activeCount = $lockedTerm->activeRegistrations()->count();
            if ($activeCount >= $lockedTerm->capacity) {
                return null;
            }

            $firstWaitlister = Registration::query()
                ->where('course_term_id', $lockedTerm->id)
                ->where('status', 'waiting_list')
                ->orderBy('waiting_order', 'asc')
                ->first();

            if (!$firstWaitlister) {
                return null;
            }

            $firstWaitlister->update([
                'status' => 'offered',
                'offer_expires_at' => Carbon::now()->addHours(24),
            ]);

            return $firstWaitlister;
        });
    }

    /**
     * Accept offered spot within deadline
     */
    public function acceptOffer(Registration $registration): void
    {
        DB::transaction(function () use ($registration) {
            $locked = Registration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            if ($locked->status !== 'offered') {
                throw ValidationException::withMessages([
                    'status' => 'Přihláška není ve stavu nabídnutého místa.',
                ]);
            }

            if ($locked->offer_expires_at && Carbon::now()->greaterThan($locked->offer_expires_at)) {
                $this->declineOffer($locked, 'Časové okno 24 hodin pro potvrzení místa vypršelo.');
                throw ValidationException::withMessages([
                    'offer_expires_at' => 'Časové okno pro přijetí nabídky uvolněného místa již vypršelo.',
                ]);
            }

            $locked->update([
                'status' => 'registered',
                'waiting_order' => null,
                'offer_expires_at' => null,
            ]);
        });
    }

    /**
     * Decline offered spot (or expired offer) -> spot offered to next waiting applicant
     */
    public function declineOffer(Registration $registration, string $reason = 'Nabídka odmítnuta zájemcem'): void
    {
        DB::transaction(function () use ($registration, $reason) {
            $locked = Registration::where('id', $registration->id)->lockForUpdate()->firstOrFail();
            $term = CourseTerm::where('id', $locked->course_term_id)->lockForUpdate()->firstOrFail();

            $locked->update([
                'status' => 'cancelled',
                'cancellation_reason' => $reason,
                'waiting_order' => null,
                'offer_expires_at' => null,
            ]);

            // Offer spot to next candidate
            $this->promoteWaitingList($term);
        });
    }
}

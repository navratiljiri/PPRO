<?php

namespace App\Services;

use App\Models\Certificate;
use App\Models\CourseTerm;
use App\Models\Registration;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Certificate Service (Application / Business Logic)
 *
 * Implements:
 * - Two-step completion validation (70% attendance + lecturer approval - Rozhodnutí 1)
 * - Sequential unique numbering per year (e.g. AT-2026-00001)
 * - Invariant: Issued certificates are immutable and non-recyclable
 */
class CertificateService
{
    public function __construct(
        protected AttendanceService $attendanceService
    ) {}

    /**
     * Issue a certificate for a student's registration
     */
    public function issueCertificate(
        Registration $registration,
        bool $lecturerApproved = true,
        bool $adminException = false,
        ?string $exceptionReason = null
    ): Certificate {
        return DB::transaction(function () use ($registration, $lecturerApproved, $adminException, $exceptionReason) {
            $lockedReg = Registration::where('id', $registration->id)->lockForUpdate()->firstOrFail();

            if ($lockedReg->status === 'cancelled') {
                throw ValidationException::withMessages([
                    'registration' => 'Nelze vystavit osvědčení pro zrušenou přihlášku.',
                ]);
            }

            if ($lockedReg->certificate()->exists()) {
                throw ValidationException::withMessages([
                    'registration' => 'Osvědčení pro tuto přihlášku již bylo vystaveno.',
                ]);
            }

            // Rozhodnutí 1: Dvoustupňový hybridní model – kontrola 70% docházky
            $stats = $this->attendanceService->calculateAttendanceStats($lockedReg);

            if (!$stats['meets_threshold'] && !$adminException) {
                throw ValidationException::withMessages([
                    'attendance' => "Student nesplnil povinnou 70% docházku (dosaženo pouze {$stats['percentage']} %). Osvědčení nelze vystavit bez schválené administrátorské výjimky.",
                ]);
            }

            if (!$lecturerApproved) {
                throw ValidationException::withMessages([
                    'lecturer_approved' => 'Lektor nepotvrdil úspěšné absolvování kurzu (Neprospěl).',
                ]);
            }

            // Generate next unique certificate number: AT-YYYY-XXXXX
            $year = Carbon::now()->year;
            $countThisYear = Certificate::whereYear('created_at', $year)->count();
            $sequence = $countThisYear + 1;
            $certificateNumber = sprintf('AT-%d-%05d', $year, $sequence);

            // Double check uniqueness loop just in case
            while (Certificate::where('certificate_number', $certificateNumber)->exists()) {
                $sequence++;
                $certificateNumber = sprintf('AT-%d-%05d', $year, $sequence);
            }

            $certificate = Certificate::create([
                'registration_id' => $lockedReg->id,
                'certificate_number' => $certificateNumber,
                'issue_date' => Carbon::now()->toDateString(),
                'lecturer_approved' => true,
            ]);

            // Update registration status to completed
            $lockedReg->update([
                'status' => 'completed',
            ]);

            return $certificate;
        });
    }

    /**
     * Get list of registrations eligible for graduation on a term
     */
    public function getGraduationSummary(CourseTerm $term): Collection
    {
        return $term->registrations()
            ->with(['student', 'certificate'])
            ->whereIn('status', ['registered', 'completed'])
            ->get()
            ->map(function (Registration $reg) {
                $stats = $this->attendanceService->calculateAttendanceStats($reg);
                $reg->attendance_stats = $stats;
                $reg->is_eligible = $stats['meets_threshold'];
                return $reg;
            });
    }
}

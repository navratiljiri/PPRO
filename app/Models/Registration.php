<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

/**
 * Domain model representing a student registration / application for a course term (Akademie Trutnov)
 *
 * @property int $id
 * @property int $course_term_id
 * @property int $student_id
 * @property string $status registered | waiting_list | offered | cancelled | completed
 * @property int|null $waiting_order FIFO queue position for waiting list
 * @property \Illuminate\Support\Carbon|null $offer_expires_at
 * @property string|null $cancellation_reason
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Registration extends Model
{
    use HasFactory;

    protected $table = 'registrations';

    protected $fillable = [
        'course_term_id',
        'student_id',
        'status',
        'waiting_order',
        'offer_expires_at',
        'cancellation_reason',
    ];

    protected $casts = [
        'waiting_order' => 'integer',
        'offer_expires_at' => 'datetime',
    ];

    /**
     * Integrity guard: a registration can never be moved to another term or student.
     * Such a change would bypass the capacity check, FIFO waiting list and Rozhodnutí 3
     * enforced by RegistrationService – cancel and create a new registration instead.
     */
    protected static function booted(): void
    {
        static::updating(function (Registration $registration) {
            if ($registration->isDirty(['course_term_id', 'student_id'])) {
                throw new \RuntimeException('Přihlášku nelze přesunout na jiný termín ani jiného studenta. Zrušte ji a vytvořte novou přihlášku.');
            }
        });
    }

    /**
     * Parent course term
     */
    public function courseTerm(): BelongsTo
    {
        return $this->belongsTo(CourseTerm::class, 'course_term_id');
    }

    /**
     * Enrolled student
     */
    public function student(): BelongsTo
    {
        return $this->belongsTo(Student::class, 'student_id');
    }

    /**
     * Attendance records across lessons
     */
    public function attendanceRecords(): HasMany
    {
        return $this->hasMany(AttendanceRecord::class, 'registration_id');
    }

    /**
     * Certificate earned by completing this registration
     */
    public function certificate(): HasOne
    {
        return $this->hasOne(Certificate::class, 'registration_id');
    }

    /**
     * Scope for confirmed active registrations
     */
    public function scopeRegistered(Builder $query): Builder
    {
        return $query->where('status', 'registered');
    }

    /**
     * Scope for waiting list
     */
    public function scopeWaitingList(Builder $query): Builder
    {
        return $query->where('status', 'waiting_list')->orderBy('waiting_order', 'asc');
    }

    /**
     * Scope for offered spots
     */
    public function scopeOffered(Builder $query): Builder
    {
        return $query->where('status', 'offered');
    }

    /**
     * Scope for active registrations (occupying capacity or pending spot)
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->whereIn('status', ['registered', 'waiting_list', 'offered']);
    }

    /**
     * Status label in Czech
     */
    protected function statusLabel(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'registered' => 'Zaregistrováno',
                'waiting_list' => 'V pořadníku (náhradník)',
                'offered' => 'Nabídnuto uvolněné místo',
                'cancelled' => 'Zrušeno',
                'completed' => 'Absolvováno',
                default => $this->status,
            },
        );
    }

    /**
     * Status color for badges
     */
    protected function statusColor(): Attribute
    {
        return Attribute::make(
            get: fn () => match ($this->status) {
                'registered' => 'success',
                'waiting_list' => 'warning',
                'offered' => 'info',
                'cancelled' => 'danger',
                'completed' => 'gray',
                default => 'secondary',
            },
        );
    }

    /**
     * Calculate attendance percentage for this registration
     */
    protected function attendancePercentage(): Attribute
    {
        return Attribute::make(
            get: function () {
                $term = $this->courseTerm;
                if (!$term) {
                    return 0.0;
                }

                $totalLessons = $term->lessons()->count();
                if ($totalLessons === 0) {
                    return 0.0;
                }

                $presentLessons = $this->attendanceRecords()
                    ->where('is_present', true)
                    ->count();

                return round(($presentLessons / $totalLessons) * 100, 1);
            },
        );
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain model representing a scheduled course term (Akademie Trutnov)
 * 
 * @property int $id
 * @property int $course_id
 * @property \Illuminate\Support\Carbon $start_date
 * @property \Illuminate\Support\Carbon $end_date
 * @property string $classroom
 * @property int $capacity
 * @property string $status
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class CourseTerm extends Model
{
    use HasFactory;

    protected $table = 'course_terms';

    protected $fillable = [
        'course_id',
        'start_date',
        'end_date',
        'classroom',
        'capacity',
        'status',
        'note',
    ];

    protected $casts = [
        'start_date' => 'datetime',
        'end_date' => 'datetime',
        'capacity' => 'integer',
    ];

    /**
     * Parent course
     */
    public function course(): BelongsTo
    {
        return $this->belongsTo(Course::class, 'course_id');
    }

    /**
     * Lessons within this term
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class, 'course_term_id')->orderBy('starts_at');
    }

    /**
     * M:N relationship: Lecturers teaching on this term
     */
    public function lecturers(): BelongsToMany
    {
        return $this->belongsToMany(Lecturer::class, 'lessons', 'course_term_id', 'lecturer_id')->distinct();
    }

    /**
     * Scope filtering by status
     */
    public function scopeStatus(Builder $query, ?string $status): Builder
    {
        if (empty($status)) {
            return $query;
        }

        return $query->where('status', $status);
    }

    /**
     * Status label in Czech
     */
    public function getStatusLabelAttribute(): string
    {
        return match ($this->status) {
            'enrolling' => 'Zápis otevřen',
            'in_progress' => 'Probíhá',
            'completed' => 'Dokončeno',
            'cancelled' => 'Zrušeno',
            default => $this->status,
        };
    }

    /**
     * Status badge CSS classes
     */
    public function getStatusBadgeClassesAttribute(): string
    {
        return match ($this->status) {
            'enrolling' => 'bg-[#ecf4ed] text-[#215a28] border-[#cde0cf]',
            'in_progress' => 'bg-[#fdf6ec] text-[#9a5b13] border-[#f5dfc6]',
            'completed' => 'bg-[#f2eee9] text-[#78716c] border-[#ebe7df]',
            'cancelled' => 'bg-[#fdf2f2] text-[#991b1b] border-[#fecaca]',
            default => 'bg-[#f2eee9] text-[#78716c] border-[#ebe7df]',
        };
    }

    /**
     * Formatted date range in Czech locale
     */
    public function getFormattedDateRangeAttribute(): string
    {
        return $this->start_date->format('d. m. Y') . ' – ' . $this->end_date->format('d. m. Y');
    }

    /**
     * Sum of duration hours across all scheduled lessons
     */
    public function getTotalLessonHoursAttribute(): int
    {
        return (int) $this->lessons()->sum('duration_hours');
    }
}

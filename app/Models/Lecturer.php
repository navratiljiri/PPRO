<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Domain model representing an external lecturer (Akademie Trutnov)
 * 
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone
 * @property string|null $specialization
 * @property string|null $bio
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Lecturer extends Model
{
    use HasFactory;

    protected $table = 'lecturers';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
        'specialization',
        'bio',
    ];

    /**
     * Lessons taught by this lecturer
     */
    public function lessons(): HasMany
    {
        return $this->hasMany(Lesson::class);
    }

    /**
     * M:N relationship: Terms in which this lecturer teaches
     */
    public function courseTerms(): BelongsToMany
    {
        return $this->belongsToMany(CourseTerm::class, 'lessons', 'lecturer_id', 'course_term_id')->distinct();
    }

    /**
     * Full name accessor
     */
    public function getFullNameAttribute(): string
    {
        return "{$this->first_name} {$this->last_name}";
    }

    /**
     * Total hours taught across all lessons
     */
    public function getTotalTaughtHoursAttribute(): int
    {
        return (int) $this->lessons()->sum('duration_hours');
    }

    /**
     * Search scope by name, email or specialization
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('first_name', 'like', "%{$term}%")
              ->orWhere('last_name', 'like', "%{$term}%")
              ->orWhere('email', 'like', "%{$term}%")
              ->orWhere('specialization', 'like', "%{$term}%");
        });
    }
}

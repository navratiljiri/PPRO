<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain model representing an individual teaching lesson/block
 * 
 * @property int $id
 * @property int $course_term_id
 * @property int $lecturer_id
 * @property \Illuminate\Support\Carbon $starts_at
 * @property \Illuminate\Support\Carbon $ends_at
 * @property int $duration_hours
 * @property string $topic
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Lesson extends Model
{
    use HasFactory;

    protected $table = 'lessons';

    protected $fillable = [
        'course_term_id',
        'lecturer_id',
        'starts_at',
        'ends_at',
        'duration_hours',
        'topic',
    ];

    protected $casts = [
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'duration_hours' => 'integer',
    ];

    /**
     * Parent term
     */
    public function courseTerm(): BelongsTo
    {
        return $this->belongsTo(CourseTerm::class, 'course_term_id');
    }

    /**
     * Assigned lecturer
     */
    public function lecturer(): BelongsTo
    {
        return $this->belongsTo(Lecturer::class, 'lecturer_id');
    }
}

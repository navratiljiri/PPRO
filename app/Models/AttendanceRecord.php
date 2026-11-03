<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain model representing student attendance at a specific lesson (Akademie Trutnov)
 *
 * @property int $id
 * @property int $registration_id
 * @property int $lesson_id
 * @property bool $is_present
 * @property string|null $note
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class AttendanceRecord extends Model
{
    use HasFactory;

    protected $table = 'attendance_records';

    protected $fillable = [
        'registration_id',
        'lesson_id',
        'is_present',
        'note',
    ];

    protected $casts = [
        'is_present' => 'boolean',
    ];

    /**
     * Associated registration
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }

    /**
     * Associated lesson
     */
    public function lesson(): BelongsTo
    {
        return $this->belongsTo(Lesson::class, 'lesson_id');
    }
}

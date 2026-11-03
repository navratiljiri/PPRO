<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasManyThrough;

/**
 * Domain model representing a Student / Attendee (Akademie Trutnov)
 *
 * @property int $id
 * @property string $first_name
 * @property string $last_name
 * @property string $email
 * @property string|null $phone
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Student extends Model
{
    use HasFactory;

    protected $table = 'students';

    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'phone',
    ];

    /**
     * All course registrations for this student
     */
    public function registrations(): HasMany
    {
        return $this->hasMany(Registration::class, 'student_id');
    }

    /**
     * Active registrations (registered, waiting_list, offered)
     */
    public function activeRegistrations(): HasMany
    {
        return $this->registrations()->whereIn('status', ['registered', 'waiting_list', 'offered']);
    }

    /**
     * Capitalize first name
     */
    protected function firstName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? ucfirst($value) : '',
        );
    }

    /**
     * Capitalize last name
     */
    protected function lastName(): Attribute
    {
        return Attribute::make(
            get: fn (?string $value) => $value ? ucfirst($value) : '',
        );
    }

    /**
     * Full name accessor
     */
    protected function fullName(): Attribute
    {
        return Attribute::make(
            get: fn () => "{$this->first_name} {$this->last_name}",
        );
    }

    /**
     * Scope for searching students by name or email
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $sub) use ($term) {
            $sub->where('first_name', 'like', "%{$term}%")
                ->orWhere('last_name', 'like', "%{$term}%")
                ->orWhere('email', 'like', "%{$term}%")
                ->orWhere('phone', 'like', "%{$term}%");
        });
    }
}

<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Domain model representing an educational course (Akademie Trutnov)
 * 
 * @property int $id
 * @property string $code
 * @property string $name
 * @property string $annotation
 * @property int $duration_hours
 * @property float $price
 * @property bool $is_accredited
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Course extends Model
{
    use HasFactory;

    protected $table = 'courses';

    protected $fillable = [
        'code',
        'name',
        'annotation',
        'duration_hours',
        'price',
        'is_accredited',
        'status',
    ];

    protected $casts = [
        'duration_hours' => 'integer',
        'price' => 'decimal:2',
        'is_accredited' => 'boolean',
    ];

    /**
     * Scope for active courses
     */
    public function scopeActive(Builder $query): Builder
    {
        return $query->where('status', 'active');
    }

    /**
     * Scope for accredited courses
     */
    public function scopeAccredited(Builder $query, bool $accredited = true): Builder
    {
        return $query->where('is_accredited', $accredited);
    }

    /**
     * Scope for searching courses by title, code or annotation
     */
    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        if (empty($term)) {
            return $query;
        }

        return $query->where(function (Builder $q) use ($term) {
            $q->where('name', 'like', "%{$term}%")
              ->orWhere('code', 'like', "%{$term}%")
              ->orWhere('annotation', 'like', "%{$term}%");
        });
    }

    /**
     * Helper accessor for price formatted in Czech koruna
     */
    public function getFormattedPriceAttribute(): string
    {
        return number_format((float) $this->price, 0, ',', ' ') . ' Kč';
    }
}

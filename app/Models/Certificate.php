<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Domain model representing a Completion Certificate (Akademie Trutnov)
 * 
 * Invariant: Certificates are immutable once issued; certificate numbers are unique and cannot be recycled.
 *
 * @property int $id
 * @property int $registration_id
 * @property string $certificate_number
 * @property \Illuminate\Support\Carbon $issue_date
 * @property bool $lecturer_approved
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 */
class Certificate extends Model
{
    use HasFactory;

    protected $table = 'certificates';

    protected $fillable = [
        'registration_id',
        'certificate_number',
        'issue_date',
        'lecturer_approved',
    ];

    protected $casts = [
        'issue_date' => 'date',
        'lecturer_approved' => 'boolean',
    ];

    /**
     * Immutability guard: once created, certificates cannot be modified or deleted.
     */
    protected static function booted(): void
    {
        static::updating(function (Certificate $certificate) {
            throw new \RuntimeException('Vydané osvědčení je neměnné (obchodní pravidlo klienta: číslo a záznam se nesmí měnit).');
        });

        static::deleting(function (Certificate $certificate) {
            throw new \RuntimeException('Vydané osvědčení nelze smazat z evidence (obchodní pravidlo klienta pro auditovatelnost).');
        });
    }

    /**
     * Associated registration
     */
    public function registration(): BelongsTo
    {
        return $this->belongsTo(Registration::class, 'registration_id');
    }
}

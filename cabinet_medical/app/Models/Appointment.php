<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Appointment extends Model
{
    use HasFactory;

    protected $fillable = [
        'patient_id',
        'doctor_id',
        'service_id',
        'appointment_date',
        'appointment_time',
        'status',
    ];

    public const STATUS_PENDING = 'pending';
    public const STATUS_CONFIRMED = 'confirmed';
    public const STATUS_CANCELLED = 'cancelled';

    public static function statuses(): array
    {
        return [
            self::STATUS_PENDING,
            self::STATUS_CONFIRMED,
            self::STATUS_CANCELLED,
        ];
    }

    public function statusBadgeClass(): string
    {
        return match ($this->status) {
            self::STATUS_CONFIRMED => 'bg-success',
            self::STATUS_CANCELLED => 'bg-danger',
            default => 'bg-warning text-dark',
        };
    }

    protected static function booted(): void
    {
        static::saving(function (Appointment $appointment): void {
            $appointment->total_price = Service::query()
                ->whereKey($appointment->service_id)
                ->value('price') ?? 0;
        });
    }

    public function getUserIdAttribute(): mixed
    {
        return $this->patient_id;
    }

    public function setUserIdAttribute(mixed $value): void
    {
        $this->attributes['patient_id'] = $value;
    }

    public function getDateAttribute(): mixed
    {
        return $this->appointment_date;
    }

    public function setDateAttribute(mixed $value): void
    {
        $this->attributes['appointment_date'] = $value;
    }

    public function getTimeAttribute(): mixed
    {
        return $this->appointment_time;
    }

    public function setTimeAttribute(mixed $value): void
    {
        $this->attributes['appointment_time'] = $value;
    }

    protected function casts(): array
    {
        return [
            'appointment_date' => 'date',
            'total_price' => 'decimal:2',
        ];
    }

    public function patient(): BelongsTo
    {
        return $this->belongsTo(User::class, 'patient_id');
    }

    public function user(): BelongsTo
    {
        return $this->patient();
    }

    public function doctor(): BelongsTo
    {
        return $this->belongsTo(User::class, 'doctor_id');
    }

    public function service(): BelongsTo
    {
        return $this->belongsTo(Service::class);
    }
}

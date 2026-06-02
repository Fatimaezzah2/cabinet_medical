<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

#[Fillable(['name', 'email', 'password', 'role', 'is_approved'])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

    public const ROLE_PATIENT = 'patient';
    public const ROLE_DOCTOR = 'doctor';
    public const ROLE_ADMIN = 'admin';

    protected $attributes = [
        'role' => self::ROLE_PATIENT,
        'is_approved' => false,
    ];

    public static function roles(): array
    {
        return [
            self::ROLE_ADMIN,
            self::ROLE_PATIENT,
            self::ROLE_DOCTOR,
        ];
    }

    public function isAdmin(): bool
    {
        return $this->role === self::ROLE_ADMIN;
    }

    public function isPatient(): bool
    {
        return $this->role === self::ROLE_PATIENT;
    }

    public function isDoctor(): bool
    {
        return $this->role === self::ROLE_DOCTOR;
    }

    public function scopePatients(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_PATIENT);
    }

    public function scopeDoctors(Builder $query): Builder
    {
        return $query->where('role', self::ROLE_DOCTOR);
    }

    public function appointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'patient_id');
    }

    public function doctorAppointments(): HasMany
    {
        return $this->hasMany(Appointment::class, 'doctor_id');
    }

    public function activityLogs(): HasMany
    {
        return $this->hasMany(ActivityLog::class);
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_approved' => 'boolean',
        ];
    }
}

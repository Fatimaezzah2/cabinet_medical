<?php

namespace App\Services;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\User;

class ActivityLogService
{
    public function userCreated(User $user, ?User $actor = null): void
    {
        $this->log(
            $actor ?? $user,
            'user_created',
            "User {$user->name} ({$user->email}) was created with role {$user->role}."
        );
    }

    public function roleChanged(User $user, string $oldRole, string $newRole, ?User $actor = null): void
    {
        $this->log(
            $actor,
            'role_changed',
            "User {$user->name} role changed from {$oldRole} to {$newRole}."
        );
    }

    public function appointmentCreated(Appointment $appointment, ?User $actor = null): void
    {
        $appointment->loadMissing(['patient', 'doctor', 'service']);

        $this->log(
            $actor,
            'appointment_created',
            "Appointment {$appointment->id} created for {$appointment->patient->name} with {$appointment->doctor->name} ({$appointment->service->name})."
        );
    }

    public function appointmentCancelled(Appointment $appointment, ?User $actor = null): void
    {
        $appointment->loadMissing(['patient', 'doctor', 'service']);

        $this->log(
            $actor,
            'appointment_cancelled',
            "Appointment {$appointment->id} for {$appointment->patient->name} with {$appointment->doctor->name} was cancelled."
        );
    }

    private function log(?User $actor, string $action, string $description): void
    {
        ActivityLog::create([
            'user_id' => $actor?->id,
            'action' => $action,
            'description' => $description,
        ]);
    }
}

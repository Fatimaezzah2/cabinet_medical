<?php

namespace App\Services;

use App\Mail\AppointmentConfirmedMail;
use App\Mail\AppointmentReminderMail;
use App\Models\ActivityLog;
use App\Models\Appointment;
use Illuminate\Support\Facades\Mail;

class AppointmentNotificationService
{
    public function sendConfirmation(Appointment $appointment): void
    {
        $appointment->load(['patient', 'doctor', 'service']);

        Mail::to($appointment->patient->email)
            ->send(new AppointmentConfirmedMail($appointment));

        Mail::to($appointment->doctor->email)
            ->send(new AppointmentConfirmedMail($appointment));
    }

    public function sendReminder(Appointment $appointment): void
    {
        $appointment->load(['patient', 'doctor', 'service']);

        Mail::to($appointment->patient->email)
            ->send(new AppointmentReminderMail($appointment));

        Mail::to($appointment->doctor->email)
            ->send(new AppointmentReminderMail($appointment));

        ActivityLog::create([
            'user_id' => $appointment->patient_id,
            'action' => 'appointment_reminder_sent',
            'description' => $this->reminderLogDescription($appointment),
        ]);
    }

    public function reminderWasSent(Appointment $appointment): bool
    {
        return ActivityLog::where('action', 'appointment_reminder_sent')
            ->where('description', $this->reminderLogDescription($appointment))
            ->exists();
    }

    private function reminderLogDescription(Appointment $appointment): string
    {
        return "appointment:{$appointment->id}:24h";
    }
}

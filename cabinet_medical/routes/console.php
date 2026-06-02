<?php

use Illuminate\Foundation\Inspiring;
use App\Models\Appointment;
use App\Services\AppointmentNotificationService;
use Illuminate\Support\Facades\Schedule;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('appointments:send-reminders', function (AppointmentNotificationService $notifications): int {
    $start = now()->addDay()->startOfMinute();
    $end = now()->addDay()->addHour()->startOfMinute();

    Appointment::with(['patient', 'doctor', 'service'])
        ->where('status', Appointment::STATUS_CONFIRMED)
        ->whereDate('appointment_date', '>=', $start->toDateString())
        ->whereDate('appointment_date', '<=', $end->toDateString())
        ->orderBy('appointment_date')
        ->orderBy('appointment_time')
        ->get()
        ->filter(function (Appointment $appointment) use ($start, $end): bool {
            $appointmentAt = $appointment->appointment_date
                ->copy()
                ->setTimeFromTimeString((string) $appointment->appointment_time);

            return $appointmentAt->greaterThanOrEqualTo($start)
                && $appointmentAt->lessThan($end);
        })
        ->reject(fn (Appointment $appointment): bool => $notifications->reminderWasSent($appointment))
        ->each(function (Appointment $appointment) use ($notifications): void {
            $notifications->sendReminder($appointment);
            $this->info("Reminder sent for appointment {$appointment->id}.");
        });

    return 0;
})->purpose('Send appointment reminder emails 24 hours before appointments');

Schedule::command('appointments:send-reminders')->hourly();

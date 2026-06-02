<?php

namespace App\Services;

use App\Models\Appointment;
use Carbon\Carbon;
use Illuminate\Validation\Validator;

class AppointmentAvailability
{
    private const MORNING_START = '08:30';
    private const MORNING_END = '13:00';
    private const AFTERNOON_START = '14:00';
    private const AFTERNOON_END = '17:30';
    private const MINIMUM_GAP_MINUTES = 60;

    public function addValidationErrors(Validator $validator, array $data, ?Appointment $appointment = null): void
    {
        if ($validator->errors()->isNotEmpty()) {
            return;
        }

        if (! $this->isWithinWorkingHours($data['appointment_time'])) {
            $validator->errors()->add(
                'appointment_time',
                'This appointment is outside doctor working hours.'
            );

            return;
        }

        $requestedAt = $this->time($data['appointment_time']);

        foreach ($this->doctorAppointments($data, $appointment) as $existingAppointment) {
            $existingAt = $this->time((string) $existingAppointment->appointment_time);

            if ($existingAt->equalTo($requestedAt)) {
                $validator->errors()->add(
                    'appointment_time',
                    'This doctor is already booked at this time.'
                );

                return;
            }

            if ($existingAt->diffInMinutes($requestedAt) < self::MINIMUM_GAP_MINUTES) {
                $validator->errors()->add(
                    'appointment_time',
                    'Appointments for this doctor must be at least 1 hour apart.'
                );

                return;
            }
        }
    }

    private function isWithinWorkingHours(string $appointmentTime): bool
    {
        $time = $this->time($appointmentTime);

        return $time->betweenIncluded($this->time(self::MORNING_START), $this->time(self::MORNING_END))
            || $time->betweenIncluded($this->time(self::AFTERNOON_START), $this->time(self::AFTERNOON_END));
    }

    private function doctorAppointments(array $data, ?Appointment $appointment = null)
    {
        return Appointment::where('doctor_id', $data['doctor_id'])
            ->where('status', '!=', Appointment::STATUS_CANCELLED)
            ->whereDate('appointment_date', $data['appointment_date'])
            ->when($appointment, function ($query) use ($appointment): void {
                $query->where('id', '!=', $appointment->id);
            })
            ->get();
    }

    private function time(string $time): Carbon
    {
        return Carbon::createFromFormat('H:i', substr($time, 0, 5));
    }
}

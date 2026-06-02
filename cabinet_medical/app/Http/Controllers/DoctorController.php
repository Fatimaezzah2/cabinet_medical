<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\AppointmentNotificationService;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;

class DoctorController extends Controller
{
    public function __construct(
        private AppointmentNotificationService $notifications,
        private ActivityLogService $activityLogs,
    ) {
        //
    }

    public function dashboard(): View
    {
        $appointments = $this->doctorAppointments()
            ->whereDate('appointment_date', today())
            ->orderBy('appointment_time')
            ->get();

        return view('doctor.dashboard', [
            'appointments' => $appointments,
            'nextAppointment' => $this->doctorAppointments()
                ->where('status', '!=', Appointment::STATUS_CANCELLED)
                ->where(function ($query): void {
                    $query->whereDate('appointment_date', '>', today())
                        ->orWhere(function ($todayQuery): void {
                            $todayQuery->whereDate('appointment_date', today())
                                ->where('appointment_time', '>=', now()->format('H:i:s'));
                        });
                })
                ->orderBy('appointment_date')
                ->orderBy('appointment_time')
                ->first(),
        ]);
    }

    public function appointments(): View
    {
        return view('doctor.appointments', [
            'appointments' => $this->doctorAppointments()
                ->latest()
                ->get(),
        ]);
    }

    public function accept(Appointment $appointment): RedirectResponse
    {
        $this->authorizeDoctorAppointment($appointment);

        $appointment->update(['status' => Appointment::STATUS_CONFIRMED]);
        $this->notifications->sendConfirmation($appointment);

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        $this->authorizeDoctorAppointment($appointment);

        $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
        $this->activityLogs->appointmentCancelled($appointment, request()->user());

        return back()->with('success', __('messages.updated_successfully'));
    }

    public function patient(User $patient): View
    {
        abort_unless(
            $this->doctorAppointments()->where('patient_id', $patient->id)->exists(),
            403
        );

        return view('doctor.patient', [
            'patient' => $patient,
            'appointments' => $this->doctorAppointments()
                ->where('patient_id', $patient->id)
                ->latest()
                ->get(),
        ]);
    }

    private function doctorAppointments()
    {
        return auth()->user()
            ->doctorAppointments()
            ->with(['patient', 'doctor', 'service']);
    }

    private function authorizeDoctorAppointment(Appointment $appointment): void
    {
        abort_unless($appointment->doctor_id === auth()->id(), 403);
    }
}

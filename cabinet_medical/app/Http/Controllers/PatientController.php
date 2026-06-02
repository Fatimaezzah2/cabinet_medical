<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use App\Services\ActivityLogService;
use App\Services\AppointmentAvailability;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Validator;
use Illuminate\View\View;

class PatientController extends Controller
{
    public function __construct(
        private AppointmentAvailability $availability,
        private ActivityLogService $activityLogs,
    ) {
        //
    }

    public function dashboard(): View
    {
        return view('patient.dashboard', [
            'nextAppointment' => $this->patientAppointments()
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
            'appointments' => $this->patientAppointments()
                ->orderByDesc('appointment_date')
                ->orderByDesc('appointment_time')
                ->limit(5)
                ->get(),
        ]);
    }

    public function appointments(): View
    {
        return view('patient.appointments', [
            'appointments' => $this->patientAppointments()
                ->orderByDesc('appointment_date')
                ->orderByDesc('appointment_time')
                ->get(),
        ]);
    }

    public function create(): View
    {
        return view('patient.create', $this->formData());
    }

    public function store(Request $request): RedirectResponse
    {
        $appointment = Appointment::create([
            ...$this->validateAppointment($request),
            'patient_id' => $request->user()->id,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->activityLogs->appointmentCreated($appointment, $request->user());

        return redirect()
            ->route('patient.appointments')
            ->with('success', __('messages.created_successfully'));
    }

    public function edit(Appointment $appointment): View
    {
        $this->authorizePatientAppointment($appointment);

        return view('patient.edit', [
            'appointment' => $appointment,
            ...$this->formData(),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizePatientAppointment($appointment);

        $appointment->update($this->validateAppointment($request, $appointment));

        return redirect()
            ->route('patient.appointments')
            ->with('success', __('messages.updated_successfully'));
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        $this->authorizePatientAppointment($appointment);

        $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
        $this->activityLogs->appointmentCancelled($appointment, request()->user());

        return back()->with('success', __('messages.updated_successfully'));
    }

    private function formData(): array
    {
        return [
            'doctors' => User::doctors()->orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
        ];
    }

    private function validateAppointment(Request $request, ?Appointment $appointment = null): array
    {
        $data = $request->validate([
            'doctor_id' => ['required', 'exists:users,id'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
        ]);

        validator($data, [])->after(function (Validator $validator) use ($data, $appointment): void {
            $this->availability->addValidationErrors($validator, $data, $appointment);
        })->validate();

        return $data;
    }

    private function patientAppointments()
    {
        return auth()->user()
            ->appointments()
            ->with(['doctor', 'service']);
    }

    private function authorizePatientAppointment(Appointment $appointment): void
    {
        abort_unless($appointment->patient_id === auth()->id(), 403);
    }
}

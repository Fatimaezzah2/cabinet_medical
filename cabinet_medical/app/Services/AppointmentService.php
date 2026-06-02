<?php

namespace App\Services;

use App\Mail\AppointmentCreatedMail;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Mail;
use Illuminate\Validation\Validator;

class AppointmentService
{
    public function __construct(
        private AppointmentAvailability $availability,
        private ActivityLogService $activityLogs,
    ) {
        //
    }

    public function allAppointments()
    {
        return $this->visibleAppointments()
            ->latest()
            ->get();
    }

    public function formData(): array
    {
        return [
            'patients' => User::patients()->orderBy('name')->get(),
            'doctors' => User::doctors()->orderBy('name')->get(),
            'services' => Service::orderBy('name')->get(),
            'statuses' => Appointment::statuses(),
        ];
    }

    public function searchByPatientName(string $search)
    {
        return $this->visibleAppointments()
            ->when($search, function ($query) use ($search): void {
                $query->where(function ($searchQuery) use ($search): void {
                    $searchQuery
                        ->whereHas('patient', function ($patientQuery) use ($search): void {
                            $patientQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('doctor', function ($doctorQuery) use ($search): void {
                            $doctorQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhereHas('service', function ($serviceQuery) use ($search): void {
                            $serviceQuery->where('name', 'like', "%{$search}%");
                        })
                        ->orWhere('appointment_date', 'like', "%{$search}%")
                        ->orWhere('appointment_time', 'like', "%{$search}%")
                        ->orWhere('status', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get()
            ->map(fn (Appointment $appointment): array => [
                'id' => $appointment->id,
                'patient_id' => $appointment->patient_id,
                'patient' => $appointment->patient->name,
                'doctor_id' => $appointment->doctor_id,
                'doctor' => $appointment->doctor->name,
                'service_id' => $appointment->service_id,
                'service' => $appointment->service->name,
                'service_price' => number_format((float) $appointment->service->price, 2),
                'date' => $appointment->appointment_date->format('Y-m-d'),
                'time' => substr($appointment->appointment_time, 0, 5),
                'total_price' => number_format((float) $appointment->total_price, 2),
                'status' => $appointment->status,
                'edit_url' => route('appointments.edit', $appointment),
            ]);
    }

    public function searchByPatientNameForApi(string $search)
    {
        return $this->visibleAppointments()
            ->when($search, function ($query) use ($search): void {
                $query->whereHas('patient', function ($patientQuery) use ($search): void {
                    $patientQuery->where('name', 'like', "%{$search}%");
                });
            })
            ->latest()
            ->get();
    }

    public function create(Request $request): Appointment
    {
        $data = $this->validate($request);

        if ($request->user()?->isPatient()) {
            $data['patient_id'] = $request->user()->id;
            $data['status'] = Appointment::STATUS_PENDING;
        }

        $appointment = Appointment::create($data);

        $this->sendAppointmentEmail($appointment);
        $this->activityLogs->appointmentCreated($appointment, $request->user());

        return $appointment->load(['user', 'doctor', 'service']);
    }

    public function update(Request $request, Appointment $appointment): Appointment
    {
        $data = $this->validate($request, $appointment);

        if ($request->user()?->isPatient()) {
            $data['patient_id'] = $request->user()->id;
            $data['status'] = Appointment::STATUS_PENDING;
        }

        $appointment->update($data);

        return $appointment;
    }

    public function validate(Request $request, ?Appointment $appointment = null): array
    {
        $data = $this->normalizedData($request);

        $validator = validator($data, [
            'patient_id' => ['required', 'exists:users,id'],
            'doctor_id' => ['required', 'exists:users,id'],
            'service_id' => ['required', 'exists:services,id'],
            'appointment_date' => ['required', 'date'],
            'appointment_time' => ['required', 'date_format:H:i'],
            'status' => ['required', 'in:'.implode(',', Appointment::statuses())],
        ]);

        $validator->after(function (Validator $validator) use ($data, $appointment): void {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }

            $this->availability->addValidationErrors($validator, $data, $appointment);
        });

        return $validator->validate();
    }

    private function normalizedData(Request $request): array
    {
        $data = $request->only([
            'patient_id',
            'doctor_id',
            'service_id',
            'appointment_date',
            'appointment_time',
            'status',
        ]);

        $data['patient_id'] ??= $request->input('user_id');
        $data['appointment_date'] ??= $request->input('date');
        $data['appointment_time'] ??= $request->input('time');
        $data['status'] ??= Appointment::STATUS_PENDING;

        return $data;
    }

    private function sendAppointmentEmail(Appointment $appointment): void
    {
        $appointment->load('user');

        Mail::to($appointment->user->email)
            ->send(new AppointmentCreatedMail($appointment));
    }

    private function visibleAppointments()
    {
        return Appointment::with(['patient', 'doctor', 'service'])
            ->when(auth()->user()?->isPatient(), function ($query): void {
                $query->where('patient_id', auth()->id());
            })
            ->when(auth()->user()?->isDoctor(), function ($query): void {
                $query->where('doctor_id', auth()->id());
            });
    }
}

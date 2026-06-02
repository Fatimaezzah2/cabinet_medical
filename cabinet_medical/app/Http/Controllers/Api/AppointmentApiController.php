<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Appointment;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class AppointmentApiController extends Controller
{
    public function __construct(private AppointmentService $appointments)
    {
        //
    }

    public function index(): JsonResponse
    {
        return response()->json(
            $this->appointments->allAppointments()
                ->map(fn (Appointment $appointment): array => $this->appointmentPayload($appointment))
        );
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', $request->query('search', ''));

        return response()->json(
            $this->appointments->searchByPatientNameForApi((string) $query)
                ->map(fn (Appointment $appointment): array => $this->appointmentPayload($appointment))
        );
    }

    public function store(Request $request): JsonResponse
    {
        $appointment = $this->appointments->create($request);

        return response()->json([
            'message' => 'Appointment created successfully.',
            'appointment' => $this->appointmentPayload($appointment),
        ], 201);
    }

    public function appointmentPayload(Appointment $appointment): array
    {
        $appointment->loadMissing(['patient', 'doctor', 'service']);

        return [
            'id' => $appointment->id,
            'patient_id' => $appointment->patient_id,
            'doctor_id' => $appointment->doctor_id,
            'service_id' => $appointment->service_id,
            'appointment_date' => $appointment->appointment_date->format('Y-m-d'),
            'appointment_time' => substr($appointment->appointment_time, 0, 5),
            'status' => $appointment->status,
            'total_price' => (float) $appointment->total_price,
            'created_at' => $appointment->created_at?->toISOString(),
            'updated_at' => $appointment->updated_at?->toISOString(),
            'patient' => [
                'id' => $appointment->patient->id,
                'name' => $appointment->patient->name,
                'email' => $appointment->patient->email,
            ],
            'doctor' => [
                'id' => $appointment->doctor->id,
                'name' => $appointment->doctor->name,
                'email' => $appointment->doctor->email,
            ],
            'service' => [
                'id' => $appointment->service->id,
                'name' => $appointment->service->name,
                'price' => (float) $appointment->service->price,
                'duration' => $appointment->service->duration,
            ],
        ];
    }
}

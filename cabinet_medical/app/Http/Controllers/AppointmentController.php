<?php

namespace App\Http\Controllers;

use App\Models\Appointment;
use App\Services\ActivityLogService;
use App\Services\AppointmentNotificationService;
use App\Services\AppointmentService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class AppointmentController extends Controller
{
    public function __construct(
        private AppointmentService $appointments,
        private AppointmentNotificationService $notifications,
        private ActivityLogService $activityLogs,
    )
    {
        //
    }

    public function index(): View
    {
        return view('appointments.index', [
            'appointments' => $this->appointments->allAppointments(),
            ...$this->appointments->formData(),
        ]);
    }

    public function create(): View
    {
        return view('appointments.create', $this->appointments->formData());
    }

    public function search(Request $request): JsonResponse
    {
        $query = $request->query('q', $request->query('search', ''));

        return response()->json(
            $this->appointments->searchByPatientName((string) $query)
        );
    }

    public function store(Request $request): RedirectResponse
    {
        $this->appointments->create($request);

        return redirect()
            ->route('appointments.index')
            ->with('success', __('messages.created_successfully'));
    }

    public function edit(Appointment $appointment): View
    {
        $this->authorizeAppointmentAccess($appointment);

        return view('appointments.edit', [
            'appointment' => $appointment,
            ...$this->appointments->formData(),
        ]);
    }

    public function update(Request $request, Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointmentAccess($appointment);

        $this->appointments->update($request, $appointment);

        return redirect()
            ->route('appointments.index')
            ->with('success', __('messages.updated_successfully'));
    }

    public function confirm(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => Appointment::STATUS_CONFIRMED]);
        $this->notifications->sendConfirmation($appointment);

        return redirect()
            ->route('appointments.index')
            ->with('success', __('messages.updated_successfully'));
    }

    public function cancel(Appointment $appointment): RedirectResponse
    {
        $appointment->update(['status' => Appointment::STATUS_CANCELLED]);
        $this->activityLogs->appointmentCancelled($appointment, request()->user());

        return redirect()
            ->route('appointments.index')
            ->with('success', __('messages.updated_successfully'));
    }

    public function destroy(Appointment $appointment): RedirectResponse
    {
        $this->authorizeAppointmentAccess($appointment);

        $appointment->delete();

        return redirect()
            ->route('appointments.index')
            ->with('success', __('messages.deleted_successfully'));
    }

    private function authorizeAppointmentAccess(Appointment $appointment): void
    {
        $user = auth()->user();

        abort_unless(
            $user?->isAdmin()
                || ($user?->isPatient() && $appointment->patient_id === $user->id)
                || ($user?->isDoctor() && $appointment->doctor_id === $user->id),
            403
        );
    }
}

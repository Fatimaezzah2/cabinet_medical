<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PatientModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_patient_can_book_appointment_and_total_price_comes_from_service(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create(['price' => 450.75]);

        $this->actingAs($patient)
            ->post('/patient/appointments', [
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'appointment_date' => '2026-06-15',
                'appointment_time' => '10:30',
            ])
            ->assertRedirect('/patient/appointments');

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'status' => Appointment::STATUS_PENDING,
            'total_price' => 450.75,
        ]);
    }

    public function test_patient_sees_only_his_appointments(): void
    {
        $patient = User::factory()->patient()->create();
        $otherPatient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create(['name' => 'Visible Doctor']);
        $otherDoctor = User::factory()->doctor()->create(['name' => 'Hidden Doctor']);
        $service = Service::factory()->create(['name' => 'Visible Service']);
        $otherService = Service::factory()->create(['name' => 'Hidden Service']);

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
        ]);

        Appointment::factory()->create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $otherDoctor->id,
            'service_id' => $otherService->id,
        ]);

        $this->actingAs($patient)
            ->get('/patient/appointments')
            ->assertOk()
            ->assertSee('Visible Doctor')
            ->assertSee('Visible Service')
            ->assertDontSee('Hidden Doctor')
            ->assertDontSee('Hidden Service');
    }

    public function test_patient_cannot_edit_another_patient_appointment(): void
    {
        $patient = User::factory()->patient()->create();
        $otherPatient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $appointment = Appointment::factory()->create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
        ]);

        $this->actingAs($patient)
            ->get("/patient/appointments/{$appointment->id}/edit")
            ->assertForbidden();
    }

    public function test_doctor_cannot_open_patient_module(): void
    {
        $doctor = User::factory()->doctor()->create();

        $this->actingAs($doctor)
            ->get('/patient')
            ->assertForbidden();
    }

    public function test_patient_cannot_book_outside_doctor_working_hours(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $this->actingAs($patient)
            ->from('/patient/appointments/create')
            ->post('/patient/appointments', [
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'appointment_date' => '2026-06-15',
                'appointment_time' => '13:30',
            ])
            ->assertRedirect('/patient/appointments/create')
            ->assertSessionHasErrors([
                'appointment_time' => 'Appointments must be between 08:30-13:00 or 14:00-17:30.',
            ]);
    }

    public function test_patient_cannot_book_within_one_hour_of_doctor_appointment(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-06-15',
            'appointment_time' => '10:00',
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($patient)
            ->from('/patient/appointments/create')
            ->post('/patient/appointments', [
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'appointment_date' => '2026-06-15',
                'appointment_time' => '10:30',
            ])
            ->assertRedirect('/patient/appointments/create')
            ->assertSessionHasErrors([
                'appointment_time' => 'Appointments for the same doctor must be at least 1 hour apart.',
            ]);
    }

    public function test_patient_cannot_override_total_price_manually(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create(['price' => 900.00]);

        $this->actingAs($patient)
            ->post('/patient/appointments', [
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'appointment_date' => '2026-06-15',
                'appointment_time' => '15:00',
                'total_price' => 1.00,
            ])
            ->assertRedirect('/patient/appointments');

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'service_id' => $service->id,
            'total_price' => 900.00,
        ]);
    }
}

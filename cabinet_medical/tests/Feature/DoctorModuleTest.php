<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DoctorModuleTest extends TestCase
{
    use RefreshDatabase;

    public function test_doctor_dashboard_shows_only_his_appointments(): void
    {
        $doctor = User::factory()->doctor()->create();
        $otherDoctor = User::factory()->doctor()->create();
        $patient = User::factory()->patient()->create(['name' => 'Visible Patient']);
        $otherPatient = User::factory()->patient()->create(['name' => 'Hidden Patient']);
        $service = Service::factory()->create();

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => today()->format('Y-m-d'),
        ]);

        Appointment::factory()->create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $otherDoctor->id,
            'service_id' => $service->id,
            'appointment_date' => today()->format('Y-m-d'),
        ]);

        $this->actingAs($doctor)
            ->get('/doctor')
            ->assertOk()
            ->assertSee('Visible Patient')
            ->assertDontSee('Hidden Patient');
    }

    public function test_doctor_can_accept_only_his_appointment(): void
    {
        $doctor = User::factory()->doctor()->create();
        $otherDoctor = User::factory()->doctor()->create();
        $patient = User::factory()->patient()->create();
        $service = Service::factory()->create();

        $ownAppointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $otherAppointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
            'service_id' => $service->id,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($doctor)
            ->post("/doctor/appointments/{$ownAppointment->id}/accept")
            ->assertRedirect();

        $this->assertDatabaseHas('appointments', [
            'id' => $ownAppointment->id,
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        $this->actingAs($doctor)
            ->post("/doctor/appointments/{$otherAppointment->id}/accept")
            ->assertForbidden();

        $this->assertDatabaseHas('appointments', [
            'id' => $otherAppointment->id,
            'status' => Appointment::STATUS_PENDING,
        ]);
    }

    public function test_patient_cannot_open_doctor_module(): void
    {
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)
            ->get('/doctor')
            ->assertForbidden();
    }
}

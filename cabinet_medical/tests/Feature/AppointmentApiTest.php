<?php

namespace Tests\Feature;

use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AppointmentApiTest extends TestCase
{
    use RefreshDatabase;

    public function test_api_can_list_appointments(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create(['price' => 250.00]);

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($patient)->getJson('/api/appointments');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonPath('0.patient_id', $patient->id)
            ->assertJsonPath('0.doctor_id', $doctor->id)
            ->assertJsonPath('0.service_id', $service->id)
            ->assertJsonPath('0.total_price', 250)
            ->assertJsonFragment(['name' => $patient->name]);
    }

    public function test_api_can_create_appointment(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $response = $this->actingAs($patient)->postJson('/api/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-06-10',
            'appointment_time' => '09:30',
            'status' => 'pending',
        ]);

        $response
            ->assertCreated()
            ->assertJsonFragment(['message' => 'Appointment created successfully.'])
            ->assertJsonPath('appointment.patient_id', $patient->id)
            ->assertJsonPath('appointment.total_price', (float) $service->price);

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
        ]);
    }

    public function test_api_prevents_doctor_double_booking(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-06-10',
            'appointment_time' => '09:30',
            'status' => 'confirmed',
        ]);

        $response = $this->actingAs($patient)->postJson('/api/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-06-10',
            'appointment_time' => '09:30',
            'status' => 'pending',
        ]);

        $response
            ->assertUnprocessable()
            ->assertJsonValidationErrors('appointment_time');

        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_api_requires_authentication(): void
    {
        $this->getJson('/api/appointments')->assertUnauthorized();
        $this->postJson('/api/appointments')->assertUnauthorized();
    }

    public function test_api_validates_input_data(): void
    {
        $patient = User::factory()->patient()->create();

        $this->actingAs($patient)
            ->postJson('/api/appointments', [])
            ->assertUnprocessable()
            ->assertJsonValidationErrors([
                'patient_id',
                'doctor_id',
                'service_id',
                'appointment_date',
                'appointment_time',
                'status',
            ]);
    }
}

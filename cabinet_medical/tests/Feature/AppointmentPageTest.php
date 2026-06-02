<?php

namespace Tests\Feature;

use App\Mail\AppointmentCreatedMail;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AppointmentPageTest extends TestCase
{
    use RefreshDatabase;

    public function test_authenticated_user_can_see_appointments_page(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($patient)->get('/appointments');

        $response
            ->assertOk()
            ->assertSee('New Appointment')
            ->assertSee('Delete Appointment')
            ->assertSee($patient->name);
    }

    public function test_authenticated_user_can_search_appointments_by_patient_name(): void
    {
        $patient = User::factory()->patient()->create(['name' => 'Alice Patient']);
        $otherPatient = User::factory()->patient()->create(['name' => 'Bob Patient']);
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
        ]);

        Appointment::factory()->create([
            'patient_id' => $otherPatient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($patient)->getJson('/appointments/search?search=Alice');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['patient' => 'Alice Patient'])
            ->assertJsonMissing(['patient' => 'Bob Patient']);
    }

    public function test_authenticated_user_can_search_appointments_by_doctor_name(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create(['name' => 'Doctor Searchable']);
        $otherDoctor = User::factory()->doctor()->create(['name' => 'Doctor Hidden']);
        $service = Service::factory()->create();

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
        ]);

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $otherDoctor->id,
            'service_id' => $service->id,
        ]);

        $response = $this->actingAs($patient)->getJson('/appointments/search?search=Searchable');

        $response
            ->assertOk()
            ->assertJsonCount(1)
            ->assertJsonFragment(['doctor' => 'Doctor Searchable'])
            ->assertJsonMissing(['doctor' => 'Doctor Hidden']);
    }

    public function test_email_is_sent_when_appointment_is_created(): void
    {
        Mail::fake();

        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $this->actingAs($patient)->post('/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => now()->addDay()->format('Y-m-d'),
            'appointment_time' => '10:30',
            'status' => 'pending',
        ])->assertRedirect('/appointments');

        Mail::assertSent(AppointmentCreatedMail::class, function (AppointmentCreatedMail $mail) use ($patient) {
            return $mail->hasTo($patient->email);
        });
    }

    public function test_doctor_cannot_be_double_booked_from_web_form(): void
    {
        Mail::fake();

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

        $response = $this->actingAs($patient)->from('/appointments')->post('/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-06-10',
            'appointment_time' => '09:30',
            'status' => 'pending',
        ]);

        $response
            ->assertRedirect('/appointments')
            ->assertSessionHasErrors([
                'appointment_time' => 'This doctor already has an appointment at this date and time.',
            ]);

        $this->assertDatabaseCount('appointments', 1);
        Mail::assertNothingSent();
    }

    public function test_cancelled_appointment_does_not_block_same_time_request(): void
    {
        Mail::fake();

        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        Appointment::factory()->create([
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-06-10',
            'appointment_time' => '09:30',
            'status' => 'cancelled',
        ]);

        $this->actingAs($patient)->post('/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-06-10',
            'appointment_time' => '09:30',
            'status' => 'pending',
        ])->assertRedirect('/appointments');

        $this->assertDatabaseCount('appointments', 2);
    }

    public function test_patient_created_appointment_stays_pending(): void
    {
        Mail::fake();

        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $this->actingAs($patient)->post('/appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-06-15',
            'appointment_time' => '11:00',
            'status' => 'confirmed',
        ])->assertRedirect('/appointments');

        $this->assertDatabaseHas('appointments', [
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);
    }

    public function test_admin_can_confirm_and_cancel_appointment(): void
    {
        $admin = User::factory()->admin()->create();
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);

        $this->actingAs($admin)
            ->post("/appointments/{$appointment->id}/confirm")
            ->assertRedirect('/appointments');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'confirmed',
        ]);

        $this->actingAs($admin)
            ->post("/appointments/{$appointment->id}/cancel")
            ->assertRedirect('/appointments');

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'cancelled',
        ]);
    }

    public function test_patient_cannot_confirm_or_cancel_appointment(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'status' => 'pending',
        ]);

        $this->actingAs($patient)
            ->post("/appointments/{$appointment->id}/confirm")
            ->assertForbidden();

        $this->actingAs($patient)
            ->post("/appointments/{$appointment->id}/cancel")
            ->assertForbidden();

        $this->assertDatabaseHas('appointments', [
            'id' => $appointment->id,
            'status' => 'pending',
        ]);
    }
}

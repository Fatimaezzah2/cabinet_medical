<?php

namespace Tests\Feature;

use App\Mail\AppointmentConfirmedMail;
use App\Mail\AppointmentReminderMail;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class AppointmentEmailTest extends TestCase
{
    use RefreshDatabase;

    public function test_confirmation_email_is_sent_to_patient_and_doctor(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create(['price' => 300.00]);

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($admin)
            ->post("/appointments/{$appointment->id}/confirm")
            ->assertRedirect('/appointments');

        Mail::assertSent(AppointmentConfirmedMail::class, function (AppointmentConfirmedMail $mail) use ($patient): bool {
            return $mail->hasTo($patient->email)
                && (float) $mail->appointment->total_price === 300.00;
        });

        Mail::assertSent(AppointmentConfirmedMail::class, function (AppointmentConfirmedMail $mail) use ($doctor): bool {
            return $mail->hasTo($doctor->email)
                && (float) $mail->appointment->total_price === 300.00;
        });
    }

    public function test_doctor_accepting_appointment_sends_confirmation_email_to_patient_and_doctor(): void
    {
        Mail::fake();

        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create(['price' => 450.00]);

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'status' => Appointment::STATUS_PENDING,
        ]);

        $this->actingAs($doctor)
            ->post("/doctor/appointments/{$appointment->id}/accept")
            ->assertRedirect();

        Mail::assertSent(AppointmentConfirmedMail::class, function (AppointmentConfirmedMail $mail) use ($patient): bool {
            return $mail->hasTo($patient->email)
                && (float) $mail->appointment->total_price === 450.00;
        });

        Mail::assertSent(AppointmentConfirmedMail::class, function (AppointmentConfirmedMail $mail) use ($doctor): bool {
            return $mail->hasTo($doctor->email)
                && (float) $mail->appointment->total_price === 450.00;
        });
    }

    public function test_reminder_email_is_sent_24_hours_before_appointment_once(): void
    {
        Mail::fake();

        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create(['price' => 700.00]);

        $appointmentAt = now()->addDay()->addMinutes(30);

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => $appointmentAt->format('Y-m-d'),
            'appointment_time' => $appointmentAt->format('H:i'),
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        Artisan::call('appointments:send-reminders');
        Artisan::call('appointments:send-reminders');

        Mail::assertSent(AppointmentReminderMail::class, 2);

        Mail::assertSent(AppointmentReminderMail::class, function (AppointmentReminderMail $mail) use ($patient, $appointment): bool {
            return $mail->hasTo($patient->email)
                && $mail->appointment->is($appointment)
                && (float) $mail->appointment->total_price === 700.00;
        });

        Mail::assertSent(AppointmentReminderMail::class, function (AppointmentReminderMail $mail) use ($doctor, $appointment): bool {
            return $mail->hasTo($doctor->email)
                && $mail->appointment->is($appointment)
                && (float) $mail->appointment->total_price === 700.00;
        });
    }

    public function test_pending_appointment_does_not_receive_reminder(): void
    {
        Mail::fake();

        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();
        $appointmentAt = now()->addDay()->addMinutes(30);

        Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => $appointmentAt->format('Y-m-d'),
            'appointment_time' => $appointmentAt->format('H:i'),
            'status' => Appointment::STATUS_PENDING,
        ]);

        Artisan::call('appointments:send-reminders');

        Mail::assertNotSent(AppointmentReminderMail::class);
    }

    public function test_confirmation_and_reminder_email_content_contains_required_appointment_details(): void
    {
        $patient = User::factory()->patient()->create(['name' => 'Alice Patient']);
        $doctor = User::factory()->doctor()->create(['name' => 'Dr Smith']);
        $service = Service::factory()->create([
            'name' => 'Cardiology Visit',
            'price' => 850.50,
        ]);

        $appointment = Appointment::factory()->create([
            'patient_id' => $patient->id,
            'doctor_id' => $doctor->id,
            'service_id' => $service->id,
            'appointment_date' => '2026-06-15',
            'appointment_time' => '09:30',
            'status' => Appointment::STATUS_CONFIRMED,
        ]);

        foreach ([new AppointmentConfirmedMail($appointment), new AppointmentReminderMail($appointment)] as $mail) {
            $body = $mail->render();

            $this->assertStringContainsString('Alice Patient', $body);
            $this->assertStringContainsString('Dr Smith', $body);
            $this->assertStringContainsString('Cardiology Visit', $body);
            $this->assertStringContainsString('2026-06-15', $body);
            $this->assertStringContainsString('09:30', $body);
            $this->assertStringContainsString('850.50', $body);
        }
    }
}

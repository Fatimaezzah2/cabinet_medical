<?php

namespace Tests\Feature;

use App\Models\ActivityLog;
use App\Models\Appointment;
use App\Models\Service;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ActivityLogTest extends TestCase
{
    use RefreshDatabase;

    public function test_user_creation_and_role_changes_are_logged(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin)
            ->post('/admin/users', [
                'name' => 'Created User',
                'email' => 'created@example.com',
                'password' => 'password',
                'password_confirmation' => 'password',
                'role' => User::ROLE_PATIENT,
                'is_approved' => '1',
            ])
            ->assertRedirect();

        $user = User::where('email', 'created@example.com')->firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'user_created',
        ]);

        $this->actingAs($admin)
            ->put("/admin/users/{$user->id}", [
                'name' => $user->name,
                'email' => $user->email,
                'role' => User::ROLE_DOCTOR,
                'is_approved' => '1',
            ])
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $admin->id,
            'action' => 'role_changed',
        ]);
    }

    public function test_appointment_creation_and_cancellation_are_logged(): void
    {
        $patient = User::factory()->patient()->create();
        $doctor = User::factory()->doctor()->create();
        $service = Service::factory()->create();

        $this->actingAs($patient)
            ->post('/patient/appointments', [
                'doctor_id' => $doctor->id,
                'service_id' => $service->id,
                'appointment_date' => '2026-06-15',
                'appointment_time' => '10:30',
            ])
            ->assertRedirect('/patient/appointments');

        $appointment = Appointment::firstOrFail();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $patient->id,
            'action' => 'appointment_created',
        ]);

        $this->actingAs($patient)
            ->post("/patient/appointments/{$appointment->id}/cancel")
            ->assertRedirect();

        $this->assertDatabaseHas('activity_logs', [
            'user_id' => $patient->id,
            'action' => 'appointment_cancelled',
        ]);
    }

    public function test_admin_dashboard_shows_recent_activity_logs(): void
    {
        $admin = User::factory()->admin()->create();

        ActivityLog::create([
            'user_id' => $admin->id,
            'action' => 'user_created',
            'description' => 'Visible activity log entry.',
        ]);

        $this->actingAs($admin)
            ->get('/admin')
            ->assertOk()
            ->assertSee('Activity Logs')
            ->assertSee('Visible activity log entry.');
    }
}

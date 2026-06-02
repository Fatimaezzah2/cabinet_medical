<?php

namespace Tests\Feature;

use App\Mail\UserApprovedMail;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class MiddlewareAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_unapproved_user_is_redirected_to_login(): void
    {
        $user = User::factory()->patient()->notApproved()->create();

        $this->actingAs($user)
            ->get('/dashboard')
            ->assertRedirect('/login');
    }

    public function test_only_admin_can_open_admin_users_page(): void
    {
        $patient = User::factory()->patient()->create();
        $admin = User::factory()->admin()->create();

        $this->actingAs($patient)
            ->get('/admin/users')
            ->assertForbidden();

        $this->actingAs($admin)
            ->get('/admin/users')
            ->assertOk();
    }

    public function test_email_is_sent_when_admin_approves_user(): void
    {
        Mail::fake();

        $admin = User::factory()->admin()->create();
        $user = User::factory()->patient()->notApproved()->create();

        $this->actingAs($admin)->put("/admin/users/{$user->id}", [
            'role' => 'patient',
            'is_approved' => '1',
        ])->assertRedirect();

        Mail::assertSent(UserApprovedMail::class, function (UserApprovedMail $mail) use ($user) {
            return $mail->hasTo($user->email);
        });
    }
}

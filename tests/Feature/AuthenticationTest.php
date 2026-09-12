<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AuthenticationTest extends TestCase
{
    use RefreshDatabase;

    public function test_login_works(): void
    {
        $user = User::factory()->create([
            'email' => 'ayan@example.com',
            'password' => 'password',
        ]);

        $response = $this->post('/login', [
            'email' => $user->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_unauthenticated_users_cannot_access_dashboard(): void
    {
        $this->get('/dashboard')->assertRedirect(route('login'));
    }

    public function test_logout_works(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->post('/logout')
            ->assertRedirect(route('login'));

        $this->assertGuest();
    }

    public function test_password_can_be_changed(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->put(route('settings.password'), [
                'current_password' => 'password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('new-password', $user->fresh()->password));
    }

    public function test_password_change_requires_current_password(): void
    {
        $user = User::factory()->create([
            'password' => 'password',
        ]);

        $this->actingAs($user)
            ->from(route('settings.edit'))
            ->put(route('settings.password'), [
                'current_password' => 'wrong-password',
                'password' => 'new-password',
                'password_confirmation' => 'new-password',
            ])
            ->assertRedirect(route('settings.edit'))
            ->assertSessionHasErrors('current_password');

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('password', $user->fresh()->password));
    }
}

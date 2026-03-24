<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class AuthAccessTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_login_with_nickname(): void
    {
        $admin = User::factory()->create([
            'nickname' => 'admin',
            'email' => 'admin@example.com',
            'password' => Hash::make('secret-pass'),
            'role' => 'admin',
            'is_claimed' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'admin',
            'password' => 'secret-pass',
        ]);

        $response->assertRedirect(route('dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_non_admin_user_gets_forbidden_on_dashboard(): void
    {
        $user = User::factory()->create([
            'nickname' => 'regular',
            'role' => 'user',
        ]);

        $response = $this->actingAs($user)->get(route('dashboard'));

        $response->assertForbidden();
    }
}

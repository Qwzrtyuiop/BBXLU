<?php

namespace Tests\Feature;

use App\Models\Player;
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

    public function test_regular_user_can_login_and_is_redirected_to_user_dashboard(): void
    {
        $user = User::factory()->create([
            'nickname' => 'regular-user',
            'email' => 'regular@example.com',
            'password' => Hash::make('secret-pass'),
            'role' => 'user',
            'is_claimed' => true,
        ]);

        $response = $this->post(route('login.store'), [
            'login' => 'regular-user',
            'password' => 'secret-pass',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertAuthenticatedAs($user);
    }

    public function test_user_can_register_a_new_account(): void
    {
        $response = $this->post(route('register.store'), [
            'mode' => 'register',
            'nickname' => 'new-player',
            'name' => 'New Player',
            'email' => 'new-player@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertDatabaseHas('users', [
            'nickname' => 'new-player',
            'name' => 'New Player',
            'email' => 'new-player@example.com',
            'is_claimed' => 1,
            'role' => 'user',
        ]);
        $this->assertDatabaseCount('players', 1);
    }

    public function test_user_can_claim_an_unclaimed_account(): void
    {
        $user = User::factory()->create([
            'nickname' => 'claim-me',
            'name' => 'claim-me',
            'email' => null,
            'password' => null,
            'role' => 'user',
            'is_claimed' => false,
        ]);

        $response = $this->post(route('register.store'), [
            'mode' => 'claim',
            'claim_nickname' => 'claim-me',
            'name' => 'Claimed User',
            'email' => 'claimed@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ]);

        $response->assertRedirect(route('user.dashboard'));
        $this->assertDatabaseHas('users', [
            'id' => $user->id,
            'nickname' => 'claim-me',
            'name' => 'Claimed User',
            'email' => 'claimed@example.com',
            'is_claimed' => 1,
        ]);
        $this->assertDatabaseCount('players', 1);
    }

    public function test_registration_with_existing_unclaimed_nickname_points_user_to_claim_account(): void
    {
        User::factory()->create([
            'nickname' => 'already-there',
            'name' => 'already-there',
            'email' => null,
            'password' => null,
            'role' => 'user',
            'is_claimed' => false,
        ]);

        $response = $this->from(route('register'))->post(route('register.store'), [
            'mode' => 'register',
            'nickname' => 'already-there',
            'name' => 'Player Trying Again',
            'email' => 'retry@example.com',
            'password' => 'secret-pass',
            'password_confirmation' => 'secret-pass',
        ]);

        $response->assertRedirect(route('register'));
        $response->assertSessionHasErrors(['nickname']);
        $response->assertSessionHasInput('mode', 'claim');
        $response->assertSessionHasInput('claim_nickname', 'already-there');
        $this->assertDatabaseCount('users', 1);
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

    public function test_user_visiting_their_public_profile_route_is_redirected_to_self_dashboard(): void
    {
        $user = User::factory()->create([
            'nickname' => 'self-view-user',
            'role' => 'user',
            'is_claimed' => true,
        ]);
        $player = Player::query()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->actingAs($user)->get(route('user.dashboard.profile', $player));

        $response->assertRedirect(route('user.dashboard'));
    }
}

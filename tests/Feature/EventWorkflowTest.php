<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventParticipant;
use App\Models\EventType;
use App\Models\Player;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EventWorkflowTest extends TestCase
{
    use RefreshDatabase;

    public function test_adding_participant_auto_creates_user_and_player(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'GT Weekly',
            'description' => null,
            'event_type_id' => $eventType->id,
            'date' => '2026-03-24',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $response = $this->post(route('events.participants.store', $event), [
            'nickname' => 'newbie',
        ]);

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('users', [
            'nickname' => 'newbie',
            'name' => 'newbie',
            'email' => null,
            'is_claimed' => 0,
        ]);

        $user = User::query()->where('nickname', 'newbie')->firstOrFail();
        $player = Player::query()->where('user_id', $user->id)->firstOrFail();

        $this->assertDatabaseHas('event_participants', [
            'event_id' => $event->id,
            'player_id' => $player->id,
        ]);
    }

    public function test_adding_multiple_selected_participants_creates_missing_profiles_once(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'host-batch']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Batch Weekly',
            'description' => null,
            'event_type_id' => $eventType->id,
            'date' => '2026-03-26',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $existingUser = User::factory()->create([
            'nickname' => 'alpha',
            'is_claimed' => true,
        ]);
        $existingPlayer = Player::query()->create([
            'user_id' => $existingUser->id,
        ]);
        EventParticipant::query()->create([
            'event_id' => $event->id,
            'player_id' => $existingPlayer->id,
        ]);

        $response = $this->post(route('events.participants.store', $event), [
            'selected_nicknames' => ['alpha', 'bravo', 'charlie', 'bravo'],
        ]);

        $response->assertRedirect(route('events.show', $event));

        $this->assertDatabaseHas('users', ['nickname' => 'bravo', 'name' => 'bravo', 'is_claimed' => 0]);
        $this->assertDatabaseHas('users', ['nickname' => 'charlie', 'name' => 'charlie', 'is_claimed' => 0]);
        $this->assertSame(3, EventParticipant::query()->where('event_id', $event->id)->count());
    }

    public function test_match_winner_is_inferred_from_scores(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'host-2']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Casual Duel',
            'description' => null,
            'event_type_id' => $eventType->id,
            'date' => '2026-03-24',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $player1 = Player::query()->create([
            'user_id' => User::factory()->create(['nickname' => 'alpha'])->id,
        ]);
        $player2 = Player::query()->create([
            'user_id' => User::factory()->create(['nickname' => 'beta'])->id,
        ]);

        EventParticipant::query()->create(['event_id' => $event->id, 'player_id' => $player1->id]);
        EventParticipant::query()->create(['event_id' => $event->id, 'player_id' => $player2->id]);

        $response = $this->post(route('events.matches.store', $event), [
            'player1_id' => $player1->id,
            'player2_id' => $player2->id,
            'player1_score' => 3,
            'player2_score' => 1,
            'round_number' => 1,
        ]);

        $response->assertRedirect(route('events.show', $event));
        $this->assertDatabaseHas('matches', [
            'event_id' => $event->id,
            'winner_id' => $player1->id,
            'player1_score' => 3,
            'player2_score' => 1,
        ]);
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'nickname' => 'admin-user',
            'role' => 'admin',
            'is_claimed' => true,
        ]);
    }
}

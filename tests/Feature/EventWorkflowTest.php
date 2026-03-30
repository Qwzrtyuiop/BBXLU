<?php

namespace Tests\Feature;

use App\Models\Award;
use App\Models\Event;
use App\Models\EventMatch;
use App\Models\EventParticipant;
use App\Models\EventType;
use App\Models\Player;
use App\Models\User;
use App\Services\BracketService;
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
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-03-24',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $response = $this->post(route('events.participants.store', $event), [
            'nickname' => 'newbie',
        ]);

        $response->assertRedirect($this->workspaceRoute($event));
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
            'bracket_type' => 'single_elim',
            'match_format' => 7,
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

        $response->assertRedirect($this->workspaceRoute($event));

        $this->assertDatabaseHas('users', ['nickname' => 'bravo', 'name' => 'bravo', 'is_claimed' => 0]);
        $this->assertDatabaseHas('users', ['nickname' => 'charlie', 'name' => 'charlie', 'is_claimed' => 0]);
        $this->assertSame(3, EventParticipant::query()->where('event_id', $event->id)->count());
    }

    public function test_locked_deck_participant_registration_requires_and_saves_deck_details(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'locked-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Locked Deck Weekly',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'is_lock_deck' => true,
            'date' => '2026-04-12',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $this->post(route('events.participants.store', $event), [
            'nickname' => 'locked-a',
        ])->assertSessionHasErrors(['deck_name', 'deck_bey1', 'deck_bey2', 'deck_bey3']);

        $response = $this->post(route('events.participants.store', $event), [
            'nickname' => 'locked-a',
            'deck_name' => 'Phoenix Rush',
            'deck_bey1' => 'Phoenix',
            'deck_bey2' => 'Dran',
            'deck_bey3' => 'Wizard',
        ]);

        $response->assertRedirect($this->workspaceRoute($event));
        $this->assertDatabaseHas('event_participants', [
            'event_id' => $event->id,
            'player_id' => Player::query()->whereHas('user', fn ($query) => $query->where('nickname', 'locked-a'))->value('id'),
            'deck_name' => 'Phoenix Rush',
            'deck_bey1' => 'Phoenix',
            'deck_bey2' => 'Dran',
            'deck_bey3' => 'Wizard',
        ]);
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
            'bracket_type' => 'single_elim',
            'match_format' => 7,
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

        EventParticipant::query()->create([
            'event_id' => $event->id,
            'player_id' => $player1->id,
            'deck_name' => 'Alpha Deck',
            'deck_bey1' => 'Phoenix',
            'deck_bey2' => 'Dran',
            'deck_bey3' => 'Wizard',
            'deck_registered_at' => now(),
        ]);
        EventParticipant::query()->create([
            'event_id' => $event->id,
            'player_id' => $player2->id,
            'deck_name' => 'Beta Deck',
            'deck_bey1' => 'Knight',
            'deck_bey2' => 'Shark',
            'deck_bey3' => 'Leon',
            'deck_registered_at' => now(),
        ]);

        $response = $this->post(route('events.matches.store', $event), [
            'player1_id' => $player1->id,
            'player2_id' => $player2->id,
            'player1_score' => 4,
            'player2_score' => 1,
            'round_number' => 1,
        ]);

        $response->assertRedirect($this->workspaceRoute($event));
        $this->assertDatabaseHas('matches', [
            'event_id' => $event->id,
            'winner_id' => $player1->id,
            'player1_score' => 4,
            'player2_score' => 1,
        ]);
    }

    public function test_match_result_types_are_saved_with_battle_results(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'typed-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Typed Results Cup',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-03-28',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $player1 = Player::query()->create([
            'user_id' => User::factory()->create(['nickname' => 'atlas'])->id,
        ]);
        $player2 = Player::query()->create([
            'user_id' => User::factory()->create(['nickname' => 'blaze'])->id,
        ]);

        EventParticipant::query()->create([
            'event_id' => $event->id,
            'player_id' => $player1->id,
            'deck_name' => 'Atlas Deck',
            'deck_bey1' => 'Phoenix',
            'deck_bey2' => 'Dran',
            'deck_bey3' => 'Wizard',
            'deck_registered_at' => now(),
        ]);
        EventParticipant::query()->create([
            'event_id' => $event->id,
            'player_id' => $player2->id,
            'deck_name' => 'Blaze Deck',
            'deck_bey1' => 'Knight',
            'deck_bey2' => 'Shark',
            'deck_bey3' => 'Leon',
            'deck_registered_at' => now(),
        ]);

        $response = $this->post(route('events.matches.store', $event), [
            'player1_id' => $player1->id,
            'player2_id' => $player2->id,
            'result_1' => 1,
            'result_type_1' => 'spin',
            'result_2' => 2,
            'result_type_2' => 'burst',
            'result_3' => 1,
            'result_type_3' => 'spin',
            'result_4' => 1,
            'result_type_4' => 'extreme',
            'result_5' => 1,
            'result_type_5' => 'spin',
            'round_number' => 1,
        ]);

        $response->assertRedirect($this->workspaceRoute($event));
        $this->assertDatabaseHas('matches', [
            'event_id' => $event->id,
            'winner_id' => $player1->id,
            'player1_score' => 4,
            'player2_score' => 1,
            'result_type_1' => 'spin',
            'result_type_2' => 'burst',
            'result_type_4' => 'extreme',
        ]);
    }

    public function test_swiss_standings_use_weighted_finish_points(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'points-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Weighted Points Swiss',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'swiss_single_elim',
            'swiss_rounds' => 1,
            'top_cut_size' => 2,
            'match_format' => 7,
            'date' => '2026-04-11',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $player1 = Player::query()->create([
            'user_id' => User::factory()->create(['nickname' => 'scorer-a'])->id,
        ]);
        $player2 = Player::query()->create([
            'user_id' => User::factory()->create(['nickname' => 'scorer-b'])->id,
        ]);

        EventParticipant::query()->create(['event_id' => $event->id, 'player_id' => $player1->id]);
        EventParticipant::query()->create(['event_id' => $event->id, 'player_id' => $player2->id]);

        $this->post(route('events.bracket.generate', $event))->assertRedirect($this->workspaceRoute($event));

        $match = $event->fresh('matches')->matches->firstOrFail();

        $this->submitMatchResult($event, $match, [
            [1, 'spin'],
            [1, 'burst'],
            [2, 'over'],
            [1, 'extreme'],
            [1, 'spin'],
        ])->assertRedirect($this->workspaceRoute($event));

        $standings = app(BracketService::class)->swissStandings($event->fresh());
        $player1Row = $standings->firstWhere('player.id', $player1->id);
        $player2Row = $standings->firstWhere('player.id', $player2->id);

        $this->assertSame(7, $player1Row['battle_points']);
        $this->assertSame(2, $player2Row['battle_points']);
        $this->assertSame(5, $player1Row['points_diff']);
        $this->assertSame(-5, $player2Row['points_diff']);
    }

    public function test_single_elimination_round_generation_creates_first_round_matches(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'bracket-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Single Elim Weekly',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-03-30',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        collect(['alpha', 'beta', 'charlie', 'delta'])->each(function (string $nickname) use ($event): void {
            $player = Player::query()->create([
                'user_id' => User::factory()->create(['nickname' => $nickname])->id,
            ]);

            EventParticipant::query()->create([
                'event_id' => $event->id,
                'player_id' => $player->id,
                'deck_name' => strtoupper($nickname).' Deck',
                'deck_bey1' => 'Bey A',
                'deck_bey2' => 'Bey B',
                'deck_bey3' => 'Bey C',
                'deck_registered_at' => now(),
            ]);
        });

        $response = $this->post(route('events.bracket.generate', $event));

        $response->assertRedirect($this->workspaceRoute($event));
        $this->assertDatabaseHas('event_rounds', [
            'event_id' => $event->id,
            'stage' => 'single_elim',
            'round_number' => 1,
        ]);
        $this->assertSame(2, $event->matches()->count());
        $this->assertDatabaseHas('matches', [
            'event_id' => $event->id,
            'stage' => 'single_elim',
            'round_number' => 1,
            'status' => 'pending',
            'is_bye' => 0,
        ]);
    }

    public function test_completed_single_elimination_event_auto_generates_results_and_bird_king_award(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'auto-final-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Auto Final Cup',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-04-02',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $players = collect(['alpha', 'beta'])->map(function (string $nickname) use ($event) {
            $player = Player::query()->create([
                'user_id' => User::factory()->create(['nickname' => $nickname])->id,
            ]);

            EventParticipant::query()->create([
                'event_id' => $event->id,
                'player_id' => $player->id,
                'deck_name' => strtoupper($nickname).' Deck',
                'deck_bey1' => 'Bey A',
                'deck_bey2' => 'Bey B',
                'deck_bey3' => 'Bey C',
                'deck_registered_at' => now(),
            ]);

            return $player;
        });

        $this->post(route('events.bracket.generate', $event))->assertRedirect($this->workspaceRoute($event));

        $finalMatch = $event->fresh('matches')->matches->firstOrFail();

        $this->submitMatchResult($event, $finalMatch, [
            [1, 'spin'],
            [1, 'burst'],
            [2, 'spin'],
            [1, 'extreme'],
            [1, 'spin'],
        ])->assertRedirect($this->workspaceRoute($event));

        $event->refresh();

        $this->assertSame('completed', $event->bracket_status);
        $this->assertSame('finished', $event->status);
        $this->assertDatabaseHas('event_results', [
            'event_id' => $event->id,
            'player_id' => $players[0]->id,
            'placement' => 1,
        ]);
        $this->assertDatabaseHas('event_results', [
            'event_id' => $event->id,
            'player_id' => $players[1]->id,
            'placement' => 2,
        ]);
        $this->assertDatabaseHas('event_awards', [
            'event_id' => $event->id,
            'player_id' => $players[0]->id,
            'award_id' => Award::query()->where('name', 'Bird King')->value('id'),
        ]);
    }

    public function test_single_elimination_round_one_requires_registered_decks_when_deck_lock_is_disabled(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'elim-deck-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Elim Deck Gate',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-04-13',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $players = collect(['alpha', 'beta'])->map(function (string $nickname) use ($event) {
            $player = Player::query()->create([
                'user_id' => User::factory()->create(['nickname' => $nickname])->id,
            ]);

            EventParticipant::query()->create([
                'event_id' => $event->id,
                'player_id' => $player->id,
            ]);

            return $player;
        });

        $this->post(route('events.bracket.generate', $event))
            ->assertSessionHasErrors('bracket');

        foreach ($players as $player) {
            $this->post(route('events.participants.deck.store', [$event, $player]), [
                'deck_name' => strtoupper($player->user->nickname).' Deck',
                'deck_bey1' => 'Bey A',
                'deck_bey2' => 'Bey B',
                'deck_bey3' => 'Bey C',
            ])->assertRedirect($this->workspaceRoute($event));
        }

        $this->post(route('events.bracket.generate', $event))
            ->assertRedirect($this->workspaceRoute($event));

        $this->assertDatabaseHas('event_rounds', [
            'event_id' => $event->id,
            'stage' => 'single_elim',
            'round_number' => 1,
        ]);
    }

    public function test_swiss_round_generation_creates_a_bye_for_odd_player_counts(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'swiss-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Swiss Major',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'swiss_single_elim',
            'swiss_rounds' => 3,
            'top_cut_size' => 4,
            'match_format' => 7,
            'date' => '2026-04-01',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        collect(['alpha', 'beta', 'charlie', 'delta', 'echo'])->each(function (string $nickname) use ($event): void {
            $player = Player::query()->create([
                'user_id' => User::factory()->create(['nickname' => $nickname])->id,
            ]);

            EventParticipant::query()->create([
                'event_id' => $event->id,
                'player_id' => $player->id,
            ]);
        });

        $response = $this->post(route('events.bracket.generate', $event));

        $response->assertRedirect($this->workspaceRoute($event));
        $this->assertDatabaseHas('event_rounds', [
            'event_id' => $event->id,
            'stage' => 'swiss',
            'round_number' => 1,
        ]);
        $this->assertSame(3, $event->matches()->count());
        $this->assertDatabaseHas('matches', [
            'event_id' => $event->id,
            'stage' => 'swiss',
            'round_number' => 1,
            'is_bye' => 1,
            'status' => 'completed',
        ]);
    }

    public function test_second_swiss_round_is_auto_generated_after_first_round_completes(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'auto-swiss-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Auto Swiss Round Two',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'swiss_single_elim',
            'swiss_rounds' => 2,
            'top_cut_size' => 2,
            'match_format' => 7,
            'date' => '2026-04-05',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        collect(['alpha', 'beta', 'charlie', 'delta'])->each(function (string $nickname) use ($event): void {
            $player = Player::query()->create([
                'user_id' => User::factory()->create(['nickname' => $nickname])->id,
            ]);

            EventParticipant::query()->create([
                'event_id' => $event->id,
                'player_id' => $player->id,
            ]);
        });

        $this->post(route('events.bracket.generate', $event))->assertRedirect($this->workspaceRoute($event));

        $roundOneMatches = $event->fresh('matches')->matches->where('stage', 'swiss')->where('round_number', 1)->sortBy('match_number')->values();

        $this->submitMatchResult($event, $roundOneMatches[0], [
            [1, 'spin'],
            [1, 'burst'],
            [1, 'spin'],
            [1, 'extreme'],
        ])->assertRedirect($this->workspaceRoute($event));

        $response = $this->submitMatchResult($event, $roundOneMatches[1], [
            [2, 'spin'],
            [2, 'burst'],
            [1, 'spin'],
            [2, 'extreme'],
            [2, 'spin'],
        ]);

        $response->assertRedirect($this->workspaceRoute($event));
        $response->assertSessionHas('status', 'Match updated. Swiss round 2 generated.');

        $this->assertDatabaseHas('event_rounds', [
            'event_id' => $event->id,
            'stage' => 'swiss',
            'round_number' => 2,
        ]);
    }

    public function test_top_cut_waits_for_qualified_players_to_register_decks(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'topcut-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Swiss Deck Gate',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'swiss_single_elim',
            'swiss_rounds' => 1,
            'top_cut_size' => 2,
            'match_format' => 7,
            'date' => '2026-04-14',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $players = collect(['alpha', 'beta', 'charlie', 'delta'])->map(function (string $nickname) use ($event) {
            $player = Player::query()->create([
                'user_id' => User::factory()->create(['nickname' => $nickname])->id,
            ]);

            EventParticipant::query()->create([
                'event_id' => $event->id,
                'player_id' => $player->id,
            ]);

            return $player;
        });

        $this->post(route('events.bracket.generate', $event))->assertRedirect($this->workspaceRoute($event));
        $swissMatches = $event->fresh('matches')->matches->sortBy('match_number')->values();

        $this->submitMatchResult($event, $swissMatches[0], [
            [1, 'spin'],
            [1, 'burst'],
            [1, 'spin'],
            [1, 'extreme'],
        ])->assertRedirect($this->workspaceRoute($event));

        $response = $this->submitMatchResult($event, $swissMatches[1], [
            [1, 'spin'],
            [1, 'burst'],
            [1, 'spin'],
            [1, 'extreme'],
        ]);

        $response->assertRedirect($this->workspaceRoute($event));
        $response->assertSessionHasErrors('bracket');
        $this->assertDatabaseMissing('event_rounds', [
            'event_id' => $event->id,
            'stage' => 'single_elim',
            'round_number' => 1,
        ]);

        $qualifiers = app(BracketService::class)->deckRegistrationTargets($event->fresh());

        foreach ($qualifiers as $participant) {
            $this->post(route('events.participants.deck.store', [$event, $participant->player]), [
                'deck_name' => strtoupper($participant->player->user->nickname).' Top Cut',
                'deck_bey1' => 'Phoenix',
                'deck_bey2' => 'Dran',
                'deck_bey3' => 'Wizard',
            ])->assertRedirect($this->workspaceRoute($event));
        }

        $this->post(route('events.bracket.generate', $event))
            ->assertRedirect($this->workspaceRoute($event));

        $this->assertDatabaseHas('event_rounds', [
            'event_id' => $event->id,
            'stage' => 'single_elim',
            'round_number' => 1,
        ]);
    }

    public function test_single_elimination_matches_use_registered_deck_beys(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'deck-bey-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Registered Bey Elims',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-04-15',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $players = collect([
            'alpha' => ['deck' => 'Alpha Deck', 'beys' => ['Phoenix', 'Dran', 'Wizard']],
            'beta' => ['deck' => 'Beta Deck', 'beys' => ['Knight', 'Shark', 'Leon']],
        ])->map(function (array $deckData, string $nickname) use ($event) {
            $player = Player::query()->create([
                'user_id' => User::factory()->create(['nickname' => $nickname])->id,
            ]);

            EventParticipant::query()->create([
                'event_id' => $event->id,
                'player_id' => $player->id,
                'deck_name' => $deckData['deck'],
                'deck_bey1' => $deckData['beys'][0],
                'deck_bey2' => $deckData['beys'][1],
                'deck_bey3' => $deckData['beys'][2],
                'deck_registered_at' => now(),
            ]);

            return $player;
        })->values();

        $this->post(route('events.bracket.generate', $event))->assertRedirect($this->workspaceRoute($event));
        $match = $event->fresh('matches')->matches->firstOrFail();

        $this->post(route('events.matches.store', $event), [
            'match_id' => $match->id,
            'event_round_id' => $match->event_round_id,
            'player1_id' => $match->player1_id,
            'player2_id' => $match->player2_id,
            'round_number' => $match->round_number,
            'match_number' => $match->match_number,
            'player1_bey1' => 'Wrong Bey 1',
            'player1_bey2' => 'Wrong Bey 2',
            'player1_bey3' => 'Wrong Bey 3',
            'player2_bey1' => 'Wrong Bey 4',
            'player2_bey2' => 'Wrong Bey 5',
            'player2_bey3' => 'Wrong Bey 6',
            'result_1' => 1,
            'result_type_1' => 'spin',
            'result_2' => 1,
            'result_type_2' => 'burst',
            'result_3' => 1,
            'result_type_3' => 'extreme',
            'result_4' => 1,
            'result_type_4' => 'spin',
        ])->assertRedirect($this->workspaceRoute($event));

        $this->assertDatabaseHas('matches', [
            'id' => $match->id,
            'player1_bey1' => 'Phoenix',
            'player1_bey2' => 'Dran',
            'player1_bey3' => 'Wizard',
            'player2_bey1' => 'Knight',
            'player2_bey2' => 'Shark',
            'player2_bey3' => 'Leon',
        ]);
    }

    public function test_completed_swiss_event_auto_generates_results_and_swiss_awards(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'swiss-auto-host']);
        $eventType = EventType::query()->first();

        $event = Event::query()->create([
            'title' => 'Auto Swiss Finals',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'swiss_single_elim',
            'swiss_rounds' => 1,
            'top_cut_size' => 2,
            'match_format' => 7,
            'date' => '2026-04-03',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $players = collect(['alpha', 'beta', 'charlie', 'delta'])->map(function (string $nickname) use ($event) {
            $player = Player::query()->create([
                'user_id' => User::factory()->create(['nickname' => $nickname])->id,
            ]);

            EventParticipant::query()->create([
                'event_id' => $event->id,
                'player_id' => $player->id,
            ]);

            return $player;
        });

        $this->post(route('events.bracket.generate', $event))->assertRedirect($this->workspaceRoute($event));

        $swissMatches = $event->fresh('matches')->matches->sortBy('match_number')->values();

        $this->submitMatchResult($event, $swissMatches[0], [
            [1, 'spin'],
            [1, 'spin'],
            [1, 'burst'],
            [1, 'extreme'],
        ])->assertRedirect($this->workspaceRoute($event));

        $this->submitMatchResult($event, $swissMatches[1], [
            [1, 'spin'],
            [2, 'spin'],
            [1, 'burst'],
            [2, 'spin'],
            [1, 'extreme'],
            [2, 'burst'],
            [1, 'spin'],
        ])->assertRedirect($this->workspaceRoute($event));

        $qualifiers = app(BracketService::class)->deckRegistrationTargets($event->fresh());

        foreach ($qualifiers as $participant) {
            $this->post(route('events.participants.deck.store', [$event, $participant->player]), [
                'deck_name' => strtoupper($participant->player->user->nickname).' Top Cut',
                'deck_bey1' => 'Phoenix',
                'deck_bey2' => 'Dran',
                'deck_bey3' => 'Wizard',
            ])->assertRedirect($this->workspaceRoute($event));
        }

        $this->post(route('events.bracket.generate', $event))
            ->assertRedirect($this->workspaceRoute($event));

        $finalMatch = $event->fresh('matches')->matches->where('stage', 'single_elim')->firstOrFail();

        $this->submitMatchResult($event, $finalMatch, [
            [2, 'spin'],
            [2, 'burst'],
            [1, 'spin'],
            [2, 'extreme'],
            [2, 'spin'],
        ])->assertRedirect($this->workspaceRoute($event));

        $event->refresh();

        $this->assertSame('completed', $event->bracket_status);
        $this->assertSame('finished', $event->status);
        $this->assertDatabaseHas('event_awards', [
            'event_id' => $event->id,
            'player_id' => $players[2]->id,
            'award_id' => Award::query()->where('name', 'Swiss Champ')->value('id'),
        ]);
        $this->assertDatabaseHas('event_awards', [
            'event_id' => $event->id,
            'player_id' => $players[0]->id,
            'award_id' => Award::query()->where('name', 'Swiss King')->value('id'),
        ]);
        $this->assertDatabaseHas('event_results', [
            'event_id' => $event->id,
            'player_id' => $players[2]->id,
            'placement' => 1,
        ]);
        $this->assertDatabaseHas('event_results', [
            'event_id' => $event->id,
            'player_id' => $players[0]->id,
            'placement' => 2,
        ]);
    }

    public function test_admin_can_set_an_upcoming_event_as_active(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'active-host']);
        $eventType = EventType::query()->first();

        $firstEvent = Event::query()->create([
            'title' => 'First Active Candidate',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-04-06',
            'location' => null,
            'status' => 'upcoming',
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        $secondEvent = Event::query()->create([
            'title' => 'Second Active Candidate',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-04-07',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $response = $this->post(route('events.activate', $secondEvent), [
            'dashboard_redirect' => 1,
            'dashboard_panel' => 'events',
            'dashboard_event_id' => $secondEvent->id,
        ]);

        $response->assertRedirect(route('dashboard', ['panel' => 'events', 'event' => $secondEvent->id]));
        $this->assertDatabaseHas('events', [
            'id' => $firstEvent->id,
            'is_active' => 0,
        ]);
        $this->assertDatabaseHas('events', [
            'id' => $secondEvent->id,
            'is_active' => 1,
        ]);
    }

    public function test_workspace_only_shows_the_active_event(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'workspace-host']);
        $eventType = EventType::query()->first();

        $activeEvent = Event::query()->create([
            'title' => 'Active Finals',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-04-08',
            'location' => null,
            'status' => 'upcoming',
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        $inactiveEvent = Event::query()->create([
            'title' => 'Inactive Weekly',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-04-09',
            'location' => null,
            'status' => 'upcoming',
            'created_by' => $creator->id,
        ]);

        $response = $this->get(route('dashboard', ['panel' => 'workspace', 'event' => $inactiveEvent->id]));

        $response->assertOk();
        $response->assertSee('Active Event');
        $response->assertSee($activeEvent->title);
        $response->assertDontSee($inactiveEvent->title);
    }

    public function test_events_panel_defaults_to_create_mode_without_selected_event(): void
    {
        $this->actingAs($this->createAdmin());

        $creator = User::factory()->create(['nickname' => 'events-host']);
        $eventType = EventType::query()->first();

        Event::query()->create([
            'title' => 'Existing Active Event',
            'description' => null,
            'event_type_id' => $eventType->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-04-10',
            'location' => null,
            'status' => 'upcoming',
            'is_active' => true,
            'created_by' => $creator->id,
        ]);

        $response = $this->get(route('dashboard', ['panel' => 'events']));

        $response->assertOk();
        $response->assertSee('Create Mode');
        $response->assertSee('Ready to create a new event.');
        $response->assertDontSee('Editing: Existing Active Event');
    }

    private function createAdmin(): User
    {
        return User::factory()->create([
            'nickname' => 'admin-user',
            'role' => 'admin',
            'is_claimed' => true,
        ]);
    }

    private function submitMatchResult(Event $event, EventMatch $match, array $battles)
    {
        $payload = [
            'match_id' => $match->id,
            'event_round_id' => $match->event_round_id,
            'player1_id' => $match->player1_id,
            'player2_id' => $match->player2_id,
            'round_number' => $match->round_number,
            'match_number' => $match->match_number,
        ];

        foreach ($battles as $index => [$winner, $type]) {
            $slot = $index + 1;
            $payload["result_{$slot}"] = $winner;
            $payload["result_type_{$slot}"] = $type;
        }

        return $this->post(route('events.matches.store', $event), $payload);
    }

    private function workspaceRoute(Event $event): string
    {
        return route('dashboard', ['panel' => 'workspace', 'event' => $event->id]);
    }
}

<?php

namespace Tests\Feature;

use App\Models\Event;
use App\Models\EventMatch;
use App\Models\EventRound;
use App\Models\EventType;
use App\Models\Player;
use App\Models\StadiumSide;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ExampleTest extends TestCase
{
    use RefreshDatabase;

    public function test_dashboard_redirects_guests_to_login(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect(route('login'));
    }

    public function test_login_page_is_accessible_for_guests(): void
    {
        $response = $this->get(route('login'));

        $response->assertOk();
    }

    public function test_public_home_page_is_accessible(): void
    {
        $response = $this->get(route('home'));

        $response->assertOk();
    }

    public function test_public_players_page_is_accessible(): void
    {
        $response = $this->get(route('players.index'));

        $response->assertOk();
        $response->assertSee('Players');
    }

    public function test_public_live_event_page_is_accessible_by_event_id(): void
    {
        $event = $this->createPublicEvent('Viewer Finals', 'finished');

        $response = $this->get(route('live.viewer.event', $event));

        $response->assertOk();
        $response->assertSee('Viewer Finals');
    }

    public function test_public_live_match_page_is_accessible_by_match_id(): void
    {
        $event = $this->createPublicEvent('Match Archive', 'finished');
        $round = EventRound::query()->create([
            'event_id' => $event->id,
            'stage' => 'single_elim',
            'round_number' => 1,
            'label' => 'Elimination Final',
            'status' => 'completed',
        ]);

        $playerOne = Player::query()->create([
            'user_id' => User::factory()->create(['nickname' => 'viewer-alpha'])->id,
        ]);
        $playerTwo = Player::query()->create([
            'user_id' => User::factory()->create(['nickname' => 'viewer-beta'])->id,
        ]);

        $match = EventMatch::query()->create([
            'event_id' => $event->id,
            'event_round_id' => $round->id,
            'stage' => 'single_elim',
            'player1_id' => $playerOne->id,
            'player2_id' => $playerTwo->id,
            'player1_score' => 4,
            'player2_score' => 2,
            'winner_id' => $playerOne->id,
            'round_number' => 1,
            'match_number' => 1,
            'status' => 'completed',
            'is_bye' => false,
            'result_1' => 1,
            'result_type_1' => 'spin',
            'result_2' => 1,
            'result_type_2' => 'burst',
            'result_3' => 2,
            'result_type_3' => 'spin',
            'result_4' => 1,
            'result_type_4' => 'spin',
        ]);

        $response = $this->get(route('live.viewer.match', $match));

        $response->assertOk();
        $response->assertSee('Match #'.$match->id);
        $response->assertSee('viewer-alpha');
        $response->assertSee('viewer-beta');
    }

    public function test_public_player_profile_page_is_accessible_by_player_id(): void
    {
        $user = User::factory()->create([
            'nickname' => 'public-player',
            'name' => 'Public Player',
            'role' => 'user',
            'is_claimed' => true,
        ]);
        $player = Player::query()->create([
            'user_id' => $user->id,
        ]);

        $response = $this->get(route('user.dashboard.profile', $player));

        $response->assertOk();
        $response->assertSee('public-player');
    }

    public function test_public_player_profile_displays_x_and_b_side_win_rates(): void
    {
        $user = User::factory()->create([
            'nickname' => 'side-player',
            'name' => 'Side Player',
            'role' => 'user',
            'is_claimed' => true,
        ]);
        $player = Player::query()->create([
            'user_id' => $user->id,
        ]);
        $opponent = Player::query()->create([
            'user_id' => User::factory()->create(['nickname' => 'side-opponent'])->id,
        ]);
        $event = $this->createPublicEvent('Side Stats Finals', 'finished');
        $round = EventRound::query()->create([
            'event_id' => $event->id,
            'stage' => 'single_elim',
            'round_number' => 1,
            'label' => 'Main Bracket',
            'status' => 'completed',
        ]);
        $xSideId = StadiumSide::query()->where('code', 'X')->value('id');
        $bSideId = StadiumSide::query()->where('code', 'B')->value('id');

        EventMatch::query()->create([
            'event_id' => $event->id,
            'event_round_id' => $round->id,
            'stage' => 'single_elim',
            'player1_id' => $player->id,
            'player1_stadium_side_id' => $xSideId,
            'player2_id' => $opponent->id,
            'player2_stadium_side_id' => $bSideId,
            'player1_score' => 4,
            'player2_score' => 1,
            'winner_id' => $player->id,
            'round_number' => 1,
            'match_number' => 1,
            'status' => 'completed',
            'is_bye' => false,
        ]);

        EventMatch::query()->create([
            'event_id' => $event->id,
            'event_round_id' => $round->id,
            'stage' => 'single_elim',
            'player1_id' => $opponent->id,
            'player1_stadium_side_id' => $xSideId,
            'player2_id' => $player->id,
            'player2_stadium_side_id' => $bSideId,
            'player1_score' => 4,
            'player2_score' => 2,
            'winner_id' => $opponent->id,
            'round_number' => 1,
            'match_number' => 2,
            'status' => 'completed',
            'is_bye' => false,
        ]);

        $response = $this->get(route('user.dashboard.profile', $player));

        $response->assertOk();
        $response->assertSee('X Side Win %');
        $response->assertSee('B Side Win %');
        $response->assertSee('100.0%');
        $response->assertSee('0.0%');
        $response->assertSee('1-0');
        $response->assertSee('0-1');
    }

    private function createPublicEvent(string $title, string $status): Event
    {
        return Event::query()->create([
            'title' => $title,
            'description' => null,
            'event_type_id' => EventType::query()->firstOrFail()->id,
            'bracket_type' => 'single_elim',
            'match_format' => 7,
            'date' => '2026-03-31',
            'location' => 'Viewer Arena',
            'status' => $status,
            'created_by' => User::factory()->create(['nickname' => strtolower(str_replace(' ', '-', $title)).'-host'])->id,
        ]);
    }
}

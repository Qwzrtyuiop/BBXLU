<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class LocalMockDataSeeder extends Seeder
{
    private const TAG = '[MOCK]';

    private const PATTERNS = [
        'sweep' => [['W', 'extreme'], ['W', 'spin']],
        'steady' => [['W', 'spin'], ['L', 'spin'], ['W', 'burst'], ['W', 'spin']],
        'close' => [['W', 'spin'], ['L', 'burst'], ['W', 'spin'], ['L', 'spin'], ['W', 'burst']],
        'wide' => [['W', 'burst'], ['L', 'spin'], ['W', 'spin'], ['W', 'burst']],
        'grind' => [['L', 'spin'], ['W', 'burst'], ['L', 'spin'], ['W', 'spin'], ['W', 'spin']],
    ];

    private array $playerIds = [];

    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        DB::transaction(function (): void {
            $now = now();

            $this->purgeMockData();

            $eventTypeIds = DB::table('event_types')->pluck('id', 'name')->all();
            $awardIds = DB::table('awards')->pluck('id', 'name')->all();
            $adminId = $this->createAdmin($now);

            $this->playerIds = $this->createClaimedPlayers(140, $now);
            $this->createUnclaimedUsers(36, $now);

            $this->seedSwissEvent($adminId, $awardIds, $now, [
                'title' => self::TAG.' Grand Prix Finals',
                'description' => self::TAG.' Completed swiss event where the swiss king wins top cut.',
                'event_type_id' => $eventTypeIds['GT'] ?? reset($eventTypeIds),
                'date' => now()->subDay()->toDateString(),
                'location' => self::TAG.' Arena Alpha',
                'status' => 'finished',
                'bracket_status' => 'completed',
                'participants' => range(1, 8),
                'decks' => range(1, 8),
                'top_cut_size' => 4,
                'swiss_king' => 1,
                'bird_king' => 8,
                'swiss' => [
                    [[1, 8, 1, 'wide'], [2, 7, 1, 'steady'], [3, 6, 1, 'close'], [4, 5, 1, 'grind']],
                    [[1, 4, 1, 'sweep'], [2, 3, 1, 'steady'], [5, 8, 1, 'steady'], [6, 7, 1, 'close']],
                    [[1, 2, 1, 'close'], [3, 4, 1, 'steady'], [5, 6, 1, 'steady'], [7, 8, 1, 'wide']],
                ],
                'elim' => [
                    [[1, 5, 1, 'steady'], [2, 3, 1, 'close']],
                    [[1, 2, 1, 'steady']],
                ],
                'placements' => [1 => 1, 2 => 2, 3 => 3, 4 => 5],
                'awards' => ['Swiss King' => 1, 'Bird King' => 8, 'Swiss Champ' => 1],
            ]);

            $this->seedSwissEvent($adminId, $awardIds, $now, [
                'title' => self::TAG.' Upset Swiss Masters',
                'description' => self::TAG.' Completed swiss event where the swiss king loses top cut.',
                'event_type_id' => $eventTypeIds['GT'] ?? reset($eventTypeIds),
                'date' => now()->subDays(2)->toDateString(),
                'location' => self::TAG.' Arena Beta',
                'status' => 'finished',
                'bracket_status' => 'completed',
                'participants' => range(9, 16),
                'decks' => range(9, 16),
                'top_cut_size' => 4,
                'swiss_king' => 9,
                'bird_king' => 16,
                'swiss' => [
                    [[9, 16, 1, 'sweep'], [10, 15, 1, 'steady'], [11, 14, 1, 'close'], [12, 13, 2, 'grind']],
                    [[9, 13, 1, 'steady'], [10, 11, 1, 'close'], [12, 16, 1, 'steady'], [14, 15, 1, 'wide']],
                    [[9, 10, 1, 'close'], [11, 13, 2, 'steady'], [12, 14, 1, 'grind'], [15, 16, 1, 'wide']],
                ],
                'elim' => [
                    [[9, 13, 2, 'steady'], [10, 12, 1, 'close']],
                    [[13, 10, 1, 'steady']],
                ],
                'placements' => [1 => 13, 2 => 10, 3 => 9, 4 => 12],
                'awards' => ['Swiss King' => 9, 'Bird King' => 16],
            ]);

            $this->seedSwissEvent($adminId, $awardIds, $now, [
                'title' => self::TAG.' Legacy Swiss Classic',
                'description' => self::TAG.' Older swiss archive to deepen leaderboard and award history.',
                'event_type_id' => $eventTypeIds['Casual'] ?? reset($eventTypeIds),
                'date' => now()->subDays(6)->toDateString(),
                'location' => self::TAG.' Archive Hall',
                'status' => 'finished',
                'bracket_status' => 'completed',
                'participants' => [1, 2, 3, 4, 41, 42, 43, 44],
                'decks' => [1, 2, 3, 4, 41, 42, 43, 44],
                'top_cut_size' => 4,
                'swiss_king' => 1,
                'bird_king' => 44,
                'swiss' => [
                    [[1, 44, 1, 'wide'], [2, 43, 1, 'steady'], [3, 42, 1, 'close'], [4, 41, 2, 'grind']],
                    [[1, 41, 1, 'sweep'], [2, 3, 1, 'steady'], [4, 44, 1, 'close'], [42, 43, 1, 'wide']],
                    [[1, 2, 1, 'close'], [3, 41, 1, 'grind'], [4, 42, 1, 'steady'], [43, 44, 1, 'wide']],
                ],
                'elim' => [
                    [[1, 4, 1, 'steady'], [2, 3, 1, 'close']],
                    [[1, 2, 1, 'steady']],
                ],
                'placements' => [1 => 1, 2 => 2, 3 => 3, 4 => 4],
                'awards' => ['Swiss King' => 1, 'Bird King' => 44, 'Swiss Champ' => 1],
            ]);

            $this->seedSingleElimEvent($adminId, $awardIds, $now, [
                'title' => self::TAG.' Knockout Cup',
                'description' => self::TAG.' Completed single elimination showcase event.',
                'event_type_id' => $eventTypeIds['Others'] ?? reset($eventTypeIds),
                'date' => now()->subDays(3)->toDateString(),
                'location' => self::TAG.' Knockout Dome',
                'status' => 'finished',
                'bracket_status' => 'completed',
                'participants' => range(17, 24),
                'decks' => range(17, 24),
                'rounds' => [
                    [[17, 24, 1, 'sweep'], [18, 23, 1, 'steady'], [19, 22, 1, 'close'], [20, 21, 1, 'grind']],
                    [[17, 20, 1, 'steady'], [18, 19, 1, 'close']],
                    [[17, 18, 1, 'wide']],
                ],
                'placements' => [1 => 17, 2 => 18, 3 => 20, 4 => 19],
                'awards' => ['Bird King' => 17],
            ]);

            $this->seedSwissEvent($adminId, [], $now, [
                'title' => self::TAG.' Metro Open Live',
                'description' => self::TAG.' Active swiss event for the workspace board and live score entry.',
                'event_type_id' => $eventTypeIds['GT'] ?? reset($eventTypeIds),
                'date' => now()->toDateString(),
                'location' => self::TAG.' Main Stage',
                'status' => 'upcoming',
                'bracket_status' => 'in_progress',
                'is_active' => true,
                'participants' => range(25, 40),
                'decks' => [],
                'top_cut_size' => 8,
                'swiss' => [
                    [[25, 26, 1, 'steady'], [27, 28, 2, 'close'], [29, 30, 1, 'wide'], [31, 32, 2, 'grind'], [33, 34, 2, 'steady'], [35, 36, 1, 'sweep'], [37, 38, 2, 'wide'], [39, 40, 1, 'steady']],
                    [[25, 28, 1, 'sweep'], [29, 32, 1, 'close'], [34, 35, 1, 'steady'], [38, 39, 2, 'grind'], [26, 27, 1, 'steady'], [30, 31, 2, 'close'], [33, 36, 2, 'wide'], [37, 40, 1, 'steady']],
                    [[25, 29], [34, 40], [28, 35], [26, 32], [27, 39], [30, 38], [31, 36], [33, 37]],
                ],
            ]);

            $this->seedSwissEvent($adminId, [], $now, [
                'title' => self::TAG.' Road To Top Cut',
                'description' => self::TAG.' Swiss rounds are complete and deck registration is waiting for top cut qualifiers.',
                'event_type_id' => $eventTypeIds['GT'] ?? reset($eventTypeIds),
                'date' => now()->addDay()->toDateString(),
                'location' => self::TAG.' Qualifier Station',
                'status' => 'upcoming',
                'bracket_status' => 'in_progress',
                'participants' => range(41, 48),
                'decks' => [41, 42],
                'top_cut_size' => 4,
                'swiss_king' => 41,
                'bird_king' => 48,
                'swiss' => [
                    [[41, 48, 1, 'sweep'], [42, 47, 1, 'steady'], [43, 46, 1, 'close'], [44, 45, 1, 'grind']],
                    [[41, 44, 1, 'steady'], [42, 43, 1, 'close'], [45, 48, 1, 'steady'], [46, 47, 1, 'wide']],
                    [[41, 42, 1, 'close'], [43, 44, 1, 'grind'], [45, 46, 1, 'steady'], [47, 48, 1, 'wide']],
                ],
            ]);

            $this->seedSingleElimEvent($adminId, [], $now, [
                'title' => self::TAG.' Locked Deck Clash',
                'description' => self::TAG.' Single elimination event with locked decks already registered.',
                'event_type_id' => $eventTypeIds['Casual'] ?? reset($eventTypeIds),
                'date' => now()->addDays(2)->toDateString(),
                'location' => self::TAG.' Lockdown Stage',
                'status' => 'upcoming',
                'bracket_status' => 'in_progress',
                'is_lock_deck' => true,
                'participants' => range(49, 56),
                'decks' => range(49, 56),
                'rounds' => [
                    [[49, 56, 1, 'sweep'], [50, 55, 1, 'close'], [51, 54, 1, 'steady'], [52, 53, 1, 'grind']],
                    [[49, 50], [51, 52]],
                ],
            ]);

            $this->seedUpcomingDraftEvents($adminId, $eventTypeIds, $now);
            $this->seedArchiveSingleEliminationEvents($adminId, $eventTypeIds, $awardIds, $now);
        });
    }

    private function createAdmin($now): int
    {
        return (int) DB::table('users')->insertGetId([
            'nickname' => self::TAG.' admin',
            'name' => self::TAG.' admin',
            'email' => 'mock.admin+bbxlu@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_claimed' => true,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function createClaimedPlayers(int $count, $now): array
    {
        $playerIds = [];

        for ($index = 1; $index <= $count; $index++) {
            $suffix = str_pad((string) $index, 3, '0', STR_PAD_LEFT);
            $nickname = self::TAG.' player '.$suffix;

            $userId = (int) DB::table('users')->insertGetId([
                'nickname' => $nickname,
                'name' => $nickname,
                'email' => 'mock.player'.$suffix.'+bbxlu@example.com',
                'password' => Hash::make('password'),
                'role' => 'user',
                'is_claimed' => true,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            $playerIds[$index] = (int) DB::table('players')->insertGetId([
                'user_id' => $userId,
                'created_at' => $now,
            ]);
        }

        return $playerIds;
    }

    private function createUnclaimedUsers(int $count, $now): void
    {
        for ($index = 1; $index <= $count; $index++) {
            $suffix = str_pad((string) $index, 3, '0', STR_PAD_LEFT);

            DB::table('users')->insert([
                'nickname' => self::TAG.' reserve '.$suffix,
                'name' => self::TAG.' reserve '.$suffix,
                'email' => null,
                'password' => null,
                'role' => 'user',
                'is_claimed' => false,
                'created_at' => $now,
                'updated_at' => $now,
            ]);
        }
    }

    private function seedSwissEvent(int $adminId, array $awardIds, $now, array $config): void
    {
        $eventId = $this->createEvent([
            'title' => $config['title'],
            'description' => $config['description'],
            'event_type_id' => $config['event_type_id'],
            'bracket_type' => 'swiss_single_elim',
            'swiss_rounds' => count($config['swiss']),
            'top_cut_size' => $config['top_cut_size'] ?? 4,
            'match_format' => 7,
            'date' => $config['date'],
            'location' => $config['location'],
            'status' => $config['status'],
            'bracket_status' => $config['bracket_status'],
            'is_lock_deck' => false,
            'is_active' => $config['is_active'] ?? false,
            'swiss_king_player_id' => isset($config['swiss_king']) ? $this->playerIds[$config['swiss_king']] : null,
            'bird_king_player_id' => isset($config['bird_king']) ? $this->playerIds[$config['bird_king']] : null,
            'created_by' => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->attachParticipants($eventId, $config['participants'], $config['decks'] ?? [], $now);

        foreach ($config['swiss'] as $roundIndex => $matches) {
            $roundNumber = $roundIndex + 1;
            $roundId = $this->createRound(
                $eventId,
                'swiss',
                $roundNumber,
                'Swiss Round '.$roundNumber,
                $this->roundStatus($matches),
                $now
            );

            $this->createConfiguredMatches($eventId, $roundId, 'swiss', $roundNumber, $matches, $now);
        }

        foreach ($config['elim'] ?? [] as $roundIndex => $matches) {
            $roundNumber = $roundIndex + 1;
            $roundId = $this->createRound(
                $eventId,
                'single_elim',
                $roundNumber,
                $roundIndex === 0 ? 'Top Cut Round 1' : ($roundIndex === 1 ? 'Top Cut Final' : 'Top Cut Round '.($roundNumber + 1)),
                $this->roundStatus($matches),
                $now
            );

            $this->createConfiguredMatches($eventId, $roundId, 'single_elim', $roundNumber, $matches, $now);
        }

        if (! empty($config['placements'])) {
            $this->createPlacements($eventId, $config['placements']);
        }

        if (! empty($config['awards']) && $awardIds !== []) {
            $this->createAwards($eventId, $awardIds, $config['awards']);
        }
    }

    private function seedSingleElimEvent(int $adminId, array $awardIds, $now, array $config): void
    {
        $eventId = $this->createEvent([
            'title' => $config['title'],
            'description' => $config['description'],
            'event_type_id' => $config['event_type_id'],
            'bracket_type' => 'single_elim',
            'swiss_rounds' => null,
            'top_cut_size' => null,
            'match_format' => 7,
            'date' => $config['date'],
            'location' => $config['location'],
            'status' => $config['status'],
            'bracket_status' => $config['bracket_status'],
            'is_lock_deck' => $config['is_lock_deck'] ?? false,
            'is_active' => $config['is_active'] ?? false,
            'created_by' => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->attachParticipants($eventId, $config['participants'], $config['decks'] ?? [], $now);

        foreach ($config['rounds'] as $roundIndex => $matches) {
            $roundNumber = $roundIndex + 1;
            $label = match ($roundNumber) {
                1 => 'Elimination Round 1',
                2 => count($config['rounds']) === 2 ? 'Elimination Final' : 'Elimination Round 2',
                default => 'Elimination Final',
            };

            $roundId = $this->createRound(
                $eventId,
                'single_elim',
                $roundNumber,
                $label,
                $this->roundStatus($matches),
                $now
            );

            $this->createConfiguredMatches($eventId, $roundId, 'single_elim', $roundNumber, $matches, $now);
        }

        if (! empty($config['placements'])) {
            $this->createPlacements($eventId, $config['placements']);
        }

        if (! empty($config['awards']) && $awardIds !== []) {
            $this->createAwards($eventId, $awardIds, $config['awards']);
        }
    }

    private function seedUpcomingDraftEvents(int $adminId, array $eventTypeIds, $now): void
    {
        $eventTypes = array_values($eventTypeIds);

        for ($index = 1; $index <= 8; $index++) {
            $usesSwiss = $index % 2 === 1;
            $eventId = $this->createEvent([
                'title' => self::TAG.' Upcoming Event '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'description' => self::TAG.' Draft event shell for admin flow checks and denser mock coverage.',
                'event_type_id' => $eventTypes[($index - 1) % count($eventTypes)],
                'bracket_type' => $usesSwiss ? 'swiss_single_elim' : 'single_elim',
                'swiss_rounds' => $usesSwiss ? 4 : null,
                'top_cut_size' => $usesSwiss ? 8 : null,
                'match_format' => 7,
                'date' => now()->addDays($index + 2)->toDateString(),
                'location' => self::TAG.' Venue '.str_pad((string) $index, 2, '0', STR_PAD_LEFT),
                'status' => 'upcoming',
                'bracket_status' => 'draft',
                'is_lock_deck' => $index % 3 === 0,
                'is_active' => false,
                'created_by' => $adminId,
                'created_at' => $now,
                'updated_at' => $now,
            ]);

            if ($index > 4) {
                continue;
            }

            $start = 57 + (($index - 1) * 8);
            $participants = range($start, $start + 7);
            $decks = $index % 3 === 0 ? $participants : [];

            $this->attachParticipants($eventId, $participants, $decks, $now);
        }
    }

    private function seedArchiveSingleEliminationEvents(int $adminId, array $eventTypeIds, array $awardIds, $now): void
    {
        $archiveConfigs = [
            ['title' => self::TAG.' Archive Knockout 01', 'event_type_id' => $eventTypeIds['Casual'] ?? reset($eventTypeIds), 'participants' => [17, 57, 58, 59], 'placements' => [1 => 17, 2 => 57, 3 => 59, 4 => 58], 'date' => now()->subDays(4)->toDateString()],
            ['title' => self::TAG.' Archive Knockout 02', 'event_type_id' => $eventTypeIds['Others'] ?? reset($eventTypeIds), 'participants' => [17, 60, 61, 62], 'placements' => [1 => 17, 2 => 60, 3 => 62, 4 => 61], 'date' => now()->subDays(5)->toDateString()],
            ['title' => self::TAG.' Archive Knockout 03', 'event_type_id' => $eventTypeIds['GT'] ?? reset($eventTypeIds), 'participants' => [18, 63, 64, 65], 'placements' => [1 => 18, 2 => 63, 3 => 65, 4 => 64], 'date' => now()->subDays(7)->toDateString()],
            ['title' => self::TAG.' Archive Knockout 04', 'event_type_id' => $eventTypeIds['Casual'] ?? reset($eventTypeIds), 'participants' => [17, 66, 67, 68], 'placements' => [1 => 17, 2 => 66, 3 => 68, 4 => 67], 'date' => now()->subDays(8)->toDateString()],
            ['title' => self::TAG.' Archive Knockout 05', 'event_type_id' => $eventTypeIds['Others'] ?? reset($eventTypeIds), 'participants' => [20, 69, 70, 71], 'placements' => [1 => 20, 2 => 69, 3 => 71, 4 => 70], 'date' => now()->subDays(9)->toDateString()],
            ['title' => self::TAG.' Archive Knockout 06', 'event_type_id' => $eventTypeIds['GT'] ?? reset($eventTypeIds), 'participants' => [17, 72, 73, 74], 'placements' => [1 => 17, 2 => 72, 3 => 74, 4 => 73], 'date' => now()->subDays(10)->toDateString()],
        ];

        foreach ($archiveConfigs as $config) {
            $champion = $config['placements'][1];
            $runnerUp = $config['placements'][2];
            $semiLoserOne = $config['placements'][3];
            $semiLoserTwo = $config['placements'][4];

            $this->seedSingleElimEvent($adminId, $awardIds, $now, [
                'title' => $config['title'],
                'description' => self::TAG.' Archived micro bracket for richer history and rankings.',
                'event_type_id' => $config['event_type_id'],
                'date' => $config['date'],
                'location' => self::TAG.' Archive Wing',
                'status' => 'finished',
                'bracket_status' => 'completed',
                'participants' => $config['participants'],
                'decks' => $config['participants'],
                'rounds' => [
                    [[$champion, $semiLoserOne, 1, 'steady'], [$runnerUp, $semiLoserTwo, 1, 'close']],
                    [[$champion, $runnerUp, 1, 'wide']],
                ],
                'placements' => $config['placements'],
                'awards' => ['Bird King' => $champion],
            ]);
        }
    }

    private function createEvent(array $attributes): int
    {
        return (int) DB::table('events')->insertGetId(array_merge([
            'challonge_url' => null,
            'challonge_link' => null,
        ], $attributes));
    }

    private function attachParticipants(int $eventId, array $playerNumbers, array $deckNumbers, $now): void
    {
        foreach ($playerNumbers as $playerNumber) {
            DB::table('event_participants')->insert(array_merge([
                'event_id' => $eventId,
                'player_id' => $this->playerIds[$playerNumber],
            ], in_array($playerNumber, $deckNumbers, true) ? $this->deckPayload($playerNumber, $now) : [
                'deck_name' => null,
                'deck_bey1' => null,
                'deck_bey2' => null,
                'deck_bey3' => null,
                'deck_registered_at' => null,
            ]));
        }
    }

    private function createRound(int $eventId, string $stage, int $roundNumber, string $label, string $status, $now): int
    {
        return (int) DB::table('event_rounds')->insertGetId([
            'event_id' => $eventId,
            'stage' => $stage,
            'round_number' => $roundNumber,
            'label' => $label,
            'status' => $status,
            'created_at' => $now,
            'updated_at' => $now,
        ]);
    }

    private function createConfiguredMatches(int $eventId, int $roundId, string $stage, int $roundNumber, array $matches, $now): void
    {
        foreach ($matches as $matchIndex => $match) {
            [$left, $right, $winnerSlot, $patternName] = array_pad($match, 4, null);
            $battles = $winnerSlot ? $this->battlePattern($patternName ?? 'steady', $winnerSlot) : [];
            $battleColumns = $this->battleColumns($battles);

            DB::table('matches')->insert(array_merge([
                'event_id' => $eventId,
                'event_round_id' => $roundId,
                'stage' => $stage,
                'player1_id' => $this->playerIds[$left],
                'player2_id' => $this->playerIds[$right],
                'player1_score' => $battleColumns['player1_score'],
                'player2_score' => $battleColumns['player2_score'],
                'winner_id' => $winnerSlot ? $this->playerIds[$winnerSlot === 1 ? $left : $right] : null,
                'round_number' => $roundNumber,
                'match_number' => $matchIndex + 1,
                'status' => $winnerSlot ? 'completed' : 'pending',
                'is_bye' => false,
                'source_match1_id' => null,
                'source_match2_id' => null,
                'player1_bey1' => $stage === 'single_elim' ? $this->deckParts($left)['deck_bey1'] : null,
                'player1_bey2' => $stage === 'single_elim' ? $this->deckParts($left)['deck_bey2'] : null,
                'player1_bey3' => $stage === 'single_elim' ? $this->deckParts($left)['deck_bey3'] : null,
                'player2_bey1' => $stage === 'single_elim' ? $this->deckParts($right)['deck_bey1'] : null,
                'player2_bey2' => $stage === 'single_elim' ? $this->deckParts($right)['deck_bey2'] : null,
                'player2_bey3' => $stage === 'single_elim' ? $this->deckParts($right)['deck_bey3'] : null,
                'created_at' => $now,
            ], $battleColumns['results'], $battleColumns['types']));
        }
    }

    private function createPlacements(int $eventId, array $placements): void
    {
        foreach ($placements as $placement => $playerNumber) {
            DB::table('event_results')->insert([
                'event_id' => $eventId,
                'player_id' => $this->playerIds[$playerNumber],
                'placement' => $placement,
            ]);
        }
    }

    private function createAwards(int $eventId, array $awardIds, array $awards): void
    {
        foreach ($awards as $awardName => $playerNumber) {
            DB::table('event_awards')->insert([
                'event_id' => $eventId,
                'player_id' => $this->playerIds[$playerNumber],
                'award_id' => $awardIds[$awardName],
            ]);
        }
    }

    private function roundStatus(array $matches): string
    {
        foreach ($matches as $match) {
            if (! isset($match[2])) {
                return 'pending';
            }
        }

        return 'completed';
    }

    private function deckPayload(int $playerNumber, $now): array
    {
        return array_merge($this->deckParts($playerNumber), [
            'deck_registered_at' => $now,
        ]);
    }

    private function deckParts(int $playerNumber): array
    {
        $suffix = str_pad((string) $playerNumber, 3, '0', STR_PAD_LEFT);

        return [
            'deck_name' => self::TAG.' Deck '.$suffix,
            'deck_bey1' => self::TAG.' Bey '.$suffix.'-A',
            'deck_bey2' => self::TAG.' Bey '.$suffix.'-B',
            'deck_bey3' => self::TAG.' Bey '.$suffix.'-C',
        ];
    }

    private function battlePattern(string $patternName, int $winnerSlot): array
    {
        $loserSlot = $winnerSlot === 1 ? 2 : 1;

        return array_map(function (array $battle) use ($winnerSlot, $loserSlot): array {
            return [
                'winner' => $battle[0] === 'W' ? $winnerSlot : $loserSlot,
                'type' => $battle[1],
            ];
        }, self::PATTERNS[$patternName]);
    }

    private function battleColumns(array $battles): array
    {
        $results = [];
        $types = [];
        $player1Score = 0;
        $player2Score = 0;

        foreach (range(1, 7) as $slot) {
            $battle = $battles[$slot - 1] ?? null;
            $results["result_{$slot}"] = $battle['winner'] ?? null;
            $types["result_type_{$slot}"] = $battle['type'] ?? null;

            if (! $battle) {
                continue;
            }

            $points = match ($battle['type']) {
                'burst', 'over' => 2,
                'extreme' => 3,
                default => 1,
            };

            if ((int) $battle['winner'] === 1) {
                $player1Score += $points;
            } else {
                $player2Score += $points;
            }
        }

        return [
            'player1_score' => $player1Score,
            'player2_score' => $player2Score,
            'results' => $results,
            'types' => $types,
        ];
    }

    private function purgeMockData(): void
    {
        $mockEventIds = DB::table('events')->where('title', 'like', self::TAG.'%')->pluck('id');

        if ($mockEventIds->isNotEmpty()) {
            DB::table('matches')->whereIn('event_id', $mockEventIds)->delete();
            DB::table('event_rounds')->whereIn('event_id', $mockEventIds)->delete();
            DB::table('event_awards')->whereIn('event_id', $mockEventIds)->delete();
            DB::table('event_results')->whereIn('event_id', $mockEventIds)->delete();
            DB::table('event_participants')->whereIn('event_id', $mockEventIds)->delete();
            DB::table('events')->whereIn('id', $mockEventIds)->delete();
        }

        $mockAwardIds = DB::table('awards')->where('name', 'like', self::TAG.'%')->pluck('id');
        if ($mockAwardIds->isNotEmpty()) {
            DB::table('event_awards')->whereIn('award_id', $mockAwardIds)->delete();
            DB::table('awards')->whereIn('id', $mockAwardIds)->delete();
        }

        DB::table('event_types')->where('name', 'like', self::TAG.'%')->delete();

        $mockUserIds = DB::table('users')->where('nickname', 'like', self::TAG.'%')->pluck('id');
        if ($mockUserIds->isNotEmpty()) {
            if (Schema::hasTable('sessions')) {
                DB::table('sessions')->whereIn('user_id', $mockUserIds)->delete();
            }

            DB::table('players')->whereIn('user_id', $mockUserIds)->delete();
            DB::table('users')->whereIn('id', $mockUserIds)->delete();
        }
    }
}

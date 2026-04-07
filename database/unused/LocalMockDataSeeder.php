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
        'marathon' => [
            ['W', 'spin'],
            ['L', 'spin'],
            ['W', 'burst'],
            ['L', 'over'],
            ['W', 'spin'],
            ['L', 'spin'],
            ['W', 'burst'],
            ['L', 'spin'],
            ['W', 'spin'],
        ],
    ];

    private const SWISS_TEMPLATES = [
        'alpha' => [
            'swiss' => [
                [[1, 8, 1, 'wide'], [2, 7, 1, 'steady'], [3, 6, 1, 'close'], [4, 5, 1, 'grind']],
                [[1, 4, 1, 'sweep'], [2, 3, 1, 'steady'], [5, 8, 1, 'steady'], [6, 7, 1, 'close']],
                [[1, 2, 1, 'close'], [3, 4, 1, 'steady'], [5, 6, 1, 'steady'], [7, 8, 1, 'wide']],
            ],
            'elim' => [
                [[1, 5, 1, 'steady'], [2, 3, 1, 'close']],
                [[1, 2, 1, 'marathon']],
            ],
            'placements' => [1 => 1, 2 => 2, 3 => 3, 4 => 5],
            'awards' => ['Swiss King' => 1, 'Bird King' => 8, 'Swiss Champ' => 1],
            'swiss_king' => 1,
            'bird_king' => 8,
        ],
        'beta' => [
            'swiss' => [
                [[1, 8, 1, 'sweep'], [2, 7, 1, 'steady'], [3, 6, 1, 'close'], [4, 5, 2, 'grind']],
                [[1, 5, 1, 'steady'], [2, 3, 1, 'close'], [4, 8, 1, 'steady'], [6, 7, 1, 'wide']],
                [[1, 2, 1, 'close'], [3, 5, 2, 'steady'], [4, 6, 1, 'grind'], [7, 8, 1, 'wide']],
            ],
            'elim' => [
                [[1, 5, 2, 'steady'], [2, 4, 1, 'close']],
                [[5, 2, 1, 'marathon']],
            ],
            'placements' => [1 => 5, 2 => 2, 3 => 1, 4 => 4],
            'awards' => ['Swiss King' => 1, 'Bird King' => 8],
            'swiss_king' => 1,
            'bird_king' => 8,
        ],
        'gamma' => [
            'swiss' => [
                [[1, 8, 1, 'wide'], [2, 7, 1, 'steady'], [3, 6, 1, 'close'], [4, 5, 2, 'grind']],
                [[1, 4, 1, 'sweep'], [2, 3, 1, 'steady'], [5, 8, 1, 'close'], [6, 7, 1, 'wide']],
                [[1, 2, 1, 'close'], [3, 4, 1, 'steady'], [5, 6, 1, 'grind'], [7, 8, 1, 'wide']],
            ],
            'elim' => [
                [[1, 4, 1, 'steady'], [2, 3, 1, 'close']],
                [[1, 2, 1, 'marathon']],
            ],
            'placements' => [1 => 1, 2 => 2, 3 => 3, 4 => 4],
            'awards' => ['Swiss King' => 1, 'Bird King' => 8, 'Swiss Champ' => 1],
            'swiss_king' => 1,
            'bird_king' => 8,
        ],
        'delta' => [
            'swiss' => [
                [[1, 8, 2, 'wide'], [2, 7, 1, 'steady'], [3, 6, 2, 'close'], [4, 5, 1, 'grind']],
                [[2, 5, 1, 'sweep'], [4, 8, 2, 'steady'], [1, 6, 1, 'close'], [3, 7, 2, 'wide']],
                [[2, 8, 2, 'close'], [5, 6, 1, 'steady'], [1, 3, 2, 'grind'], [4, 7, 1, 'wide']],
            ],
            'elim' => [
                [[8, 2, 1, 'steady'], [6, 5, 1, 'close']],
                [[8, 6, 1, 'marathon']],
            ],
            'placements' => [1 => 8, 2 => 6, 3 => 2, 4 => 5],
            'awards' => ['Swiss King' => 2, 'Bird King' => 8, 'Swiss Champ' => 8],
            'swiss_king' => 2,
            'bird_king' => 8,
        ],
    ];

    private array $playerIds = [];

    private array $stadiumSideIds = [];

    public function run(): void
    {
        if (! app()->environment('local')) {
            return;
        }

        DB::transaction(function (): void {
            $now = now();

            $this->call([
                EventTypeSeeder::class,
                AwardSeeder::class,
                StadiumSideSeeder::class,
            ]);

            $this->purgeMockData();

            $eventTypeIds = DB::table('event_types')->pluck('id', 'name')->all();
            $awardIds = DB::table('awards')->pluck('id', 'name')->all();
            $this->stadiumSideIds = DB::table('stadium_sides')->pluck('id', 'code')->all();

            $adminId = $this->createAdmin($now);

            $this->playerIds = $this->createClaimedPlayers(120, $now);
            $this->createUnclaimedUsers(24, $now);

            $this->seedFinishedSwissEvents($adminId, $eventTypeIds, $awardIds, $now);
            $this->seedActiveSwissEvents($adminId, $eventTypeIds, $awardIds, $now);
            $this->seedDraftSwissEvents($adminId, $eventTypeIds, $now);
        });
    }

    private function seedFinishedSwissEvents(int $adminId, array $eventTypeIds, array $awardIds, $now): void
    {
        $definitions = [
            ['number' => 1, 'template' => 'alpha', 'event_type' => 'GT', 'players' => [17, 1, 2, 3, 4, 5, 6, 7], 'days_ago' => 14, 'is_lock_deck' => true],
            ['number' => 2, 'template' => 'beta', 'event_type' => 'Casual', 'players' => [8, 2, 9, 10, 1, 11, 12, 13], 'days_ago' => 12],
            ['number' => 3, 'template' => 'gamma', 'event_type' => 'Others', 'players' => [17, 18, 14, 15, 16, 19, 20, 21], 'days_ago' => 10],
            ['number' => 4, 'template' => 'alpha', 'event_type' => 'GT', 'players' => [1, 17, 22, 23, 24, 25, 26, 27], 'days_ago' => 8],
            ['number' => 5, 'template' => 'beta', 'event_type' => 'Casual', 'players' => [28, 3, 29, 30, 17, 31, 32, 33], 'days_ago' => 6, 'is_lock_deck' => true],
            ['number' => 6, 'template' => 'delta', 'event_type' => 'GT', 'players' => [2, 34, 35, 36, 37, 38, 39, 40], 'days_ago' => 4],
        ];

        foreach ($definitions as $definition) {
            $this->seedTemplateSwissEvent($adminId, $awardIds, $now, [
                'number' => $definition['number'],
                'template' => $definition['template'],
                'event_type_id' => $this->eventTypeId($eventTypeIds, $definition['event_type']),
                'participants' => $definition['players'],
                'date' => $now->copy()->subDays($definition['days_ago'])->toDateString(),
                'status' => 'finished',
                'bracket_status' => 'completed',
                'is_lock_deck' => $definition['is_lock_deck'] ?? false,
            ]);
        }
    }

    private function seedActiveSwissEvents(int $adminId, array $eventTypeIds, array $awardIds, $now): void
    {
        $this->seedSwissEvent($adminId, $awardIds, $now, $this->mockEventConfig(7, [
            'event_type_id' => $this->eventTypeId($eventTypeIds, 'GT'),
            'date' => $now->copy()->toDateString(),
            'status' => 'upcoming',
            'bracket_status' => 'in_progress',
            'is_active' => true,
            'participants' => range(41, 56),
            'top_cut_size' => 8,
            'swiss_rounds' => 4,
            'swiss' => [
                [[41, 42, 1, 'steady'], [43, 44, 2, 'close'], [45, 46, 1, 'wide'], [47, 48, 2, 'grind'], [49, 50, 2, 'steady'], [51, 52, 1, 'sweep'], [53, 54, 2, 'wide'], [55, 56, 1, 'steady']],
                [[41, 44, 1, 'sweep'], [45, 48, 1, 'close'], [50, 51, 1, 'steady'], [54, 55, 2, 'grind'], [42, 43, 1, 'steady'], [46, 47, 2, 'close'], [49, 52, 2, 'wide'], [53, 56, 1, 'steady']],
                [[41, 45], [50, 56], [44, 51], [42, 48], [43, 55], [46, 54], [47, 52], [49, 53]],
            ],
        ]));

        $this->seedSwissEvent($adminId, $awardIds, $now, $this->mockEventConfig(8, [
            'event_type_id' => $this->eventTypeId($eventTypeIds, 'Casual'),
            'date' => $now->copy()->addDay()->toDateString(),
            'status' => 'upcoming',
            'bracket_status' => 'in_progress',
            'is_active' => true,
            'participants' => [17, 1, 57, 58, 59, 60, 61, 62],
            'top_cut_size' => 4,
            'swiss_rounds' => 3,
            'swiss' => [
                [[17, 62, 1, 'wide'], [1, 61, 1, 'steady'], [57, 60, 1, 'close'], [58, 59, 2, 'grind']],
                [[17, 58], [1, 57], [59, 62], [60, 61]],
            ],
        ]));

        $this->seedTemplateSwissEvent($adminId, [], $now, [
            'number' => 9,
            'template' => 'gamma',
            'event_type_id' => $this->eventTypeId($eventTypeIds, 'Others'),
            'participants' => [63, 64, 65, 66, 67, 68, 69, 70],
            'date' => $now->copy()->addDays(2)->toDateString(),
            'status' => 'upcoming',
            'bracket_status' => 'in_progress',
            'include_elim' => false,
            'include_placements' => false,
            'include_awards' => false,
            'is_lock_deck' => true,
        ]);
    }

    private function seedDraftSwissEvents(int $adminId, array $eventTypeIds, $now): void
    {
        $definitions = [
            ['number' => 10, 'event_type' => 'GT', 'participants' => range(71, 78), 'days_ahead' => 3, 'top_cut_size' => 4],
            ['number' => 11, 'event_type' => 'Casual', 'participants' => range(79, 86), 'days_ahead' => 4, 'top_cut_size' => 4, 'is_lock_deck' => true],
            ['number' => 12, 'event_type' => 'Others', 'participants' => range(87, 94), 'days_ahead' => 5, 'top_cut_size' => 8],
            ['number' => 13, 'event_type' => 'GT', 'participants' => range(95, 102), 'days_ahead' => 6, 'top_cut_size' => 4],
        ];

        foreach ($definitions as $definition) {
            $this->seedSwissEvent($adminId, [], $now, $this->mockEventConfig($definition['number'], [
                'event_type_id' => $this->eventTypeId($eventTypeIds, $definition['event_type']),
                'date' => $now->copy()->addDays($definition['days_ahead'])->toDateString(),
                'status' => 'upcoming',
                'bracket_status' => 'draft',
                'participants' => $definition['participants'],
                'top_cut_size' => $definition['top_cut_size'],
                'swiss_rounds' => 4,
                'is_lock_deck' => $definition['is_lock_deck'] ?? false,
                'swiss' => [],
                'elim' => [],
                'placements' => [],
                'awards' => [],
            ]));
        }
    }

    private function seedTemplateSwissEvent(int $adminId, array $awardIds, $now, array $definition): void
    {
        $template = self::SWISS_TEMPLATES[$definition['template']];
        $slotMap = $this->slotPlayerMap($definition['participants']);

        $config = $this->mockEventConfig($definition['number'], [
            'event_type_id' => $definition['event_type_id'],
            'date' => $definition['date'],
            'status' => $definition['status'],
            'bracket_status' => $definition['bracket_status'],
            'participants' => array_values($slotMap),
            'top_cut_size' => $definition['top_cut_size'] ?? 4,
            'swiss_rounds' => $definition['swiss_rounds'] ?? count($template['swiss']),
            'swiss' => $this->mapTemplateRounds($template['swiss'], $slotMap),
            'elim' => ($definition['include_elim'] ?? true)
                ? $this->mapTemplateRounds($template['elim'], $slotMap)
                : [],
            'placements' => ($definition['include_placements'] ?? true)
                ? $this->mapTemplatePlacements($template['placements'], $slotMap)
                : [],
            'awards' => ($definition['include_awards'] ?? true)
                ? $this->mapTemplateAwards($template['awards'], $slotMap)
                : [],
            'swiss_king' => $slotMap[$template['swiss_king']] ?? null,
            'bird_king' => $slotMap[$template['bird_king']] ?? null,
            'is_lock_deck' => $definition['is_lock_deck'] ?? false,
            'is_active' => $definition['is_active'] ?? false,
        ]);

        $this->seedSwissEvent($adminId, $awardIds, $now, $config);
    }

    private function mockEventConfig(int $eventNumber, array $overrides): array
    {
        $base = [
            'title' => $this->eventTitle($eventNumber),
            'description' => $this->eventDescription($eventNumber),
            'challonge_url' => $this->eventLink($eventNumber),
            'challonge_link' => $this->eventLink($eventNumber),
            'location' => $this->eventLocation($eventNumber),
            'bracket_type' => 'swiss_single_elim',
            'match_format' => 7,
            'status' => 'upcoming',
            'bracket_status' => 'draft',
            'is_active' => false,
            'is_lock_deck' => false,
            'swiss_rounds' => 3,
            'top_cut_size' => 4,
            'participants' => [],
            'swiss' => [],
            'elim' => [],
            'placements' => [],
            'awards' => [],
            'swiss_king' => null,
            'bird_king' => null,
        ];

        return array_merge($base, $overrides);
    }

    private function eventTypeId(array $eventTypeIds, string $name): int
    {
        return (int) ($eventTypeIds[$name] ?? reset($eventTypeIds));
    }

    private function slotPlayerMap(array $playerNumbers): array
    {
        $slotMap = [];

        foreach (array_values($playerNumbers) as $index => $playerNumber) {
            $slotMap[$index + 1] = $playerNumber;
        }

        return $slotMap;
    }

    private function mapTemplateRounds(array $rounds, array $slotMap): array
    {
        return array_map(function (array $matches) use ($slotMap): array {
            return array_map(function (array $match) use ($slotMap): array {
                [$leftSlot, $rightSlot, $winnerSlot, $patternName] = array_pad($match, 4, null);

                $mapped = [
                    $slotMap[$leftSlot],
                    $slotMap[$rightSlot],
                ];

                if ($winnerSlot !== null) {
                    $mapped[] = $winnerSlot;
                }

                if ($patternName !== null) {
                    $mapped[] = $patternName;
                }

                return $mapped;
            }, $matches);
        }, $rounds);
    }

    private function mapTemplatePlacements(array $placements, array $slotMap): array
    {
        $mapped = [];

        foreach ($placements as $placement => $slot) {
            $mapped[$placement] = $slotMap[$slot];
        }

        return $mapped;
    }

    private function mapTemplateAwards(array $awards, array $slotMap): array
    {
        $mapped = [];

        foreach ($awards as $awardName => $slot) {
            $mapped[$awardName] = $slotMap[$slot];
        }

        return $mapped;
    }

    private function eventTitle(int $eventNumber): string
    {
        return self::TAG.' Event '.str_pad((string) $eventNumber, 2, '0', STR_PAD_LEFT);
    }

    private function eventDescription(int $eventNumber): string
    {
        return self::TAG.' Placeholder description for Event '.str_pad((string) $eventNumber, 2, '0', STR_PAD_LEFT).'.';
    }

    private function eventLocation(int $eventNumber): string
    {
        return self::TAG.' Venue '.str_pad((string) $eventNumber, 2, '0', STR_PAD_LEFT);
    }

    private function eventLink(int $eventNumber): string
    {
        return 'https://challonge.com/mock_event_'.str_pad((string) $eventNumber, 2, '0', STR_PAD_LEFT);
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
            'challonge_url' => $config['challonge_url'],
            'challonge_link' => $config['challonge_link'],
            'event_type_id' => $config['event_type_id'],
            'bracket_type' => $config['bracket_type'],
            'swiss_rounds' => $config['swiss_rounds'] ?? count($config['swiss']),
            'top_cut_size' => $config['top_cut_size'],
            'match_format' => $config['match_format'],
            'date' => $config['date'],
            'location' => $config['location'],
            'status' => $config['status'],
            'bracket_status' => $config['bracket_status'],
            'is_lock_deck' => $config['is_lock_deck'],
            'is_active' => $config['is_active'],
            'swiss_king_player_id' => isset($config['swiss_king']) ? $this->playerIds[$config['swiss_king']] : null,
            'bird_king_player_id' => isset($config['bird_king']) ? $this->playerIds[$config['bird_king']] : null,
            'created_by' => $adminId,
            'created_at' => $now,
            'updated_at' => $now,
        ]);

        $this->attachParticipants($eventId, $config['participants'], $now);

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

        $previousEliminationMatchIds = [];

        foreach ($config['elim'] as $roundIndex => $matches) {
            $roundNumber = $roundIndex + 1;
            $roundLabel = match ($roundNumber) {
                1 => 'Top Cut Round 1',
                2 => count($config['elim']) === 2 ? 'Top Cut Final' : 'Top Cut Round 2',
                default => 'Top Cut Round '.$roundNumber,
            };

            $roundId = $this->createRound(
                $eventId,
                'single_elim',
                $roundNumber,
                $roundLabel,
                $this->roundStatus($matches),
                $now
            );

            $previousEliminationMatchIds = $this->createConfiguredMatches(
                $eventId,
                $roundId,
                'single_elim',
                $roundNumber,
                $matches,
                $now,
                $previousEliminationMatchIds
            );
        }

        if (! empty($config['placements'])) {
            $this->createPlacements($eventId, $config['placements']);
        }

        if (! empty($config['awards']) && $awardIds !== []) {
            $this->createAwards($eventId, $awardIds, $config['awards']);
        }
    }

    private function createEvent(array $attributes): int
    {
        return (int) DB::table('events')->insertGetId($attributes);
    }

    private function attachParticipants(int $eventId, array $playerNumbers, $now): void
    {
        foreach ($playerNumbers as $playerNumber) {
            DB::table('event_participants')->insert(array_merge([
                'event_id' => $eventId,
                'player_id' => $this->playerIds[$playerNumber],
            ], $this->deckPayload($playerNumber, $now)));
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

    private function createConfiguredMatches(
        int $eventId,
        int $roundId,
        string $stage,
        int $roundNumber,
        array $matches,
        $now,
        array $sourceMatchIds = []
    ): array {
        $createdMatchIds = [];

        foreach ($matches as $matchIndex => $match) {
            [$left, $right, $winnerSlot, $patternName] = array_pad($match, 4, null);
            $battles = $winnerSlot ? $this->battlePattern($patternName ?? 'steady', $winnerSlot) : [];
            $battleColumns = $this->battleColumns($battles);
            [$player1SideId, $player2SideId] = $this->stadiumSideIdsForMatch($roundNumber, $matchIndex + 1, $right !== null);

            $sourceMatch1Id = null;
            $sourceMatch2Id = null;

            if ($stage === 'single_elim' && $roundNumber > 1) {
                $sourceOffset = $matchIndex * 2;
                $sourceMatch1Id = $sourceMatchIds[$sourceOffset] ?? null;
                $sourceMatch2Id = $sourceMatchIds[$sourceOffset + 1] ?? null;
            }

            $createdMatchIds[] = (int) DB::table('matches')->insertGetId(array_merge([
                'event_id' => $eventId,
                'event_round_id' => $roundId,
                'stage' => $stage,
                'player1_id' => $this->playerIds[$left],
                'player1_stadium_side_id' => $player1SideId,
                'player2_id' => $right !== null ? $this->playerIds[$right] : null,
                'player2_stadium_side_id' => $player2SideId,
                'player1_score' => $battleColumns['player1_score'],
                'player2_score' => $battleColumns['player2_score'],
                'winner_id' => $winnerSlot ? $this->playerIds[$winnerSlot === 1 ? $left : $right] : null,
                'round_number' => $roundNumber,
                'match_number' => $matchIndex + 1,
                'status' => $winnerSlot ? 'completed' : 'pending',
                'is_bye' => false,
                'source_match1_id' => $sourceMatch1Id,
                'source_match2_id' => $sourceMatch2Id,
                'player1_bey1' => $this->deckParts($left)['deck_bey1'],
                'player1_bey2' => $this->deckParts($left)['deck_bey2'],
                'player1_bey3' => $this->deckParts($left)['deck_bey3'],
                'player2_bey1' => $right !== null ? $this->deckParts($right)['deck_bey1'] : null,
                'player2_bey2' => $right !== null ? $this->deckParts($right)['deck_bey2'] : null,
                'player2_bey3' => $right !== null ? $this->deckParts($right)['deck_bey3'] : null,
                'created_at' => $now,
            ], $battleColumns['results'], $battleColumns['types']));
        }

        return $createdMatchIds;
    }

    private function stadiumSideIdsForMatch(int $roundNumber, int $matchNumber, bool $hasOpponent): array
    {
        $patterns = [
            ['X', 'B'],
            ['B', 'X'],
        ];

        $pattern = $patterns[($roundNumber + $matchNumber - 2) % count($patterns)];

        return [
            $this->stadiumSideIds[$pattern[0]] ?? null,
            $hasOpponent ? ($this->stadiumSideIds[$pattern[1]] ?? null) : null,
        ];
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
        $pattern = self::PATTERNS[$patternName] ?? self::PATTERNS['steady'];
        $loserSlot = $winnerSlot === 1 ? 2 : 1;

        return array_map(function (array $battle) use ($winnerSlot, $loserSlot): array {
            return [
                'winner' => $battle[0] === 'W' ? $winnerSlot : $loserSlot,
                'type' => $battle[1],
            ];
        }, $pattern);
    }

    private function battleColumns(array $battles): array
    {
        $results = [];
        $types = [];
        $player1Score = 0;
        $player2Score = 0;

        foreach (range(1, 13) as $slot) {
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

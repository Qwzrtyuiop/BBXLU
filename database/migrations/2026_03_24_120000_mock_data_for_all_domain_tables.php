<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

return new class extends Migration
{
    /**
     * MARKED MOCK DATA MIGRATION
     * - All inserted rows are prefixed with "[MOCK]"
     * - Safe to rollback / delete when no longer needed
     */
    private const TAG = '[MOCK]';

    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! $this->shouldSeedMockData()) {
            return;
        }

        DB::transaction(function (): void {
            $now = now();

            $this->purgeMockData();

            DB::table('users')->updateOrInsert(
                ['nickname' => self::TAG.' admin'],
                [
                    'nickname' => self::TAG.' admin',
                    'name' => self::TAG.' Admin',
                    'email' => 'mock.admin+bbxlu@example.com',
                    'password' => Hash::make('password'),
                    'role' => 'admin',
                    'is_claimed' => true,
                    'created_at' => $now,
                    'updated_at' => $now,
                ]
            );

            for ($i = 1; $i <= 50; $i++) {
                $nickname = self::TAG.' player '.str_pad((string) $i, 3, '0', STR_PAD_LEFT);
                $email = 'mock.player'.str_pad((string) $i, 3, '0', STR_PAD_LEFT).'+bbxlu@example.com';

                DB::table('users')->updateOrInsert(
                    ['nickname' => $nickname],
                    [
                        'nickname' => $nickname,
                        'name' => self::TAG.' Player '.str_pad((string) $i, 3, '0', STR_PAD_LEFT),
                        'email' => $email,
                        'password' => Hash::make('password'),
                        'role' => 'user',
                        'is_claimed' => true,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $adminId = (int) DB::table('users')
                ->where('nickname', self::TAG.' admin')
                ->value('id');

            $mockPlayerUserIds = DB::table('users')
                ->where('nickname', 'like', self::TAG.' player %')
                ->orderBy('nickname')
                ->pluck('id');

            foreach ($mockPlayerUserIds as $userId) {
                DB::table('players')->updateOrInsert(
                    ['user_id' => $userId],
                    ['created_at' => $now]
                );
            }

            $playerIds = DB::table('players')
                ->join('users', 'users.id', '=', 'players.user_id')
                ->where('users.nickname', 'like', self::TAG.' player %')
                ->orderBy('users.nickname')
                ->pluck('players.id')
                ->values();

            $eventTypeIds = DB::table('event_types')->orderBy('id')->pluck('id')->values();
            if ($eventTypeIds->isEmpty()) {
                DB::table('event_types')->insert(['name' => self::TAG.' Event Type']);
                $eventTypeIds = DB::table('event_types')->orderBy('id')->pluck('id')->values();
            }

            DB::table('awards')->updateOrInsert(
                ['name' => self::TAG.' Award'],
                ['name' => self::TAG.' Award']
            );

            $awardIds = DB::table('awards')->orderBy('id')->pluck('id')->values();

            for ($eventIndex = 1; $eventIndex <= 20; $eventIndex++) {
                $isUpcoming = $eventIndex <= 10;
                $title = self::TAG.' Event '.str_pad((string) $eventIndex, 2, '0', STR_PAD_LEFT);
                $eventDate = $isUpcoming
                    ? now()->addDays($eventIndex)->toDateString()
                    : now()->subDays($eventIndex - 10)->toDateString();

                DB::table('events')->updateOrInsert(
                    ['title' => $title],
                    [
                        'title' => $title,
                        'description' => self::TAG.' Sample event #'.$eventIndex.' for UI and CRUD checks.',
                        'event_type_id' => $eventTypeIds[($eventIndex - 1) % $eventTypeIds->count()],
                        'date' => $eventDate,
                        'location' => self::TAG.' Venue '.$eventIndex,
                        'status' => $isUpcoming ? 'upcoming' : 'finished',
                        'created_by' => $adminId,
                        'created_at' => $now,
                        'updated_at' => $now,
                    ]
                );
            }

            $mockEvents = DB::table('events')
                ->where('title', 'like', self::TAG.' Event %')
                ->orderBy('title')
                ->get(['id', 'title', 'status']);

            foreach ($mockEvents as $index => $event) {
                $base = ($index * 3) % $playerIds->count();

                $participantCount = $event->status === 'upcoming' ? 8 : 10;
                $selectedPlayers = collect();
                for ($i = 0; $i < $participantCount; $i++) {
                    $selectedPlayers->push($playerIds[($base + $i) % $playerIds->count()]);
                }

                foreach ($selectedPlayers as $playerId) {
                    DB::table('event_participants')->updateOrInsert(
                        ['event_id' => $event->id, 'player_id' => $playerId],
                        ['event_id' => $event->id, 'player_id' => $playerId]
                    );
                }

                if ($event->status !== 'finished') {
                    continue;
                }

                $resultPlayers = $selectedPlayers->take(4)->values();
                for ($placement = 1; $placement <= 4; $placement++) {
                    DB::table('event_results')->updateOrInsert(
                        ['event_id' => $event->id, 'player_id' => $resultPlayers[$placement - 1]],
                        ['placement' => $placement]
                    );
                }

                $awardId = $awardIds[$index % $awardIds->count()];
                DB::table('event_awards')->updateOrInsert(
                    ['event_id' => $event->id, 'award_id' => $awardId],
                    ['player_id' => $resultPlayers[0]]
                );

                DB::table('matches')->insert([
                    [
                        'event_id' => $event->id,
                        'player1_id' => $resultPlayers[0],
                        'player2_id' => $resultPlayers[1],
                        'player1_score' => 3,
                        'player2_score' => 1,
                        'winner_id' => $resultPlayers[0],
                        'round_number' => 1,
                        'created_at' => $now,
                    ],
                    [
                        'event_id' => $event->id,
                        'player1_id' => $resultPlayers[2],
                        'player2_id' => $resultPlayers[3],
                        'player1_score' => 1,
                        'player2_score' => 3,
                        'winner_id' => $resultPlayers[3],
                        'round_number' => 1,
                        'created_at' => $now,
                    ],
                ]);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (! $this->shouldSeedMockData()) {
            return;
        }

        DB::transaction(function (): void {
            $this->purgeMockData();
        });
    }

    private function shouldSeedMockData(): bool
    {
        return app()->environment('local');
    }

    private function purgeMockData(): void
    {
        $mockEventIds = DB::table('events')
            ->where('title', 'like', self::TAG.'%')
            ->pluck('id');

        if ($mockEventIds->isNotEmpty()) {
            DB::table('matches')->whereIn('event_id', $mockEventIds)->delete();
            DB::table('event_awards')->whereIn('event_id', $mockEventIds)->delete();
            DB::table('event_results')->whereIn('event_id', $mockEventIds)->delete();
            DB::table('event_participants')->whereIn('event_id', $mockEventIds)->delete();
            DB::table('events')->whereIn('id', $mockEventIds)->delete();
        }

        $mockAwardIds = DB::table('awards')
            ->where('name', 'like', self::TAG.'%')
            ->pluck('id');

        if ($mockAwardIds->isNotEmpty()) {
            DB::table('event_awards')->whereIn('award_id', $mockAwardIds)->delete();
            DB::table('awards')->whereIn('id', $mockAwardIds)->delete();
        }

        DB::table('event_types')
            ->where('name', 'like', self::TAG.'%')
            ->delete();

        $mockUserIds = DB::table('users')
            ->where('nickname', 'like', self::TAG.'%')
            ->pluck('id');

        if ($mockUserIds->isNotEmpty()) {
            DB::table('players')->whereIn('user_id', $mockUserIds)->delete();
            DB::table('users')->whereIn('id', $mockUserIds)->delete();
        }
    }
};

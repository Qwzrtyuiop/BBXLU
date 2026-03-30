<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::transaction(function (): void {
            DB::table('event_results')
                ->whereNotIn('player_id', DB::table('players')->select('id'))
                ->delete();

            DB::table('event_awards')
                ->whereNotIn('player_id', DB::table('players')->select('id'))
                ->delete();

            DB::table('event_participants')
                ->whereNotIn('player_id', DB::table('players')->select('id'))
                ->delete();

            DB::table('matches')
                ->whereNotIn('player1_id', DB::table('players')->select('id'))
                ->delete();

            DB::table('matches')
                ->whereNotNull('player2_id')
                ->whereNotIn('player2_id', DB::table('players')->select('id'))
                ->delete();

            DB::table('matches')
                ->whereNotNull('winner_id')
                ->whereNotIn('winner_id', DB::table('players')->select('id'))
                ->delete();
        });
    }

    public function down(): void
    {
        // Cleanup migration only; deleted orphan rows cannot be restored.
    }
};

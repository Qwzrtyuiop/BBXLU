<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->index(['status', 'date', 'id'], 'events_status_date_id_idx');
            $table->index(['is_active', 'status', 'date', 'id'], 'events_active_status_date_id_idx');
            $table->index(['bracket_status', 'bracket_type'], 'events_bracket_status_type_idx');
        });

        Schema::table('event_results', function (Blueprint $table) {
            $table->index(['player_id', 'event_id', 'placement'], 'event_results_player_event_place_idx');
        });

        Schema::table('event_awards', function (Blueprint $table) {
            $table->index(['award_id', 'player_id', 'event_id'], 'event_awards_award_player_event_idx');
        });

        Schema::table('event_participants', function (Blueprint $table) {
            $table->index(['player_id', 'event_id'], 'event_participants_player_event_idx');
        });

        Schema::table('event_rounds', function (Blueprint $table) {
            $table->index(['event_id', 'stage', 'status', 'round_number'], 'event_rounds_event_stage_status_round_idx');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->unique(['event_round_id', 'match_number'], 'matches_round_match_number_unique');
            $table->index(['event_id', 'stage', 'status', 'round_number'], 'matches_event_stage_status_round_idx');
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropUnique('matches_round_match_number_unique');
            $table->dropIndex('matches_event_stage_status_round_idx');
        });

        Schema::table('event_awards', function (Blueprint $table) {
            $table->dropIndex('event_awards_award_player_event_idx');
        });

        Schema::table('event_rounds', function (Blueprint $table) {
            $table->dropIndex('event_rounds_event_stage_status_round_idx');
        });

        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropIndex('event_participants_player_event_idx');
        });

        Schema::table('event_results', function (Blueprint $table) {
            $table->dropIndex('event_results_player_event_place_idx');
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_status_date_id_idx');
            $table->dropIndex('events_active_status_date_id_idx');
            $table->dropIndex('events_bracket_status_type_idx');
        });
    }
};

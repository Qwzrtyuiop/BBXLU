<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_lock_deck')
                ->default(false)
                ->after('bracket_status');
        });

        Schema::table('event_participants', function (Blueprint $table) {
            $table->string('deck_name')->nullable()->after('player_id');
            $table->string('deck_bey1')->nullable()->after('deck_name');
            $table->string('deck_bey2')->nullable()->after('deck_bey1');
            $table->string('deck_bey3')->nullable()->after('deck_bey2');
            $table->timestamp('deck_registered_at')->nullable()->after('deck_bey3');
        });
    }

    public function down(): void
    {
        Schema::table('event_participants', function (Blueprint $table) {
            $table->dropColumn([
                'deck_name',
                'deck_bey1',
                'deck_bey2',
                'deck_bey3',
                'deck_registered_at',
            ]);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn('is_lock_deck');
        });
    }
};

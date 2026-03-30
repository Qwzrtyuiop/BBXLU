<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->foreignId('swiss_king_player_id')
                ->nullable()
                ->after('is_active')
                ->constrained('players')
                ->nullOnDelete();

            $table->foreignId('bird_king_player_id')
                ->nullable()
                ->after('swiss_king_player_id')
                ->constrained('players')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropConstrainedForeignId('bird_king_player_id');
            $table->dropConstrainedForeignId('swiss_king_player_id');
        });
    }
};

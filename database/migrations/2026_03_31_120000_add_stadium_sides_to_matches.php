<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('stadium_sides', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->timestamps();
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('player1_stadium_side_id')
                ->nullable()
                ->after('player1_id')
                ->constrained('stadium_sides')
                ->nullOnDelete();
            $table->foreignId('player2_stadium_side_id')
                ->nullable()
                ->after('player2_id')
                ->constrained('stadium_sides')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['player1_stadium_side_id']);
            $table->dropForeign(['player2_stadium_side_id']);
            $table->dropColumn([
                'player1_stadium_side_id',
                'player2_stadium_side_id',
            ]);
        });

        Schema::dropIfExists('stadium_sides');
    }
};

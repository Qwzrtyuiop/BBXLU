<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('event_rounds', function (Blueprint $table) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->enum('stage', ['swiss', 'single_elim']);
            $table->unsignedInteger('round_number');
            $table->string('label')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->timestamps();

            $table->unique(['event_id', 'stage', 'round_number']);
        });

        Schema::table('events', function (Blueprint $table) {
            $table->enum('bracket_type', ['single_elim', 'swiss_single_elim'])
                ->default('single_elim')
                ->after('event_type_id');
            $table->unsignedTinyInteger('swiss_rounds')->nullable()->after('bracket_type');
            $table->unsignedTinyInteger('top_cut_size')->nullable()->after('swiss_rounds');
            $table->unsignedTinyInteger('match_format')->default(7)->after('top_cut_size');
            $table->enum('bracket_status', ['draft', 'in_progress', 'completed'])
                ->default('draft')
                ->after('status');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['player2_id']);
            $table->dropForeign(['winner_id']);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('player2_id')->nullable()->change();
            $table->foreignId('winner_id')->nullable()->change();
            $table->integer('player1_score')->default(0)->change();
            $table->integer('player2_score')->default(0)->change();

            $table->foreignId('event_round_id')->nullable()->after('event_id');
            $table->enum('stage', ['swiss', 'single_elim'])->nullable()->after('event_round_id');
            $table->unsignedInteger('match_number')->nullable()->after('round_number');
            $table->enum('status', ['pending', 'completed'])->default('pending')->after('match_number');
            $table->boolean('is_bye')->default(false)->after('status');
            $table->foreignId('source_match1_id')->nullable()->after('is_bye');
            $table->foreignId('source_match2_id')->nullable()->after('source_match1_id');

            for ($index = 1; $index <= 7; $index++) {
                $table->unsignedTinyInteger("result_{$index}")->nullable()->after($index === 1 ? 'source_match2_id' : 'result_'.($index - 1));
            }

            $table->string('player1_bey1')->nullable()->after('result_7');
            $table->string('player1_bey2')->nullable()->after('player1_bey1');
            $table->string('player1_bey3')->nullable()->after('player1_bey2');
            $table->string('player2_bey1')->nullable()->after('player1_bey3');
            $table->string('player2_bey2')->nullable()->after('player2_bey1');
            $table->string('player2_bey3')->nullable()->after('player2_bey2');
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreign('player2_id')->references('id')->on('players')->nullOnDelete();
            $table->foreign('winner_id')->references('id')->on('players')->nullOnDelete();
            $table->foreign('event_round_id')->references('id')->on('event_rounds')->nullOnDelete();
            $table->foreign('source_match1_id')->references('id')->on('matches')->nullOnDelete();
            $table->foreign('source_match2_id')->references('id')->on('matches')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropForeign(['player2_id']);
            $table->dropForeign(['winner_id']);
            $table->dropForeign(['event_round_id']);
            $table->dropForeign(['source_match1_id']);
            $table->dropForeign(['source_match2_id']);

            $table->dropColumn([
                'event_round_id',
                'stage',
                'match_number',
                'status',
                'is_bye',
                'source_match1_id',
                'source_match2_id',
                'result_1',
                'result_2',
                'result_3',
                'result_4',
                'result_5',
                'result_6',
                'result_7',
                'player1_bey1',
                'player1_bey2',
                'player1_bey3',
                'player2_bey1',
                'player2_bey2',
                'player2_bey3',
            ]);
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreignId('player2_id')->nullable(false)->change();
            $table->foreignId('winner_id')->nullable(false)->change();
            $table->integer('player1_score')->change();
            $table->integer('player2_score')->change();
        });

        Schema::table('matches', function (Blueprint $table) {
            $table->foreign('player2_id')->references('id')->on('players')->cascadeOnDelete();
            $table->foreign('winner_id')->references('id')->on('players')->cascadeOnDelete();
        });

        Schema::table('events', function (Blueprint $table) {
            $table->dropColumn([
                'bracket_type',
                'swiss_rounds',
                'top_cut_size',
                'match_format',
                'bracket_status',
            ]);
        });

        Schema::dropIfExists('event_rounds');
    }
};

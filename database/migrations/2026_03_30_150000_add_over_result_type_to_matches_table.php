<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    private const LEGACY_FINISH_TYPES = ['spin', 'burst', 'extreme'];
    private const CURRENT_FINISH_TYPES = ['spin', 'burst', 'over', 'extreme'];

    public function up(): void
    {
        $this->syncFinishTypes(self::CURRENT_FINISH_TYPES);
    }

    public function down(): void
    {
        foreach (range(1, 7) as $index) {
            DB::table('matches')
                ->where("result_type_{$index}", 'over')
                ->update(["result_type_{$index}" => 'burst']);
        }

        $this->syncFinishTypes(self::LEGACY_FINISH_TYPES);
    }

    private function syncFinishTypes(array $finishTypes): void
    {
        $driver = DB::getDriverName();

        if ($driver === 'mysql') {
            $enumValues = implode(', ', array_map(fn (string $type) => "'{$type}'", $finishTypes));

            foreach (range(1, 7) as $index) {
                DB::statement(
                    "ALTER TABLE matches MODIFY result_type_{$index} ENUM({$enumValues}) NULL"
                );
            }

            return;
        }

        if ($driver === 'sqlite') {
            $this->rebuildSqliteMatchesTable($finishTypes);
        }
    }

    private function rebuildSqliteMatchesTable(array $finishTypes): void
    {
        $legacyTable = 'matches_legacy_result_types';

        Schema::disableForeignKeyConstraints();
        Schema::dropIfExists($legacyTable);
        Schema::rename('matches', $legacyTable);

        Schema::create('matches', function (Blueprint $table) use ($finishTypes) {
            $table->id();
            $table->foreignId('event_id')->constrained('events')->cascadeOnDelete();
            $table->foreignId('event_round_id')->nullable()->constrained('event_rounds')->nullOnDelete();
            $table->enum('stage', ['swiss', 'single_elim'])->nullable();
            $table->foreignId('player1_id')->constrained('players')->cascadeOnDelete();
            $table->foreignId('player2_id')->nullable()->constrained('players')->nullOnDelete();
            $table->integer('player1_score')->default(0);
            $table->integer('player2_score')->default(0);
            $table->foreignId('winner_id')->nullable()->constrained('players')->nullOnDelete();
            $table->unsignedInteger('round_number')->nullable();
            $table->unsignedInteger('match_number')->nullable();
            $table->enum('status', ['pending', 'completed'])->default('pending');
            $table->boolean('is_bye')->default(false);
            $table->foreignId('source_match1_id')->nullable()->constrained('matches')->nullOnDelete();
            $table->foreignId('source_match2_id')->nullable()->constrained('matches')->nullOnDelete();

            foreach (range(1, 7) as $index) {
                $table->unsignedTinyInteger("result_{$index}")->nullable();
            }

            $table->string('player1_bey1')->nullable();
            $table->string('player1_bey2')->nullable();
            $table->string('player1_bey3')->nullable();
            $table->string('player2_bey1')->nullable();
            $table->string('player2_bey2')->nullable();
            $table->string('player2_bey3')->nullable();

            foreach (range(1, 7) as $index) {
                $table->enum("result_type_{$index}", $finishTypes)->nullable();
            }

            $table->timestamp('created_at')->useCurrent();
        });

        $columns = [
            'id',
            'event_id',
            'event_round_id',
            'stage',
            'player1_id',
            'player2_id',
            'player1_score',
            'player2_score',
            'winner_id',
            'round_number',
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
            'result_type_1',
            'result_type_2',
            'result_type_3',
            'result_type_4',
            'result_type_5',
            'result_type_6',
            'result_type_7',
            'created_at',
        ];

        $columnList = implode(', ', $columns);
        DB::statement("INSERT INTO matches ({$columnList}) SELECT {$columnList} FROM {$legacyTable}");

        Schema::drop($legacyTable);
        Schema::enableForeignKeyConstraints();
    }
};

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
        Schema::table('matches', function (Blueprint $table) {
            for ($index = 1; $index <= 7; $index++) {
                $afterColumn = $index === 1 ? 'player2_bey3' : 'result_type_'.($index - 1);
                $table->enum("result_type_{$index}", ['spin', 'burst', 'extreme'])
                    ->nullable()
                    ->after($afterColumn);
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn([
                'result_type_1',
                'result_type_2',
                'result_type_3',
                'result_type_4',
                'result_type_5',
                'result_type_6',
                'result_type_7',
            ]);
        });
    }
};

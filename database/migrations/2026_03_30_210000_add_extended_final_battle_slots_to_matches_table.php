<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $finishTypes = ['spin', 'burst', 'over', 'extreme'];

            for ($index = 8; $index <= 13; $index++) {
                $afterColumn = $index === 8 ? 'result_type_7' : "result_type_".($index - 1);
                $table->unsignedTinyInteger("result_{$index}")->nullable()->after($afterColumn);
                $table->enum("result_type_{$index}", $finishTypes)->nullable()->after("result_{$index}");
            }
        });
    }

    public function down(): void
    {
        Schema::table('matches', function (Blueprint $table) {
            $table->dropColumn([
                'result_8',
                'result_type_8',
                'result_9',
                'result_type_9',
                'result_10',
                'result_type_10',
                'result_11',
                'result_type_11',
                'result_12',
                'result_type_12',
                'result_13',
                'result_type_13',
            ]);
        });
    }
};

<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->boolean('is_active')->default(false)->after('status');
            $table->index('is_active');
        });

        $activeExists = DB::table('events')->where('is_active', true)->exists();

        if (! $activeExists) {
            $firstUpcomingId = DB::table('events')
                ->where('status', 'upcoming')
                ->orderBy('date')
                ->orderBy('id')
                ->value('id');

            if ($firstUpcomingId) {
                DB::table('events')
                    ->where('id', $firstUpcomingId)
                    ->update(['is_active' => true]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('events', function (Blueprint $table) {
            $table->dropIndex('events_is_active_index');
            $table->dropColumn('is_active');
        });
    }
};

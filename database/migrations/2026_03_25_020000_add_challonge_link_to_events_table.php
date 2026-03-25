<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'challonge_link')) {
            Schema::table('events', function (Blueprint $table): void {
                $table->string('challonge_link')->nullable()->after('challonge_url');
            });
        }

        if (Schema::hasColumn('events', 'challonge_url') && Schema::hasColumn('events', 'challonge_link')) {
            DB::table('events')
                ->whereNull('challonge_link')
                ->whereNotNull('challonge_url')
                ->update([
                    'challonge_link' => DB::raw('challonge_url'),
                ]);
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('events', 'challonge_link')) {
            Schema::table('events', function (Blueprint $table): void {
                $table->dropColumn('challonge_link');
            });
        }
    }
};


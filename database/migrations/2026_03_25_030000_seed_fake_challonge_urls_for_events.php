<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (! Schema::hasColumn('events', 'challonge_link') || ! Schema::hasColumn('events', 'challonge_url')) {
            return;
        }

        DB::table('events')
            ->select(['id', 'title', 'challonge_link', 'challonge_url'])
            ->orderBy('id')
            ->chunkById(200, function ($events): void {
                foreach ($events as $event) {
                    if (! empty($event->challonge_link) && ! empty($event->challonge_url)) {
                        continue;
                    }

                    $slug = Str::slug((string) $event->title);
                    if ($slug === '') {
                        $slug = 'event-'.$event->id;
                    }

                    $fakeUrl = 'https://challonge.com/bbxlu_event_'.$event->id.'_'.$slug;

                    $updates = [];
                    if (empty($event->challonge_link)) {
                        $updates['challonge_link'] = $fakeUrl;
                    }
                    if (empty($event->challonge_url)) {
                        $updates['challonge_url'] = $fakeUrl;
                    }

                    if ($updates !== []) {
                        DB::table('events')
                            ->where('id', $event->id)
                            ->update($updates);
                    }
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasColumn('events', 'challonge_link')) {
            DB::table('events')
                ->where('challonge_link', 'like', 'https://challonge.com/bbxlu_event_%')
                ->update(['challonge_link' => null]);
        }

        if (Schema::hasColumn('events', 'challonge_url')) {
            DB::table('events')
                ->where('challonge_url', 'like', 'https://challonge.com/bbxlu_event_%')
                ->update(['challonge_url' => null]);
        }
    }
};


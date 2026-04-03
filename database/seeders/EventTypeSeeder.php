<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EventTypeSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'GT',
            'Casual',
            'Others',
        ] as $name) {
            DB::table('event_types')->updateOrInsert(
                ['name' => $name],
                ['name' => $name],
            );
        }
    }
}

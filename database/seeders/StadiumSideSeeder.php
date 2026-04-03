<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class StadiumSideSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        foreach ([
            'X',
            'B',
            'Other',
        ] as $code) {
            DB::table('stadium_sides')->updateOrInsert(
                ['code' => $code],
                [
                    'code' => $code,
                    'created_at' => $now,
                    'updated_at' => $now,
                ],
            );
        }
    }
}

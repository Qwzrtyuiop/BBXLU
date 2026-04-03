<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AwardSeeder extends Seeder
{
    public function run(): void
    {
        foreach ([
            'Swiss King',
            'Swiss Champ',
            'Bird King',
        ] as $name) {
            DB::table('awards')->updateOrInsert(
                ['name' => $name],
                ['name' => $name],
            );
        }
    }
}

<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            EventTypeSeeder::class,
            AwardSeeder::class,
            StadiumSideSeeder::class,
        ]);

        User::query()->updateOrCreate([
            'nickname' => 'test-admin',
        ], [
            'name' => 'Test User',
            'email' => 'test@example.com',
            'password' => Hash::make('password'),
            'role' => 'admin',
            'is_claimed' => true,
        ]);

        if (
            app()->environment('local')
            && filter_var((string) env('SEED_LOCAL_MOCK_DATA', false), FILTER_VALIDATE_BOOL)
        ) {
            $this->call(LocalMockDataSeeder::class);
        }
    }
}

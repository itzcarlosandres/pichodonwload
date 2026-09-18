<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        // 1. Create Super Admin User
        User::updateOrCreate(
            ['email' => 'admin@romhub.io'],
            [
                'name' => 'Super Administrator',
                'username' => 'admin',
                'password' => Hash::make('AdminPass123!'),
                'role' => 'ADMIN',
                'level' => 99,
                'xp' => 99999,
                'is_active' => true,
            ]
        );

        // 2. Create Regular Demo User
        User::updateOrCreate(
            ['email' => 'alex_retro@gmail.com'],
            [
                'name' => 'Alex Archive',
                'username' => 'AlexArchive_99',
                'password' => Hash::make('UserPass123!'),
                'role' => 'USER',
                'level' => 14,
                'xp' => 7450,
                'is_active' => true,
            ]
        );

        // 3. Call all Domain Seeders
        $this->call([
            ConsoleSeeder::class,
            CategorySeeder::class,
            BadgeSeeder::class,
            SettingSeeder::class,
            GameSeeder::class,
        ]);
    }
}
